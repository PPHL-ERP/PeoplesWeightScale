<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EpLedgerResource;
use App\Models\EpLedger;
use App\Models\Product;
use Illuminate\Http\Request;

class EpLedgerController extends Controller
{

    // public function index(Request $request)
    // {
    //   $oneYearAgo = now()->subYear()->format('Y-m-d');
    //   $today = today()->format('Y-m-d');

    //   $sectorId = $request->sectorId ?? null;
    //   $productId = $request->productId ?? null;

    //   $startDate = $request->input('startDate', $oneYearAgo);
    //   $endDate = $request->input('endDate', $today);


    //   $query = EpLedger::query();
    //     // Filter by sectorId
    //   if ($sectorId) {
    //     $query->where('sectorId', $sectorId);
    //   }
    //     // Filter by productId
    //   if ($productId) {
    //     $query->orWhere('productId', $productId);
    //   }
    //     //Filter Date
    //   if ($startDate && $endDate) {
    //         $query->whereBetween('date', [$startDate, $endDate]);
    //   }

    //   $epLedger = $query->latest()->get();

    //   if ($epLedger->isEmpty()) {
    //     return response()->json([
    //       'message' => 'No Data found',
    //       'data' => []
    //     ], 200);
    //   }
    //   return EpLedgerResource::collection($epLedger);
    // }

    public function index1(Request $request)
{

     $oneYearAgo = now()->subYear()->format('Y-m-d');
     $today = today()->format('Y-m-d');

     $sectorId = $request->sectorId ?? null;
     $childCategoryId = $request->childCategoryId ?? null;
     $productId = $request->productId ?? null;
     $transactionType = $request->transactionType ?? null;
     $startDate = $request->input('startDate', $oneYearAgo);
     $endDate = $request->input('endDate', $today);

     $query = EpLedger::query();

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

    // $epLedger = $query->latest()->get();
    $epLedger = $query->with(['product', 'childCategory'])->latest()->get();


    if ($epLedger->isEmpty()) {
        return response()->json([
            'message' => 'No Data found',
            'data' => []
        ], 200);
    }

    return EpLedgerResource::collection($epLedger);

}

public function index(Request $request)
{

     $oneYearAgo = now()->subYear()->format('Y-m-d');
     $today = today()->format('Y-m-d');

    // Filters
    $sectorId        = $request->sectorId;
    $childCategoryId = $request->childCategoryId;
    $productId       = $request->productId;
    $transactionType = $request->transactionType;
    $startDate      = $request->input('startDate', $oneYearAgo);
    $endDate        = $request->input('endDate', $today);
    $limit          = $request->input('limit', 100); // Default 100

     $query = EpLedger::query();

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

    // $epLedger = $query->latest()->get();
    $epLedger = $query->with(['product', 'childCategory'])->latest()->paginate($limit);

    // Return paginated response
    return response()->json([
        'message' => 'Success!',
        'data' => EpLedgerResource::collection($epLedger),
        'meta' => [
            'current_page' => $epLedger->currentPage(),
            'last_page' => $epLedger->lastPage(),
            'per_page' => $epLedger->perPage(),
            'total' => $epLedger->total(),
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
