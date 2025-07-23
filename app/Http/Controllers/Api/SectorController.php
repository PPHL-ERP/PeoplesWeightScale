<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SectorRequest;
use App\Http\Resources\SectorResource;
use App\Models\Sector;
use App\Models\UserManagesSectors;
use App\Traits\SectorFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\AccountLedgerName;
use App\Services\AddAccountLedgerService;
use App\Services\CacheService;
use Illuminate\Support\Facades\DB;

class SectorController extends Controller
{
    use SectorFilter;
    private $dealerAccountLedger;
    protected $cacheService;
    public function __construct(AddAccountLedgerService $dealerAccountLedger,CacheService $cacheService)
    {
      $this->dealerAccountLedger = $dealerAccountLedger;
      $this->cacheService = $cacheService;
    }
    public function index()
    {
        // $query = Sector::query();
        // $query = $this->applySectorFilter($query);
        // $sectors = $query->get();

        // if($sectors->isEmpty()){
        //     return response()->json(['message' => 'No Sector found'], 200);
        // }
        // return SectorResource::collection($sectors);

        $sectors = Sector::latest()->get();

       if($sectors->isEmpty()){
            return response()->json(['message' => 'No Sector found'], 200);
        }

        return SectorResource::collection($sectors);
    }

    public function store(SectorRequest $request)
    {
        $sector = Sector::create([
            'name' => $request->name,
            'companyId' => $request->companyId,
            'isFarm' => $request->isFarm,
            'isSalesPoint' => $request->isSalesPoint,
            'salesPointName' => $request->salesPointName,
            'feedDepotCost' => $request->feedDepotCost,
            'chicksDepotCost' => $request->chicksDepotCost,
            'sectorType' => $request->sectorType,
            'inchargeName' => $request->inchargeName,
            'inchargePhone' => $request->inchargePhone,
            'inchargeAddress' => $request->inchargeAddress,
            'description' => $request->description,
            'status' => 'pending',
        ]);
        // $userManagedSector = UserManagesSectors::create([
        //     'sectorId' => $sector->id,
        //     'userId' => '4',
        // ]);

        // $userManagedSector = UserManagesSectors::create([
        //     'sectorId' => $sector->id,
        //     'userId' => '2',
        // ]);

        return response()->json([
            'message' => 'Sector created successfully',
            'data' => new SectorResource($sector),
        ],200);
    }


    public function show($id)
    {
        $sector = Sector::find($id);
        if (!$sector) {
            return response()->json(['message' => 'Sector not found'], 404);
        }
        return new SectorResource($sector);
    }


    public function updateold(SectorRequest $request, $id)
    {
        $sector = Sector::find($id);
        if (!$sector) {
            return response()->json(['message' => 'Sector not found'], 404);
        }
        $sector->update([
            'name' => $request->name,
            'companyId' => $request->companyId,
            'isFarm' => $request->isFarm,
            'isSalesPoint' => $request->isSalesPoint,
            'salesPointName' => $request->salesPointName,
            'feedDepotCost' => $request->feedDepotCost,
            'chicksDepotCost' => $request->chicksDepotCost,
            'description' => $request->description,
            'status' => 'pending',
        ]);
        return response()->json([
            'message' => 'Sector updated successfully',
            'data' => new SectorResource($sector),
        ],200);
    }

    public function update(SectorRequest $request, $id)
{
    DB::beginTransaction();
    try {
        $sector = Sector::find($id);

        if (!$sector) {
            return response()->json(['message' => 'Sector not found'], 404);
        }

        $sector->update([
            'name' => $request->name,
            'companyId' => $request->companyId,
            'isFarm' => $request->isFarm,
            'isSalesPoint' => $request->isSalesPoint,
            'salesPointName' => $request->salesPointName,
            'feedDepotCost' => $request->feedDepotCost,
            'chicksDepotCost' => $request->chicksDepotCost,
            'sectorType' => $request->sectorType,
            'inchargeName' => $request->inchargeName,
            'inchargePhone' => $request->inchargePhone,
            'inchargeAddress' => $request->inchargeAddress,
            'description' => $request->description,
            'status' => 'pending', // Default set again pending
        ]);

        // 🔥 Clear cache
        $this->cacheService->clearAllCache();

        // 🔥 Create SalesPoint Ledger if needed
        if ($sector->isSalesPoint) {
            $existingLedger = AccountLedgerName::where('partyId', $sector->id)
                ->where('partyType', 'S')
                ->first();

            if (!$existingLedger) {
                $this->dealerAccountLedger->addAccountLedger(
                    $sector->salesPointName .' Charge' ,
                    $sector->id,
                    6, 29, $sector->salesPointName. ' Sales Depot Income',
                    2, 3, 'Credit', 0, 0,
                    true, true, $sector->id, 'S'
                );
            } else {
                // 🔥 If salesPointName changed, update ledger name also
                $existingLedger->update(['ledgerName' => $sector->salesPointName]);
            }
        }

        DB::commit();

        return response()->json([
            'message' => 'Sector updated successfully',
            'data' => new SectorResource($sector),
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
    }
}

    public function statusUpdateold(Request $request,$id){
        $sector = Sector::find($id);
        if (!$sector) {
            return response()->json(['message' => 'Sector not found'], 404);
        }
        $sector->status = $request->status;
        $sector->update();
        return response()->json([
            'message' => 'Sector Status change successfully',
        ],200);
    }
    public function statusUpdate(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $sector = Sector::find($id);

            if (!$sector) {
                return response()->json(['message' => 'Sector not found'], 404);
            }

            $sector->status = $request->status;
            $sector->update();

            // 🔥 Clear cache
            $this->cacheService->clearAllCache();

            // 🔥 If isSalesPoint and status becomes active, create ledger if missing
            if ($sector->isSalesPoint && $sector->status === 'approved') {
                $existingLedger = AccountLedgerName::where('partyId', $sector->id)
                    ->where('partyType', 'S')
                    ->first();

                if (!$existingLedger) {
                    $this->dealerAccountLedger->addAccountLedger(
                        $sector->salesPointName .' Charge' ,
                        $sector->id,
                        6, 29, $sector->salesPointName. ' Sales Depot Income',
                        2, 3, 'Credit', 0, 0,
                        true, true, $sector->id, 'S'
                    );
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Sector status changed successfully',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }


    public function destroy($id)
    {
        $sector = Sector::find($id);
        if (!$sector) {
            return response()->json(['message' => 'Sector not found'], 404);
        }
        UserManagesSectors::where('sectorId', $id)->delete();
        $sector->delete();
        return response()->json([
            'message' => 'Sector Deleted Successfully',
        ],200);
    }





        // All approved sectors (with farm and salesPoint info)
    public function getSectorList()
        {
            $approvedSectors = Cache::remember('sector_list_all', 9200, function () {
                return Sector::approved()
                    ->select('id', 'name', 'isFarm', 'isSalesPoint', 'salesPointName','feedDepotCost')
                    ->get();
            });

            return response()->json(['data' => $approvedSectors], 200);
        }

        public function getSectorFilterList()
        {
            $userId = auth()->id();
            $canPass = $this->adminFilter($userId);

            if ($canPass) {
                // Admin: Return all approved sectors from cache
                $approvedSectors = Cache::remember('sector_list_all', 8200, function () {
                    return Sector::approved()
                        ->select('id', 'name', 'isFarm', 'isSalesPoint', 'salesPointName','feedDepotCost')
                        ->get();
                });
            } else {
                // Non-admin: Only return sectors managed by user
                $sectorIds = \App\Models\UserManagesSectors::where('userId', $userId)->pluck('sectorId')->toArray();

                $approvedSectors = Sector::approved()
                    ->whereIn('id', $sectorIds)
                    ->select('id', 'name', 'isFarm', 'isSalesPoint', 'salesPointName','feedDepotCost')
                    ->get();
            }

            return response()->json(['data' => $approvedSectors], 200);
        }

        // Only Sales Points
        public function getSalesPointList()
        {
            $salesPoints = Cache::remember('sector_list_sales_points', 7200, function () {
                return Sector::salesPoints()
                    ->select('id', 'name', 'isSalesPoint', 'salesPointName', 'feedDepotCost')
                    ->get();
            });

            return response()->json(['data' => $salesPoints], 200);
        }

        // Only Farms
        public function getFarmList()
        {
            $farms = Cache::remember('sector_list_farms', 7200, function () {
                return Sector::farms()
                    ->select('id', 'name', 'isFarm')
                    ->get();
            });

            return response()->json(['data' => $farms], 200);
        }


//     public function getSectorList(Request $request)
//   {
//     $mealProvider = $request->mealProvider;
//     $salesPoint = $request->salesPoint;

//     $svc = new \App\Services\MealProviderConsumerService();

//     if ($mealProvider) {
//       // Query mealproviders with their consumers list => make a service class method
//       $sectors = $svc->getProvidersOptionsList();
//       return response()->json([
//         'data' => $sectors
//       ], 200);
//     }

//     if ($salesPoint) {

//     }

//     $approvedSectors = Sector::where('status', 'approved')
//       ->select('id', 'name', 'isFarm','isSalesPoint','salesPointName',)
//       ->get();

//     return response()->json([
//       'data' => $approvedSectors
//     ], 200);
//   }

public function getSalesPointFilterList()
{
    $userId = auth()->id();
    $canPass = $this->adminFilter($userId);

    if ($canPass) {
        // Admin: Return all approved sectors from cache (only sales points)
            $approvedSectors = Cache::remember('sector_list_sales_points', 7200, function () {

            return Sector::approved()
                ->where('isSalesPoint', 1)
                ->select('id', 'name', 'isSalesPoint', 'salesPointName', 'feedDepotCost')
                ->get();
        });
    } else {
        // Non-admin: Only return sectors managed by user (only sales points)
        $sectorIds = \App\Models\UserManagesSectors::where('userId', $userId)->pluck('sectorId')->toArray();

        $approvedSectors = Sector::approved()
            ->where('isSalesPoint', 1)
            ->whereIn('id', $sectorIds)
            ->select('id', 'name', 'isSalesPoint', 'salesPointName', 'feedDepotCost')
            ->get();
    }

    return response()->json(['data' => $approvedSectors], 200);
}


public function getFarmFilterList()
{
    $userId = auth()->id();
    $canPass = $this->adminFilter($userId);

    if ($canPass) {
        // Admin: Return all approved sectors from cache (only Farm)
            $approvedSectors = Cache::remember('sector_list_farms', 7200, function () {

            return Sector::approved()
                ->where('isFarm', 1)
                ->select('id', 'name', 'isFarm','feedDepotCost')
                ->get();
        });
    } else {
        // Non-admin: Only return sectors managed by user (only sales points)
        $sectorIds = \App\Models\UserManagesSectors::where('userId', $userId)->pluck('sectorId')->toArray();

        $approvedSectors = Sector::approved()
            ->where('isFarm', 1)
            ->whereIn('id', $sectorIds)
            ->select('id', 'name', 'isFarm','feedDepotCost')
            ->get();
    }

    return response()->json(['data' => $approvedSectors], 200);
}


public function getSectorTypeFilterList()
{
    $userId = auth()->id();
    $canPass = $this->adminFilter($userId);

    if ($canPass) {
        // Admin: Return all approved "Chicks" sectors from cache
        $approvedSectors = Cache::remember('sector_list_chicks', 7200, function () {
            return Sector::approved()
                ->where('sectorType', 'Chicks') // ✅ Only Chicks
                ->select('id', 'name', 'feedDepotCost')
                ->get();
        });
    } else {
        // Non-admin: Only return user-managed "Chicks" sectors
        $sectorIds = \App\Models\UserManagesSectors::where('userId', $userId)->pluck('sectorId')->toArray();

        $approvedSectors = Sector::approved()
            ->whereIn('id', $sectorIds)
            ->where('sectorType', 'Chicks') // ✅ Only Chicks
            ->select('id', 'name', 'feedDepotCost')
            ->get();
    }

    return response()->json(['data' => $approvedSectors], 200);
}


}