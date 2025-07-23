<?php

namespace App\Http\Controllers\Api;

use App\Models\Dealer;
use Illuminate\Http\Request;
use App\Http\Requests\DealerRequest;
use App\Http\Resources\DealerResource;
use App\Http\Controllers\Controller;
use App\Models\AccountLedgerName;
use App\Services\AddAccountLedgerService;
use Illuminate\Support\Facades\DB;
use App\Services\CacheService;

class DealerController extends Controller
{
  private $dealerAccountLedger;
  protected $cacheService;

  public function __construct(AddAccountLedgerService $dealerAccountLedger,CacheService $cacheService)
  {
    $this->dealerAccountLedger = $dealerAccountLedger;
    $this->cacheService = $cacheService;
  }

  public function index(Request $request)
  {

    $dealerCode = $request->dealerCode ?? null;
    $bnEnName = $request->bnEnName ?? null;
    $phone = $request->phone ?? null;
    $zoneId = $request->zoneId ?? null;
    $districtId = $request->districtId ?? null;
    $dealerType = $request->dealerType ?? null;
    $dealerGroup = $request->dealerGroup ?? null;
    $status = $request->status ?? null;


    $query = Dealer::query();

    // Filter by dealerCode
    if ($dealerCode) {
      $query->where('dealerCode', 'LIKE', '%' . $dealerCode . '%');
    }

    //Filter by tradeName
    if (!empty($bnEnName)) {
      $query->where(function ($query) use ($bnEnName) {
        $query->where('tradeName', 'LIKE', '%' . $bnEnName . '%')
          ->orWhere('tradeNameBn', 'LIKE', '%' . $bnEnName . '%');
      });
    }
    //Filter by phone
    if ($phone) {
      $query->where('phone', 'LIKE', '%' . $phone . '%');
    }

    //Filter by zoneId
    if ($zoneId) {
      $query->where('zoneId', $zoneId);
    }

    //Filter by districtId
    if ($districtId) {
      $query->where('districtId', $districtId);
    }

    //Filter by dealerType
    if ($dealerType) {
      $query->where('dealerType', 'LIKE', '%' . $dealerType . '%');
    }

    //Filter by dealerGroup
    if ($dealerGroup) {
      $query->where('dealerGroup', 'LIKE', '%' . $dealerGroup . '%');
    }
    // Filter by status
    if ($status) {
      $query->where('status', $status);
    }

    // Fetch dealers with eager loading of related data
    $dealers = $query->latest()->get();

    // Check if any dealers found
    if ($dealers->isEmpty()) {
      return response()->json(['message' => 'No Dealer found', 'data' => []], 200);
    }

    // Use the DealerResource to transform the data
    $transformedDealers = DealerResource::collection($dealers);

    // Return dealers transformed with the resource
    return response()->json([
      'message' => 'Success!',
      'data' => $transformedDealers
    ], 200);
  }

  public function store(DealerRequest $request)
  {
    DB::beginTransaction();
    try {
      $dealer = new Dealer();
      $dealer->dealerCode = $request->dealerCode;
      $dealer->dealerType = $request->dealerType;
      $dealer->tradeName = $request->tradeName;
      $dealer->tradeNameBn = $request->tradeNameBn;
      $dealer->contactPerson = $request->contactPerson;
      $dealer->address = $request->address;
      $dealer->addressBn = $request->addressBn;
      $dealer->shippingAddress = $request->shippingAddress;
      $dealer->zoneId = $request->zoneId;
      $dealer->divisionId = $request->divisionId;
      $dealer->districtId = $request->districtId;
      $dealer->upazilaId = $request->upazilaId;
      $dealer->phone = $request->phone;
      $dealer->email = $request->email;
      $dealer->tradeLicenseNo = $request->tradeLicenseNo;
      $dealer->isDueable = strtolower($request->isDueable) == 'yes';
      $dealer->dueLimit = $request->dueLimit;
      $dealer->referenceBy = $request->referenceBy;
      $dealer->salesPerson = $request->salesPerson;
      $dealer->openingBalance = $request->openingBalance;
      $dealer->guarantor = $request->guarantor;
      $dealer->guarantorPerson = $request->guarantorPerson;
      $dealer->guarantorByCheck = json_encode($request->guarantorByCheck);
      $dealer->dealerGroup = $request->dealerGroup;
      $dealer->crBy = auth()->id();
      $dealer->status = 'inactive';

      $dealer->save();
      $this->cacheService->clearAllCache();

    //   $this->dealerAccountLedger->addAccountLedger($request->tradeName, $dealer->dealerCode, 2, 2, 'Receivable Account', null, 1, 'Debit', 0, 0,true, true, $dealer->id, 'D');
      DB::commit();
      // dd($dealer);
      return response()->json([
        'message' => 'Dealer created successfully',
        'data' => new DealerResource($dealer),
      ], 200);
    } catch (\Exception $e) {
      DB::rollBack();
      // Handle the exception here
      return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
    }
  }


  public function show($id)
  {
    $dealer = Dealer::find($id);

    if (!$dealer) {
      return response()->json(['message' => 'Dealer not found'], 404);
    }
    return new DealerResource($dealer);
  }

  public function update(DealerRequest $request, $id)
  {
      DB::beginTransaction();
      try {
          $dealer = Dealer::find($id);

          if (!$dealer) {
              return response()->json(['message' => 'Dealer not found.'], 404);
          }

          $oldTradeName = $dealer->tradeName;

          $dealer->dealerType = $request->dealerType;
          $dealer->tradeName = $request->tradeName;
          $dealer->tradeNameBn = $request->tradeNameBn;
          $dealer->contactPerson = $request->contactPerson;
          $dealer->address = $request->address;
          $dealer->addressBn = $request->addressBn;
          $dealer->shippingAddress = $request->shippingAddress;
          $dealer->zoneId = $request->zoneId;
          $dealer->divisionId = $request->divisionId;
          $dealer->districtId = $request->districtId;
          $dealer->upazilaId = $request->upazilaId !== null ? (is_numeric($request->upazilaId) ? (int) $request->upazilaId : null) : null;
          $dealer->phone = $request->phone;
          $dealer->email = $request->email;
          $dealer->tradeLicenseNo = $request->tradeLicenseNo;
          $dealer->isDueable = strtolower($request->isDueable) == 'yes';
          $dealer->dueLimit = $request->dueLimit;
          $dealer->referenceBy = $request->referenceBy;
          $dealer->salesPerson = $request->salesPerson;
          $dealer->openingBalance = $request->openingBalance;
          $dealer->guarantor = $request->guarantor;
          $dealer->guarantorPerson = $request->guarantorPerson;
          $dealer->guarantorByCheck = json_encode($request->guarantorByCheck);
          $dealer->dealerGroup = $request->dealerGroup;
          $dealer->status = $request->status;

          $dealer->update();
          $this->cacheService->clearAllCache();

          // ✅ Update ledger name if tradeName changed
          if ($oldTradeName !== $dealer->tradeName) {
              AccountLedgerName::where('partyId', $dealer->id)
                  ->where('partyType', 'D')
                  ->update(['name' => $dealer->tradeName]);
          }

          $ledger = AccountLedgerName::where('partyId', $dealer->id)
          ->where('partyType', 'D')
          ->first();



         // ✅ Update opening balance in PF or FF
            $dealerOpening = floatval($dealer->openingBalance ?? 0);

            if (in_array($dealer->dealerGroup, ['Feed And Chicks', 'Feed'])) {
                $pfLedger = AccountLedgerName::where('name', 'Opening Balance (PF)')
                    ->where('id', 287)->first();

                if ($pfLedger) {
                    $pfLedger->current_balance += $dealerOpening;
                    $pfLedger->save();
                }
            }

            if ($dealer->dealerGroup === 'Egg') {
                $ffLedger = AccountLedgerName::where('name', 'Opening Balance (FF)')
                    ->where('id', 68)->first();

                if ($ffLedger) {
                    $ffLedger->current_balance += $dealerOpening;
                    $ffLedger->save();
                }
            }
      if ($ledger && ($ledger->opening_balance === null || $ledger->opening_balance == 0)) {
          $dealerOpening = floatval($dealer->openingBalance ?? 0);
          $ledger->opening_balance = $dealerOpening;
          $ledger->current_balance = floatval($ledger->current_balance) + $dealerOpening;
          $ledger->save();
      }


          // ✅ Create ledger if not exists and dealer is active
          if ($dealer->status === 'active' && !$ledger) {
              $this->dealerAccountLedger->addAccountLedger(
                  $dealer->tradeName,
                  $dealer->dealerCode,
                  2, 2, 'Receivable Account',
                  null, 1, 'Debit', 0, 0,
                  true, true, $dealer->id, 'D'
              );
          }

          DB::commit();

          return response()->json([
              'message' => 'Dealer Updated successfully',
              'data' => new DealerResource($dealer),
          ], 200);
      } catch (\Exception $e) {
          DB::rollBack();
          return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
      }
  }




  public function statusUpdate(Request $request, $id)
{
    DB::beginTransaction();
    try {
        $dealer = Dealer::find($id);

        if (!$dealer) {
            return response()->json(['message' => 'Dealer not found.'], 404);
        }

        // if ($dealer->status === 'active') {
        //     return response()->json(['message' => 'Status is already active and cannot be changed.'], 403);
        // }
        $dealer->status = $request->status;
        $dealer->appBy = auth()->id();
        $dealer->update();
        $this->cacheService->clearAllCache();

        // ✅ If status now becomes active, add ledger if not exists
        if ($dealer->status === 'active') {
            $existingLedger = AccountLedgerName::where('partyId', $dealer->id)
                ->where('partyType', 'D')
                ->first();

            if (!$existingLedger) {
                $this->dealerAccountLedger->addAccountLedger(
                    $dealer->tradeName,
                    $dealer->dealerCode,
                    2, 2, 'Receivable Account',
                    null, 1, 'Debit', $dealer->openingBalance, 0,
                    true, true, $dealer->id, 'D'
                );
            }

            // ✅ Update opening balance in PF or FF
            $dealerOpening = floatval($dealer->openingBalance ?? 0);

            if (in_array($dealer->dealerGroup, ['Feed And Chicks', 'Feed'])) {
                $pfLedger = AccountLedgerName::where('name', 'Opening Balance (PF)')
                    ->where('id', 287)->first();

                if ($pfLedger) {
                    $pfLedger->current_balance += $dealerOpening;
                    $pfLedger->save();
                }
            }

            if ($dealer->dealerGroup === 'Egg') {
                $ffLedger = AccountLedgerName::where('name', 'Opening Balance (FF)')
                    ->where('id', 68)->first();

                if ($ffLedger) {
                    $ffLedger->current_balance += $dealerOpening;
                    $ffLedger->save();
                }
            }
        }

        DB::commit();

        return response()->json([
            'message' => 'Dealer Status changed successfully',
        ], 200);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
    }
}


  public function destroy($id)
  {
    $dealer = Dealer::find($id);
    if (!$dealer) {
      return response()->json(['message' => 'Dealer not found'], 404);
    }
    $dealer->delete();
    return response()->json([
      'message' => 'Dealer deleted successfully',
    ], 200);
  }

  //   public function getDealList()
  // {
  //   $dealList = Dealer::where('status', 'active')
  //     ->select('id','tradeName','dealerCode','contactPerson','phone','zoneId')
  //     ->get();
  //   return response()->json([
  //     'data' => $dealList
  //   ], 200);
  // }
  public function getDealList()
  {
    $dealList = Dealer::where('status', 'active')
      ->with('zone:id,zoneName')
      ->select('id', 'tradeName', 'dealerCode', 'contactPerson', 'phone','address','zoneId')
      ->get();

    $dealList->transform(function ($dealer) {
      $dealer->zoneName = $dealer->zone->zoneName ?? null;
      unset($dealer->zoneId);
      return $dealer;
    });

    return response()->json([
      'data' => $dealList
    ], 200);
  }

  public function getDealerBalance($id)
  {
    $dealer = Dealer::find($id);
    if (!$dealer) {
      return response()->json(['message' => 'Dealer not found'], 404);
    }
    $balance = AccountLedgerName::where(['partyId' => $id, 'partyType' => 'D'])->value('current_balance');
    return response()->json([
      'balance' => $balance,
    ], 200);
  }

  // Only Egg group dealers show
  public function getEggDealList()
{
    $eggDealList = Dealer::where('status', 'active')
        ->where('dealerGroup', 'Egg')
        ->with('zone:id,zoneName')
        ->select('id', 'tradeName', 'dealerCode', 'contactPerson', 'phone', 'address', 'zoneId')
        ->get();

    $eggDealList->transform(function ($dealer) {
        $dealer->zoneName = $dealer->zone->zoneName ?? null;
        unset($dealer->zoneId);
        return $dealer;
    });

    return response()->json([
        'data' => $eggDealList
    ], 200);
}

// Only Feed group dealers show
// public function getFeedDealList()
// {
//     $feedDealList = Dealer::where('status', 'active')
//         ->where('dealerGroup', 'Feed')
//         ->with('zone:id,zoneName')
//         ->select('id', 'tradeName', 'dealerCode', 'contactPerson', 'phone', 'address', 'zoneId')
//         ->get();

//     $feedDealList->transform(function ($dealer) {
//         $dealer->zoneName = $dealer->zone->zoneName ?? null;
//         unset($dealer->zoneId);
//         return $dealer;
//     });

//     return response()->json([
//         'data' => $feedDealList
//     ], 200);
// }

//with Feed And Chicks
public function getFeedDealList()
{
    $feedDealList = Dealer::where('status', 'active')
        ->whereIn('dealerGroup', ['Feed', 'Feed And Chicks'])
        ->with('zone:id,zoneName')
        ->select('id', 'tradeName', 'dealerCode', 'contactPerson', 'phone', 'address', 'zoneId')
        ->get();

    $feedDealList->transform(function ($dealer) {
        $dealer->zoneName = $dealer->zone->zoneName ?? null;
        unset($dealer->zoneId);
        return $dealer;
    });

    return response()->json([
        'data' => $feedDealList
    ], 200);
}


}
