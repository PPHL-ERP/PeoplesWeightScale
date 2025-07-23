<?php

namespace App\Http\Controllers\Api\Feed;

use App\Http\Controllers\Controller;
use App\Http\Resources\Feed\FeedStockResource;
use App\Models\FeedStock;
use App\Models\Product;
use App\Services\FeedStockService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Traits\SectorFilter;

class FeedStockController extends Controller
{
    use SectorFilter;

    protected $feedStockService;

    public function __construct(FeedStockService $feedStockService)
    {
        $this->feedStockService = $feedStockService;
    }
    public function indexold(Request $request)
    {
        $startDate = $request->startDate ?? null;
        $endDate = $request->endDate ?? null;
        $sectorId = $request->sectorId ?? null;
        $productId = $request->productId ?? null;
        $childCategoryId = $request->childCategoryId ?? null;

        // Start the base query
        $query = FeedStock::query();

        // Apply filters for sector and date range
        if ($sectorId) {
            $query->where('feed_stocks.sectorId', $sectorId);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('trDate', [$startDate, $endDate]);
        }

        if ($productId) {
            $query->where('feed_stocks.productId', $productId);
        }

        if ($childCategoryId) {
            $productIds = Product::where('childCategoryId', $childCategoryId)->pluck('id');
            $query->whereIn('feed_stocks.productId', $productIds);
        }

        // Join to include productName and sectorName
        $query->leftJoin('products', 'feed_stocks.productId', '=', 'products.id')
              ->leftJoin('sectors', 'feed_stocks.sectorId', '=', 'sectors.id')
              ->select(
                  'feed_stocks.*',
                  'products.productName as product_name',
                  'products.sizeOrWeight  as sizeOrWeight',
                  'sectors.name as sector_name'
              );

        // Group data by sector and date
        $query->orderBy('feed_stocks.sectorId')
              ->orderByDesc('feed_stocks.trDate');

        // Load matching records based on filters
        $feed_stocks = $query->get();

        // Transform data and calculate opening balance using the service
        $transformedEggStocks = $feed_stocks->map(function ($stock) {
            $openingBalance = $this->feedStockService->getFeedOpeningBalance(
                $stock->sectorId,
                $stock->productId,
                $stock->trDate
            );
            $bagCount = ($stock->sizeOrWeight > 0) ? ($stock->closing / $stock->sizeOrWeight) : 0;

            return [
                'id' => $stock->id,
                'sectorId' => $stock->sectorId,
                'sectorName' => $stock->sector_name,  // Sector Name
                'productId' => $stock->productId,
                'productName' => $stock->product_name, // Product Name
                'sizeOrWeight' => $stock->sizeOrWeight , // Product Name
                'trDate' => $stock->trDate,
                'openingBalance' => $openingBalance,
                'closingBalance' => $stock->closing,
                'bag' => round($bagCount, 2),
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
        $startDate = $request->startDate ?? null;
        $endDate = $request->endDate ?? null;
        $sectorId = $request->sectorId ?? null;
        $productId = $request->productId ?? null;
        $childCategoryId = $request->childCategoryId ?? null;

        // Base query
        $query = FeedStock::query();

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
            $query->where('feed_stocks.sectorId', $sectorId);
        } elseif (!$canPass) {
            // No sector requested: apply sector filter for non-admins
            $sectorIds = \App\Models\UserManagesSectors::where('userId', $userId)->pluck('sectorId')->toArray();
            if (empty($sectorIds)) {
                return response()->json(['message' => 'No assigned sectors.'], 403);
            }
            $query->whereIn('feed_stocks.sectorId', $sectorIds);
        }

        // Date range
        if ($startDate && $endDate) {
            $query->whereBetween('trDate', [$startDate, $endDate]);
        }

        // Product filter
        if ($productId) {
            $query->where('feed_stocks.productId', $productId);
        }

        // Child category filter
        if ($childCategoryId) {
            $productIds = Product::where('childCategoryId', $childCategoryId)->pluck('id');
            $query->whereIn('feed_stocks.productId', $productIds);
        }

        // Join product and sector info
        $query->leftJoin('products', 'feed_stocks.productId', '=', 'products.id')
              ->leftJoin('sectors', 'feed_stocks.sectorId', '=', 'sectors.id')
              ->select(
                  'feed_stocks.*',
                  'products.productName as product_name',
                  'products.sizeOrWeight as sizeOrWeight',
                  'sectors.name as sector_name'
              )
              ->orderBy('feed_stocks.sectorId')
              ->orderByDesc('feed_stocks.trDate');

        $feed_stocks = $query->get();

        // Transform
        $transformedEggStocks = $feed_stocks->map(function ($stock) {
            $openingBalance = $this->feedStockService->getFeedOpeningBalance(
                $stock->sectorId,
                $stock->productId,
                $stock->trDate
            );
            $bagCount = ($stock->sizeOrWeight > 0) ? ($stock->closing / $stock->sizeOrWeight) : 0;

            return [
                'id' => $stock->id,
                'sectorId' => $stock->sectorId,
                'sectorName' => $stock->sector_name,
                'productId' => $stock->productId,
                'productName' => $stock->product_name,
                'sizeOrWeight' => $stock->sizeOrWeight,
                'trDate' => $stock->trDate,
                'openingBalance' => $openingBalance,
                'closingBalance' => $stock->closing,
                'bag' => round($bagCount, 2),
                'lockQty' => $stock->lockQty,
            ];
        });

        return response()->json([
            'message' => 'Success!',
            'data' => $transformedEggStocks
        ], 200);
    }


    public function getFeedStock(Request $request)
    {
        $data = $this->feedStockService->getFeedData($request);

        if ($data->isEmpty()) {
            return response()->json([
                'message' => 'No Feed Stock data found',
                'data' => []
            ], 200);
        }

        $transformedData = FeedStockResource::collection($data);

        return response()->json([
            'message' => 'Success',
            'data' => $transformedData
        ], 200);
    }

    public function getFeedProductStocksByChildCategory(Request $request)
    {
            // Validate the request parameters
            $request->validate([
                'sectorId' => 'required|integer',
                'childCategoryId' => 'required|integer',
            ]);

            $sectorId = $request->sectorId;
            $childCategoryId = $request->childCategoryId;

            // Call the service to get the data
            $stockData = $this->feedStockService->getFeedLatestProductStocksByChildCategory($sectorId, $childCategoryId);

            return response()->json([
                'status' => 'success',
                'data' => $stockData
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
