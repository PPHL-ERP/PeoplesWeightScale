<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\EggStockResource;
use App\Models\EggStock;
use App\Models\Product;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\EggStockService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
class EggStockController extends Controller
{

    protected $eggStockService;

    public function __construct(EggStockService $eggStockService)
    {
        $this->eggStockService = $eggStockService;
    }



public function indexOLD(Request $request)
{
    $startDate = $request->startDate ?? null;
    $endDate = $request->endDate ?? null;
    $sectorId = $request->sectorId ?? null;
    $productId = $request->productId ?? null;
    $childCategoryId = $request->childCategoryId ?? null;

    // Start the base query
    $query = EggStock::query();

    // Check if any search parameters are provided
    $hasSearchParameters = $startDate || $sectorId || $productId || $childCategoryId;

    if ($hasSearchParameters) {
        // If search parameters exist, apply filters
        if ($sectorId) {
            $query->where('egg_stocks.sectorId', $sectorId);
        }

        if ($productId) {
            $query->where('egg_stocks.productId', $productId);
        }

        if ($childCategoryId) {
            $productIds = Product::where('childCategoryId', $childCategoryId)->pluck('id');
            $query->whereIn('egg_stocks.productId', $productIds);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('trDate', [$startDate, $endDate]);
        }

        // Join to include productName and sectorName
        $query->leftJoin('products', 'egg_stocks.productId', '=', 'products.id')
              ->leftJoin('sectors', 'egg_stocks.sectorId', '=', 'sectors.id')
              ->select(
                  'egg_stocks.*',
                  'products.productName as product_name',
                  'sectors.name as sector_name'
              );

        // Load all matching records based on search
        $egg_stocks = $query->get();
    } else {
        // If no search parameters, fetch the latest records for each sectorId and productId
        $egg_stocks = EggStock::select(
                'egg_stocks.*',
                'products.productName as product_name',
                'sectors.name as sector_name'
            )
            ->leftJoin('products', 'egg_stocks.productId', '=', 'products.id')
            ->leftJoin('sectors', 'egg_stocks.sectorId', '=', 'sectors.id')
            ->join(DB::raw('(
                    SELECT "sectorId", "productId", MAX("trDate") AS "latestDate", MAX("id") AS "latestId"
                    FROM egg_stocks
                    GROUP BY "sectorId", "productId"
                ) AS grouped'),
                function ($join) {
                    $join->on('egg_stocks.sectorId', '=', 'grouped.sectorId')
                         ->on('egg_stocks.productId', '=', 'grouped.productId')
                         ->on('egg_stocks.trDate', '=', 'grouped.latestDate')
                         ->on('egg_stocks.id', '=', 'grouped.latestId');
                })
            ->get();
    }

    // Transform data and calculate opening balance using the service
    $transformedEggStocks = $egg_stocks->map(function ($stock) {
        $openingBalance = $this->eggStockService->getOpeningBalance(
            $stock->sectorId,
            $stock->productId,
            $stock->trDate
        );

        return [
            'id' => $stock->id,
            'sectorId' => $stock->sectorId,
            'sectorName' => $stock->sector_name,  // Sector Name
            'productId' => $stock->productId,
            'productName' => $stock->product_name, // Product Name
            'trDate' => $stock->trDate,
            'openingBalance' => $openingBalance,
            'closingBalance' => $stock->closing,
            'lockQty' => $stock->lockQty,
        ];
    });

    // Return response
    return response()->json([
        'message' => 'Success!',
        'data' => $transformedEggStocks
    ], 200);
}

public function index(Request $request)
{
    Cache::flush();

    $startDate = $request->startDate ?? null;
    $endDate = $request->endDate ?? null;
    $sectorId = $request->sectorId ?? null;
    $productId = $request->productId ?? null;
    $childCategoryId = $request->childCategoryId ?? null;

    // Start the base query
    $query = EggStock::query();

    // Apply filters for sector and date range
    if ($sectorId) {
        $query->where('egg_stocks.sectorId', $sectorId);
    }

    if ($startDate && $endDate) {
        $query->whereBetween('trDate', [$startDate, $endDate]);
    }

    if ($productId) {
        $query->where('egg_stocks.productId', $productId);
    }

    if ($childCategoryId) {
        $productIds = Product::where('childCategoryId', $childCategoryId)->pluck('id');
        $query->whereIn('egg_stocks.productId', $productIds);
    }

    // Join to include productName and sectorName
    $query->leftJoin('products', 'egg_stocks.productId', '=', 'products.id')
          ->leftJoin('sectors', 'egg_stocks.sectorId', '=', 'sectors.id')
          ->select(
              'egg_stocks.*',
              'products.productName as product_name',
              'sectors.name as sector_name'
          );

    // Group data by sector and date
    $query->orderBy('egg_stocks.sectorId')
          ->orderByDesc('egg_stocks.trDate');

    // Load matching records based on filters
    $egg_stocks = $query->get();

    // Transform data and calculate opening balance using the service
    $transformedEggStocks = $egg_stocks->map(function ($stock) {
        $openingBalance = $this->eggStockService->getOpeningBalance(
            $stock->sectorId,
            $stock->productId,
            $stock->trDate
        );

        return [
            'id' => $stock->id,
            'sectorId' => $stock->sectorId,
            'sectorName' => $stock->sector_name,  // Sector Name
            'productId' => $stock->productId,
            'productName' => $stock->product_name, // Product Name
            'trDate' => $stock->trDate,
            'openingBalance' => $openingBalance,
            'closingBalance' => $stock->closing,
            'lockQty' => $stock->lockQty,
        ];
    });

    // Return response
    return response()->json([
        'message' => 'Success!',
        'data' => $transformedEggStocks
    ], 200);
}

    public function getEggStock(Request $request)
    {
        $data = $this->eggStockService->getData($request);

        if ($data->isEmpty()) {
            return response()->json([
                'message' => 'No Egg Stock data found',
                'data' => []
            ], 200);
        }

        $transformedData = EggStockResource::collection($data);

        return response()->json([
            'message' => 'Success',
            'data' => $transformedData
        ], 200);
    }

    public function getProductStocksByChildCategory(Request $request)
    {
            // Validate the request parameters
            $request->validate([
                'sectorId' => 'required|integer',
                'childCategoryId' => 'required|integer',
            ]);

            $sectorId = $request->sectorId;
            $childCategoryId = $request->childCategoryId;

            // Call the service to get the data
            $stockData = $this->eggStockService->getLatestProductStocksByChildCategory($sectorId, $childCategoryId);

            return response()->json([
                'status' => 'success',
                'data' => $stockData
            ], 200);
    }



    public function store(Request $request)
    {

    }


    public function show(string $id)
    {

    }


    public function update(Request $request, string $id)
    {

    }


    public function destroy(string $id)
    {

    }
}
