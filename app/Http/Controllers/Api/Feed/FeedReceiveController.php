<?php

namespace App\Http\Controllers\Api\Feed;

use App\Http\Controllers\Controller;
use App\Http\Requests\Feed\FeedReceiveRequest;
use App\Http\Resources\Feed\FeedReceiveResource;
use App\Models\FeedReceive;
use App\Models\FeedReceiveDetail;
use App\Services\FeedProductionLedgerService;
use App\Services\FeedStockService;
use Illuminate\Http\Request;
use App\Services\CacheService;
use Illuminate\Support\Facades\DB;
use App\Services\LabourDetailsAddService;
use App\Traits\SectorFilter;

class FeedReceiveController extends Controller
{
    use SectorFilter;

    protected $ledgerService;
    protected $feedStockService;
    protected $cacheService;

    protected $labourDetailsAddService;
    public function __construct(FeedProductionLedgerService $ledgerService, FeedStockService $feedStockService, CacheService $cacheService,LabourDetailsAddService $labourDetailsAddService)
    {
        $this->ledgerService = $ledgerService;
        $this->feedStockService = $feedStockService;
        $this->cacheService = $cacheService;
        $this->labourDetailsAddService = $labourDetailsAddService;

    }

    public function indexOld(Request $request)
    {
        $startDate = $request->startDate ?? now()->subMonth()->format('Y-m-d');
        $endDate = $request->endDate ?? now()->format('Y-m-d');
        $recId = $request->recId ?? null;
        $transferFrom = $request->transferFrom ?? null;
        $recStore = $request->recStore ?? null;
        $status = $request->status ?? null;
        $productId = $request->productId ?? null;
        $childCategoryId = $request->childCategoryId ?? null;
        $query = FeedReceive::query();

        // Filter by recId
        if ($recId) {
            $query->where('recId', 'LIKE', '%' . $recId . '%');
        }
        // Filter by transferFrom
        if ($transferFrom) {
            $query->orWhere('transferFrom', $transferFrom);
        }

        // Filter by recStore
        if ($recStore) {
            $query->orWhere('recStore', $recStore);
        }

        //filter date
        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        // Filter by status
        if ($status) {
            $query->where('status', $status);
        }

        // Filter by productId within feedTransferDetails
        if ($productId) {
            $query->whereHas('details', function ($q) use ($productId) {
                $q->where('productId', $productId);
            });
        }
        // Filter by childCategoryId within feedTransferDetails' products
        if ($childCategoryId) {
            $query->whereHas('details.product', function ($q) use ($childCategoryId) {
                $q->where('childCategoryId', $childCategoryId);
            });
        }

        // Fetch receives with eager loading of related data
        //$receives = $query->orderBy('created_at', 'desc')->paginate(100);
        $receives = $query->with(['details.product.childCategory'])->orderBy('created_at', 'desc')->paginate(100);


        // Check if any Production found
        if ($receives->isEmpty()) {
            return response()->json(['message' => 'No feed Receive found', 'data' => []], 200);
        }

        // Use the FeedReceiveResource to transform the data
        $transformedReceives = FeedReceiveResource::collection($receives);

        // Return receives transformed with the resource
        return response()->json([
            'message' => 'Success!',
            'data' => $transformedReceives
        ], 200);
    }

    public function index(Request $request)
    {
        $startDate = $request->startDate ?? now()->subMonth()->format('Y-m-d');
        $endDate = $request->endDate ?? now()->format('Y-m-d');

        $recId = $request->recId;
        $transferFrom = $request->transferFrom;
        $recStore = $request->recStore;
        $status = $request->status;
        $productId = $request->productId;
        $childCategoryId = $request->childCategoryId;
        $limit = $request->input('limit', 100);

        $query = FeedReceive::query();

        // ✅ Sector-based filter
        $userId = auth()->id();
        $canPass = $this->adminFilter($userId);

        if (!$canPass) {
            $sectorIds = \App\Models\UserManagesSectors::where('userId', $userId)->pluck('sectorId')->toArray();

            if (!empty($sectorIds)) {
                $query->where(function ($q) use ($sectorIds) {
                    $q->whereIn('transferFrom', $sectorIds)
                      ->orWhereIn('recStore', $sectorIds);
                });
            } else {
                return response()->json(['message' => 'No sector access assigned.'], 403);
            }
        }

        // Filters
        if ($recId) {
            $query->where('recId', 'LIKE', '%' . $recId . '%');
        }

        if ($transferFrom) {
            $query->where('transferFrom', $transferFrom);
        }

        if ($recStore) {
            $query->where('recStore', $recStore);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($productId) {
            $query->whereHas('details', function ($q) use ($productId) {
                $q->where('productId', $productId);
            });
        }

        if ($childCategoryId) {
            $query->whereHas('details.product', function ($q) use ($childCategoryId) {
                $q->where('childCategoryId', $childCategoryId);
            });
        }

        // Eager load
        $receives = $query->with(['details.product.childCategory'])
                          ->orderBy('created_at', 'desc')
                          ->paginate($limit);

        return response()->json([
            'message' => 'Success!',
            'data' => FeedReceiveResource::collection($receives),
            'meta' => [
                'current_page' => $receives->currentPage(),
                'last_page'    => $receives->lastPage(),
                'per_page'     => $receives->perPage(),
                'total'        => $receives->total(),
            ]
        ], 200);
    }


    public function store(FeedReceiveRequest $request)
    {
        DB::beginTransaction();
        try {
            // Create feed transfer record
            $feedReceive = FeedReceive::create([
                'recId' => $request->recId,
                'transferId' => $request->transferId,
                //'transferFrom' => $request->transferFrom['id'] ?? null, // Store only the ID
                'transferFrom' => $request->transferFrom, // Store only the ID
                'recHead' => $request->recHead,
                'recStore' => $request->recStore,
                'chalanNo' => $request->chalanNo,
                'date' => $request->date,
                'unLoadBy' => $request->unLoadBy,
                'labourGroupId' => $request->labourGroupId,
                'labourBill' => $request->labourBill,
                'isLabourBill' => $request->isLabourBill,
                'remarks' => $request->remarks,
                'crBy' => auth()->id(),
                'appBy' => null,
                'status' => 'pending',
            ]);

            // Create feed transfer details
            foreach ($request->details as $detail) {
                FeedReceiveDetail::create([
                    'receiveId' => $feedReceive->id,
                    'productId' => $detail['productId'],
                    'trQty' => $detail['trQty'],
                    'rQty' => $detail['rQty'],
                    'deviationQty' => $detail['deviationQty'],
                    'batchNo' => $detail['batchNo'],
                    'note' => $detail['note'],
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Feed Receive created successfully.',
                'data' => new FeedReceiveResource($feedReceive)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while creating the feed transfer.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function show($id)
    {
        $feedReceive = FeedReceive::with('details')->firstWhere('id', $id);
        if (!$feedReceive) {
            return response()->json(['message' => 'Feed receive not found'], 404);
        }
        return new FeedReceiveResource($feedReceive);
    }



    public function update(FeedReceiveRequest $request, $id)
    {
        $feedReceive = FeedReceive::findOrFail($id);

        if ($feedReceive->status === 'approved') {
            return response()->json([
                'error' => 'This record is approved and cannot be updated.'
            ], 403);
        }

        DB::beginTransaction();
        try {
            // Update feedReceive record
            $feedReceive->update($request->validated());

            // Update FeedReceiveDetail records
            FeedReceiveDetail::where('receiveId', $feedReceive->id)->delete();
            foreach ($request->details as $detail) {
                FeedReceiveDetail::create(array_merge($detail, ['receiveId' => $feedReceive->id]));
            }

            DB::commit();
            return new FeedReceiveResource($feedReceive->load('details'));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }



    // public function updateStatus(Request $request, $id)
    // {
    //     // Find the feed receive record
    //     $feedReceive = feedReceive::find($id);

    //     if (!$feedReceive) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Feed receive not found.'
    //         ], 404);
    //     }

    //     // Validate the status field
    //     $request->validate([
    //         'status' => 'required|string|in:approved,pending,declined'
    //     ]);

    //     // Prevent status updates if already approved or declined
    //     if (in_array($feedReceive->status, ['approved', 'declined'])) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Cannot update status of an approved or declined feed receive.'
    //         ], 403);
    //     }

    //     if ($request->status === 'approved') {
    //         // Loop through each detail in the egg receive details
    //         foreach ($feedReceive->details as $detail) {
    //             $productId = $detail->productId;
    //             $trQty = $detail->trQty;
    //             // Reduce lockQty for the specified product and sector
    //             DB::table('feed_stocks')
    //                 ->where('sectorId', $feedReceive->transferFrom)
    //                 ->where('productId', $productId)
    //                 ->decrement('lockQty', $trQty);

    //             // Ensure lockQty doesn't go negative
    //             DB::table('feed_stocks')
    //                 ->where('sectorId', $feedReceive->transferFrom)
    //                 ->where('productId', $productId)
    //                 ->update([
    //                     'lockQty' => DB::raw('GREATEST("lockQty", 0)') // Wrap lockQty in double quotes
    //                 ]);
    //             // Retrieve the last closing balance for this sector and product
    //             $lastClosingBalance = DB::table('feed_production_ledgers')
    //                 ->where('sectorId', $feedReceive->transferFrom)
    //                 ->where('productId', $productId)
    //                 ->where('status', 'approved')
    //                 ->orderBy('id', 'desc')
    //                 ->value('closingBalance') ?? 0;

    //             // Calculate new closing balance after transfer
    //             $newClosingBalance = $lastClosingBalance - $trQty;

    //             // Retrieve trId from the related feed transfer
    //             $trId = $feedReceive->feedTransfers->trId ?? null;

    //             if ($trId) {
    //                 // Update the `feed_production_ledgers` entry for the transfer
    //                 DB::table('feed_production_ledgers')
    //                     ->where('transactionId', $trId) // Using trId from feed_transfers
    //                     ->where('sectorId', $feedReceive->transferFrom)
    //                     ->where('productId', $productId)
    //                     ->update([
    //                         'lockQty' => 0,
    //                         'closingBalance' => $newClosingBalance,
    //                         'remarks' => 'Feed Transfer received',
    //                         'appBy' => auth()->id(),
    //                         'status' => 'approved',
    //                         'updated_at' => now(),
    //                     ]);
    //             }

    //             // Create a new ledger entry for the receiving sector
    //             $this->ledgerService->createFeedReceiveLedgerEntry(
    //                 $feedReceive->recStore,
    //                 $productId,
    //                 $feedReceive->recId,
    //                 'SalesPointReceive',
    //                 $feedReceive->date,
    //                 $trQty,
    //                 'Feed Receive'
    //             );



    //             // Update or add to stock in the receiving sector
    //             $this->feedStockService->FeedstoreOrUpdateStock(
    //                 $feedReceive->recStore,  // Receiving store ID
    //                 $productId,
    //                 $trQty,
    //                 $feedReceive->date
    //             );
    //         }
    //     }

    //     // Update the feed receive status
    //     $feedReceive->status = $request->status;
    //     $feedReceive->appBy = auth()->id();
    //     $feedReceive->save();
    //     $this->cacheService->clearAllCache();

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Feed receive status updated successfully.',
    //         'data' => $feedReceive
    //     ], 200);
    // }


   // ReceiveId approved to transferId status auto received
//    public function updateStatus(Request $request, $id)
//    {
//        // Find the feed receive record
//        $feedReceive = FeedReceive::find($id);

//        if (!$feedReceive) {
//            return response()->json([
//                'status' => 'error',
//                'message' => 'Feed receive not found.'
//            ], 404);
//        }

//        // Validate the status field
//        $request->validate([
//            'status' => 'required|string|in:approved,pending,declined'
//        ]);

//        // Prevent status updates if already approved or declined
//        if (in_array($feedReceive->status, ['approved', 'declined'])) {
//            return response()->json([
//                'status' => 'error',
//                'message' => 'Cannot update status of an approved or declined feed receive.'
//            ], 403);
//        }

//        if ($request->status === 'approved') {
//            foreach ($feedReceive->details as $detail) {
//                $productId = $detail->productId;
//                $trQty = $detail->trQty;

//                // Reduce lockQty for the specified product and sector
//                DB::table('feed_stocks')
//                    ->where('sectorId', $feedReceive->transferFrom)
//                    ->where('productId', $productId)
//                    ->decrement('lockQty', $trQty);

//                DB::table('feed_stocks')
//                    ->where('sectorId', $feedReceive->transferFrom)
//                    ->where('productId', $productId)
//                    ->update([
//                        'lockQty' => DB::raw('GREATEST("lockQty", 0)')
//                    ]);

//                $lastClosingBalance = DB::table('feed_production_ledgers')
//                    ->where('sectorId', $feedReceive->transferFrom)
//                    ->where('productId', $productId)
//                    ->where('status', 'approved')
//                    ->orderBy('id', 'desc')
//                    ->value('closingBalance') ?? 0;

//                $newClosingBalance = $lastClosingBalance - $trQty;

//                $trId = $feedReceive->feedTransfers->trId ?? null;

//                if ($trId) {
//                    DB::table('feed_production_ledgers')
//                        ->where('transactionId', $trId)
//                        ->where('sectorId', $feedReceive->transferFrom)
//                        ->where('productId', $productId)
//                        ->update([
//                            'lockQty' => 0,
//                            'closingBalance' => $newClosingBalance,
//                            'remarks' => 'Feed Transfer received',
//                            'appBy' => auth()->id(),
//                            'status' => 'approved',
//                            'updated_at' => now(),
//                        ]);

//                    // feed_transfers
//                    DB::table('feed_transfers')
//                        ->where('trId', $trId)
//                        ->update([
//                            'status' => 'received',
//                            'updated_at' => now(),
//                        ]);
//                }

//                $this->ledgerService->createFeedReceiveLedgerEntry(
//                    $feedReceive->recStore,
//                    $productId,
//                    $feedReceive->recId,
//                    'SalesPointReceive',
//                    $feedReceive->date,
//                    $trQty,
//                    'Feed Receive'
//                );

//                $this->feedStockService->FeedstoreOrUpdateStock(
//                    $feedReceive->recStore,
//                    $productId,
//                    $trQty,
//                    $feedReceive->date
//                );
//            }
//        }

//        // Update the feed receive status
//        $feedReceive->status = $request->status;
//        $feedReceive->appBy = auth()->id();
//        $feedReceive->save();
//        $this->cacheService->clearAllCache();

//        return response()->json([
//            'status' => 'success',
//            'message' => 'Feed receive status updated successfully.',
//            'data' => $feedReceive
//        ], 200);
//    }

// with addLabourDetail
public function updateStatus(Request $request, $id)
{
    // Find the feed receive record
    $feedReceive = FeedReceive::find($id);

    if (!$feedReceive) {
        return response()->json([
            'status' => 'error',
            'message' => 'Feed receive not found.'
        ], 404);
    }

    // Validate the status field
    $request->validate([
        'status' => 'required|string|in:approved,pending,declined'
    ]);

    // Prevent status updates if already approved or declined
    if (in_array($feedReceive->status, ['approved', 'declined'])) {
        return response()->json([
            'status' => 'error',
            'message' => 'Cannot update status of an approved or declined feed receive.'
        ], 403);
    }

    if ($request->status === 'approved') {
        foreach ($feedReceive->details as $detail) {
            $productId = $detail->productId;
            $trQty = $detail->trQty;
            $rQty = $detail->rQty;

            // Reduce lockQty for the specified product and sector
            DB::table('feed_stocks')
                ->where('sectorId', $feedReceive->transferFrom)
                ->where('productId', $productId)
                ->decrement('lockQty', $trQty);

            DB::table('feed_stocks')
                ->where('sectorId', $feedReceive->transferFrom)
                ->where('productId', $productId)
                ->update([
                    'lockQty' => DB::raw('GREATEST("lockQty", 0)')
                ]);

            $lastClosingBalance = DB::table('feed_production_ledgers')
                ->where('sectorId', $feedReceive->transferFrom)
                ->where('productId', $productId)
                ->where('status', 'approved')
                ->orderBy('id', 'desc')
                ->value('closingBalance') ?? 0;

            $newClosingBalance = $lastClosingBalance - $trQty;

            $trId = $feedReceive->feedTransfers->trId ?? null;

            if ($trId) {
                DB::table('feed_production_ledgers')
                    ->where('transactionId', $trId)
                    ->where('sectorId', $feedReceive->transferFrom)
                    ->where('productId', $productId)
                    ->update([
                        'lockQty' => 0,
                        'closingBalance' => $newClosingBalance,
                        'remarks' => 'Feed Transfer received',
                        'appBy' => auth()->id(),
                        'status' => 'approved',
                        'updated_at' => now(),
                    ]);

                // feed_transfers
                DB::table('feed_transfers')
                    ->where('trId', $trId)
                    ->update([
                        'status' => 'received',
                        'updated_at' => now(),
                    ]);
            }

            $this->ledgerService->createFeedReceiveLedgerEntry(
                $feedReceive->recStore,
                $productId,
                $feedReceive->recId,
                'SalesPointReceive',
                $feedReceive->date,
                $trQty,
                'Feed Receive'
            );

            $this->feedStockService->FeedstoreOrUpdateStock(
                $feedReceive->recStore,
                $productId,
                $trQty,
                $feedReceive->date
            );

            // $this->labourDetailsAddService->addLabourDetail(
            //     labourId: $feedReceive->unLoadBy ?? null,
            //     depotId: $feedReceive->recStore,
            //     transactionId: $feedReceive->recId,
            //     transactionType: 'productionReceive',
            //     workType: 'Feed Receive',
            //     tDate: $feedReceive->date,
            //     qty: $rQty,
            //     status: 'approved'
            // );

             // ✅ Only add labour detail if loadBy exists
             if (!empty($feedReceive->unLoadBy)) {
                $this->labourDetailsAddService->addLabourDetail(
                    labourId: $feedReceive->unLoadBy,
                    depotId: $feedReceive->recStore,
                    transactionId: $feedReceive->recId,
                    transactionType: 'productionReceive',
                    workType: 'Feed Receive',
                    tDate: $feedReceive->date,
                    qty: $rQty,
                    status: 'approved'
                );
            }

        }
    }

    // Update the feed receive status
    $feedReceive->status = $request->status;
    $feedReceive->appBy = auth()->id();
    $feedReceive->save();
    $this->cacheService->clearAllCache();

    return response()->json([
        'status' => 'success',
        'message' => 'Feed receive status updated successfully.',
        'data' => $feedReceive
    ], 200);
}

    public function destroy($id)
    {
        $feedReceive = FeedReceive::find($id);
        if (!$feedReceive) {
            return response()->json(['message' => 'Feed Receive not found'], 404);
        }
        $feedReceive->delete();
        return response()->json([
            'message' => 'Feed Transfer deleted successfully',
        ],200);
    }
}
