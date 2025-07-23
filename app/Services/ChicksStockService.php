<?php

namespace App\Services;

use App\Models\ChicksStock;
use App\Models\FeedStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
class ChicksStockService
{
    /**
     * Get egg stock data based on search parameters.
     */
    public function getChicksData(Request $request)
    {
        $cacheKey = 'chicks_stock_data_' . md5(serialize($request->all()));
        $cacheDuration = now()->addMinutes(10);

        return Cache::remember($cacheKey, $cacheDuration, function () use ($request) {
            $query = ChicksStock::select(
                    'chicks_stocks.id',
                    'chicks_stocks.sectorId',
                    'chicks_stocks.productId',
                    'chicks_stocks.breedId',
                    'chicks_stocks.stockDate',
                    'chicks_stocks.closing',
                    'products.batchNo'
                )
                ->join('products', 'chicks_stocks.productId', '=', 'products.id')
                ->leftJoin('categories', 'products.categoryId', '=', 'categories.id')
                ->with([
                    'product:id,productName,batchNo',
                    'sector:id,name,salesPointName',
                    'breed:id,breedName'
                ]);

            if ($request->filled('sectorId')) {
                $query->where('chicks_stocks.sectorId', $request->sectorId);
            }

            if ($request->filled('productId')) {
                $query->where('chicks_stocks.productId', $request->productId);
            }

            if ($request->filled('closing')) {
                $query->where('chicks_stocks.closing', $request->closing);
            }

            if ($request->filled('categoryId')) {
                $query->where('products.categoryId', $request->categoryId);
            }

            if ($request->filled('stockDate')) {
                $query->whereDate('chicks_stocks.stockDate', $request->stockDate);
            } else {
                $latestDate = ChicksStock::max('stockDate');
                if ($latestDate) {
                    $query->whereDate('chicks_stocks.stockDate', $latestDate);
                }
            }

            $query->orderBy('chicks_stocks.stockDate', 'desc');

            $perPage = $request->get('per_page', 100);
            return $query->paginate($perPage);
        });
    }

    public function ChicksstoreOrUpdateStock(
        $sectorId,
        $productId,
        $breedId,
        $stockDate,
        $approxQty,
        $finalQty,
        $batchNo,
        $stockType,
        $status // Pass status: approx / finalized
    ) {
        return DB::transaction(function () use (
            $sectorId,
            $productId,
            $breedId,
            $stockDate,
            $approxQty,
            $finalQty,
            $batchNo,
            $stockType,
            $status
        ) {
            // Get previous closing balance for this batch
            $previousBatchStock = ChicksStock::where('sectorId', $sectorId)
                ->where('productId', $productId)
                ->where('breedId', $breedId)
                ->where('batchNo', $batchNo)
                ->where('stockDate', '<', $stockDate)
                ->orderBy('stockDate', 'desc')
                ->first();

            $previousClosingBalance = $previousBatchStock ? $previousBatchStock->closing : 0;

            // Check for existing record on the same date and batch
            $existingStock = ChicksStock::where('sectorId', $sectorId)
                ->where('productId', $productId)
                ->where('breedId', $breedId)
                ->where('batchNo', $batchNo)
                ->whereDate('stockDate', $stockDate)
                ->first();

            if ($existingStock) {
                $newClosing = $existingStock->closing;

                if ($status === 'approx') {
                    // Add approxQty to closing
                    $newClosing += $approxQty;

                    $existingStock->update([
                        'approxQty' => $existingStock->approxQty + $approxQty,
                        'closing'   => $newClosing,
                        'stockType'    => $status,
                    ]);
                } elseif ($status === 'finalized') {
                    // Adjust closing: replace approxQty with finalQty
                    $newClosing = $newClosing - $existingStock->approxQty + $finalQty;

                    $existingStock->update([
                        'finalQty'  => $finalQty,
                        'closing'   => $newClosing,
                        'stockType'    => $status,
                    ]);
                }
            } else {
                // New stock entry
                $closing = $previousClosingBalance;

                if ($status === 'approx') {
                    $closing += $approxQty;
                } elseif ($status === 'finalized') {
                    $closing += $finalQty;
                }

                $existingStock = ChicksStock::create([
                    'sectorId'   => $sectorId,
                    'productId'  => $productId,
                    'breedId'    => $breedId,
                    'stockDate'  => $stockDate,
                    'approxQty'  => $approxQty,
                    'finalQty'   => $finalQty,
                    'closing'    => $closing,
                    'batchNo'    => $batchNo,
                    'stockType'  => $stockType,
                    // 'stockType'     => $status,
                ]);
            }

            return $existingStock;
        });
    }


    public function ChicksstoreOrUpdateStockdeDuction(
        $sectorId,
        $productId,
        $breedId,
        $stockDate,
        $approxQty,
        $finalQty,
        $batchNo,
        $stockType,
        $status // 'approx' or 'finalized'
    ) {
        return DB::transaction(function () use (
            $sectorId,
            $productId,
            $breedId,
            $stockDate,
            $approxQty,
            $finalQty,
            $batchNo,
            $stockType,
            $status
        ) {
            // Step 1: Get previous closing balance for this batch
            $previousStock = ChicksStock::where('sectorId', $sectorId)
                ->where('productId', $productId)
                ->where('breedId', $breedId)
                ->where('batchNo', $batchNo)
                ->where('stockDate', '<', $stockDate)
                ->orderBy('stockDate', 'desc')
                ->first();

            $previousClosing = $previousStock ? $previousStock->closing : 0;

            // Step 2: Check if an entry exists for this day and batch
            $existingStock = ChicksStock::where('sectorId', $sectorId)
                ->where('productId', $productId)
                ->where('breedId', $breedId)
                ->where('batchNo', $batchNo)
                ->whereDate('stockDate', $stockDate)
                ->first();

            if ($existingStock) {
                if ($status === 'approx') {
                    // Subtract approxQty from closing
                    $newClosing = $existingStock->closing - $approxQty;

                    $existingStock->update([
                        'approxQty' => max(0, $existingStock->approxQty - $approxQty),
                        'closing'   => $newClosing,
                        'stockType' => $stockType,
                    ]);
                } elseif ($status === 'finalized') {
                    // Adjust closing: remove existing approxQty and subtract finalQty
                    $newClosing = $existingStock->closing - $existingStock->approxQty - $finalQty;

                    $existingStock->update([
                        'finalQty'  => $finalQty,
                        'closing'   => $newClosing,
                        'stockType' => $stockType,
                    ]);
                }
            } else {
                // Step 3: Create new stock row with deduction
                $closing = $previousClosing;

                if ($status === 'approx') {
                    $closing -= $approxQty;
                } elseif ($status === 'finalized') {
                    $closing -= $finalQty;
                }

                $existingStock = ChicksStock::create([
                    'sectorId'  => $sectorId,
                    'productId' => $productId,
                    'breedId'   => $breedId,
                    'stockDate' => $stockDate,
                    'approxQty' => $status === 'approx' ? -$approxQty : 0,
                    'finalQty'  => $status === 'finalized' ? -$finalQty : 0,
                    'closing'   => $closing,
                    'batchNo'   => $batchNo,
                    'stockType' => $stockType,
                ]);
            }

            return $existingStock;
        });
    }


public function getChicksOpeningBalance($sectorId, $productId, $breedId, $stockDate)
    {
        // Query to find the latest closing balance before the given stockDate
        $result = DB::table('chicks_stocks')
            ->where('sectorId', $sectorId)
            ->where('productId', $productId)
            ->where('breedId', $breedId)
            ->where('stockDate', '<', $stockDate)
            ->orderBy('stockDate', 'desc')
            ->orderBy('id', 'desc')
            ->value('closing'); // Fetch the 'closing' value

        // Return the result or default to 0 if no previous record is found
        return $result ?? 0;
    }

    public function getChicksLatestProductStocksByChildCategory(int $sectorId, int $childCategoryId)
    {
        $cacheKey = "latest_stock_sector_{$sectorId}_childCategory_{$childCategoryId}";
        $cacheDuration = now()->addMinutes(5);

        return Cache::remember($cacheKey, $cacheDuration, function () use ($sectorId, $childCategoryId) {
            return DB::table('chicks_stocks as es')
                ->join('products as p', 'es.productId', '=', 'p.id')
                ->join('child_categories as cc', 'p.childCategoryId', '=', 'cc.id')
                ->select(
                    'cc.id as childCategoryId',
                    'cc.childCategoryName as childCategoryName',
                    'p.id as productId',
                    'p.productName',
                    'p.sizeOrWeight',
                    'p.shortName',
                    'p.batchNo',
                    'es.closing as closingBalance',
                    'es.stockDate as latestDate'
                )
                ->where('es.sectorId', $sectorId)
                ->where('p.childCategoryId', $childCategoryId)
                ->whereIn('es.id', function ($query) use ($sectorId) {
                    $query->select(DB::raw('MAX(id)'))
                        ->from('chicks_stocks')
                        ->where('sectorId', $sectorId)
                        ->groupBy('productId');
                })
                ->orderBy('cc.id')
                ->orderBy('p.id')
                ->get();
        });
    }




}
