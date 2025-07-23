<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EggStockAdjustmentRequest;
use App\Http\Resources\EggStockAdjustmentResource;
use App\Models\EggStockAdjustment;
use App\Models\Product;
use App\Models\Sector;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ChildCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Services\EpLedgerService;
use App\Services\EggStockService;
use App\Services\CacheService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
class EggStockAdjustmentController extends Controller
{
    protected $ledgerService;
    protected $eggStockService;
    protected $cacheService;

    /**
     * Inject services into the controller.
     *
     * @param  EpLedgerService  $ledgerService
     * @param  EggStockService  $eggStockService
     * @param  CacheService  $cacheService
     * @return void
     */
    public function __construct(EpLedgerService $ledgerService, EggStockService $eggStockService, CacheService $cacheService)
    {
        $this->ledgerService = $ledgerService;
        $this->eggStockService = $eggStockService;
        $this->cacheService = $cacheService;
    }

    /**
     * Display a listing of the egg stock adjustments.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexOld(Request $request)
    {
        $startDate = $request->startDate ?? now()->subMonth()->format('Y-m-d');
        $endDate = $request->endDate ?? now()->format('Y-m-d');
        $adjId = $request->adjId ?? null;
        $productId = $request->productId ?? null;
        $sectorId = $request->sectorId ?? null;
        $adjCategory = $request->adjCategory ?? null;
        $status = $request->status ?? null;
        $childCategoryId = $request->childCategoryId ?? null;

        $query = EggStockAdjustment::query();

        // Filter by adjId
        if ($adjId) {
            $query->where('adjId', 'LIKE', '%' . $adjId . '%');
        }

        // Filter by childCategoryId
        if ($childCategoryId) {
            $productIds = Product::where('childCategoryId', $childCategoryId)->pluck('id');
            $query->whereIn('productId', $productIds);
        }

        // Filter by productId
        if ($productId) {
            $query->orWhere('productId', $productId);
        }

        // Filter by sectorId
        if ($sectorId) {
            $query->orWhere('sectorId', $sectorId);
        }

        // Filter by adjCategory
        if ($adjCategory) {
            // Corrected the syntax by removing the 'operator:' named parameter
            $query->orWhere('adjCategory', $adjCategory);
        }

        // Filter date
        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        // Filter by status
        if ($status) {
            $query->where('status', $status);
        }

        // Fetch adjustments with eager loading of related data
        $adjustments = $query->with(['product', 'childCategory'])->latest()->get();

        // Check if any adjustment found
        if ($adjustments->isEmpty()) {
            return response()->json(['message' => 'No EggStockAdjustment found', 'data' => []], 200);
        }

        // Use the EggStockAdjustmentResource to transform the data
        $transformedAdjustments = EggStockAdjustmentResource::collection($adjustments);

        // Return the transformed data
        return response()->json([
            'message' => 'Success!',
            'data' => $transformedAdjustments
        ], 200);
    }


    public function index(Request $request)
    {
        $startDate = $request->startDate ?? now()->subMonth()->format('Y-m-d');
        $endDate = $request->endDate ?? now()->format('Y-m-d');

        $adjId           = $request->adjId;
        $productId       = $request->productId;
        $sectorId        = $request->sectorId;
        $adjCategory     = $request->adjCategory;
        $status          = $request->status;
        $childCategoryId = $request->childCategoryId;
        $limit           = $request->input('limit', 100); // Default 100 items per page

        $query = EggStockAdjustment::query();

        // Filter by adjId
        if ($adjId) {
            $query->where('adjId', 'LIKE', '%' . $adjId . '%');
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

        // Filter by sectorId
        if ($sectorId) {
            $query->where('sectorId', $sectorId);
        }

        // Filter by adjCategory
        if ($adjCategory) {
            // Corrected the syntax by removing the 'operator:' named parameter
            $query->where('adjCategory', $adjCategory);
        }

        // Filter date
        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        // Filter by status
        if ($status) {
            $query->where('status', $status);
        }

        // Fetch adjustments with eager loading of related data
        $adjustments = $query->with(['product', 'childCategory'])->latest()->paginate($limit);

         // Return paginated response
         return response()->json([
            'message' => 'Success!',
            'data' => EggStockAdjustmentResource::collection($adjustments),
            'meta' => [
                'current_page' => $adjustments->currentPage(),
                'last_page'    => $adjustments->lastPage(),
                'per_page'     => $adjustments->perPage(),
                'total'        => $adjustments->total(),
            ]
        ], 200);
    }
    /**
     * Store a newly created egg stock adjustment in storage.
     *
     * @param  \App\Http\Requests\EggStockAdjustmentRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(EggStockAdjustmentRequest $request)
    {
        DB::beginTransaction();
        try {
            // Extract general adjustment data from the request
            $salesPoint = $request->salesPoint;
            $adjustmentDate = $request->adjustmentDate;
            $adjCategory = $request->adjCategory;
            //$adjustmentBy = $request->adjustmentBy;

            // Initialize an array to store all generated adjIds
            $adjIds = [];

            // Iterate over each 'fields' entry
            foreach ($request->fields as $field) {
                // Generate a unique adjustment ID for this pair
                $adjId = 'EAJ' . now()->format('y') . now()->format('m') . str_pad(EggStockAdjustment::max('id') + 1, 4, '0', STR_PAD_LEFT);

                // First product adjustment
                EggStockAdjustment::create([
                    'adjId' => $adjId,
                    'sectorId' => $salesPoint,
                    'productId' => $field['firstProductId'],
                    'date' => $adjustmentDate,
                    'initialQty' => $field['originalFirstProductStock'], // Initial quantity for first product
                    'adjQty' => $field['adjustStock'], // Adjusted stock for first product
                    'finalQty' => $field['originalFirstProductStock'] - $field['adjustStock'], // Final quantity after adjustment
                    'adjType' => $field['firstProductAdjustmentType'],
                    'adjCategory' => $adjCategory,
                    'note' => $field['remarks'],
                    //'crBy' => $adjustmentBy,
                    'crBy' => auth()->id(),
                    // 'appBy' => null,
                    'status' => 'pending',
                ]);

                // Second product adjustment
                EggStockAdjustment::create([
                    'adjId' => $adjId,
                    'sectorId' => $salesPoint,
                    'productId' => $field['secondProductId'],
                    'date' => $adjustmentDate,
                    'initialQty' => $field['originalSecondProductStock'], // Initial quantity for second product
                    'adjQty' => $field['adjustStock'], // Adjusted stock for second product
                    'finalQty' => $field['originalSecondProductStock'] + $field['adjustStock'], // Final quantity after adjustment
                    'adjType' => $field['secondProductAdjustmentType'],
                    'adjCategory' => $adjCategory,
                    'note' => $field['remarks'],
                    // 'crBy' => $adjustmentBy,
                    'crBy' => auth()->id(),
                    // 'appBy' => null,
                    'status' => 'pending',
                ]);

                // Store the generated adjId
                $adjIds[] = $adjId;
            }

            // Commit the transaction
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Egg adjustments created successfully.',
                'adjIds' => $adjIds, // Return the array of unique adjustment IDs for reference
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while creating the egg adjustment.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Display the specified egg stock adjustment.
     *
     * @param  string  $adjId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $adjId): AnonymousResourceCollection|JsonResponse
    {
        // Retrieve all rows matching the adjId with eager loading for relationships
        $eggAdjs = EggStockAdjustment::where('adjId', $adjId)
            ->with(['sector', 'product', 'createdBy', 'approvedBy']) // Eager load relationships
            ->orderBy('id', 'asc') // Order rows by ID
            ->get();

        // Check if records exist
        if ($eggAdjs->isEmpty()) {
            return response()->json(['message' => 'Egg Adjustments not found'], 404);
        }

        // Return the collection of resources
        return EggStockAdjustmentResource::collection($eggAdjs);
    }

/**
 * Update the specified egg stock adjustment in storage.
 *
 * @param  \Illuminate\Http\Request  $request
 * @param  string  $adjId
 * @return \Illuminate\Http\JsonResponse
 */
public function update(Request $request, string $adjId)
{
    DB::beginTransaction();
    try {
        // Retrieve all adjustments with the same adjId, ordered by ID (smaller ID first)
        $eggAdjustments = EggStockAdjustment::where('adjId', $adjId)->orderBy('id')->get();

        if ($eggAdjustments->isEmpty()) {
            return response()->json(['error' => 'Egg adjustments not found'], 404);
        }

        // Check if any adjustment is approved
        if ($eggAdjustments->contains('status', 'approved')) {
            return response()->json(['error' => 'Approved adjustments cannot be updated'], 403);
        }

        // Ensure there are exactly two rows to update
        if ($eggAdjustments->count() !== 2) {
            return response()->json(['error' => 'Invalid adjustment data. Exactly two rows are required.'], 400);
        }

        // Map adjustments to the first and second product
        $firstAdjustment = $eggAdjustments[0]; // Row with the smaller ID
        $secondAdjustment = $eggAdjustments[1]; // Row with the larger ID

        // Update the first product adjustment
        $firstAdjustment->update([
            'sectorId' => $request->salesPoint,
            'productId' => $request->fields[0]['firstProductId'],
            'date' => $request->adjustmentDate,
            'initialQty' => $request->fields[0]['originalFirstProductStock'],
            'adjQty' => $request->fields[0]['adjustStock'],
            'finalQty' => $request->fields[0]['originalFirstProductStock'] - $request->fields[0]['adjustStock'],
            'adjType' => $request->fields[0]['firstProductAdjustmentType'],
            'adjCategory' => $request->adjCategory,
            'note' => $request->fields[0]['remarks'],
            // 'crBy' => $request->adjustmentBy,
        ]);

        // Update the second product adjustment
        $secondAdjustment->update([
            'sectorId' => $request->salesPoint,
            'productId' => $request->fields[0]['secondProductId'],
            'date' => $request->adjustmentDate,
            'initialQty' => $request->fields[0]['originalSecondProductStock'],
            'adjQty' => $request->fields[0]['adjustStock'],
            'finalQty' => $request->fields[0]['originalSecondProductStock'] + $request->fields[0]['adjustStock'],
            'adjType' => $request->fields[0]['secondProductAdjustmentType'],
            'adjCategory' => $request->adjCategory,
            'note' => $request->fields[0]['remarks'],
            // 'crBy' => $request->adjustmentBy,
        ]);

        DB::commit();

        return response()->json([
            'status' => 'success',
            'message' => 'Egg adjustments updated successfully.',
        ], 200);
    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to update egg adjustments.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


    public function updateStatus(Request $request, $adjId)
    {
        DB::beginTransaction();
        try {
            // Retrieve all adjustments with the same adjId, ordered by ID
            $eggAdjustments = EggStockAdjustment::where('adjId', $adjId)
                ->orderBy('id', 'asc') // Ensure first ID is processed first
                ->get();

            if ($eggAdjustments->isEmpty()) {
                return response()->json(['message' => 'Egg adjustments not found'], 404);
            }

            if ($eggAdjustments->count() !== 2) {
                return response()->json(['message' => 'Exactly two adjustments are required for this operation.'], 400);
            }

            foreach ($eggAdjustments as $eggAdj) {
                // Check if the adjustment is already approved
                if ($eggAdj->status === 'approved') {
                    return response()->json(['message' => 'Approved adjustments cannot be modified.'], 403);
                }
            }

            // Process the first row (Deduction)
            $deductionAdj = $eggAdjustments[0];
            $deductionAdj->status = $request->status;
            $deductionAdj->appBy = auth()->id();

            // Deduction: Adjust stock and create ledger entry
            $this->eggStockService->EggstoreOrUpdateStockdeDuction(
                $deductionAdj->sectorId,
                $deductionAdj->productId,
                $deductionAdj->adjQty,
                $deductionAdj->date
            );

            $this->ledgerService->createStockAdjLedgerEntry(
                $deductionAdj->sectorId,
                $deductionAdj->productId,
                $deductionAdj->adjId,
                'StockAdjustment',
                $deductionAdj->date,
                -$deductionAdj->adjQty, // Negative quantity for deduction
                'Egg adjustment - Stock deduction'
            );

            $deductionAdj->save(); // Save changes to Deduction adjustment

            // Process the second row (Addition)
            $additionAdj = $eggAdjustments[1];
            $additionAdj->status = $request->status;
            $additionAdj->appBy = auth()->id();

            // Addition: Adjust stock and create ledger entry
            $this->eggStockService->EggstoreOrUpdateStock(
                $additionAdj->sectorId,
                $additionAdj->productId,
                $additionAdj->adjQty,
                $additionAdj->date
            );

            $this->ledgerService->createStockAdjLedgerEntryAdd(
                $additionAdj->sectorId,
                $additionAdj->productId,
                $additionAdj->adjId,
                'StockAdjustment',
                $additionAdj->date,
                $additionAdj->adjQty, // Positive quantity for addition
                'Egg adjustment - Stock addition'
            );

            $additionAdj->save(); // Save changes to Addition adjustment

            // Clear cache
            $this->cacheService->clearAllCache();

            DB::commit();

            return response()->json(['message' => 'Egg adjustment status updated successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update Egg Adjustment status and stock.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }





    /**
     * Remove the specified egg stock adjustment from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $eggAdj = EggStockAdjustment::find($id);

        if (!$eggAdj) {
            return response()->json(['message' => 'Egg adjustment not found'], 404);
        }

        if ($eggAdj->status === 'approved') {
            return response()->json(['message' => 'Approved adjustments cannot be deleted'], 403);
        }

        $eggAdj->delete();

        return response()->json(['message' => 'Egg adjustment deleted successfully'], 200);
    }

    /**
     * Retrieve detailed information about a specific egg stock adjustment.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAdjustmentDetails($id)
    {
        try {
            // Step 1: Retrieve the EggStockAdjustment record by 'id' with related Product and Sector
            $eggAdjRecord = EggStockAdjustment::with([
                'product.category',
                'product.subCategory',
                'product.childCategory',
                'sector'
            ])->find($id);

            if (!$eggAdjRecord) {
                return response()->json(['message' => 'EggStockAdjustment not found'], 404);
            }

            // Step 2: Get the 'adjId' from the retrieved record
            $adjId = $eggAdjRecord->adjId;

            // Step 3: Retrieve all EggStockAdjustment records with the same 'adjId' including related Product and Sector
            $adjustments = EggStockAdjustment::with([
                'product.category',
                'product.subCategory',
                'product.childCategory',
                'sector'
            ])->where('adjId', $adjId)->get();

            if ($adjustments->isEmpty()) {
                return response()->json(['message' => 'No adjustments found for the given adjId'], 404);
            }

            // Step 4: Transform adjustments into an array of objects
            $adjustmentsArray = $adjustments->map(function ($item) {
                return [
                    'id' => $item->id,
                    'adjId' => $item->adjId,
                    'sectorId' => $item->sectorId,
                    'productId' => $item->productId,
                    'date' => Carbon::parse($item->date)->toDateString(), // Parsed using Carbon
                    'initialQty' => $item->initialQty,
                    'adjQty' => $item->adjQty,
                    'finalQty' => $item->finalQty,
                    'adjType' => $item->adjType,
                    'note' => $item->note,
                    'crBy' => $item->crBy,
                    'appBy' => $item->appBy,
                    'status' => $item->status,
                    'adjCategory' => $item->adjCategory,
                    'created_at' => Carbon::parse($item->created_at)->toDateTimeString(), // Parsed using Carbon
                    'updated_at' => Carbon::parse($item->updated_at)->toDateTimeString(), // Parsed using Carbon
                ];
            })->toArray();

            // Step 5: Retrieve the sector name from the first adjustment
            $sectorName = $adjustments->first()->sector->name ?? 'N/A';

            // Step 6: Retrieve category details from the first adjustment's product
            $product = $adjustments->first()->product;

            if (!$product) {
                return response()->json(['message' => 'Product not found'], 404);
            }

            $categoryName = $product->category->name ?? 'N/A';
            $subCategoryName = $product->subCategory->subCategoryName ?? 'N/A';
            $childCategoryName = $product->childCategory->childCategoryName ?? 'N/A';

            // Step 7: Fetch employee list from HRM API to map 'crBy' to employeeName
            $employeeApiUrl = 'https://hrm.peoplestechpark.com/api/get-sales-employee-list';
            $employeeName = 'N/A'; // Default value

            try {
                $response = Http::get($employeeApiUrl);

                if ($response->successful()) {
                    $employees = $response->json('data', []);

                    // Create a mapping of employeeId to employeeName
                    $employeeMap = collect($employees)->pluck('employeeName', 'employeeId')->toArray();

                    // Get the 'crBy' ID from the first adjustment
                    $crById = $adjustments->first()->crBy;

                    // Map the 'crBy' ID to employeeName
                    if (isset($employeeMap[$crById])) {
                        $employeeName = $employeeMap[$crById];
                    }
                } else {
                    // Log if the employee API response is not successful
                    \Log::error('Failed to fetch employee list. Status: ' . $response->status());
                }
            } catch (\Exception $e) {
                // Log the exception if the HTTP request fails
                \Log::error('Error fetching employee list: ' . $e->getMessage());
            }

            // Step 8: Format and return the response with employee name
            return response()->json([
                'Sales Point' => $sectorName,
                'Product Category' => $categoryName,
                'SubCategory' => $subCategoryName,
                'Child Category' => $childCategoryName,
                'Adjustment Category' => $adjustments->first()->adjCategory,
                'Adjustment Date' => Carbon::parse($adjustments->first()->date)->toDateString(),
                'Adjusted By' => $employeeName, // Now displays employee name
                'Adjustments' => $adjustmentsArray,
            ], 200);

        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Error in getAdjustmentDetails: ' . $e->getMessage());

            return response()->json([
                'message' => 'An unexpected error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    }