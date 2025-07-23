<?php

namespace App\Http\Controllers\Api\Chicks;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chicks\ChicksFarmProductionRequest;
use App\Http\Resources\Chicks\ChicksFarmProductionResource;
use App\Models\ChicksFarmProduction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\Log;
use App\Models\ChicksFarmProductionDetails;
use App\Services\CacheService;
use App\Services\ChicksProductionLedgerService;
use App\Services\ChicksStockService;

class ChicksFarmProductionController extends Controller
{

     protected $chicksLedgerService;
     protected $chicksStockService;
     protected $cacheService;


    public function __construct(ChicksProductionLedgerService $chicksLedgerService,ChicksStockService $chicksStockService, CacheService $cacheService)
    {
        $this->chicksLedgerService = $chicksLedgerService;
        $this->chicksStockService = $chicksStockService;
        $this->cacheService = $cacheService;

    }
    public function index(Request $request)
    {
        // Set default start date to two months ago and end date to today
        $startDate = $request->startDate ?? now()->subMonths(2)->startOfMonth()->format('Y-m-d');
        $endDate = $request->endDate ?? now()->endOfMonth()->format('Y-m-d');

        $productionId    = $request->productionId;
        $productId       = $request->productId;
        $hatcheryId        = $request->hatcheryId;
        $status          = $request->status;
        $childCategoryId = $request->childCategoryId;
        $limit           = $request->input('limit', 100); // Default 100 items per page


        // Initialize the query builder
        $query = ChicksFarmProduction::query();

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

         // Filter by hatcheryId
         if (isset($hatcheryId) && $hatcheryId !== '') {
            $query->where('hatcheryId', $hatcheryId);
            $isFiltered = true;
        }

        // Filter by date range (default to last 2 months)
        if ($startDate && $endDate) {
            $query->whereBetween('hatchDate', [$startDate, $endDate]);
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
       // $productions = $query->with(['product', 'childCategory'])->latest()->paginate($limit);
        $productions = $query
        ->with([
            'product',
            'childCategory',
            'details.product',      // 👈 This is important
            'hatchery',
            'flock',
            'createdBy',
            'approvedBy'
        ])
        ->latest()
        ->paginate($limit);
        // Check if any production data was found
        if ($productions->isEmpty()) {
            return response()->json(['message' => 'No Chicks Farm Production found', 'data' => []], 200);
        }

       // Return paginated response
       return response()->json([
        'message' => 'Success!',
        'data' => ChicksFarmProductionResource::collection($productions),
        'meta' => [
            'current_page' => $productions->currentPage(),
            'last_page'    => $productions->lastPage(),
            'per_page'     => $productions->perPage(),
            'total'        => $productions->total(),
        ]
    ], 200);
    }


    public function store(Request $request)
{
    $data = $request->all();
    $details = $data['details'] ?? [];

    DB::beginTransaction();
    try {

        // ✅ Check for existing settingId (even if status is approx)
        $existingSetting = ChicksFarmProduction::where('settingId', $data['settingId'])
        // ->where('status', '!=', 'declined')
        ->whereIn('status', ['approx', 'finalized'])
        ->exists();

    if ($existingSetting) {
        return response()->json([
            'status' => 'error',
            'message' => 'This setting ID has already been used. Please choose another.',
        ], 422);
    }

        // Store main production data
        $production = new ChicksFarmProduction();
        $production->productionId = $data['productionId'] ?? null;
        $production->hatcheryId = $data['hatcheryId'] ?? null;
        $production->settingId = $data['settingId'] ?? null;
        $production->eggSource = $data['eggSource'] ?? null;
        $production->settingDate = $data['settingDate'] ?? null;
        $production->hatchDate = $data['hatchDate'] ?? null;
        $production->breedId = $data['breedId'] ?? null;
        $production->flockId = $data['flockId'] ?? null;
        $production->color = $data['color'] ?? null;
        $production->totalEggSetting = $data['totalEggSetting'] ?? 0;
        $production->note = $data['note'] ?? null;
        $production->crBy = auth()->id();
        $production->status = 'pending';
        $production->save();

        // Store details
        foreach ($details as $detail) {
            ChicksFarmProductionDetails::create([
                'pId' => $production->id,
                'productId' => $detail['productId'] ?? null,
                'settingId' => $detail['settingId'] ?? null,
                'breedId' => $detail['breedId'] ?? null,
                'chicksType' => $detail['chicksType'] ?? null,
                'batchNo' => $detail['batchNo'] ?? null,
                'grade' => $detail['grade'] ?? null,
                'approxQty' => $detail['approxQty'] ?? 0,
                'finalQty' => $detail['finalQty'] ?? 0,
            ]);
        }

        DB::commit();
        return response()->json([
            'status' => 'success',
            'message' => 'Created successfully',
            'data' => $production
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['status' => 'error', 'message' => $e->getMessage()]);


    }
}


    // public function show(string $id)
    // {
    //     $production = ChicksFarmProduction::find($id);
    //     if (!$production) {
    //         return response()->json(['message' => 'Production not found'], 404);
    //     }
    //     return new ChicksFarmProductionResource($production);

    // }

    public function show(string $id)
{
    $production = ChicksFarmProduction::with([
        'details.product',
        'details.breed',
        'hatchery',
        'breed',
        'flock',
        'createdBy',
        'approvedBy'
    ])->find($id);

    if (!$production) {
        return response()->json(['message' => 'Production not found'], 404);
    }

    return response()->json([
        'message' => 'Success!',
        'data' => new ChicksFarmProductionResource($production)
    ]);
}


public function update(Request $request, $id)
{
    $data = $request->all();
    $details = $data['details'] ?? [];


    $production = ChicksFarmProduction::findOrFail($id);


    if (!in_array($production->status, ['pending', 'approx'])) {
        return response()->json([
            'status' => 'error',
            'message' => 'Update not allowed. Production status is not pending or approx.'
        ], 403);
    }

    DB::beginTransaction();
    try {
        $production->update([
            'productionId' => $data['productionId'] ?? $production->productionId,
            'hatcheryId' => $data['hatcheryId'] ?? null,
            'settingId' => $data['settingId'] ?? null,
            'eggSource' => $data['eggSource'] ?? null,
            'settingDate' => $data['settingDate'] ?? null,
            'hatchDate' => $data['hatchDate'] ?? null,
            'breedId' => $data['breedId'] ?? null,
            'flockId' => $data['flockId'] ?? null,
            'color' => $data['color'] ?? null,
            'totalEggSetting' => $data['totalEggSetting'] ?? 0,
            'note' => $data['note'] ?? null,
            'appBy' => auth()->id(),
            'status' => $data['status'] ?? $production->status,
        ]);


        ChicksFarmProductionDetails::where('pId', $id)->delete();

        foreach ($details as $detail) {
            ChicksFarmProductionDetails::create([
                'pId' => $production->id,
                'productId' => $detail['productId'] ?? null,
                'settingId' => $detail['settingId'] ?? null,
                'breedId' => $detail['breedId'] ?? null,
                'chicksType' => $detail['chicksType'] ?? null,
                'batchNo' => $detail['batchNo'] ?? null,
                'grade' => $detail['grade'] ?? null,
                'approxQty' => $detail['approxQty'] ?? 0,
                'finalQty' => $detail['finalQty'] ?? 0,
            ]);
        }

        DB::commit();

        return response()->json([
            'message' => 'Production updated successfully',
            'data' => new ChicksFarmProductionResource($production->load(['details', 'hatchery', 'flock'])),
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
}



    public function destroy($id)
    {
        DB::beginTransaction();
        try{
            $production = ChicksFarmProduction::findOrFail($id);
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

    //
    public function updateStatus(Request $request, $id)
    {
        try {
            // Find the existing production record
            $production = ChicksFarmProduction::findOrFail($id);

            // Validate request
            $request->validate([
                'status' => 'required|string|in:pending,finalized,declined,approx',
                'details' => 'array|required',
                'details.*.id' => 'required|exists:chicks_production_details,id',
                'details.*.approxQty' => 'required|numeric|min:0',
                'details.*.finalQty' => 'required|numeric|min:0',
            ]);

            // Prevent updates if already finalized
            if ($production->status === 'finalized') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The production is already finalized. You cannot update stock or quantities.',
                ], 400);
            }

            // If trying to finalize, check for earlier approx records
            if ($request->status === 'finalized') {
                $hasEarlierApproxRecords = ChicksFarmProduction::where('hatcheryId', $production->hatcheryId)
                    ->where('hatchDate', '<', $production->hatchDate)
                    ->where('status', 'approx')
                    ->exists();

                if ($hasEarlierApproxRecords) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Cannot finalize. There are approximate records for earlier dates.',
                    ], 422);
                }
            }

            // Loop through details
            foreach ($request->details as $detailData) {
                $detail = ChicksFarmProductionDetails::findOrFail($detailData['id']);

                // Always update detail rows (approxQty and finalQty)
                $detail->update([
                    'approxQty' => $detailData['approxQty'],
                    'finalQty'  => $detailData['finalQty'],
                ]);

                // Update stock ONLY if status is approx
                if ($request->status === 'approx') {
                    $this->chicksStockService->ChicksstoreOrUpdateStock(
                        $production->hatcheryId,    // sectorId
                        $detail->productId,         // productId
                        $production->breedId,       // breedId
                        $production->hatchDate,     // stockDate
                        $detailData['approxQty'],   // approxQty
                        $detailData['finalQty'],    // finalQty
                        $detail->batchNo,           // batchNo
                        $request->status,     // stockType
                        'approx'                    // status
                    );
                }elseif($request->status === 'finalized') {
                    $this->chicksStockService->ChicksstoreOrUpdateStock(
                        $production->hatcheryId,    // sectorId
                        $detail->productId,         // productId
                        $production->breedId,       // breedId
                        $production->hatchDate,     // stockDate
                        $detailData['approxQty'],   // approxQty
                        $detailData['finalQty'],    // finalQty
                        $detail->batchNo,           // batchNo
                        $request->status,     // stockType
                        'finalized'                    // status
                    );
                }
                // Create ledger entry in both cases
                $this->chicksLedgerService->createChicksLedgerEntry(
                    $production->hatcheryId,
                    $detail->productId,
                    $production->productionId,
                    $production->breedId,
                    'Hatchery Production',
                    $production->hatchDate,
                    $detailData['approxQty'],
                    $detailData['finalQty'],
                    $detail->batchNo,
                    $request->status // approx or finalized
                );
            }

            // Update production status
            $production->update([
                'status' => $request->status,
                'appBy' => auth()->id(),
            ]);

            // Clear cached data
            $this->cacheService->clearAllCache();

            return response()->json([
                'status' => 'success',
                'message' => 'Production status updated successfully.',
                'data' => $production->load('details'),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating the status.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    //
    //Multi status
    public function updateMultiStatus(Request $request)
    {
        try {
            $request->validate([
                'status' => 'required|string|in:finalized,approx,declined',
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:chicks_farm_productions,id',
                'details' => 'array|required',
                'details.*.id' => 'required|exists:chicks_production_details,id',
                'details.*.approxQty' => 'required|numeric|min:0',
                'details.*.finalQty' => 'required|numeric|min:0',
            ]);

            $status = $request->status;
            $ids = $request->input('ids');
            $unchangedIds = [];
            $invalidDateIds = [];

            // Check if any record is already finalized
            $alreadyFinalized = ChicksFarmProduction::whereIn('id', $ids)
                ->where('status', 'finalized')
                ->pluck('id')
                ->toArray();

            if (!empty($alreadyFinalized)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'One or more records are already finalized and cannot be updated.',
                    'unchanged' => $alreadyFinalized,
                ], 422);
            }

            DB::beginTransaction();

            foreach ($ids as $id) {
                $production = ChicksFarmProduction::findOrFail($id);

                if (is_null($production->hatchDate)) {
                    $invalidDateIds[] = $production->id;
                    continue;
                }

                if ($status === 'finalized') {
                    // Check for earlier approx records
                    $hasEarlierApproxRecords = ChicksFarmProduction::where('hatcheryId', $production->hatcheryId)
                        ->where('hatchDate', '<', $production->hatchDate)
                        ->where('status', 'approx')
                        ->exists();

                    if ($hasEarlierApproxRecords) {
                        DB::rollBack();
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Cannot finalize. There are approximate records for earlier dates.',
                            'hatcheryId' => $production->hatcheryId,
                            'conflicting_date' => $production->hatchDate,
                        ], 422);
                    }
                }

                // Update details
                foreach ($request->details as $detailData) {
                    $detail = ChicksFarmProductionDetails::findOrFail($detailData['id']);

                    // Update approxQty and finalQty
                    $detail->update([
                        'approxQty' => $detailData['approxQty'],
                        'finalQty'  => $detailData['finalQty'],
                    ]);

                    // Update stock
                    $this->chicksStockService->ChicksstoreOrUpdateStock(
                        $production->hatcheryId,    // sectorId
                        $detail->productId,         // productId
                        $production->breedId,       // breedId
                        $production->hatchDate,     // stockDate
                        $detailData['approxQty'],   // approxQty
                        $detailData['finalQty'],    // finalQty
                        $detail->batchNo,           // batchNo
                        $status,                    // stockType
                        $status                     // status
                    );

                    // Create ledger entry
                    $this->chicksLedgerService->createChicksLedgerEntry(
                        $production->hatcheryId,
                        $detail->productId,
                        $production->productionId,
                        $production->breedId,
                        'Hatchery Production',
                        $production->hatchDate,
                        $detailData['approxQty'],
                        $detailData['finalQty'],
                        $detail->batchNo,
                        $status // approx or finalized
                    );
                }

                // Update production status
                $production->update([
                    'status' => $status,
                    'appBy' => auth()->id(),
                ]);
            }

            // Clear cache
            $this->cacheService->clearAllCache();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Status updated successfully for ' . count($ids) . ' record(s)',
                'invalid_dates' => $invalidDateIds,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating multiple statuses.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function getEggSettings(Request $request)
{
    $hatchery = $request->query('hatchery');
    $hatchDate = $request->query('hatchDate');
    $settingID = $request->query('settingID');
    $settingDate = $request->query('settingDate');

    $url = "http://43.224.116.185:2186/sale/flock/eggSettingFinalApi.php?hatchery=$hatchery&hatchDate=$hatchDate&settingID=$settingID&settingDate=$settingDate";

    $client = new \GuzzleHttp\Client();
    $response = $client->get($url);
    $data = json_decode($response->getBody(), true);

    return response()->json($data);
}


//
public function checkSettingId($settingId)
{
    $exists = ChicksFarmProduction::where('settingId', $settingId)
        ->where('status', '!=', 'declined')
        ->exists();

    return response()->json(['exists' => $exists]);
}




}