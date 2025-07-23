<?php

namespace App\Http\Controllers\Api\Chicks;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chicks\ChicksStockAdjustmentRequest;
use App\Http\Resources\Chicks\ChicksStockAdjustmentResource;
use App\Models\ChicksStockAdjustment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\UploadAble;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use App\Services\CacheService;
use App\Services\ChicksProductionLedgerService;
use App\Services\ChicksStockService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Api\Log;

class ChicksStockAdjustmentController extends Controller
{
    protected $ledgerService;
    protected $chicksStockService;
    protected $cacheService;

    /**
     * Inject services into the controller.
     *
     * @param  ChicksProductionLedgerService  $ledgerService
     * @param  ChicksStockService  $chicksStockService
     * @param  CacheService  $cacheService
     * @return void
     */
    public function __construct(ChicksProductionLedgerService $ledgerService, ChicksStockService $chicksStockService, CacheService $cacheService)
    {
        $this->ledgerService = $ledgerService;
        $this->chicksStockService = $chicksStockService;
        $this->cacheService = $cacheService;
    }

    use  UploadAble;
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


        $query = ChicksStockAdjustment::query();

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
            'data' => ChicksStockAdjustmentResource::collection($adjustments),
            'meta' => [
                'current_page' => $adjustments->currentPage(),
                'last_page'    => $adjustments->lastPage(),
                'per_page'     => $adjustments->perPage(),
                'total'        => $adjustments->total(),
            ]
        ], 200);
    }

    public function store(ChicksStockAdjustmentRequest $request)
    {
        DB::beginTransaction();
        try {
            $salesPoint = $request->salesPoint;
            $adjustmentDate = $request->adjustmentDate;
            $adjCategory = $request->adjCategory;
            $referenceId = $request->referenceId;
            $referenceType = $request->referenceType;
            $breedId = $request->breedId;

            $adjIds = [];

            foreach ($request->fields as $field) {
                $adjId = 'CAJ' . now()->format('y') . now()->format('m') . str_pad(ChicksStockAdjustment::max('id') + 1, 4, '0', STR_PAD_LEFT);

                // Handle image upload once
                $image = null;
                if ($request->hasFile('image')) {
                    $image = $this->uploadOne($request->file('image'), 300, 300, config('imagepath.adjustment'));
                }

                // First product adjustment
                ChicksStockAdjustment::create([
                    'adjId' => $adjId,
                    'sectorId' => $salesPoint,
                    'breedId' => $breedId,
                    'productId' => $field['firstProductId'],
                    'date' => $adjustmentDate,
                    'initialQty' => $field['originalFirstProductStock'],
                    'adjQty' => $field['adjustStock'],
                    'finalQty' => $field['originalFirstProductStock'] - $field['adjustStock'],
                    'adjType' => $field['firstProductAdjustmentType'],
                    'adjCategory' => $adjCategory,
                    'referenceId' => $referenceId,
                    'referenceType' => $referenceType,
                    'batchNo' => $field['batchNo'],
                    'note' => $field['remarks'],
                    'image' => $image,
                    'crBy' => auth()->id(),
                    'status' => 'pending',
                ]);

                // Second product adjustment
                ChicksStockAdjustment::create([
                    'adjId' => $adjId,
                    'sectorId' => $salesPoint,
                    'productId' => $field['secondProductId'],
                    'breedId' => $breedId,
                    'date' => $adjustmentDate,
                    'initialQty' => $field['originalSecondProductStock'],
                    'adjQty' => $field['adjustStock'],
                    'finalQty' => $field['originalSecondProductStock'] + $field['adjustStock'],
                    'adjType' => $field['secondProductAdjustmentType'],
                    'adjCategory' => $adjCategory,
                    'referenceId' => $referenceId,
                    'referenceType' => $referenceType,
                    'batchNo' => $field['batchNo'],
                    'note' => $field['remarks'],
                    'image' => $image,
                    'crBy' => auth()->id(),
                    'status' => 'pending',
                ]);

                $adjIds[] = $adjId;
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Chicks adjustments created successfully.',
                'adjIds' => $adjIds,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while creating the chicks adjustment.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function show(string $adjId): AnonymousResourceCollection|JsonResponse
    {
        // Retrieve all rows matching the adjId with eager loading for relationships
        $chicksAdjs = ChicksStockAdjustment::where('adjId', $adjId)
            ->with(['sector', 'product', 'createdBy', 'approvedBy']) // Eager load relationships
            ->orderBy('id', 'asc') // Order rows by ID
            ->get();

        // Check if records exist
        if ($chicksAdjs->isEmpty()) {
            return response()->json(['message' => 'Chicks Adjustments not found'], 404);
        }

        // Return the collection of resources
        return ChicksStockAdjustmentResource::collection($chicksAdjs);
    }


    public function update(Request $request, string $adjId)
    {
        DB::beginTransaction();
        try {
            $chicksAdjustments = ChicksStockAdjustment::where('adjId', $adjId)->orderBy('id')->get();

            if ($chicksAdjustments->isEmpty()) {
                return response()->json(['error' => 'Chicks adjustments not found'], 404);
            }

            if ($chicksAdjustments->contains('status', 'approved')) {
                return response()->json(['error' => 'Approved adjustments cannot be updated'], 403);
            }

            if ($chicksAdjustments->count() !== 2) {
                return response()->json(['error' => 'Invalid adjustment data. Exactly two rows are required.'], 400);
            }

            $firstAdjustment = $chicksAdjustments[0];
            $secondAdjustment = $chicksAdjustments[1];

            // Handle image upload (optional)
            $image = $firstAdjustment->image; // retain existing image
            if ($request->hasFile('image')) {
                $image = $this->uploadOne($request->file('image'), 300, 300, config('imagepath.adjustment'));
            }

            // Update first product adjustment
            $firstAdjustment->update([
                'sectorId' => $request->salesPoint,
                'productId' => $request->fields[0]['firstProductId'],
                'breedId' => $request->breedId,
                'date' => $request->adjustmentDate,
                'initialQty' => $request->fields[0]['originalFirstProductStock'],
                'adjQty' => $request->fields[0]['adjustStock'],
                'finalQty' => $request->fields[0]['originalFirstProductStock'] - $request->fields[0]['adjustStock'],
                'adjType' => $request->fields[0]['firstProductAdjustmentType'],
                'adjCategory' => $request->adjCategory,
                'referenceId' => $request->referenceId ?? null,
                'referenceType' => $request->referenceType ?? null,
                'batchNo' => $request->fields[0]['batchNo'],
                'note' => $request->fields[0]['remarks'],
                'image' => $image,
                'status' => 'pending',
                'appBy' => auth()->id(),
            ]);

            // Update second product adjustment
            $secondAdjustment->update([
                'sectorId' => $request->salesPoint,
                'productId' => $request->fields[0]['secondProductId'],
                'breedId' => $request->breedId,
                'date' => $request->adjustmentDate,
                'initialQty' => $request->fields[0]['originalSecondProductStock'],
                'adjQty' => $request->fields[0]['adjustStock'],
                'finalQty' => $request->fields[0]['originalSecondProductStock'] + $request->fields[0]['adjustStock'],
                'adjType' => $request->fields[0]['secondProductAdjustmentType'],
                'adjCategory' => $request->adjCategory,
                'referenceId' => $request->referenceId ?? null,
                'referenceType' => $request->referenceType ?? null,
                'batchNo' => $request->fields[0]['batchNo'],
                'note' => $request->fields[0]['remarks'],
                'image' => $image,
                'status' => 'pending',
                'appBy' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Chicks adjustments updated successfully.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update chicks adjustments.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateStatus(Request $request, $adjId)
    {
        DB::beginTransaction();
        try {
            // Retrieve all adjustments with the same adjId, ordered by ID
            $chicksAdjustments = ChicksStockAdjustment::where('adjId', $adjId)
                ->orderBy('id', 'asc') // Ensure first ID is processed first
                ->get();

            if ($chicksAdjustments->isEmpty()) {
                return response()->json(['message' => 'Feed adjustments not found'], 404);
            }

            if ($chicksAdjustments->count() !== 2) {
                return response()->json(['message' => 'Exactly two adjustments are required for this operation.'], 400);
            }

            foreach ($chicksAdjustments as $feedAdj) {
                // Check if the adjustment is already approved
                if ($feedAdj->status === 'approved') {
                    return response()->json(['message' => 'Approved adjustments cannot be modified.'], 403);
                }
            }

            // Process the first row (Deduction)
            $deductionAdj = $chicksAdjustments[0];
            $deductionAdj->status = $request->status;
            $deductionAdj->appBy = auth()->id();

            // Deduction: Adjust stock and create ledger entry
            $this->chicksStockService->ChicksstoreOrUpdateStockdeDuction(
                $deductionAdj->sectorId,
                $deductionAdj->productId,
                $deductionAdj->breedId,
                $deductionAdj->date,
                $deductionAdj->adjQty,
                $deductionAdj->adjQty,
                $deductionAdj->batchNo,
                'adjustment',
                'approx'
            );

            $this->ledgerService->createChicksStockAdjLedgerEntry(
                $deductionAdj->sectorId,                         // hatcheryId
                $deductionAdj->productId,     // productId
                $deductionAdj->adjId,         // transactionId (string e.g., "CAJ25070001")
                $deductionAdj->breedId,       // breedId
                'StockAdjustment',            // trType
                $deductionAdj->date,          // date
                $deductionAdj->adjQty,        // qty
                $deductionAdj->batchNo,       // batchNo
                'Chicks adjustment - Stock deduction'
            );


            $deductionAdj->save(); // Save changes to Deduction adjustment

            // Process the second row (Addition)
            $additionAdj = $chicksAdjustments[1];
            $additionAdj->status = $request->status;
            $additionAdj->appBy = auth()->id();

            // Addition: Adjust stock and create ledger entry
            $this->chicksStockService->ChicksstoreOrUpdateStock(
                $additionAdj->sectorId,
                $additionAdj->productId,
                $additionAdj->breedId,
                $additionAdj->date,
                $additionAdj->adjQty,
                $additionAdj->adjQty,
                $additionAdj->batchNo,
                'adjustment',
                'approx'
            );

            $this->ledgerService->createChicksStockAdjLedgerEntryAdd(
                $additionAdj->sectorId,                       // hatcheryId
                $additionAdj->productId,      // productId
                $additionAdj->adjId,          // transactionId (string)
                $additionAdj->breedId,        // breedId
                'StockAdjustment',            // trType
                $additionAdj->date,           // date
                $additionAdj->adjQty,         // qty
                $additionAdj->batchNo,        // batchNo
                'Chicks adjustment - Stock addition'
            );

            $additionAdj->save(); // Save changes to Addition adjustment

            // Clear cache
            $this->cacheService->clearAllCache();

            DB::commit();

            return response()->json(['message' => 'Chicks adjustment status updated successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update Chicks Adjustment status and stock.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $chicksAdj = ChicksStockAdjustment::find($id);

        if (!$chicksAdj) {
            return response()->json(['message' => 'Chicks adjustment not found'], 404);
        }

        if ($chicksAdj->status === 'approved') {
            return response()->json(['message' => 'Approved adjustments cannot be deleted'], 403);
        }

        $chicksAdj->delete();

        return response()->json(['message' => 'Chicks adjustment deleted successfully'], 200);
    }


    public function getChicksAdjustmentDetails($id)
    {
        try {
            // Step 1: Retrieve the ChicksStockAdjustment record by 'id' with related Product and Sector
            $chicksAdjRecord = ChicksStockAdjustment::with([
                'product.category',
                'product.subCategory',
                'product.childCategory',
                'sector'
            ])->find($id);

            if (!$chicksAdjRecord) {
                return response()->json(['message' => 'ChicksStockAdjustment not found'], 404);
            }

            // Step 2: Get the 'adjId' from the retrieved record
            $adjId = $chicksAdjRecord->adjId;

            // Step 3: Retrieve all FeedStockAdjustment records with the same 'adjId' including related Product and Sector
            $adjustments = ChicksStockAdjustment::with([
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
            \Log::error('Error in getChicksAdjustmentDetails: ' . $e->getMessage());

            return response()->json([
                'message' => 'An unexpected error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
