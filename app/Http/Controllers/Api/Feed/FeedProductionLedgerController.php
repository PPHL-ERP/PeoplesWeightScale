<?php

namespace App\Http\Controllers\Api\Feed;

use App\Http\Controllers\Controller;
use App\Http\Resources\Feed\FeedProductionLedgerResource;
use App\Models\FeedProductionLedger;
use App\Models\Product;
use Illuminate\Http\Request;

class FeedProductionLedgerController extends Controller
{

    public function indexOld(Request $request)
    {

         $oneYearAgo = now()->subYear()->format('Y-m-d');
         $today = today()->format('Y-m-d');

         $sectorId = $request->sectorId ?? null;
         $productId = $request->productId ?? null;
         $childCategoryId = $request->childCategoryId ?? null;
         $transactionType = $request->transactionType ?? null;
         $startDate = $request->input('startDate', $oneYearAgo);
         $endDate = $request->input('endDate', $today);

         $query = FeedProductionLedger::query();

        // Filter by sectorId
        if ($sectorId) {
            $query->where('sectorId', $sectorId);
        }

         // Filter by childCategoryId
         if ($childCategoryId) {
            $productIds = Product::where('childCategoryId', $childCategoryId)->pluck('id');
            $query->whereIn('productId', $productIds);
        }

        // Filter by productId
        if ($productId) {
            $query->where('productId', $productId);
        }

        // Filter Date
        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        // Filter by single transactionType
        if ($transactionType) {
            $query->where('trType', $transactionType);
        }

       // $fpLedger = $query->latest()->get();
        $fpLedger = $query->with(['product', 'childCategory'])->latest()->get();


        if ($fpLedger->isEmpty()) {
            return response()->json([
                'message' => 'No Data found',
                'data' => []
            ], 200);
        }

        return FeedProductionLedgerResource::collection($fpLedger);

    }

    public function index(Request $request)
    {

         $oneYearAgo = now()->subYear()->format('Y-m-d');
         $today = today()->format('Y-m-d');

        // Filters
        $sectorId        = $request->sectorId;
        $productId       = $request->productId;
        $childCategoryId = $request->childCategoryId;
        $transactionType = $request->transactionType;
        $startDate      = $request->input('startDate', $oneYearAgo);
        $endDate        = $request->input('endDate', $today);
        $limit          = $request->input('limit', 100); // Default 100


         $query = FeedProductionLedger::query();

        // Filter by sectorId
        if ($sectorId) {
            $query->where('sectorId', $sectorId);
        }

         // Filter by childCategoryId
         if ($childCategoryId) {
            $productIds = Product::where('childCategoryId', $childCategoryId)->pluck('id');
            $query->whereIn('productId', $productIds);
        }

        // Filter by productId
        if ($productId) {
            $query->where('productId', $productId);
        }

        // Filter Date
        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        // Filter by single transactionType
        if ($transactionType) {
            $query->where('trType', $transactionType);
        }

       // $fpLedger = $query->latest()->get();
        $fpLedger = $query->with(['product', 'childCategory'])->latest()->paginate($limit);


     // Return paginated response
     return response()->json([
        'message' => 'Success!',
        'data' => FeedProductionLedgerResource::collection($fpLedger),
        'meta' => [
            'current_page' => $fpLedger->currentPage(),
            'last_page' => $fpLedger->lastPage(),
            'per_page' => $fpLedger->perPage(),
            'total' => $fpLedger->total(),
        ]
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