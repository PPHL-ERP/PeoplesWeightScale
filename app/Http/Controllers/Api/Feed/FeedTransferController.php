<?php

namespace App\Http\Controllers\Api\Feed;

use App\Http\Controllers\Controller;
use App\Http\Requests\Feed\FeedTransferRequest;
use App\Http\Resources\Feed\FeedTransferResource;
use App\Models\FeedTransfer;
use App\Models\FeedTransferDetail;
use App\Services\FeedProductionLedgerService;
use App\Services\FeedStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\FeedReceive;
use App\Models\FeedStock;
use App\Services\LabourDetailsAddService;
use App\Traits\SectorFilter;
class FeedTransferController extends Controller
{
    protected $ledgerService;
    protected $feedStockService;
    protected $labourDetailsAddService;
    use SectorFilter;
    // Inject both FeedProductionLedgerService and FeedStockService
    public function __construct(FeedProductionLedgerService $ledgerService, FeedStockService $feedStockService,LabourDetailsAddService $labourDetailsAddService)
    {
        $this->ledgerService = $ledgerService;
        $this->feedStockService = $feedStockService;
        $this->labourDetailsAddService = $labourDetailsAddService;
    }

        public function indexold(Request $request)
        {
            $startDate = $request->startDate ?? now()->subMonths(2)->startOfMonth()->format('Y-m-d');
            $endDate = $request->endDate ?? now()->format('Y-m-d');

            $trId    = $request->trId;
            $transferHead       = $request->transferHead;
            $fromStore        = $request->fromStore;
            $toStore         = $request->toStore;
            $trType      = $request->trType;
            $status          = $request->status;
            $productId = $request->productId;
            $childCategoryId = $request->childCategoryId;
            $limit           = $request->input('limit', 100); // Default 100 items per page


            // $query = FeedTransfer::query();
            $query = $this->applySectorFilter(FeedTransfer::query(), 'fromStore');

            // Filter by trId
            if ($trId) {
                $query->where('trId', 'LIKE', '%' . $trId . '%');
              }
         // Filter by transferHead
               if ($transferHead) {
              $query->orWhere('transferHead', $transferHead);
            }

              // Filter by fromStore
             if ($fromStore) {
                $query->orWhere('fromStore', $fromStore);
              }

            // Filter by toStore
             if ($toStore) {
                $query->orWhere('toStore', $toStore);
              }

           // Filter by trType
            if ($trType) {
              $query->orWhere('trType', $trType);
            }
            // dd($startDate && $endDate);

            // //filter date
            if ($startDate && $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            }

            // // Filter by status
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

            // Fetch transfer with eager loading of related data
            //$feedtransfers = $query->latest()->get();
            $feedtransfers = $query->with(['details.product.childCategory'])->latest()->paginate($limit);


    // dd($feedtransfers);
            // Return paginated response
        return response()->json([
            'message' => 'Success!',
            'data' => FeedTransferResource::collection($feedtransfers),
            'meta' => [
                'current_page' => $feedtransfers->currentPage(),
                'last_page' => $feedtransfers->lastPage(),
                'per_page' => $feedtransfers->perPage(),
                'total' => $feedtransfers->total(),
            ]
        ], 200);
            }
            public function index(Request $request)
            {
                $startDate = $request->startDate ?? now()->subMonths(2)->startOfMonth()->format('Y-m-d');
                $endDate = $request->endDate ?? now()->format('Y-m-d');

                $trId = $request->trId;
                $transferHead = $request->transferHead;
                $fromStore = $request->fromStore;
                $toStore = $request->toStore;
                $trType = $request->trType;
                $status = $request->status;
                $productId = $request->productId;
                $childCategoryId = $request->childCategoryId;
                $limit = $request->input('limit', 100);

                $query = FeedTransfer::query();

                // ✅ Apply sector-wise filter if not admin
                $userId = auth()->id();
                $canPass = $this->adminFilter($userId);

                if (!$canPass) {
                    $sectorIds = \App\Models\UserManagesSectors::where('userId', $userId)->pluck('sectorId')->toArray();

                    if (!empty($sectorIds)) {
                        $query->where(function ($q) use ($sectorIds) {
                            $q->whereIn('fromStore', $sectorIds)
                              ->orWhereIn('toStore', $sectorIds);
                        });
                    } else {
                        return response()->json([
                            'message' => 'No permission to view feed transfers.',
                            'data' => [],
                            'meta' => []
                        ], 403);
                    }
                }

                // ✅ Apply other filters
                if ($trId) {
                    $query->where('trId', 'LIKE', '%' . $trId . '%');
                }

                if ($transferHead) {
                    $query->where('transferHead', $transferHead);
                }

                if ($fromStore) {
                    $query->where('fromStore', $fromStore);
                }

                if ($toStore) {
                    $query->where('toStore', $toStore);
                }

                if ($trType) {
                    $query->where('trType', $trType);
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

                // ✅ Load related models and paginate
                $feedtransfers = $query->with(['details.product.childCategory'])->latest()->paginate($limit);

                return response()->json([
                    'message' => 'Success!',
                    'data' => FeedTransferResource::collection($feedtransfers),
                    'meta' => [
                        'current_page' => $feedtransfers->currentPage(),
                        'last_page' => $feedtransfers->lastPage(),
                        'per_page' => $feedtransfers->perPage(),
                        'total' => $feedtransfers->total(),
                    ]
                ], 200);
            }


          public function store(FeedTransferRequest $request)
          {
              DB::beginTransaction();
              try {
                  // Create feed transfer record
                  $feedTransfer = FeedTransfer::create([
                      'trId' => $request->trId,
                      'transferHead' => $request->transferHead,
                      'trType' => $request->trType,
                      'fromStore' => $request->fromStore,
                      'toStore' => $request->toStore,
                      'transportType' => $request->transportType,
                      'driverName' => $request->driverName,
                      'mobile' => $request->mobile,
                      'vehicleNo' => $request->vehicleNo,
                      'date' => $request->date,
                      'loadBy' => $request->loadBy,
                      'labourGroupId' => $request->labourGroupId,
                      'labourBill' => $request->labourBill,
                      'isLabourBill' => $request->isLabourBill,
                      'note' => $request->note,
                      'crBy' => auth()->id(), // Assuming the user is logged in
                      'appBy' => null,
                      'status' => 'pending',
                  ]);

                  // Create feed transfer details
                  foreach ($request->details as $detail) {
                      FeedTransferDetail::create([
                          'transferId' => $feedTransfer->id,
                          'productId' => $detail['productId'],
                          'qty' => $detail['qty'],
                          'transferFor' => $detail['transferFor'],
                          'note' => $detail['note'],
                      ]);
                  }

                  DB::commit();

                  return response()->json([
                      'status' => 'success',
                      'message' => 'Feed transfer created successfully.',
                      'data' => new FeedTransferResource($feedTransfer)
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
              $feedTransfer = FeedTransfer::find($id);
              if (!$feedTransfer) {
                return response()->json(['message' => 'Feed Transfer not found'], 404);
              }
              return new FeedTransferResource($feedTransfer);
          }

          public function update(FeedTransferRequest $request, $id)
          {
              // Find the FeedTransfer record by ID
              $feedTransfer = FeedTransfer::find($id);

              if (!$feedTransfer) {
                  return response()->json([
                      'status' => 'error',
                      'message' => 'Feed transfer not found.'
                  ], 404);
              }

              // Check if the status is 'approved', in which case it cannot be updated
              if (in_array($feedTransfer->status, ['approved', 'received'])) {
                  return response()->json([
                      'status' => 'error',
                      'message' => 'Cannot update an approved or received Feed transfer.'
                  ], 403);  // HTTP status 403: Forbidden
              }

              DB::beginTransaction();
              try {
                  // Update feed transfer record
                  $feedTransfer->update([
                      'transferHead' => $request->transferHead,
                      'trType' => $request->trType,
                      'fromStore' => $request->fromStore,
                      'toStore' => $request->toStore,
                      'transportType' => $request->transportType,
                      'driverName' => $request->driverName,
                      'mobile' => $request->mobile,
                      'vehicleNo' => $request->vehicleNo,
                      'date' => $request->date,
                      'loadBy' => $request->loadBy,
                      'labourGroupId' => $request->labourGroupId,
                      'labourBill' => $request->labourBill,
                      'isLabourBill' => $request->isLabourBill,
                      'note' => $request->note,
                      'appBy' => $request->appBy,
                  ]);

                  // Delete and recreate feed transfer details
                  FeedTransferDetail::where('transferId', $feedTransfer->id)->delete();
                  foreach ($request->details as $detail) {
                      FeedTransferDetail::create([
                          'transferId' => $feedTransfer->id,
                          'productId' => $detail['productId'],
                          'qty' => $detail['qty'],
                          'transferFor' => $detail['transferFor'],
                          'note' => $detail['note'],
                      ]);
                  }

                  DB::commit();

                  return response()->json([
                      'status' => 'success',
                      'message' => 'Feed transfer updated successfully.',
                      'data' => $feedTransfer->load('details')
                  ], 200);

              } catch (\Exception $e) {
                  DB::rollBack();

                  return response()->json([
                      'status' => 'error',
                      'message' => 'An error occurred while updating the feed transfer.',
                      'error' => $e->getMessage(),
                  ], 500);
              }
          }

          public function updateStatus(Request $request, $id)
          {

              $feedTransfer = FeedTransfer::find($id);

              if (!$feedTransfer) {
                  return response()->json([
                      'status' => 'error',
                      'message' => 'feed transfer not found.'
                  ], 404);
              }


              if (in_array($feedTransfer->status, ['approved', 'received'])) {
                  return response()->json([
                      'status' => 'error',
                      'message' => 'Cannot update an feed transfer with approved or received status.'
                  ], 403);
              }


              $request->validate([
                  'status' => 'required|string|in:approved,pending,declined'
              ]);

              if ($request->status === 'approved') {
                  foreach ($feedTransfer->details as $detail) {
                      $qty = $detail->qty;
                      $productId = $detail->productId;


                      $this->ledgerService->createFeedTransferLedgerEntry(
                          $feedTransfer->fromStore,
                          $productId,
                          $feedTransfer->trId,
                          'productionTransfer',
                          $feedTransfer->date,
                          $qty,
                          'Feed transfer'
                      );

                      $stockRecord = DB::table('feed_stocks')
                          ->where('sectorId', $feedTransfer->fromStore)
                          ->where('productId', $productId)
                          ->orderBy('updated_at', 'desc')
                          ->first();


                      $isToday = $stockRecord ? \Carbon\Carbon::parse($stockRecord->updated_at)->isToday() : false;

                      if ($stockRecord && $isToday) {

                          DB::table('feed_stocks')
                              ->where('id', $stockRecord->id)
                              ->update([
                                  'lockQty' => $stockRecord->lockQty + $qty,
                                  'closing' => $stockRecord->closing - $qty,
                                  'updated_at' => now()
                              ]);
                      } else {

                          DB::table('feed_stocks')->insert([
                              'sectorId' => $feedTransfer->fromStore,
                              'productId' => $productId,
                              'lockQty' => $stockRecord->lockQty+$qty,
                              'closing' =>$stockRecord->closing-$qty,
                              'trDate' => $feedTransfer->date,
                              'created_at' => now(),
                              'updated_at' => now(),
                          ]);
                      }

                    //   $this->labourDetailsAddService->addLabourDetail(
                    //     labourId: $feedTransfer->loadBy ?? null,
                    //     depotId: $feedTransfer->fromStore,
                    //     //unitId: $feedTransfer->unitId ?? null,
                    //     transactionId: $feedTransfer->trId,
                    //     transactionType: 'productionTransfer',
                    //     workType: 'Feed Transfer',
                    //     tDate: $feedTransfer->date,
                    //     qty: $qty,
                    //    // bAmount: 0,  // Set appropriate amount if needed
                    //     status: 'approved'
                    // );

                     // ✅ Only add labour detail if loadBy exists
                    if (!empty($feedTransfer->loadBy)) {
                        $this->labourDetailsAddService->addLabourDetail(
                            labourId: $feedTransfer->loadBy,
                            depotId: $feedTransfer->fromStore,
                            transactionId: $feedTransfer->trId,
                            transactionType: 'productionTransfer',
                            workType: 'Feed Transfer',
                            tDate: $feedTransfer->date,
                            qty: $qty,
                            status: 'approved'
                        );
                    }
                  }
              }

              $feedTransfer->status = $request->status;
              $feedTransfer->save();

              return response()->json([
                  'status' => 'success',
                  'message' => 'Feed transfer status updated successfully.',
                  'data' => $feedTransfer
              ], 200);
          }



    public function destroy($id)
    {
        $feedTransfer = FeedTransfer::find($id);
        if (!$feedTransfer) {
            return response()->json(['message' => 'Feed Transfer not found'], 404);
        }
        $feedTransfer->delete();
        return response()->json([
            'message' => 'Feed Transfer deleted successfully',
        ],200);
    }

    public function getFeedTransferList()
    {
      $approveTrList = FeedTransfer::where('status', 'approved')
        ->select('id', 'trId',)
        ->get();
      return response()->json([
        'data' => $approveTrList
      ], 200);
    }


public function getFeedTransferSecListold()
{
    $approveTrList = FeedTransfer::where('status', 'approved')
        ->with(['fStore:id,name', 'tStore:id,name'])
        ->select('id', 'trId', 'fromStore', 'toStore','date')
        ->get();

    return response()->json([
        'data' => $approveTrList
    ], 200);
}
public function getFeedTransferSecList()
{
    $userId = auth()->id();
    $canPass = $this->adminFilter($userId);

    $query = FeedTransfer::where('status', 'approved');

    if (!$canPass) {
        $sectorIds = \App\Models\UserManagesSectors::where('userId', $userId)->pluck('sectorId')->toArray();

        if (empty($sectorIds)) {
            return response()->json(['message' => 'No sector access assigned.'], 403);
        }

        $query->where(function ($q) use ($sectorIds) {
            $q->whereIn('fromStore', $sectorIds)
              ->orWhereIn('toStore', $sectorIds);
        });
    }

    $approveTrList = $query
        ->with(['fStore:id,name', 'tStore:id,name'])
        ->select('id', 'trId', 'fromStore', 'toStore', 'date')
        ->get();

    return response()->json([
        'data' => $approveTrList
    ], 200);
}

//declineTransfer for receive

// with stocks

// public function declineFeedTransfer($id)
// {
//     $feedTransfer = FeedTransfer::find($id);

//     if (!$feedTransfer) {
//         return response()->json([
//             'status' => 'error',
//             'message' => 'Feed transfer not found.'
//         ], 404);
//     }

//     // Only allow decline if the status is 'approved'
//     if ($feedTransfer->status !== 'approved') {
//         return response()->json([
//             'status' => 'error',
//             'message' => 'Only approved transfers can be declined.'
//         ], 403);
//     }

//     // Update the status to 'declined'
//     $feedTransfer->status = 'declined';
//     $feedTransfer->save();

//     // Loop through each detail and update the stock
//     foreach ($feedTransfer->details as $detail) {
//         $qty = $detail->qty;
//         $productId = $detail->productId;

//         $stockRecord = DB::table('feed_stocks')
//             ->where('sectorId', $feedTransfer->fromStore)
//             ->where('productId', $productId)
//             ->orderBy('updated_at', 'desc')
//             ->first();

//         if ($stockRecord) {
//             // Ensure lockQty is a number and not null
//             $currentLockQty = (int) $stockRecord->lockQty;
//             $newLockQty = max(0, $currentLockQty - $qty); // Ensure it doesn't go negative

//             // Restore the closing (stockQty) and set lockQty to 0
//             DB::table('feed_stocks')
//                 ->where('id', $stockRecord->id)
//                 ->update([
//                     'closing' => $stockRecord->closing + $qty,
//                     'lockQty' => $newLockQty,
//                     'updated_at' => now()
//                 ]);
//         }
//     }

//     return response()->json([
//         'status' => 'success',
//         'message' => 'Feed transfer declined successfully.',
//         'data' => $feedTransfer
//     ], 200);
// }

// with fp_ledgers and stocks
public function declineFeedTransfer($id)
{
    $feedTransfer = FeedTransfer::find($id);

    if (!$feedTransfer) {
        return response()->json([
            'status' => 'error',
            'message' => 'Feed transfer not found.'
        ], 404);
    }

    // Only allow decline if the status is 'approved'
    if ($feedTransfer->status !== 'approved') {
        return response()->json([
            'status' => 'error',
            'message' => 'Only approved transfers can be declined.'
        ], 403);
    }

    // Update the status to 'declined'
    $feedTransfer->status = 'declined';
    $feedTransfer->save();


  // Decline the associated LabourDetails records
  $this->labourDetailsAddService->updateLabourDetailStatus($feedTransfer->trId, 'declined');  // Assuming this method exists in the service



    // Loop through each detail and update the stock in both feed_stocks and feed_production_ledgers
    foreach ($feedTransfer->details as $detail) {
        $qty = $detail->qty;
        $productId = $detail->productId;

        // Update feed_stocks table
        $stockRecord = DB::table('feed_stocks')
            ->where('sectorId', $feedTransfer->fromStore)
            ->where('productId', $productId)
            ->orderBy('updated_at', 'desc')
            ->first();

        if ($stockRecord) {
            // Ensure lockQty is a number and not null
            $currentLockQty = (int) $stockRecord->lockQty;
            $newLockQty = max(0, $currentLockQty - $qty); // Ensure it doesn't go negative

            // Restore the closing (stockQty) and set lockQty to 0
            DB::table('feed_stocks')
                ->where('id', $stockRecord->id)
                ->update([
                    'closing' => $stockRecord->closing + $qty,
                    'lockQty' => $newLockQty,
                    'updated_at' => now()
                ]);
        }

        // Update feed_production_ledgers table
        $ledgerRecord = DB::table('feed_production_ledgers')
            ->where('sectorId', $feedTransfer->fromStore)
            ->where('productId', $productId)
            ->orderBy('updated_at', 'desc')
            ->first();

        if ($ledgerRecord) {
            // Set lockQty to 0 in feed_production_ledgers table
            DB::table('feed_production_ledgers')
                ->where('id', $ledgerRecord->id)
                ->update([
                    'lockQty' => 0, // Reset lockQty to 0
                    'updated_at' => now()
                ]);
        }
    }

    return response()->json([
        'status' => 'success',
        'message' => 'Feed transfer declined successfully.',
        'data' => $feedTransfer
    ], 200);
}


}
