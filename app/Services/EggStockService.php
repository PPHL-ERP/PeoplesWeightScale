<?php

namespace App\Services;

use App\Models\EggStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Resources\EggStockResource;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
class EggStockService
{
    /**
     * Get egg stock data based on search parameters.
     */
    public function getData(Request $request)
    {
        // Generate a unique cache key based on request parameters
        $cacheKey = 'egg_stock_data_' . md5(serialize($request->all()));
        $cacheDuration = now()->addMinutes(1); // Cache for 10 minutes

        return Cache::remember($cacheKey, $cacheDuration, function () use ($request) {
            // Initialize the query builder
            $query = EggStock::select('egg_stocks.id', 'egg_stocks.sectorId', 'egg_stocks.productId', 'egg_stocks.trDate', 'egg_stocks.closing')
                             ->join('products', 'egg_stocks.productId', '=', 'products.id') // Join with products to access categoryId
                             ->leftJoin('categories', 'products.categoryId', '=', 'categories.id') // Left join with categories
                             ->with(['product:id,productName,categoryId', 'sector:id,name']); // Eager load related data

            // Apply filters based on request parameters
            if ($request->has('sectorId') && $request->sectorId != null) {
                $query->where('egg_stocks.sectorId', $request->sectorId);
            }

            if ($request->has('productId') && $request->productId != null) {
                $query->where('egg_stocks.productId', $request->productId);
            }

            if ($request->has('closing') && $request->closing != null) {
                $query->where('egg_stocks.closing', $request->closing);
            }

            if ($request->has('categoryId') && $request->categoryId != null) {
                $query->where('products.categoryId', $request->categoryId); // Filter by category ID
            }

            // Check if trDate filter is provided
            if ($request->has('trDate') && $request->trDate != null) {
                // Filter by specific transaction date
                $query->whereDate('egg_stocks.trDate', $request->trDate);
            } else {
                // If no trDate filter is provided, get the latest date available
                $latestDate = EggStock::max('trDate');
                $query->whereDate('egg_stocks.trDate', $latestDate);
            }

            // Order by date in descending order to get recent data first
            $query->orderBy('egg_stocks.trDate', 'desc');

            // Apply pagination for efficient handling of large datasets
            $perPage = $request->get('per_page', 100); // Default to 100 items per page
            return $query->paginate($perPage);
        });
    }

    public function EggstoreOrUpdateStockMINUS($sectorId, $productId, $qty, $trDate)
    {
        return DB::transaction(function () use ($sectorId, $productId, $qty, $trDate) {
            // Step 1: Get the most recent closing balance before the current transaction date
            $previousDayStock = EggStock::where('sectorId', $sectorId)
                ->where('productId', $productId)
                ->where('trDate', '<', $trDate)
                ->orderBy('trDate', 'desc')
                ->first();

            // If no previous entry exists, start with a balance of 0
            $previousClosingBalance = $previousDayStock ? $previousDayStock->closing : 0;

            // Step 2: Check if an entry exists for the current day
            $existingStock = EggStock::where('sectorId', $sectorId)
                ->where('productId', $productId)
                ->whereDate('trDate', $trDate)
                ->first();

            if ($existingStock) {
                // If stock exists for the same day, update the closing balance by adding qty
                $existingStock->update([
                    'closing' => $existingStock->closing - $qty
                ]);
            } else {
                // If no stock exists for the current day, create a new entry with the new closing balance
                $newClosingBalance = $previousClosingBalance - $qty;

                $existingStock = EggStock::create([
                    'sectorId' => $sectorId,
                    'productId' => $productId,
                    'closing' => $newClosingBalance,
                    'trDate' => $trDate,
                ]);
            }

            return $existingStock;
        });
    }
    public function EggstoreOrUpdateStock($sectorId, $productId, $qty, $trDate)
    {
        return DB::transaction(function () use ($sectorId, $productId, $qty, $trDate) {
            // Step 1: Get the most recent closing balance before the current transaction date
            $previousDayStock = EggStock::where('sectorId', $sectorId)
                ->where('productId', $productId)
                ->where('trDate', '<', $trDate)
                ->orderBy('trDate', 'desc')
                ->first();

            // If no previous entry exists, start with a balance of 0
            $previousClosingBalance = $previousDayStock ? $previousDayStock->closing : 0;

            // Step 2: Check if an entry exists for the current day
            $existingStock = EggStock::where('sectorId', $sectorId)
                ->where('productId', $productId)
                ->whereDate('trDate', $trDate)
                ->first();

            if ($existingStock) {
                // If stock exists for the same day, update the closing balance by adding qty
                $existingStock->update([
                    'closing' => $existingStock->closing + $qty
                ]);
            } else {
                // If no stock exists for the current day, create a new entry with the new closing balance
                $newClosingBalance = $previousClosingBalance + $qty;

                $existingStock = EggStock::create([
                    'sectorId' => $sectorId,
                    'productId' => $productId,
                    'closing' => $newClosingBalance,
                    'trDate' => $trDate,
                ]);
            }

            return $existingStock;
        });
    }

    public function EggstoreOrUpdateStockdeDuction($sectorId, $productId, $qty, $trDate)
    {
        return DB::transaction(function () use ($sectorId, $productId, $qty, $trDate) {
            // Step 1: Get the most recent closing balance before the current transaction date
            $previousDayStock = EggStock::where('sectorId', $sectorId)
                ->where('productId', $productId)
                ->where('trDate', '<', $trDate)
                ->orderBy('trDate', 'desc')
                ->first();

            // If no previous entry exists, start with a balance of 0
            $previousClosingBalance = $previousDayStock ? $previousDayStock->closing : 0;

            // Step 2: Check if an entry exists for the current day
            $existingStock = EggStock::where('sectorId', $sectorId)
                ->where('productId', $productId)
                ->whereDate('trDate', $trDate)
                ->first();

            if ($existingStock) {
                // If stock exists for the same day, update the closing balance by adding qty
                $existingStock->update([
                    'closing' => $existingStock->closing - $qty
                ]);
            } else {
                // If no stock exists for the current day, create a new entry with the new closing balance
                $newClosingBalance = $previousClosingBalance - $qty;

                $existingStock = EggStock::create([
                    'sectorId' => $sectorId,
                    'productId' => $productId,
                    'closing' => $newClosingBalance,
                    'trDate' => $trDate,
                ]);
            }

            return $existingStock;
        });
    }

    public function getLatestProductStocksByChildCategory(int $sectorId, int $childCategoryId)
{
    // Cache key for this query
    $cacheKey = "latest_stock_sector_{$sectorId}_childCategory_{$childCategoryId}";
    $cacheDuration = now()->addMinutes(1); // Cache for 10 minutes

    return Cache::remember($cacheKey, $cacheDuration, function () use ($sectorId, $childCategoryId) {
        // Step 1: Optimize using subquery to get the latest closing for each product
        return DB::table('egg_stocks as es')
            ->join('products as p', 'es.productId', '=', 'p.id')
            ->join('child_categories as cc', 'p.childCategoryId', '=', 'cc.id')
            ->leftJoin('ep_ledgers as el', function ($join) use ($sectorId) {
                // Join to get the latest lockQty from ep_ledgers for each product in the sector
                $join->on('el.productId', '=', 'p.id')
                     ->on('el.sectorId', '=', DB::raw($sectorId))
                     ->whereIn('el.id', function ($query) use ($sectorId) {
                         $query->select(DB::raw('MAX(id)'))
                               ->from('ep_ledgers')
                               ->where('sectorId', $sectorId)
                               ->groupBy('productId');
                     });
            })
            ->select(
                'cc.id as childCategoryId',
                'cc.childCategoryName as childCategoryName', // Adjust if renamed to 'name'
                'p.id as productId',
                'p.productName',
                'p.shortName',
                'es.closing as closingBalance',
                'es.trDate as latestDate',
                DB::raw('COALESCE(CAST(el."lockQty" AS NUMERIC), 0) as lockQuantity') // Use double quotes around lockQty
            )
            ->where('es.sectorId', $sectorId)
            ->where('p.childCategoryId', $childCategoryId)
            ->whereIn('es.id', function ($query) use ($sectorId) {
                // Subquery to get the latest record ID for each product in egg_stocks
                $query->select(DB::raw('MAX(id)'))
                    ->from('egg_stocks')
                    ->where('sectorId', $sectorId)
                    ->groupBy('productId');
            })
            ->orderBy('cc.id')
            ->orderBy('p.id')
            ->get();
    });
}

public function getOpeningBalance($sectorId, $productId, $trDate)
    {
        // Query to find the latest closing balance before the given trDate
        $result = DB::table('egg_stocks')
            ->where('sectorId', $sectorId)
            ->where('productId', $productId)
            ->where('trDate', '<', $trDate)
            ->orderBy('trDate', 'desc')
            ->orderBy('id', 'desc')
            ->value('closing'); // Fetch the 'closing' value

        // Return the result or default to 0 if no previous record is found
        return $result ?? 0;
    }



}
