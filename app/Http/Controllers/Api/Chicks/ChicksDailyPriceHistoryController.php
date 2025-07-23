<?php

namespace App\Http\Controllers\Api\Chicks;

use App\Http\Controllers\Controller;
use App\Http\Resources\Chicks\ChicksDailyPriceHistoryResource;
use App\Models\ChicksDailyPriceHistory;
use App\Models\Product;
use Illuminate\Http\Request;

class ChicksDailyPriceHistoryController extends Controller
{

    public function index(Request $request)
    {
        $oneYearAgo = now()->subYear()->format('Y-m-d');
        $today = today()->format('Y-m-d');
        $pId = $request->pId ?? null;
        $childCategoryId = $request->childCategoryId ?? null;

        $startDate = $request->input('startDate', $oneYearAgo);
        $endDate = $request->input('endDate', $today);

        $query = ChicksDailyPriceHistory::query();

         // Filter by childCategoryId
         if ($childCategoryId) {
            $productIds = Product::where('childCategoryId', $childCategoryId)->pluck('id');
            $query->whereIn('pId', $productIds);
        }

        // Filter by pId
           if ($pId) {
          $query->where('pId', $pId);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        // Fetch chicks daily price histories with eager loading of related data
        $chicks_dp_histories = $query->with(['product', 'childCategory'])->latest()->get();


        // Check if any chicks daily price histories found
        if ($chicks_dp_histories->isEmpty()) {
          return response()->json(['message' => 'No Chicks Daily Price History found', 'data' => []], 200);
        }

        // Use the DailyPriceHistoryResource to transform the data
        $transformedDailyPriceHistories = ChicksDailyPriceHistoryResource::collection($chicks_dp_histories);

        // Return DailyPriceHistories transformed with the resource
        return response()->json([
          'message' => 'Success!',
          'data' => $transformedDailyPriceHistories
        ], 200);
    }

}