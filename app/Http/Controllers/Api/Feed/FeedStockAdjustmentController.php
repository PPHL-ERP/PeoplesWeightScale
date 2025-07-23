<?php

namespace App\Http\Controllers\Api\Feed;

use App\Http\Controllers\Controller;
use App\Http\Requests\Feed\FeedStockAdjustmentRequest;
use App\Http\Resources\Feed\FeedStockAdjustmentResource;
use App\Models\FeedStockAdjustment;
use App\Models\Product;
use App\Models\Sector;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ChildCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Services\FeedProductionLedgerService;
use App\Services\FeedStockService;
use App\Services\CacheService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Log;

class FeedStockAdjustmentController extends Controller
{

    protected $ledgerService;
    protected $feedStockService;
    protected $cacheService;

    /**
     * Inject services into the controller.
     *
     * @param  FeedProductionLedgerService  $ledgerService
     * @param  feedStockService  $feedStockService
     * @param  CacheService  $cacheService
     * @return void
     */
    public function __construct(FeedProductionLedgerService $ledgerService, FeedStockService $feedStockService, CacheService $cacheService)
    {
        $this->ledgerService = $ledgerService;
        $this->feedStockService = $feedStockService;
        $this->cacheService = $cacheService;
    }

    /**
     * Display a listing of the feed stock adjustments.
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

        $query = FeedStockAdjustment::query();

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
            return response()->json(['message' => 'No FeedStockAdjustment found', 'data' => []], 200);
        }

        // Use the FeedStockAdjustmentResource to transform the data
        $transformedAdjustments = FeedStockAdjustmentResource::collection($adjustments);

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


        $query = FeedStockAdjustment::query();

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
            'data' => FeedStockAdjustmentResource::collection($adjustments),
            'meta' => [
                'current_page' => $adjustments->currentPage(),
                'last_page'    => $adjustments->lastPage(),
                'per_page'     => $adjustments->perPage(),
                'total'        => $adjustments->total(),
            ]
        ], 200);
    }

    public function store(FeedStockAdjustmentRequest $request)
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
                $adjId = 'FAJ' . now()->format('y') . now()->format('m') . str_pad(FeedStockAdjustment::max('id') + 1, 4, '0', STR_PAD_LEFT);

                // First product adjustment
                FeedStockAdjustment::create([
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
                    // 'crBy' => $adjustmentBy,
                    'crBy' => auth()->id(),
                    //'appBy' => null,
                    'status' => 'pending',
                ]);

                // Second product adjustment
                FeedStockAdjustment::create([
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
                    //'crBy' => $adjustmentBy,
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
                'message' => 'Feed adjustments created successfully.',
                'adjIds' => $adjIds, // Return the array of unique adjustment IDs for reference
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while creating the feed adjustment.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $adjId): AnonymousResourceCollection|JsonResponse
    {
        // Retrieve all rows matching the adjId with eager loading for relationships
        $feedAdjs = FeedStockAdjustment::where('adjId', $adjId)
            ->with(['sector', 'product', 'createdBy', 'approvedBy']) // Eager load relationships
            ->orderBy('id', 'asc') // Order rows by ID
            ->get();

        // Check if records exist
        if ($feedAdjs->isEmpty()) {
            return response()->json(['message' => 'Feed Adjustments not found'], 404);
        }

        // Return the collection of resources
        return FeedStockAdjustmentResource::collection($feedAdjs);
    }

    public function update(Request $request, string $adjId)
    {
        DB::beginTransaction();
        try {
            // Retrieve all adjustments with the same adjId, ordered by ID (smaller ID first)
            $feedAdjustments = FeedStockAdjustment::where('adjId', $adjId)->orderBy('id')->get();

            if ($feedAdjustments->isEmpty()) {
                return response()->json(['error' => 'Feed adjustments not found'], 404);
            }

            // Check if any adjustment is approved
            if ($feedAdjustments->contains('status', 'approved')) {
                return response()->json(['error' => 'Approved adjustments cannot be updated'], 403);
            }

            // Ensure there are exactly two rows to update
            if ($feedAdjustments->count() !== 2) {
                return response()->json(['error' => 'Invalid adjustment data. Exactly two rows are required.'], 400);
            }

            // Map adjustments to the first and second product
            $firstAdjustment = $feedAdjustments[0]; // Row with the smaller ID
            $secondAdjustment = $feedAdjustments[1]; // Row with the larger ID

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
                //'crBy' => $request->adjustmentBy,
                'appBy' => auth()->id(),
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
                //'crBy' => $request->adjustmentBy,
                'appBy' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Feed adjustments updated successfully.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update feed adjustments.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function updateStatus(Request $request, $adjId)
    {
        DB::beginTransaction();
        try {
            // Retrieve all adjustments with the same adjId, ordered by ID
            $feedAdjustments = FeedStockAdjustment::where('adjId', $adjId)
                ->orderBy('id', 'asc') // Ensure first ID is processed first
                ->get();

            if ($feedAdjustments->isEmpty()) {
                return response()->json(['message' => 'Feed adjustments not found'], 404);
            }

            if ($feedAdjustments->count() !== 2) {
                return response()->json(['message' => 'Exactly two adjustments are required for this operation.'], 400);
            }

            foreach ($feedAdjustments as $feedAdj) {
                // Check if the adjustment is already approved
                if ($feedAdj->status === 'approved') {
                    return response()->json(['message' => 'Approved adjustments cannot be modified.'], 403);
                }
            }

            // Process the first row (Deduction)
            $deductionAdj = $feedAdjustments[0];
            $deductionAdj->status = $request->status;
            $deductionAdj->appBy = auth()->id();

            // Deduction: Adjust stock and create ledger entry
            $this->feedStockService->FeedstoreOrUpdateStockdeDuction(
                $deductionAdj->sectorId,
                $deductionAdj->productId,
                $deductionAdj->adjQty,
                $deductionAdj->date
            );

            $this->ledgerService->createFeedStockAdjLedgerEntry(
                $deductionAdj->sectorId,
                $deductionAdj->productId,
                $deductionAdj->adjId,
                'StockAdjustment',
                $deductionAdj->date,
                -$deductionAdj->adjQty, // Negative quantity for deduction
                'Feed adjustment - Stock deduction'
            );

            $deductionAdj->save(); // Save changes to Deduction adjustment

            // Process the second row (Addition)
            $additionAdj = $feedAdjustments[1];
            $additionAdj->status = $request->status;
            $additionAdj->appBy = auth()->id();

            // Addition: Adjust stock and create ledger entry
            $this->feedStockService->FeedstoreOrUpdateStock(
                $additionAdj->sectorId,
                $additionAdj->productId,
                $additionAdj->adjQty,
                $additionAdj->date
            );

            $this->ledgerService->createFeedStockAdjLedgerEntryAdd(
                $additionAdj->sectorId,
                $additionAdj->productId,
                $additionAdj->adjId,
                'StockAdjustment',
                $additionAdj->date,
                $additionAdj->adjQty, // Positive quantity for addition
                'Feed adjustment - Stock addition'
            );

            $additionAdj->save(); // Save changes to Addition adjustment

            // Clear cache
            $this->cacheService->clearAllCache();

            DB::commit();

            return response()->json(['message' => 'Feed adjustment status updated successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update Feed Adjustment status and stock.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified feed stock adjustment from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $feedAdj = FeedStockAdjustment::find($id);

        if (!$feedAdj) {
            return response()->json(['message' => 'Feed adjustment not found'], 404);
        }

        if ($feedAdj->status === 'approved') {
            return response()->json(['message' => 'Approved adjustments cannot be deleted'], 403);
        }

        $feedAdj->delete();

        return response()->json(['message' => 'Feed adjustment deleted successfully'], 200);
    }

    public function getFeedAdjustmentDetails($id)
    {
        try {
            // Step 1: Retrieve the FeedStockAdjustment record by 'id' with related Product and Sector
            $feedAdjRecord = FeedStockAdjustment::with([
                'product.category',
                'product.subCategory',
                'product.childCategory',
                'sector'
            ])->find($id);

            if (!$feedAdjRecord) {
                return response()->json(['message' => 'FeedStockAdjustment not found'], 404);
            }

            // Step 2: Get the 'adjId' from the retrieved record
            $adjId = $feedAdjRecord->adjId;

            // Step 3: Retrieve all FeedStockAdjustment records with the same 'adjId' including related Product and Sector
            $adjustments = FeedStockAdjustment::with([
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
            \Log::error('Error in getFeedAdjustmentDetails: ' . $e->getMessage());

            return response()->json([
                'message' => 'An unexpected error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
