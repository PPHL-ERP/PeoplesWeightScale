<?php

namespace App\Http\Controllers\Api\Feed;

use App\Http\Controllers\Controller;
use App\Http\Resources\Feed\FeedFarmProductionResource;
use App\Models\FeedFarmProduction;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Log;
use App\Http\Requests\Feed\FeedFarmProductionRequest;
use App\Http\Requests\FeedFarmProductionUpdateRequest;
use Illuminate\Support\Facades\DB;
use App\Services\CacheService;
use App\Services\FeedProductionLedgerService;
use App\Services\FeedStockService;

class FeedFarmProductionController extends Controller
{

    protected $feedLedgerService;
    protected $feedStockService;
    protected $cacheService;


    // Inject both FeedProductionLedgerService and FeedStockService
    public function __construct(FeedProductionLedgerService $feedLedgerService, FeedStockService $feedStockService, CacheService $cacheService)
    {
        $this->feedLedgerService = $feedLedgerService;
        $this->feedStockService = $feedStockService;
        $this->cacheService = $cacheService;

    }
    public function indexOld(Request $request)
    {
        // Set default start date to two months ago and end date to today
        $startDate = $request->startDate ?? now()->subMonths(2)->startOfMonth()->format('Y-m-d');
        $endDate = $request->endDate ?? now()->endOfMonth()->format('Y-m-d');

        $productionId = $request->productionId;
        $productId = $request->productId;
        $sectorId = $request->sectorId;
        // $flockId = $request->flockId;
        // $flockTotal = $request->flockTotal;
        $status = $request->status;
        $childCategoryId = $request->childCategoryId ?? null;

        // Initialize the query builder
        $query = FeedFarmProduction::query();

        // Track whether any filters are applied
        $isFiltered = false;

        // Filter by productionId
        if (isset($productionId) && $productionId !== '') {
            $query->where('productionId', 'LIKE', '%' . $productionId . '%');
            $isFiltered = true;
        }


        // Filter by childCategoryId
        if ($childCategoryId) {
            $productIds = Product::where('childCategoryId', $childCategoryId)->pluck('id');
            $query->whereIn('productId', $productIds);
        }

        // Filter by productId
        if (isset($productId) && $productId !== '') {
            $query->where('productId', $productId);
            $isFiltered = true;
        }

        // Filter by sectorId
        if (isset($sectorId) && $sectorId !== '') {
            $query->where('sectorId', $sectorId);
            $isFiltered = true;
        }

        // // Filter by flockId
        // if (isset($flockId) && $flockId !== '') {
        //     $query->where('flockId', $flockId);
        //     $isFiltered = true;
        // }

        // // Filter by flockTotal
        // if (isset($flockTotal) && $flockTotal !== '') {
        //     $query->where('flockTotal', $flockTotal);
        //     $isFiltered = true;
        // }

        // Filter by date range (default to last 2 months)
        if ($startDate && $endDate) {
            $query->whereBetween('productionDate', [$startDate, $endDate]);
            $isFiltered = true;
        }

        // Filter by status
        if (isset($status) && $status !== '') {
            $query->where('status', $status);
            $isFiltered = true;
        }

        // Log the generated query for debugging
        // \Log::info($query->toSql());

        // Fetch filtered data
        //$productions = $query->latest()->get();
        $productions = $query->with(['product', 'childCategory'])->latest()->get();

        // Check if any production data was found
        if ($productions->isEmpty()) {
            return response()->json(['message' => 'No Feed Farm Production found', 'data' => []], 200);
        }

        // Transform the data using the resource class
        $transformedProductions = FeedFarmProductionResource::collection($productions);

        // Return the transformed data
        return response()->json([
            'message' => 'Success!',
            'data' => $transformedProductions
        ], 200);
    }

    public function index(Request $request)
    {
        // Set default start date to two months ago and end date to today
        $startDate = $request->startDate ?? now()->subMonths(2)->startOfMonth()->format('Y-m-d');
        $endDate = $request->endDate ?? now()->endOfMonth()->format('Y-m-d');

        $productionId    = $request->productionId;
        $productId       = $request->productId;
        $sectorId        = $request->sectorId;
        $status          = $request->status;
        $childCategoryId = $request->childCategoryId;
        $limit           = $request->input('limit', 100); // Default 100 items per page


        // Initialize the query builder
        $query = FeedFarmProduction::query();

        // Track whether any filters are applied
        $isFiltered = false;

        // Filter by productionId
        if (isset($productionId) && $productionId !== '') {
            $query->where('productionId', 'LIKE', '%' . $productionId . '%');
            $isFiltered = true;
        }


        // Filter by childCategoryId
        if ($childCategoryId) {
            $productIds = Product::where('childCategoryId', $childCategoryId)->pluck('id');
            $query->whereIn('productId', $productIds);
        }

        // Filter by productId
        if (isset($productId) && $productId !== '') {
            $query->where('productId', $productId);
            $isFiltered = true;
        }

        // Filter by sectorId
        if (isset($sectorId) && $sectorId !== '') {
            $query->where('sectorId', $sectorId);
            $isFiltered = true;
        }

        // Filter by date range (default to last 2 months)
        if ($startDate && $endDate) {
            $query->whereBetween('productionDate', [$startDate, $endDate]);
            $isFiltered = true;
        }

        // Filter by status
        if (isset($status) && $status !== '') {
            $query->where('status', $status);
            $isFiltered = true;
        }

        // Log the generated query for debugging
        // \Log::info($query->toSql());

        // Fetch filtered data
        //$productions = $query->latest()->get();
        $productions = $query->with(['product', 'childCategory'])->latest()->paginate($limit);

        // Check if any production data was found
        if ($productions->isEmpty()) {
            return response()->json(['message' => 'No Feed Farm Production found', 'data' => []], 200);
        }

       // Return paginated response
       return response()->json([
        'message' => 'Success!',
        'data' => FeedFarmProductionResource::collection($productions),
        'meta' => [
            'current_page' => $productions->currentPage(),
            'last_page'    => $productions->lastPage(),
            'per_page'     => $productions->perPage(),
            'total'        => $productions->total(),
        ]
    ], 200);
    }

    public function store(FeedFarmProductionRequest $request)
    {
        $data = $request->all();
        $data['crBy'] = auth()->id();
        $rows = $data['rows'];
        try {

            foreach ($rows as $row) {

                $productionData = [
                    'sectorId' => $data['sectorId'],
                    //'batchNo' => $data['batchNo'],
                    'batchNo' => $row['batchNo'],
                    'productionDate' => $data['productionDate'],
                    'expDate' => $data['expDate'],
                    'note' => $data['note'] ?? null, // Note can be null
                    'productId' => $row['productId'], // Product from the row
                    'qty' => $row['qty'], // Quantity from the row
                    'crBy' => $data['crBy'], // Created by the authenticated user
                    'status' => 'pending', // Set default status
                ];

                // Insert the record into the database
                FeedFarmProduction::create($productionData);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Production records added successfully'
            ], 201);

        } catch (\Exception $e) {
            // Catch any errors during the insertion process
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeold(FeedFarmProductionRequest $request)
    {
        $data = $request->all();
        $data['crBy'] = auth()->id();
        $rows = $data['rows'];
        try {

            foreach ($rows as $row) {

                $productionData = [
                    'sectorId' => $data['sectorId'],
                    // 'flockId' => $data['flockId'],
                    'batchNo' => $data['batchNo'],
                    'productionDate' => $data['productionDate'],
                    'note' => $data['note'] ?? null, // Note can be null
                    'productId' => $row['productId'], // Product from the row
                    'qty' => $row['qty'], // Quantity from the row
                    'crBy' => $data['crBy'], // Created by the authenticated user
                    'status' => 'pending', // Set default status
                ];

                // Insert the record into the database
                FeedFarmProduction::create($productionData);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Production records added successfully'
            ], 201);

        } catch (\Exception $e) {
            // Catch any errors during the insertion process
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        // Retrieve a specific record by ID
        $production = FeedFarmProduction::find($id);
        if (!$production) {
            return response()->json(['message' => 'Production not found'], 404);
        }
        return new FeedFarmProductionResource($production);
    }

    public function update(FeedFarmProductionUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $production = FeedFarmProduction::findOrFail($id);


            // Check if the status is 'approved'
            if ($production->status === 'approved') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot update record because the status is approved.',
                ], 403);
            }
            $production->sectorId = $request->sectorId;
            $production->batchNo = $request->batchNo;
            $production->productionDate = $request->productionDate;
            $production->expDate = $request->expDate;
            $production->note = $request->note;
            $production->productId = $request->productId;
            $production->qty = $request->qty;

            // Update the record
            $production->save();

            // Return success response
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Record updated successfully',
                'data' => $production
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            // Handle error response
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating the record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function destroy($id)
    {
        DB::beginTransaction();
        try{
            $production = FeedFarmProduction::findOrFail($id);
            if(!$production){
                return response()->json(['message' => 'Production not found'], 404);
            }
            if($production === 'approved'){
                return response()->json(['message' => 'Cannot delete record because the status is approved.'], 403);
            }
            $production->delete();
            DB::commit();
            return response()->json(['message' => 'Record deleted successfully'], 200);
        }
        catch(\Exception $e){
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            // Find the existing production record
            $production = FeedFarmProduction::findOrFail($id);

            // Validate the new status value
            $request->validate([
                'status' => 'required|string|in:approved,declined,pending',
            ]);

            // Prevent any updates if the current status is already approved
            if ($production->status === 'approved') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The status is already approved and cannot be changed.',
                ], 400);
            }

            // Check if the requested status is the same as the current status
            if ($production->status === $request->status) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The status is already ' . $request->status,
                ], 400);
            }

            // If the status is being updated to 'approved'
            if ($request->status === 'approved') {
                // Check for pending records for earlier dates in the same sector
                $hasPendingEarlierRecords = FeedFarmProduction::where('sectorId', $production->sectorId)
                    ->where('productionDate', '<', $production->productionDate)
                    ->where('status', 'pending')
                    ->exists();

                if ($hasPendingEarlierRecords) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Cannot approve the production. There are pending records for earlier dates in the same sector.',
                        'sectorId' => $production->sectorId,
                        'conflicting_date' => $production->productionDate,
                    ], 422);
                }

                // Create ledger entry using the service
                $this->feedLedgerService->createFeedLedgerEntry(
                    $production->sectorId,
                    $production->productId,
                    $production->productionId,
                    'Sector Production', // Transaction type
                    $production->productionDate,
                    $production->qty,
                    'Feed Production'
                );

                // Store or update feed stock using the FeedStockService
                $this->feedStockService->FeedstoreOrUpdateStock(
                    $production->sectorId,
                    $production->productId,
                    $production->qty,
                    $production->productionDate
                );

                // Update the current production record with the new status
                $production->update([
                    'status' => $request->status,
                    'appBy' => auth()->id()
                ]);
            } else {
                // If the status is something other than 'approved' (like 'pending' or 'declined')
                $production->update([
                    'status' => $request->status,
                    'appBy' => auth()->id(),
                ]);
            }

            // Clear cache after updating status
            $this->cacheService->clearAllCache();

            // Return success response
            return response()->json([
                'status' => 'success',
                'message' => 'Status updated successfully.',
                'data' => $production
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating the status.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateMultiStatus(Request $request)
    {
        try {
            $request->validate([
                'status' => 'required|string|in:approved,pending,declined',
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:feed_farm_productions,id'
            ]);

            $ids = $request->input('ids');
            $unchangedIds = [];
            $dateSet = [];
            $invalidDateIds = [];

            // Prevent updates to already approved records
            $alreadyApproved = FeedFarmProduction::whereIn('id', $ids)
                ->where('status', 'approved')
                ->pluck('id')
                ->toArray();

            if (!empty($alreadyApproved)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'One or more records are already approved and cannot be updated.',
                    'unchanged' => $alreadyApproved,
                ], 422);
            }

            DB::beginTransaction();

            foreach ($ids as $id) {
                $production = FeedFarmProduction::findOrFail($id);

                // Check for null or invalid dates
                if (is_null($production->productionDate)) {
                    $invalidDateIds[] = $production->id;
                    continue;
                }

                if ($request->status === 'approved') {
                    // Validate for conflicting dates
                    $dateSet[$production->productionDate] = true;
                    if (count($dateSet) > 1) {
                        DB::rollBack();
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Multiple productions with different dates cannot be approved together.',
                            'conflicting_date' => $production->productionDate,
                        ], 422);
                    }

                    // Check for pending records for earlier dates in the same sector
                    $hasPendingEarlierRecords = FeedFarmProduction::where('sectorId', $production->sectorId)
                        ->where('productionDate', '<', $production->productionDate)
                        ->where('status', 'pending')
                        ->exists();

                    if ($hasPendingEarlierRecords) {
                        DB::rollBack();
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Cannot approve the production. There are pending records for earlier dates in the same sector.',
                            'sectorId' => $production->sectorId,
                            'conflicting_date' => $production->productionDate,
                        ], 422);
                    }

                    // Ledger and stock update
                    $this->feedLedgerService->createFeedLedgerEntry(
                        $production->sectorId,
                        $production->productId,
                        $production->productionId,
                        'Sector Production',
                        $production->productionDate,
                        $production->qty,
                        ''
                    );

                    $this->feedStockService->FeedstoreOrUpdateStock(
                        $production->sectorId,
                        $production->productId,
                        $production->qty,
                        $production->productionDate
                    );
                }

                $production->update([
                    'status' => $request->status,
                    'appBy' => auth()->id(),
                ]);
            }

            $this->cacheService->clearAllCache();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Status updated successfully for ' . count($ids) . ' record(s)',
                'unchanged' => $unchangedIds,
                'invalid_dates' => $invalidDateIds,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating the status and creating ledger entries',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function getFeedTotalStockAndClosingBalance(Request $request)
    {
        // Validate input to ensure sectorId and productId are provided
        $validatedData = $request->validate([
            'sectorId' => 'required|integer',
            'productId' => 'required|integer',
        ]);

        $sectorId = $validatedData['sectorId'];
        $productId = $validatedData['productId'];

        // Get both the closing balance and lockQty from the ledger service
        $result = $this->feedLedgerService->getFeedTotalClosingBalance($sectorId, $productId);

        return response()->json([
            'status' => 'success',
            'lastClosingBalance' => $result['closingBalance'],  // Return the closing balance
            'lockQty' => $result['lockQty'],  // Return the lockQty
        ]);
    }


}
