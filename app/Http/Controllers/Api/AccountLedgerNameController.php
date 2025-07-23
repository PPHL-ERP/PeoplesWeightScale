<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountLedgerNameRequest;
use App\Http\Resources\AccountLedgerNameResource;
use App\Models\AccountLedgerName;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
class AccountLedgerNameController extends Controller
{
    public function index(Request $request)
    {

        $code = $request->code ?? null;
        $subGroupId = $request->subGroupId ?? null;
        $name = $request->name ?? null;

        $query = AccountLedgerName::query();


        // Filter by Code
        if ($code) {
            $query->where('code', 'LIKE', '%' . $code . '%');
        }

        // Filter by subGroupId
        if ($subGroupId) {
            $query->where('subGroupId', $subGroupId);
        }

        // Filter by name
        if ($name) {
            $query->where('name', 'LIKE', '%' . $name . '%');
        }

        // Fetch AccountLedgerName with eager loading of related data
        $acc_ledgers = $query->latest()->get();

        // Check if any AccountLedgerName found
        if ($acc_ledgers->isEmpty()) {
            return response()->json(['message' => 'No Account Ledger Name found', 'data' => []], 200);
        }

        // Use the AccountLedgerNameResource to transform the data
        $transformedAccountLedgerName = AccountLedgerNameResource::collection($acc_ledgers);

        // Return AccountLedgerName transformed with the resource
        return response()->json([
            'message' => 'Success!',
            'data' => $transformedAccountLedgerName
        ], 200);
    }


    //code auto generated
    public function store(AccountLedgerNameRequest $request)
    {
        try {

            $acc_ledger = new AccountLedgerName();
            $acc_ledger->company_id = $request->company_id;
            $acc_ledger->name = $request->name;

            $code = null;

            // switch ($request->classId) {
            //     case 1: // Assets
            //         $code = AccountLedgerName::whereBetween('code', [1000, 1999])->max('code') ?? 999;
            //         $code += 1;
            //         break;
            //     case 2: // Liabilities
            //         $code = AccountLedgerName::whereBetween('code', [2000, 2999])->max('code') ?? 1999;
            //         $code += 1;
            //         break;
            //     case 3: // Income
            //         $code = AccountLedgerName::whereBetween('code', [3000, 3999])->max('code') ?? 2999;
            //         $code += 1;
            //         break;
            //     case 4: // Equity
            //         $code = AccountLedgerName::whereBetween('code', [4000, 4999])->max('code') ?? 3999;
            //         $code += 1;
            //         break;
            //     case 5: // Expanse
            //         $code = AccountLedgerName::where('code', '>=', 5000)->max('code') ?? 4999;
            //         $code += 1;
            //         break;
            //     default:
            //         return response()->json(['message' => 'Invalid account class'], 400);
            // }
            switch ($request->classId) {
                case 1: // Assets
                    $code = AccountLedgerName::whereBetween('code', [1000, 1999])
                        ->whereRaw('code ~ \'^[0-9]+$\'') // PostgreSQL regex for numeric-only codes
                        ->max('code') ?? 999;
                    $code = (int)$code + 1;
                    break;
                case 2: // Liabilities
                    $code = AccountLedgerName::whereBetween('code', [2000, 2999])
                        ->whereRaw('code ~ \'^[0-9]+$\'')
                        ->max('code') ?? 1999;
                    $code = (int)$code + 1;
                    break;
                case 3: // Income
                    $code = AccountLedgerName::whereBetween('code', [3000, 3999])
                        ->whereRaw('code ~ \'^[0-9]+$\'')
                        ->max('code') ?? 2999;
                    $code = (int)$code + 1;
                    break;
                case 4: // Equity
                    $code = AccountLedgerName::whereBetween('code', [4000, 4999])
                        ->whereRaw('code ~ \'^[0-9]+$\'')
                        ->max('code') ?? 3999;
                    $code = (int)$code + 1;
                    break;
                case 5: // Expense
                    $code = AccountLedgerName::where('code', '>=', 5000)
                        ->whereRaw('code ~ \'^[0-9]+$\'')
                        ->max('code') ?? 4999;
                    $code = (int)$code + 1;
                    break;
                default:
                    return response()->json(['message' => 'Invalid account class'], 400);
            }


            $acc_ledger->code = $code;
            $acc_ledger->groupId = $request->groupId;
            $acc_ledger->subGroupId = $request->subGroupId;
            $acc_ledger->classId = $request->classId;
            $acc_ledger->nature = $request->nature;
            $acc_ledger->opening_balance = $request->opening_balance;
            $acc_ledger->current_balance = $request->current_balance;
            $acc_ledger->is_active = strtolower($request->is_active) == 'yes';
            $acc_ledger->is_posting_allowed = strtolower($request->is_posting_allowed) == 'yes';
            $acc_ledger->description = $request->description;
            $acc_ledger->partyId = $request->partyId;
            $acc_ledger->partyType = $request->partyType;

            $acc_ledger->save();
            //dd($acc_ledger);

            return response()->json([
                'message' => 'Account LedgerName created successfully',
                'data' => new AccountLedgerNameResource($acc_ledger),
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    //
    private function getDefaultCode($accountClass)
    {
        switch ($accountClass) {
            case 1: // Assets
                return 1000;
            case 2: // Liabilities
                return 2000;
            case 3: // Income
                return 3000;
            case 4: // Equity
                return 4000;
            case 5: // Expanse
                return 5000;
            default:
                return null;
        }
    }

    public function getDealerDropdown(Request $request)
    {
        $cachedDealers = Cache::get('dealer_dropdown', collect());

        // Fetch latest dealers from the database (excluding cached dealers)
        $latestDealers = AccountLedgerName::where('account_ledger_name.partyType', 'D')
            ->where('account_ledger_name.is_active', true)
            ->whereNotIn('account_ledger_name.id', $cachedDealers->pluck('id'))
            ->leftJoin('dealers', DB::raw('dealers.id::bigint'), '=', DB::raw('account_ledger_name."partyId"::bigint'))  // Join with dealers table
            ->select([
                'account_ledger_name.id',
                'account_ledger_name.name',
                'dealers.dealerCode',
                'dealers.tradeName',
                'dealers.zoneId',
                'dealers.phone',
                'dealers.contactPerson'
            ])
            ->orderBy('account_ledger_name.name')
            ->get();

        // Merge cached and latest dealer records
        $dealers = $cachedDealers->merge($latestDealers);

        // If new dealers are fetched, update the cache
        if ($latestDealers->isNotEmpty()) {
            Cache::put('dealer_dropdown', $dealers, 600);  // Cache for 10 minutes
        }

        return response()->json([
            'message' => 'Dealer List Retrieved!',
            'data' => $dealers,
        ], 200);
    }





    public function getBankDropdown(Request $request)
    {
        $cachedBanks = Cache::get('bank_dropdown', collect());

        $latestBanks = AccountLedgerName::where('partyType', 'B')
            ->where('is_active', true)
            ->whereNotIn('id', $cachedBanks->pluck('id'))
            ->leftJoin('companies', 'companies.id', '=', 'account_ledger_name.company_id')
            ->select([
                'account_ledger_name.id',
                'account_ledger_name.name',
                'companies.nameEn as companyName'
            ])
            ->orderBy('account_ledger_name.name')
            ->get();

        $banks = $cachedBanks->merge($latestBanks);

        if ($latestBanks->isNotEmpty()) {
            Cache::put('bank_dropdown', $banks, 600);
        }

        return response()->json([
            'message' => 'Bank List Retrieved!',
            'data' => $banks,
        ], 200);
    }


    public function show($id)
    {
        $acc_ledger = AccountLedgerName::find($id);

        if (!$acc_ledger) {
            return response()->json(['message' => 'Account LedgerName not found'], 404);
        }
        return new AccountLedgerNameResource($acc_ledger);
    }

    public function update(AccountLedgerNameRequest $request, $id)
    {
        try {

            $acc_ledger = AccountLedgerName::find($id);

            if (!$acc_ledger) {
                return $this->sendError('Account LedgerName not found.');
            }

            $acc_ledger->company_id = $request->company_id;
            $acc_ledger->name = $request->name;
            $acc_ledger->code = $request->code;
            $acc_ledger->groupId = $request->groupId;
            $acc_ledger->subGroupId = $request->subGroupId;
            $acc_ledger->classId = $request->classId;
            $acc_ledger->nature = $request->nature;
            $acc_ledger->opening_balance = $request->opening_balance;
            $acc_ledger->current_balance = $request->current_balance;
            $acc_ledger->is_active = strtolower($request->is_active) == 'yes';
            $acc_ledger->is_posting_allowed = strtolower($request->is_posting_allowed) == 'yes';
            $acc_ledger->description = $request->description;
            $acc_ledger->partyId = $request->partyId;
            $acc_ledger->partyType = $request->partyType;
            $acc_ledger->update();

            return response()->json([
                'message' => 'Account LedgerName Updated successfully',
                'data' => new AccountLedgerNameResource($acc_ledger),
            ], 200);
        } catch (\Exception $e) {
            // Handle the exception here
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $acc_ledger = AccountLedgerName::find($id);
        if (!$acc_ledger) {
            return response()->json(['message' => 'Account LedgerName not found'], 404);
        }

        $acc_ledger->delete();
        return response()->json([
            'message' => 'Account LedgerName deleted successfully',
        ], 200);
    }


    // auto code generated api frontend
    public function getAutoCode(Request $request)
    {
        $classId = $request->input('classId');
        $code = null;

        switch ($classId) {
            case 1:
                $code = AccountLedgerName::whereBetween('code', [1000, 1999])->max('code') ?? 999;
                $code += 1;
                break;
            case 2:
                $code = AccountLedgerName::whereBetween('code', [2000, 2999])->max('code') ?? 1999;
                $code += 1;
                break;
            case 3:
                $code = AccountLedgerName::whereBetween('code', [3000, 3999])->max('code') ?? 2999;
                $code += 1;
                break;
            case 4:
                $code = AccountLedgerName::whereBetween('code', [4000, 4999])->max('code') ?? 3999;
                $code += 1;
                break;
            case 5:
                $code = AccountLedgerName::where('code', '>=', 5000)->max('code') ?? 4999;
                $code += 1;
                break;
            default:
                return response()->json(['message' => 'Invalid classId'], 400);
        }

        return response()->json(['code' => $code], 200);
    }

    //gett sales revenue heads
    public function getSalesRevenueHeads(Request $request)
    {
        $sales_revenue_heads = AccountLedgerName::where(['subGroupId' => 21, 'is_active' => true, 'company_id' => $request->companyId])->get(['id', 'name', 'code']);
        return response()->json(['data' => $sales_revenue_heads], 200);
    }

// only partyType D data
public function getDLedgerNames()
{
    $accountLedgers = DB::table('account_ledger_name')
        ->where('partyType', 'D')
        ->orderBy('id', 'desc')
        ->get();

    return response()->json([
        'message' => 'Success!',
        'data' => $accountLedgers
    ]);
}


// only partyType D data with egg

public function getEggDLedgerNames()
{
    $accountLedgers = DB::table('account_ledger_name as al')
        ->join('dealers as d', 'al.name', '=', 'd.tradeName')
        ->where('al.partyType', 'D')
        ->where('d.dealerGroup', 'Egg')
        ->select('al.*', 'd.id as dealerId', 'd.tradeName')
        ->orderBy('al.id', 'desc')
        ->get();

    return response()->json([
        'message' => 'Success!',
        'data' => $accountLedgers
    ]);
}

// only partyType D data with feed
public function getFeedDLedgerNames()
{
    $accountLedgers = DB::table('account_ledger_name as al')
        ->join('dealers as d', 'al.name', '=', 'd.tradeName')
        ->where('al.partyType', 'D')
        // ->where('d.dealerGroup', 'Feed')
        ->whereIn('d.dealerGroup', ['Feed', 'Feed And Chicks'])
        ->select('al.*', 'd.id as dealerId', 'd.tradeName')
        ->orderBy('al.id', 'desc')
        ->get();

    return response()->json([
        'message' => 'Success!',
        'data' => $accountLedgers
    ]);
}

}
