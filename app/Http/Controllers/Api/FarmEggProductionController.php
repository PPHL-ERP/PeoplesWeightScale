<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FarmEggProduction;
use App\Http\Requests\FarmEggProductionRequest;
use Illuminate\Http\Request;
use App\Services\EpLedgerService;
use App\Services\EggStockService;
use App\Http\Resources\FarmEggProductionResource;
use App\Http\Controllers\Api\Log;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Services\CacheService;
class FarmEggProductionController extends Controller
{
    protected $ledgerService;
    protected $eggStockService;
    protected $cacheService;


    // Inject both EpLedgerService and EggStockService
    public function __construct(EpLedgerService $ledgerService, EggStockService $eggStockService,CacheService $cacheService)
    {
        $this->ledgerService = $ledgerService;
        $this->eggStockService = $eggStockService;
        $this->cacheService = $cacheService;

    }
    public function indexOLD(Request $request)
    {
        // Set default start date to two months ago and end date to today
        $startDate = $request->startDate ?? now()->subMonths(2)->startOfMonth()->format('Y-m-d');
        $endDate = $request->endDate ?? now()->endOfMonth()->format('Y-m-d');

        $productionId = $request->productionId;
        $productId = $request->productId;
        $sectorId = $request->sectorId;
        $flockId = $request->flockId;
        $flockTotal = $request->flockTotal;
        $status = $request->status;
        $childCategoryId = $request->childCategoryId ?? null;

        // Initialize the query builder
        $query = FarmEggProduction::query();

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

        // Filter by flockId
        if (isset($flockId) && $flockId !== '') {
            $query->where('flockId', $flockId);
            $isFiltered = true;
        }

        // Filter by flockTotal
        if (isset($flockTotal) && $flockTotal !== '') {
            $query->where('flockTotal', $flockTotal);
            $isFiltered = true;
        }

        // Filter by date range (default to last 2 months)
        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
            $isFiltered = true;
        }

        // Filter by status
        if (isset($status) && $status !== '') {
            $query->where('status', $status);
            $isFiltered = true;
        }

        // Log the generated query for debugging
        \Log::info($query->toSql());

        // Fetch filtered data
        //$productions = $query->latest()->get();
        $productions = $query->with(['product', 'childCategory'])->latest()->get();

        // Check if any production data was found
        if ($productions->isEmpty()) {
            return response()->json(['message' => 'No Farm Egg Production found', 'data' => []], 200);
        }

        // Transform the data using the resource class
        $transformedProductions = FarmEggProductionResource::collection($productions);

        // Return the transformed data
        return response()->json([
            'message' => 'Success!',
            'data' => $transformedProductions
        ], 200);
    }


    public function index(Request $request)
    {
        // Default start date = 2 months ago (start of month), end date = today
        $startDate = $request->input('startDate', now()->subMonths(2)->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('endDate', now()->endOfMonth()->format('Y-m-d'));

        $productionId    = $request->productionId;
        $productId       = $request->productId;
        $sectorId        = $request->sectorId;
        $flockId         = $request->flockId;
        $flockTotal      = $request->flockTotal;
        $status          = $request->status;
        $childCategoryId = $request->childCategoryId;
        $limit           = $request->input('limit', 100); // Default 100 items per page

        $query = FarmEggProduction::query();

        // Filters
        if (!empty($productionId)) {
            $query->where('productionId', 'LIKE', '%' . $productionId . '%');
        }

        if (!empty($childCategoryId)) {
            $productIds = Product::where('childCategoryId', $childCategoryId)->pluck('id');
            $query->whereIn('productId', $productIds);
        }

        if (!empty($productId)) {
            $query->where('productId', $productId);
        }

        if (!empty($sectorId)) {
            $query->where('sectorId', $sectorId);
        }

        if (!empty($flockId)) {
            $query->where('flockId', $flockId);
        }

        if (!empty($flockTotal)) {
            $query->where('flockTotal', $flockTotal);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        // Fetch paginated data with eager loaded relations
        $productions = $query->with(['product', 'childCategory'])
                             ->latest()
                             ->paginate($limit);

        // Return paginated response
        return response()->json([
            'message' => 'Success!',
            'data' => FarmEggProductionResource::collection($productions),
            'meta' => [
                'current_page' => $productions->currentPage(),
                'last_page'    => $productions->lastPage(),
                'per_page'     => $productions->perPage(),
                'total'        => $productions->total(),
            ]
        ], 200);
    }



    public function show($id)
    {
        // Retrieve a specific record by ID
        $production = FarmEggProduction::find($id);
        if (!$production) {
            return response()->json(['message' => 'Company not found'], 404);
        }
        return new FarmEggProductionResource($production);
    }

    public function store(FarmEggProductionRequest $request)
    {
        $data = $request->all();
        $data['crBy'] = auth()->id();
        $rows = $data['rows'];
        try {

            foreach ($rows as $row) {

                $productionData = [
                    'sectorId' => $data['sectorId'],
                    'flockId' => $data['flockId'],
                    'date' => $data['date'],
                    'note' => $data['note'] ?? null, // Note can be null
                    'productId' => $row['productId'], // Product from the row
                    'qty' => $row['qty'], // Quantity from the row
                    'crBy' => $data['crBy'], // Created by the authenticated user
                    'status' => 'pending', // Set default status
                ];

                // Insert the record into the database
                FarmEggProduction::create($productionData);
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

    public function update(FarmEggProductionRequest $request, $id)
    {
        try {
            // Find the existing production record
            $production = FarmEggProduction::findOrFail($id);

            // Check if the status is 'approved'
            if ($production->status === 'approved') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot update record because the status is approved.',
                ], 403);
            }

            // Ensure that 'crBy' and 'appBy' are passed as valid user IDs
            $request->merge([
                'crBy' => auth()->id(), // Assuming you want the current user's ID
                'appBy' => auth()->id(), // Modify as needed based on your logic
            ]);

            // Update the record
            $production->update($request->all());

            // Recalculate flockTotal for all related records
            $relatedRows = FarmEggProduction::where('productId', $production->productId)
                ->where('sectorId', $production->sectorId)
                ->where('flockId', $production->flockId)
                ->orderBy('id')
                ->get();

            $runningFlockTotal = 0;
            foreach ($relatedRows as $row) {
                $runningFlockTotal += $row->qty;
                $row->update(['flockTotal' => $runningFlockTotal]);
            }

            // Return success response
            return response()->json([
                'status' => 'success',
                'message' => 'Record updated successfully',
                'data' => $production
            ], 200);

        } catch (\Exception $e) {
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
        // Find and delete the record by ID
        $production = FarmEggProduction::findOrFail($id);
        $production->delete();
        return response()->json(null, 204);
    }


    public function updateStatusOLD(Request $request, $id)
    {
        try {
            // Find the existing production record
            $production = FarmEggProduction::findOrFail($id);

            // Validate the new status value
            $request->validate([
                'status' => 'required|string|in:approved,declined',
            ]);

            // Check if the requested status is the same as the current status
            if ($production->status === $request->status) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Status is already ' . $request->status,
                ], 400);
            }

            // Prevent updating to 'approved' if it is already 'approved'
            if ($production->status === 'approved') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Status is already approved and cannot be updated again.',
                ], 400);
            }

            // If the status is being updated to 'approved'
            if ($request->status === 'approved') {
                // Check for pending records for earlier dates in the same sector
                $hasPendingEarlierRecords = FarmEggProduction::where('sectorId', $production->sectorId)
                    ->where('date', '<', $production->date)
                    ->where('status', 'pending')
                    ->exists();

                if ($hasPendingEarlierRecords) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Cannot approve the production. There are pending records for earlier dates in the same sector.',
                        'sectorId' => $production->sectorId,
                        'conflicting_date' => $production->date,
                    ], 422);
                }

                // Calculate the new flockTotal
                $previousFlockTotal = FarmEggProduction::where('sectorId', $production->sectorId)
                    ->where('productId', $production->productId)
                    ->where('flockId', $production->flockId)
                    ->where('status', 'approved')
                    ->sum('qty');

                $newFlockTotal = $previousFlockTotal + $production->qty;

                // Create ledger entry using the service
                $this->ledgerService->createLedgerEntry(
                    $production->sectorId,
                    $production->productId,
                    $production->productionId,
                    'Sector Production', // Transaction type
                    $production->date,
                    $production->qty,
                    'Egg Production ' . $production->flockId
                );

                // Store or update egg stock using the EggStockService
                $this->eggStockService->EggstoreOrUpdateStock(
                    $production->sectorId,
                    $production->productId,
                    $production->qty,
                    $production->date
                );

                // Update the current production record with the new flockTotal and status
                $production->update([
                    'flockTotal' => $newFlockTotal,
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
                'message' => 'Status updated and ledger entry created successfully',
                'data' => $production
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating status and creating ledger entry',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
{
    try {
        // Find the existing production record
        $production = FarmEggProduction::findOrFail($id);

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
            $hasPendingEarlierRecords = FarmEggProduction::where('sectorId', $production->sectorId)
                ->where('date', '<', $production->date)
                ->where('status', 'pending')
                ->exists();

            if ($hasPendingEarlierRecords) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot approve the production. There are pending records for earlier dates in the same sector.',
                    'sectorId' => $production->sectorId,
                    'conflicting_date' => $production->date,
                ], 422);
            }

            // Calculate the new flockTotal
            $previousFlockTotal = FarmEggProduction::where('sectorId', $production->sectorId)
                ->where('productId', $production->productId)
                ->where('flockId', $production->flockId)
                ->where('status', 'approved')
                ->sum('qty');

            $newFlockTotal = $previousFlockTotal + $production->qty;

            // Create ledger entry using the service
            $this->ledgerService->createLedgerEntry(
                $production->sectorId,
                $production->productId,
                $production->productionId,
                'Sector Production', // Transaction type
                $production->date,
                $production->qty,
                'Egg Production ' . $production->flockId
            );

            // Store or update egg stock using the EggStockService
            $this->eggStockService->EggstoreOrUpdateStock(
                $production->sectorId,
                $production->productId,
                $production->qty,
                $production->date
            );

            // Update the current production record with the new flockTotal and status
            $production->update([
                'flockTotal' => $newFlockTotal,
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

    // public function updateMultiStatus(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'status' => 'required|string|in:approved,pending,declined',
    //             'ids' => 'required|array',
    //             'ids.*' => 'integer|exists:farm_egg_productions,id'
    //         ]);

    //         $ids = $request->input('ids');
    //         $unchangedIds = [];

    //         DB::beginTransaction();

    //         foreach ($ids as $id) {
    //             $production = FarmEggProduction::findOrFail($id);

    //             if ($production->status === $request->status) {
    //                 $unchangedIds[] = $id;
    //                 continue;
    //             }

    //             if ($request->status === 'approved') {
    //                 $newFlockTotal = $this->ledgerService->calculateNewFlockTotal($production);

    //                 $this->ledgerService->createLedgerEntry(
    //                     $production->sectorId,
    //                     $production->productId,
    //                     $production->productionId,
    //                     'Sector Production',
    //                     $production->date,
    //                     $production->qty,
    //                     ''
    //                 );

    //                 $this->eggStockService->EggstoreOrUpdateStock(
    //                     $production->sectorId,
    //                     $production->productId,
    //                     $production->qty,
    //                     $production->date
    //                 );

    //                 $production->update([
    //                     'flockTotal' => $newFlockTotal,
    //                     'status' => $request->status,
    //                     'appBy' => auth()->id()
    //                 ]);
    //             } else {
    //                 $production->update([
    //                     'status' => $request->status,
    //                     'appBy' => auth()->id(),
    //                 ]);
    //             }

    //             // Clear cache for each updated production record
    //             $this->cacheService->clearAllCache();
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Status updated successfully for ' . count($ids) . ' record(s)',
    //             'unchanged' => $unchangedIds
    //         ], 200);

    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'An error occurred while updating the status and creating ledger entries',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }


     // MultipleDate not Approved Validation

    public function updateMultiStatusOLD(Request $request)
    {
        try {
            $request->validate([
                'status' => 'required|string|in:approved,pending,declined',
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:farm_egg_productions,id'
            ]);

            $ids = $request->input('ids');
            $unchangedIds = [];
            $dateSet = [];

            if ($request->status === 'approved') {
                $alreadyApproved = FarmEggProduction::whereIn('id', $ids)
                    ->where('status', 'approved')
                    ->pluck('id')
                    ->toArray();

                if (!empty($alreadyApproved)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'One or more records are already approved and cannot be approved again.',
                        'unchanged' => $alreadyApproved,
                    ], 422);
                }
            }

            DB::beginTransaction();

            foreach ($ids as $id) {
                $production = FarmEggProduction::findOrFail($id);

                if ($request->status === 'approved') {
                    // Validate for conflicting dates
                    $dateSet[$production->date] = true;
                    if (count($dateSet) > 1) {
                        DB::rollBack();
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Multiple productions with different dates cannot be approved together.',
                            'conflicting_date' => $production->date,
                        ], 422);
                    }

                    // Check for pending records for earlier dates in the same sector
                    $hasPendingEarlierRecords = FarmEggProduction::where('sectorId', $production->sectorId)
                        ->where('date', '<', $production->date)
                        ->where('status', 'pending')
                        ->exists();

                    if ($hasPendingEarlierRecords) {
                        DB::rollBack();
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Cannot approve the production. There are pending records for earlier dates in the same sector.',
                            'sectorId' => $production->sectorId,
                            'conflicting_date' => $production->date,
                        ], 422);
                    }

                    $newFlockTotal = $this->ledgerService->calculateNewFlockTotal($production);

                    $this->ledgerService->createLedgerEntry(
                        $production->sectorId,
                        $production->productId,
                        $production->productionId,
                        'Sector Production',
                        $production->date,
                        $production->qty,
                        ''
                    );

                    $this->eggStockService->EggstoreOrUpdateStock(
                        $production->sectorId,
                        $production->productId,
                        $production->qty,
                        $production->date
                    );

                    $production->update([
                        'flockTotal' => $newFlockTotal,
                        'status' => $request->status,
                        'appBy' => auth()->id()
                    ]);
                } else {
                    $production->update([
                        'status' => $request->status,
                        'appBy' => auth()->id(),
                    ]);
                }

                $this->cacheService->clearAllCache();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Status updated successfully for ' . count($ids) . ' record(s)',
                'unchanged' => $unchangedIds
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
    public function updateMultiStatus(Request $request)
    {
        try {
            $request->validate([
                'status' => 'required|string|in:approved,pending,declined',
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:farm_egg_productions,id'
            ]);

            $ids = $request->input('ids');
            $unchangedIds = [];
            $dateSet = [];

            // Prevent updates to already approved records
            $alreadyApproved = FarmEggProduction::whereIn('id', $ids)
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
                $production = FarmEggProduction::findOrFail($id);

                if ($request->status === 'approved') {
                    // Validate for conflicting dates
                    $dateSet[$production->date] = true;
                    if (count($dateSet) > 1) {
                        DB::rollBack();
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Multiple productions with different dates cannot be approved together.',
                            'conflicting_date' => $production->date,
                        ], 422);
                    }

                    // Check for pending records for earlier dates in the same sector
                    $hasPendingEarlierRecords = FarmEggProduction::where('sectorId', $production->sectorId)
                        ->where('date', '<', $production->date)
                        ->where('status', 'pending')
                        ->exists();

                    if ($hasPendingEarlierRecords) {
                        DB::rollBack();
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Cannot approve the production. There are pending records for earlier dates in the same sector.',
                            'sectorId' => $production->sectorId,
                            'conflicting_date' => $production->date,
                        ], 422);
                    }

                    $newFlockTotal = $this->ledgerService->calculateNewFlockTotal($production);

                    $this->ledgerService->createLedgerEntry(
                        $production->sectorId,
                        $production->productId,
                        $production->productionId,
                        'Sector Production',
                        $production->date,
                        $production->qty,
                        ''
                    );

                    $this->eggStockService->EggstoreOrUpdateStock(
                        $production->sectorId,
                        $production->productId,
                        $production->qty,
                        $production->date
                    );

                    $production->update([
                        'flockTotal' => $newFlockTotal,
                        'status' => $request->status,
                        'appBy' => auth()->id()
                    ]);
                } else {
                    $production->update([
                        'status' => $request->status,
                        'appBy' => auth()->id(),
                    ]);
                }
            }

            $this->cacheService->clearAllCache();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Status updated successfully for ' . count($ids) . ' record(s)',
                'unchanged' => $unchangedIds
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

    public function getTotalStockAndClosingBalance(Request $request)
    {
        // Validate input to ensure sectorId and productId are provided
        $validatedData = $request->validate([
            'sectorId' => 'required|integer',
            'productId' => 'required|integer',
        ]);

        $sectorId = $validatedData['sectorId'];
        $productId = $validatedData['productId'];

        // Get both the closing balance and lockQty from the ledger service
        $result = $this->ledgerService->getTotalClosingBalance($sectorId, $productId);

        return response()->json([
            'status' => 'success',
            'lastClosingBalance' => $result['closingBalance'],  // Return the closing balance
            'lockQty' => $result['lockQty'],  // Return the lockQty
        ]);
    }





}
