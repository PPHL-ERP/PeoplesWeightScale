<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EggReceive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\EggReceiveRequest;
use App\Http\Resources\EggReceiveResource;
use App\Models\EggReceiveDetail;
use App\Services\EpLedgerService;
use App\Services\EggStockService;
use App\Services\CacheService;

use Illuminate\Support\Facades\Cache;

class EggReceiveController extends Controller
{
    protected $ledgerService;
    protected $eggStockService;
    protected $cacheService;
    public function __construct(EpLedgerService $ledgerService, EggStockService $eggStockService,CacheService $cacheService)
    {
        $this->ledgerService = $ledgerService;
        $this->eggStockService = $eggStockService;
        $this->cacheService = $cacheService;

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
        $query = EggReceive::query();

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
          return response()->json(['message' => 'No Egg Receive found', 'data' => []], 200);
        }

        // Use the EggReceiveResource to transform the data
        $transformedReceives = EggReceiveResource::collection($receives);

        // Return receives transformed with the resource
        return response()->json([
          'message' => 'Success!',
          'data' => $transformedReceives],200);
        }


    public function index(Request $request)
    {
        $startDate = $request->startDate ?? now()->subMonth()->format('Y-m-d');
        $endDate = $request->endDate ?? now()->format('Y-m-d');

        $recId           = $request->recId;
        $transferFrom    = $request->transferFrom;
        $recStore        = $request->recStore;
        $status          = $request->status;
        $productId       = $request->productId;
        $childCategoryId = $request->childCategoryId;
        $limit           = $request->input('limit', 100); // Default 100 items per page

        $query = EggReceive::query();

        // Filter by recId
        if ($recId) {
            $query->where('recId', 'LIKE', '%' . $recId . '%');
          }
        // Filter by transferFrom
           if ($transferFrom) {
          $query->where('transferFrom', $transferFrom);
        }

        // Filter by recStore
        if ($recStore) {
          $query->where('recStore', $recStore);
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
        // $receives = $query->with(['details.product.childCategory'])->orderBy('created_at', 'desc')->paginate(100);
        $receives = $query->with(['details.product.childCategory'])->orderBy('created_at', 'desc')->paginate($limit);

        // Return paginated response
        return response()->json([
            'message' => 'Success!',
            'data' => EggReceiveResource::collection($receives),
            'meta' => [
                'current_page' => $receives->currentPage(),
                'last_page'    => $receives->lastPage(),
                'per_page'     => $receives->perPage(),
                'total'        => $receives->total(),
            ]
        ], 200);

        }

    public function store(EggReceiveRequest $request)
    {
        DB::beginTransaction();
        try {
            // Create egg transfer record
            $eggReceive = EggReceive::create([
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
                'remarks' => $request->remarks,
                'crBy' => auth()->id(),
                'appBy' => null,
                'status' => 'pending',
            ]);

            // Create egg transfer details
            foreach ($request->details as $detail) {
                EggReceiveDetail::create([
                    'receiveId' => $eggReceive->id,
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
                'message' => 'Egg Receive created successfully.',
                'data' => new EggReceiveResource($eggReceive)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while creating the egg transfer.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function show($id)
    {
        $eggReceive = EggReceive::with('details')->findOrFail($id);
        return new EggReceiveResource($eggReceive);
    }

    public function update(EggReceiveRequest $request, $id)
    {
        $eggReceive = EggReceive::findOrFail($id);

        if ($eggReceive->status === 'approved') {
            return response()->json([
                'error' => 'This record is approved and cannot be updated.'
            ], 403);
        }

        DB::beginTransaction();
        try {
            // Update EggReceive record
            $eggReceive->update($request->validated());

            // Update EggReceiveDetail records
            EggReceiveDetail::where('receiveId', $eggReceive->id)->delete();
            foreach ($request->details as $detail) {
                EggReceiveDetail::create(array_merge($detail, ['receiveId' => $eggReceive->id]));
            }

            DB::commit();
            return new EggReceiveResource($eggReceive->load('details'));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $eggReceive = EggReceive::find($id);
        if (!$eggReceive) {
            return response()->json(['message' => 'Egg Receive not found'], 404);
        }
        $eggReceive->delete();
        return response()->json([
            'message' => 'Egg Receive deleted successfully',
        ],200);
    }

    // public function updateStatus(Request $request, $id)
    // {
    //     // Find the egg receive record
    //     $eggReceive = EggReceive::find($id);

    //     if (!$eggReceive) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Egg receive not found.'
    //         ], 404);
    //     }

    //     // Validate the status field
    //     $request->validate([
    //         'status' => 'required|string|in:approved,pending,declined'
    //     ]);

    //     // Prevent status updates if already approved or declined
    //     if (in_array($eggReceive->status, ['approved', 'declined'])) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Cannot update status of an approved or declined egg receive.'
    //         ], 403);
    //     }

    //     if ($request->status === 'approved') {
    //         // Loop through each detail in the egg receive details
    //         foreach ($eggReceive->details as $detail) {
    //             $productId = $detail->productId;
    //             $trQty = $detail->trQty;
    //             // Reduce lockQty for the specified product and sector
    //             DB::table('egg_stocks')
    //             ->where('sectorId', $eggReceive->transferFrom)
    //             ->where('productId', $productId)
    //             ->decrement('lockQty', $trQty);

    //             // Ensure lockQty doesn't go negative
    //             DB::table('egg_stocks')
    //             ->where('sectorId', $eggReceive->transferFrom)
    //             ->where('productId', $productId)
    //             ->update([
    //                 'lockQty' => DB::raw('GREATEST("lockQty", 0)') // Wrap lockQty in double quotes
    //             ]);
    //             // Retrieve the last closing balance for this sector and product
    //             $lastClosingBalance = DB::table('ep_ledgers')
    //                 ->where('sectorId', $eggReceive->transferFrom)
    //                 ->where('productId', $productId)
    //                 ->where('status', 'approved')
    //                 ->orderBy('id', 'desc')
    //                 ->value('closingBalance') ?? 0;

    //             // Calculate new closing balance after transfer
    //             $newClosingBalance = $lastClosingBalance - $trQty;

    //             // Retrieve trId from the related egg transfer
    //             $trId = $eggReceive->eggTransfers->trId ?? null;

    //             if ($trId) {
    //                 // Update the `ep_ledgers` entry for the transfer
    //                 DB::table('ep_ledgers')
    //                     ->where('transactionId', $trId) // Using trId from egg_transfers
    //                     ->where('sectorId', $eggReceive->transferFrom)
    //                     ->where('productId', $productId)
    //                     ->update([
    //                         'lockQty' => 0,
    //                         'closingBalance' => $newClosingBalance,
    //                         'remarks' => 'Egg Transfer received',
    //                         'appBy' => auth()->id(),
    //                         'status' => 'approved',
    //                         'updated_at' => now(),
    //                     ]);
    //             }

    //             // Create a new ledger entry for the receiving sector
    //             $this->ledgerService->createReceiveLedgerEntry(
    //                 $eggReceive->recStore,
    //                 $productId,
    //                 $eggReceive->recId,
    //                 'SalesPointReceive',
    //                 $eggReceive->date,
    //                 $trQty,
    //                 'Egg Receive'
    //             );

    //             // Update or add to stock in the transferring sector
    //             // $this->eggStockService->EggstoreOrUpdateStock(
    //             //     $eggReceive->transferFrom,
    //             //     $productId,
    //             //     -$trQty, // Adjust the stock by reducing the adjusted quantity
    //             //     $eggReceive->date
    //             // );

    //             // Update or add to stock in the receiving sector
    //             $this->eggStockService->EggstoreOrUpdateStock(
    //                 $eggReceive->recStore,  // Receiving store ID
    //                 $productId,
    //                 $trQty,
    //                 $eggReceive->date
    //             );
    //         }
    //     }

    //     // Update the egg receive status
    //     $eggReceive->status = $request->status;
    //     $eggReceive->appBy = auth()->id();
    //     $eggReceive->save();
    //     $this->cacheService->clearAllCache();

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Egg receive status updated successfully.',
    //         'data' => $eggReceive
    //     ], 200);
    // }



    // ReceiveId approved to transferId status auto received
    public function updateStatus(Request $request, $id)
{
    // Find the egg receive record
    $eggReceive = EggReceive::find($id);

    if (!$eggReceive) {
        return response()->json([
            'status' => 'error',
            'message' => 'Egg receive not found.'
        ], 404);
    }

    // Validate the status field
    $request->validate([
        'status' => 'required|string|in:approved,pending,declined'
    ]);

    // Prevent status updates if already approved or declined
    if (in_array($eggReceive->status, ['approved', 'declined'])) {
        return response()->json([
            'status' => 'error',
            'message' => 'Cannot update status of an approved or declined egg receive.'
        ], 403);
    }
    Cache::flush();

    if ($request->status === 'approved') {
        foreach ($eggReceive->details as $detail) {
            $productId = $detail->productId;
            $trQty = $detail->trQty;

            // Reduce lockQty for the specified product and sector
            DB::table('egg_stocks')
                ->where('sectorId', $eggReceive->transferFrom)
                ->where('productId', $productId)
                ->decrement('lockQty', $trQty);

            DB::table('egg_stocks')
                ->where('sectorId', $eggReceive->transferFrom)
                ->where('productId', $productId)
                ->update([
                    'lockQty' => DB::raw('GREATEST("lockQty", 0)')
                ]);

            $lastClosingBalance = DB::table('ep_ledgers')
                ->where('sectorId', $eggReceive->transferFrom)
                ->where('productId', $productId)
                ->where('status', 'approved')
                ->orderBy('id', 'desc')
                ->value('closingBalance') ?? 0;

            $newClosingBalance = $lastClosingBalance - $trQty;

            $trId = $eggReceive->eggTransfers->trId ?? null;

            if ($trId) {
                DB::table('ep_ledgers')
                    ->where('transactionId', $trId)
                    ->where('sectorId', $eggReceive->transferFrom)
                    ->where('productId', $productId)
                    ->update([
                        'lockQty' => 0,
                        'closingBalance' => $newClosingBalance,
                        'remarks' => 'Egg Transfer received',
                        'appBy' => auth()->id(),
                        'status' => 'approved',
                        'updated_at' => now(),
                    ]);

                // egg_transfers
                DB::table('egg_transfers')
                    ->where('trId', $trId)
                    ->update([
                        'status' => 'received',
                        'updated_at' => now(),
                    ]);
            }

            $this->ledgerService->createReceiveLedgerEntry(
                $eggReceive->recStore,
                $productId,
                $eggReceive->recId,
                'SalesPointReceive',
                $eggReceive->date,
                $trQty,
                'Egg Receive'
            );

            $this->eggStockService->EggstoreOrUpdateStock(
                $eggReceive->recStore,
                $productId,
                $trQty,
                $eggReceive->date
            );
        }
    }

    // Update the egg receive status
    $eggReceive->status = $request->status;
    $eggReceive->appBy = auth()->id();
    $eggReceive->save();
    $this->cacheService->clearAllCache();

    return response()->json([
        'status' => 'success',
        'message' => 'Egg receive status updated successfully.',
        'data' => $eggReceive
    ], 200);
}


}
