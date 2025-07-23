<?php

namespace App\Http\Controllers\Api\Chicks;

use App\Http\Controllers\Controller;
use App\Http\Resources\Chicks\ChicksStockResource;
use App\Models\ChicksStock;
use App\Models\Product;
use App\Models\ViewChicksStock;
use App\Services\ChicksStockService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Traits\SectorFilter;


class ChicksStockController extends Controller
{

    use SectorFilter;

    protected $chicksStockService;

    public function __construct(ChicksStockService $chicksStockService)
    {
        $this->chicksStockService = $chicksStockService;
    }

    public function index001(Request $request)
    {
        $startDate = $request->startDate ?? null;
        $endDate = $request->endDate ?? null;
        $sectorId = $request->sectorId ?? null;
        $productId = $request->productId ?? null;
        $childCategoryId = $request->childCategoryId ?? null;

        // Base query
        $query = ChicksStock::query();

        // ✅ Apply sector permission filter (custom logic)
        $userId = auth()->id();
        $canPass = $this->adminFilter($userId);

        if ($sectorId) {
            // If sector is explicitly requested, validate access
            if (!$canPass) {
                $allowedSectors = \App\Models\UserManagesSectors::where('userId', $userId)->pluck('sectorId')->toArray();
                if (!in_array($sectorId, $allowedSectors)) {
                    return response()->json(['message' => 'Unauthorized access to this sector'], 403);
                }
            }
            $query->where('chicks_stocks.sectorId', $sectorId);
        } elseif (!$canPass) {
            // No sector requested: apply sector filter for non-admins
            $sectorIds = \App\Models\UserManagesSectors::where('userId', $userId)->pluck('sectorId')->toArray();
            if (empty($sectorIds)) {
                return response()->json(['message' => 'No assigned sectors.'], 403);
            }
            $query->whereIn('chicks_stocks.sectorId', $sectorIds);
        }

        // Date range
        if ($startDate && $endDate) {
            $query->whereBetween('stockDate', [$startDate, $endDate]);
        }

        // Product filter
        if ($productId) {
            $query->where('chicks_stocks.productId', $productId);
        }

        // Child category filter
        if ($childCategoryId) {
            $productIds = Product::where('childCategoryId', $childCategoryId)->pluck('id');
            $query->whereIn('chicks_stocks.productId', $productIds);
        }

        // Join product and sector info
        $query->leftJoin('products', 'chicks_stocks.productId', '=', 'products.id')
              ->leftJoin('sectors', 'chicks_stocks.sectorId', '=', 'sectors.id')
              ->leftJoin('breeds', 'chicks_stocks.breedId', '=', 'breeds.id')
              ->select(
                  'chicks_stocks.*',
                  'products.productName as product_name',
                  'sectors.name as sector_name',
                  'breeds.breedName as breed_name'
              )
              ->orderBy('chicks_stocks.sectorId')
              ->orderByDesc('chicks_stocks.stockDate');

        $chicks_stocks = $query->get();

        // Transform
        $transformedChicksStocks = $chicks_stocks->map(function ($stock) {
            $openingBalance = $this->chicksStockService->getChicksOpeningBalance(
                $stock->sectorId,
                $stock->productId,
                $stock->breedId,
                $stock->stockDate,
            );
            return [
                'id' => $stock->id,
                'sectorId' => $stock->sectorId,
                'sectorName' => $stock->sector_name,
                'productId' => $stock->productId,
                'productName' => $stock->product_name,
                'breedId' => $stock->breedId,
                'breedName' => $stock->breed_name,
                'stockType' => $stock->stockType,
                'batchNo' => $stock->batchNo,
                'stockDate' => $stock->stockDate,
                'approxQty' => $stock->approxQty,
                'finalQty' => $stock->finalQty,
                'openingBalance' => $openingBalance,
                'closingBalance' => $stock->closing,

            ];
        });

        return response()->json([
            'message' => 'Success!',
            'data' => $transformedChicksStocks
        ], 200);
    }

    public function index(Request $request)
    {
        $startDate = $request->startDate ?? null;
        $endDate = $request->endDate ?? null;
        $sectorId = $request->sectorId ?? null;
        $productId = $request->productId ?? null;
        $childCategoryId = $request->childCategoryId ?? null;
        $breedId = $request->breedId ?? null;


        // Use the view instead of the main table
        $query = ViewChicksStock::query();

        // ✅ Apply sector permission filter (custom logic)
        $userId = auth()->id();
        $canPass = $this->adminFilter($userId);

        if ($sectorId) {
            // If sector is explicitly requested, validate access
            if (!$canPass) {
                $allowedSectors = \App\Models\UserManagesSectors::where('userId', $userId)->pluck('sectorId')->toArray();
                if (!in_array($sectorId, $allowedSectors)) {
                    return response()->json(['message' => 'Unauthorized access to this sector'], 403);
                }
            }
            $query->where('sectorId', $sectorId);
        } elseif (!$canPass) {
            // No sector requested: apply sector filter for non-admins
            $sectorIds = \App\Models\UserManagesSectors::where('userId', $userId)->pluck('sectorId')->toArray();
            if (empty($sectorIds)) {
                return response()->json(['message' => 'No assigned sectors.'], 403);
            }
            $query->whereIn('sectorId', $sectorIds);
        }

        // ✅ Date filter
        if ($startDate && $endDate) {
            $query->whereBetween('stockDate', [$startDate, $endDate]);
        }

        // ✅ Product filter
        if ($productId) {
            $query->where('productId', $productId);
        }

        // ✅ Child category filter via product lookup
        if ($childCategoryId) {
            $productIds = Product::where('childCategoryId', $childCategoryId)->pluck('id');
            $query->whereIn('productId', $productIds);
        }

         // ✅ Breed filter
         if ($breedId) {
            $query->where('breedId', $breedId);
        }

        // ✅ Sorting
        $query->orderBy('sectorId')->orderByDesc('stockDate');

        // ✅ Fetch from view
        $stocks = $query->get();

        // ✅ Transform result
        $transformedChicksStocks = $stocks->map(function ($stock) {
            $openingBalance = $this->chicksStockService->getChicksOpeningBalance(
                $stock->sectorId,
                $stock->productId,
                $stock->breedId,
                $stock->stockDate,
            );

            return [
                'id' => $stock->chicks_stock_id, // from view alias
                'sectorId' => $stock->sectorId,
                'sectorName' => $stock->sectorname,
                'productId' => $stock->productId,
                'productName' => $stock->productname,
                'breedId' => $stock->breedId,
                'breedName' => $stock->breedname,
                'stockType' => $stock->stockType,
                'batchNo' => $stock->batchNo,
                'stockDate' => $stock->stockDate,
                'approxQty' => $stock->approxQty,
                'finalQty' => $stock->finalQty,
                'openingBalance' => $openingBalance,
                'closingBalance' => $stock->closing,
            ];
        });

        return response()->json([
            'message' => 'Success!',
            'data' => $transformedChicksStocks
        ], 200);
    }



    public function getChicksStock(Request $request)
    {
        $data = $this->chicksStockService->getChicksData($request);

        if ($data->isEmpty()) {
            return response()->json([
                'message' => 'No Chicks Stock data found',
                'data' => []
            ], 200);
        }

        $transformedData = ChicksStockResource::collection($data);

        return response()->json([
            'message' => 'Success',
            'data' => $transformedData
        ], 200);
    }

    public function store(Request $request)
    {
        //
    }


    public function show(string $id)
    {
        //
    }


    public function update(Request $request, string $id)
    {
        //
    }


    public function destroy(string $id)
    {
        //
    }
}
