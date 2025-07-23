<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DailyPriceHistoryResource;
use App\Models\Category;
use App\Models\DailyPriceHistory;
use App\Models\Product;
use Illuminate\Http\Request;

class DailyPriceHistoryController extends Controller
{

    public function index(Request $request)
    {

        // $startDate = $request->startDate ?? now()->subMonth()->format('Y-m-d');
        // $endDate = $request->endDate ?? now()->format('Y-m-d');

        $oneYearAgo = now()->subYear()->format('Y-m-d');
        $today = today()->format('Y-m-d');
        $productId = $request->productId ?? null;
        $childCategoryId = $request->childCategoryId ?? null;

        $startDate = $request->input('startDate', $oneYearAgo);
        $endDate = $request->input('endDate', $today);

        $query = DailyPriceHistory::query();

         // Filter by childCategoryId
         if ($childCategoryId) {
            $productIds = Product::where('childCategoryId', $childCategoryId)->pluck('id');
            $query->whereIn('productId', $productIds);
        }

        // Filter by productId
           if ($productId) {
          $query->where('productId', $productId);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        // Fetch daily price histories with eager loading of related data
        //$daily_price_histories = $query->latest()->get();
        $daily_price_histories = $query->with(['product', 'childCategory'])->latest()->get();


        // Check if any daily price histories found
        if ($daily_price_histories->isEmpty()) {
          return response()->json(['message' => 'No Daily Price History found', 'data' => []], 200);
        }

        // Use the DailyPriceHistoryResource to transform the data
        $transformedDailyPriceHistories = DailyPriceHistoryResource::collection($daily_price_histories);

        // Return DailyPriceHistories transformed with the resource
        return response()->json([
          'message' => 'Success!',
          'data' => $transformedDailyPriceHistories
        ], 200);
    }

    //only egg category
    // public function index(Request $request)
    // {

    //     $oneYearAgo = now()->subYear()->format('Y-m-d');
    //     $today = today()->format('Y-m-d');

    //     $productId = $request->productId ?? null;
    //     $childCategoryId = $request->childCategoryId ?? null;

    //     $startDate = $request->input('startDate', $oneYearAgo);
    //     $endDate = $request->input('endDate', $today);

    //     // Get the categoryId of Egg
    // $categoryId = Category::where('name', 'Egg')->value('id');

    // if (!$categoryId) {
    //     return response()->json(['message' => 'Egg category not found'], 400);
    // }

    //     $query = DailyPriceHistory::query();

    //      // Filter by childCategoryId
    //     if ($childCategoryId) {
    //         $productIds = Product::where('childCategoryId', $childCategoryId)->pluck('id');
    //         $query->whereIn('productId', $productIds);
    //     }

    //     // Filter by productId
    //     if ($productId) {
    //         $query->where('productId', $productId);
    //     }

    //     // Filter by Egg category
    //     $query->whereHas('product', function ($productQuery) use ($categoryId) {
    //         $productQuery->where('categoryId', $categoryId);
    //     });

    //     if ($startDate && $endDate) {
    //         $query->whereBetween('date', [$startDate, $endDate]);
    //     }

    //     // Fetch daily price histories with eager loading of related data
    //     //$daily_price_histories = $query->latest()->get();
    //     $daily_price_histories = $query->with(['product', 'childCategory'])->latest()->get();


    //     // Check if any daily price histories found
    //     if ($daily_price_histories->isEmpty()) {
    //       return response()->json(['message' => 'No Daily Price History found', 'data' => []], 200);
    //     }

    //     // Use the DailyPriceHistoryResource to transform the data
    //     $transformedDailyPriceHistories = DailyPriceHistoryResource::collection($daily_price_histories);

    //     // Return DailyPriceHistories transformed with the resource
    //     return response()->json([
    //       'message' => 'Success!',
    //       'data' => $transformedDailyPriceHistories
    //     ], 200);
    // }

}