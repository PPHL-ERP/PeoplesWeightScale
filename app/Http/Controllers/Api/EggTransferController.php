<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Requests\EggTransferRequest;
use App\Models\EggTransfer;
use App\Models\EggTransferDetail;
use App\Http\Resources\EggTransferResource;
use App\Models\EggReceive;
use App\Models\EggStock;
use App\Services\EpLedgerService;
use App\Services\EggStockService;

class EggTransferController extends Controller
{
    protected $ledgerService;
    protected $eggStockService;

    // Inject both EpLedgerService and EggStockService
    public function __construct(EpLedgerService $ledgerService, EggStockService $eggStockService)
    {
        $this->ledgerService = $ledgerService;
        $this->eggStockService = $eggStockService;
    }
    public function index1(Request $request)
    {
        $startDate = $request->startDate ?? now()->subMonths(2)->startOfMonth()->format('Y-m-d');
        $endDate = $request->endDate ?? now()->format('Y-m-d');
        $trId = $request->trId ?? null;
        $transferHead = $request->transferHead ?? null;
        $fromStore = $request->fromStore ?? null;
        $toStore = $request->toStore ?? null;
        $trType = $request->trType ?? null;
        $status = $request->status ?? null;
        $productId = $request->productId ?? null;
        $childCategoryId = $request->childCategoryId ?? null;
        $query = EggTransfer::query();

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
        // Fetch transfer with eager loading of related data
       // $transfers = $query->latest()->get();
        $transfers = $query->with(['details.product.childCategory'])->latest()->get();

        // Check if any Production found
        if ($transfers->isEmpty()) {
          return response()->json(['message' => 'No Egg Transfer found', 'data' => []], 200);
        }

        // Use the EggTransferResource to transform the data
        $transformedTransfers = EggTransferResource::collection($transfers);

        // Return transfers transformed with the resource
        return response()->json([
          'message' => 'Success!',
          'data' => $transformedTransfers],200);
    }

    public function index(Request $request)
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


        $query = EggTransfer::query();

        // Filter by trId
        if ($trId) {
            $query->where('trId', 'LIKE', '%' . $trId . '%');
          }
        // Filter by transferHead
           if ($transferHead) {
          $query->where('transferHead', $transferHead);
        }

          // Filter by fromStore
          if ($fromStore) {
            $query->where('fromStore', $fromStore);
          }

        // Filter by toStore
         if ($toStore) {
            $query->where('toStore', $toStore);
          }

        // Filter by trType
        if ($trType) {
          $query->where('trType', $trType);
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
        // Fetch transfer with eager loading of related data
       // $transfers = $query->latest()->get();
        $transfers = $query->with(['details.product.childCategory'])->latest()->paginate($limit);

        // Return paginated response
        return response()->json([
            'message' => 'Success!',
            'data' => EggTransferResource::collection($transfers),
            'meta' => [
                'current_page' => $transfers->currentPage(),
                'last_page' => $transfers->lastPage(),
                'per_page' => $transfers->perPage(),
                'total' => $transfers->total(),
            ]
        ], 200);
    }

    public function store000(EggTransferRequest $request)
    {
        DB::beginTransaction();
        try {
            // ✅ Validation: prevent duplicate pending transfer for same fromStore, toStore, and date
            $exists = EggTransfer::where('status', 'pending')
                ->where('fromStore', $request->fromStore)
                ->where('toStore', $request->toStore)
                ->where('date', $request->date)
                ->exists();

            if ($exists) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'A pending transfer already exists for the same From Store, To Store, and Date.',
                ], 409); // 409 Conflict
            }

            // ✅ Create egg transfer record
            $eggTransfer = EggTransfer::create([
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
                'note' => $request->note,
                'crBy' => auth()->id(),
                'appBy' => null,
                'status' => 'pending',
            ]);

            // ✅ Create egg transfer details
            foreach ($request->details as $detail) {
                EggTransferDetail::create([
                    'transferId' => $eggTransfer->id,
                    'productId' => $detail['productId'],
                    'qty' => $detail['qty'],
                    'transferFor' => $detail['transferFor'],
                    'note' => $detail['note'],
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Egg transfer created successfully.',
                'data' => new EggTransferResource($eggTransfer)
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
    public function store(EggTransferRequest $request)
    {
        DB::beginTransaction();
        try {
            // ✅ First: Validate if transfer already exists (same fromStore, toStore, date, status = pending)
            $existingTransfers = EggTransfer::where('status', 'pending')
                ->where('fromStore', $request->fromStore)
                ->where('toStore', $request->toStore)
                ->where('date', $request->date)
                ->pluck('id'); // Get matching transfer IDs

            if ($existingTransfers->isNotEmpty()) {
                // ✅ Check if any of the requested productIds already exist in the matching transfers
                $requestedProductIds = collect($request->details)->pluck('productId');

                $duplicateProduct = EggTransferDetail::whereIn('transferId', $existingTransfers)
                    ->whereIn('productId', $requestedProductIds)
                    ->first();

                if ($duplicateProduct) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'A pending transfer already exists with the same From Store, To Store, Date, and Product.',
                    ], 409); // Conflict
                }
            }

            // ✅ Proceed to create transfer
            $eggTransfer = EggTransfer::create([
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
                'note' => $request->note,
                'crBy' => auth()->id(),
                'appBy' => null,
                'status' => 'pending',
            ]);

            // ✅ Create egg transfer details
            foreach ($request->details as $detail) {
                EggTransferDetail::create([
                    'transferId' => $eggTransfer->id,
                    'productId' => $detail['productId'],
                    'qty' => $detail['qty'],
                    'transferFor' => $detail['transferFor'],
                    'note' => $detail['note'],
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Egg transfer created successfully.',
                'data' => new EggTransferResource($eggTransfer)
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
        $eggTransfer = EggTransfer::find($id);
        if (!$eggTransfer) {
          return response()->json(['message' => 'Egg Transfer not found'], 404);
        }
        return new EggTransferResource($eggTransfer);
    }

    public function update(EggTransferRequest $request, $id)
    {
        // Find the EggTransfer record by ID
        $eggTransfer = EggTransfer::find($id);

        if (!$eggTransfer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Egg transfer not found.'
            ], 404);
        }

        // Check if the status is 'approved', in which case it cannot be updated
        if (in_array($eggTransfer->status, ['approved', 'received'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot update an approved or received egg transfer.'
            ], 403);  // HTTP status 403: Forbidden
        }

        DB::beginTransaction();
        try {
            // Update egg transfer record
            $eggTransfer->update([
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
                'note' => $request->note,
                'appBy' => $request->appBy,
            ]);

            // Delete and recreate egg transfer details
            EggTransferDetail::where('transferId', $eggTransfer->id)->delete();
            foreach ($request->details as $detail) {
                EggTransferDetail::create([
                    'transferId' => $eggTransfer->id,
                    'productId' => $detail['productId'],
                    'qty' => $detail['qty'],
                    'transferFor' => $detail['transferFor'],
                    'note' => $detail['note'],
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Egg transfer updated successfully.',
                'data' => $eggTransfer->load('details')
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating the egg transfer.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


public function updateStatus000(Request $request, $id)
{

    $eggTransfer = EggTransfer::find($id);

    if (!$eggTransfer) {
        return response()->json([
            'status' => 'error',
            'message' => 'Egg transfer not found.'
        ], 404);
    }


    if (in_array($eggTransfer->status, ['approved', 'received'])) {
        return response()->json([
            'status' => 'error',
            'message' => 'Cannot update an egg transfer with approved or received status.'
        ], 403);
    }


    $request->validate([
        'status' => 'required|string|in:approved,pending,declined'
    ]);
    Cache::flush();

    if ($request->status === 'approved') {
        foreach ($eggTransfer->details as $detail) {
            $qty = $detail->qty;
            $productId = $detail->productId;


            $this->ledgerService->createTransferLedgerEntry(
                $eggTransfer->fromStore,
                $productId,
                $eggTransfer->trId,
                'productionTransfer',
                $eggTransfer->date,
                $qty,
                'Egg transfer'
            );

            $stockRecord = DB::table('egg_stocks')
                ->where('sectorId', $eggTransfer->fromStore)
                ->where('productId', $productId)
                ->orderBy('updated_at', 'desc')
                ->first();


            $isToday = $stockRecord ? \Carbon\Carbon::parse($stockRecord->updated_at)->isToday() : false;

            if ($stockRecord && $isToday) {

                DB::table('egg_stocks')
                    ->where('id', $stockRecord->id)
                    ->update([
                        'lockQty' => $stockRecord->lockQty + $qty,
                        'closing' => $stockRecord->closing - $qty,
                        'updated_at' => now()
                    ]);
            } else {

                DB::table('egg_stocks')->insert([
                    'sectorId' => $eggTransfer->fromStore,
                    'productId' => $productId,
                    'lockQty' => $stockRecord->lockQty+$qty,
                    'closing' =>$stockRecord->closing-$qty,
                    'trDate' => $eggTransfer->date,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    $eggTransfer->status = $request->status;
    $eggTransfer->save();
    return response()->json([
        'status' => 'success',
        'message' => 'Egg transfer status updated successfully.',
        'data' => $eggTransfer
    ], 200);
}

public function updateStatus(Request $request, $id)
{
    $eggTransfer = EggTransfer::with('details')->find($id);

    if (!$eggTransfer) {
        return response()->json([
            'status' => 'error',
            'message' => 'Egg transfer not found.'
        ], 404);
    }

    if (in_array($eggTransfer->status, ['approved', 'received'])) {
        return response()->json([
            'status' => 'error',
            'message' => 'Cannot update an egg transfer with approved or received status.'
        ], 403);
    }

    $request->validate([
        'status' => 'required|string|in:approved,pending,declined'
    ]);

    Cache::flush();

    // ✅ Only do stock validation if trying to approve
    if ($request->status === 'approved') {
        foreach ($eggTransfer->details as $detail) {
            $qty = $detail->qty;
            $productId = $detail->productId;

            // ✅ Get latest stock record
            $stockRecord = DB::table('egg_stocks')
                ->where('sectorId', $eggTransfer->fromStore)
                ->where('productId', $productId)
                ->orderBy('updated_at', 'desc')
                ->first();

            // ✅ Validate stock availability
            if (!$stockRecord || $stockRecord->closing < $qty) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Insufficient stock for product ID: $productId. Available: " . ($stockRecord->closing ?? 0) . ", Required: $qty",
                ], 422);
            }
        }

        // ✅ If all validations pass, proceed with ledger and stock update
        foreach ($eggTransfer->details as $detail) {
            $qty = $detail->qty;
            $productId = $detail->productId;

            $this->ledgerService->createTransferLedgerEntry(
                $eggTransfer->fromStore,
                $productId,
                $eggTransfer->trId,
                'productionTransfer',
                $eggTransfer->date,
                $qty,
                'Egg transfer'
            );

            $stockRecord = DB::table('egg_stocks')
                ->where('sectorId', $eggTransfer->fromStore)
                ->where('productId', $productId)
                ->orderBy('updated_at', 'desc')
                ->first();

            $isToday = $stockRecord ? \Carbon\Carbon::parse($stockRecord->updated_at)->isToday() : false;

            if ($stockRecord && $isToday) {
                DB::table('egg_stocks')
                    ->where('id', $stockRecord->id)
                    ->update([
                        'lockQty' => $stockRecord->lockQty + $qty,
                        'closing' => $stockRecord->closing - $qty,
                        'updated_at' => now()
                    ]);
            } else {
                DB::table('egg_stocks')->insert([
                    'sectorId' => $eggTransfer->fromStore,
                    'productId' => $productId,
                    'lockQty' => $stockRecord->lockQty + $qty,
                    'closing' => $stockRecord->closing - $qty,
                    'trDate' => $eggTransfer->date,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    // ✅ Finally update status
    $eggTransfer->status = $request->status;
    $eggTransfer->save();

    return response()->json([
        'status' => 'success',
        'message' => 'Egg transfer status updated successfully.',
        'data' => $eggTransfer
    ], 200);
}

    public function destroy($id)
    {
        $eggTransfer = EggTransfer::find($id);
        if (!$eggTransfer) {
            return response()->json(['message' => 'Egg Transfer not found'], 404);
        }
        $eggTransfer->delete();
        return response()->json([
            'message' => 'Egg Transfer deleted successfully',
        ],200);
    }
    public function getTransferList()
    {
      $approveTrList = EggTransfer::where('status', 'approved')
        ->select('id', 'trId',)
        ->get();
      return response()->json([
        'data' => $approveTrList
      ], 200);
    }


public function getTransferSecList()
{
    $approveTrList = EggTransfer::where('status', 'approved')
        ->with(['fStore:id,name', 'tStore:id,name'])
        ->select('id', 'trId', 'fromStore', 'toStore','date')
        ->get();

    return response()->json([
        'data' => $approveTrList
    ], 200);
}


//declineTransfer for receive
public function declineTransfer($id)
{
    $eggTransfer = EggTransfer::find($id);

    if (!$eggTransfer) {
        return response()->json([
            'status' => 'error',
            'message' => 'Egg transfer not found.'
        ], 404);
    }

    // Only allow decline if the status is 'approved'
    if ($eggTransfer->status !== 'approved') {
        return response()->json([
            'status' => 'error',
            'message' => 'Only approved transfers can be declined.'
        ], 403);
    }

    // Update the status to 'declined'
    $eggTransfer->status = 'declined';
    $eggTransfer->save();

    // Loop through each detail and update the stock in both egg_stocks and ep_ledgers
    foreach ($eggTransfer->details as $detail) {
        $qty = $detail->qty;
        $productId = $detail->productId;

        // Update feed_stocks table
        $stockRecord = DB::table('egg_stocks')
            ->where('sectorId', $eggTransfer->fromStore)
            ->where('productId', $productId)
            ->orderBy('updated_at', 'desc')
            ->first();

        if ($stockRecord) {
            // Ensure lockQty is a number and not null
            $currentLockQty = (int) $stockRecord->lockQty;
            $newLockQty = max(0, $currentLockQty - $qty); // Ensure it doesn't go negative

            // Restore the closing (stockQty) and set lockQty to 0
            DB::table('egg_stocks')
                ->where('id', $stockRecord->id)
                ->update([
                    'closing' => $stockRecord->closing + $qty,
                    'lockQty' => $newLockQty,
                    'updated_at' => now()
                ]);
        }

        // Update ep_ledgers table
        $ledgerRecord = DB::table('ep_ledgers')
            ->where('sectorId', $eggTransfer->fromStore)
            ->where('productId', $productId)
            ->orderBy('updated_at', 'desc')
            ->first();

        if ($ledgerRecord) {
            // Set lockQty to 0 in ep_ledgers table
            DB::table('ep_ledgers')
                ->where('id', $ledgerRecord->id)
                ->update([
                    'lockQty' => 0, // Reset lockQty to 0
                    'updated_at' => now()
                ]);
        }
    }

    return response()->json([
        'status' => 'success',
        'message' => 'Egg transfer declined successfully.',
        'data' => $eggTransfer
    ], 200);
}

}
