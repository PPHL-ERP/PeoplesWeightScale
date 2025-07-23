<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BankListRequest;
use App\Http\Resources\BankListResource;
use App\Models\AccountLedgerName;
use App\Models\BankList;
use App\Services\AddAccountLedgerService;
use Illuminate\Http\Request;
use App\Services\CacheService;

use function PHPUnit\Framework\isEmpty;

class BankListController extends Controller
{
  private $addCashBankAccount;
  protected $cacheService;

  public function __construct(AddAccountLedgerService $accountLedgerService,CacheService $cacheService)
  {
    $this->addCashBankAccount = $accountLedgerService;
    $this->cacheService = $cacheService;

  }
  public function indexbb(Request $request)
  {
    $bankName = $request->bankName ?? null;
    $bankBranch = $request->bankBranch ?? null;
    $accountNo = $request->accountNo ?? null;
    $bankaAccountType = $request->bankaAccountType ?? null;
    $status = $request->status ?? null;

    $query = BankList::query();

    // Filter by bankName
    if ($bankName) {
      $query->where('bankName', $bankName);
    }
    // Filter by bankBranch
    if ($bankBranch) {
      $query->orWhere('bankBranch', $bankBranch);
    }

    // Filter by accountNo
    if ($accountNo) {
      $query->orWhere('accountNo', $accountNo);
    }

     // Filter by accountType
     if ($bankaAccountType) {
        $query->orWhere('bankaAccountType', $bankaAccountType);
      }

    // Filter by status
    if ($status) {
      $query->where('status', $status);
    }

    $bank_lists = $query->latest()->get();

    // Check if any BankLists found
    if ($bank_lists->isEmpty()) {
      return response()->json(['message' => 'No BankList found', 'data' => []], 200);
    }

    // Use the BankListResource to transform the data
    $transformedBankLists = BankListResource::collection($bank_lists);

    // Return paginated terminations transformed with the resource
    return response()->json([
      'message' => 'Success!',
      'data' => $transformedBankLists
    ], 200);
  }

  public function index(Request $request)
{
    $bankName = $request->bankName ?? null;
    $bankBranch = $request->bankBranch ?? null;
    $accountNo = $request->accountNo ?? null;
    $bankaAccountType = $request->bankaAccountType ?? null;
    $status = $request->status ?? null;

    $query = BankList::query();

    // Apply filters
    if ($bankName) {
        $query->where('bankName', $bankName);
    }

    if ($bankBranch) {
        $query->where('bankBranch', $bankBranch);
    }

    if ($accountNo) {
        $query->where('accountNo', $accountNo);
    }

    if ($bankaAccountType) {
        $query->where('bankaAccountType', $bankaAccountType);
    }

    if ($status) {
        $query->where('status', $status);
    }

    // Join with account_ledger_name where partyType is 'B'
    $query->leftJoin('account_ledger_name', function ($join) {
        $join->on('bank_lists.id', '=', 'account_ledger_name.partyId')
             ->where('account_ledger_name.partyType', 'B');
    });

    // Select required fields including party details
    $query->select(
        'bank_lists.*',
        'account_ledger_name.name as party_name',
        'account_ledger_name.current_balance as party_current_balance'
    );

    // Get results
    $bank_lists = $query->latest()->get();

    // Check if any records exist
    if ($bank_lists->isEmpty()) {
        return response()->json(['message' => 'No BankList found', 'data' => []], 200);
    }

    // Use Resource for response
    return response()->json([
        'message' => 'Success!',
        'data' => BankListResource::collection($bank_lists)
    ], 200);
}

  public function store(BankListRequest $request)
  {
    $bank_list = new BankList();
    $bank_list->fill($request->all());
    $bank_list->crBy = auth()->id();
    $bank_list->status = 'active';
    // $subgroup = 5; //5,6,7
    if ($request->isCash == 'Yes') {
      $subgroup = 6;
    } elseif($request->isMobileBanking == 'Yes') {
      $subgroup = 7;
    } else{
        $subgroup = 5;
    }
    $code = AccountLedgerName::whereBetween('code', [1000, 1999])->max('code') ?? 999;
    $code += 1;
    $bank_list->save();

    $this->addCashBankAccount->addAccountLedger($request->shortName . '(' . $request->accountNo . ')', $code, 2, $subgroup, 'Cash Bank Account', $bank_list->companyId, 1, 'Debit', $request->openingBalance, $request->openingBalance, true, true, $bank_list->id, 'B');
    $this->cacheService->clearAllCache();

    return response()->json([
      'message' => 'Bank List created successfully',
      'data' => new BankListResource($bank_list),
    ], 200);
  }


  public function show($id)
  {
    $bank_list = BankList::find($id);
    if (!$bank_list) {
      return response()->json(['message' => 'Bank List not found'], 404);
    }
    return new BankListResource($bank_list);
  }


  public function update(BankListRequest $request, $id)
  {
    try {
      $bank_list = BankList::find($id);
      if (!$bank_list) {
        return $this->sendError('BankList not found.');
      }
      $bank_list->fill($request->all());
      $bank_list->crBy = auth()->id();


      $bank_list->update();

      return response()->json([
        'message' => 'Bank List Updated successfully',
        'data' => new BankListResource($bank_list),
      ], 200);
    } catch (\Exception $e) {
      return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
    }
  }


  public function statusUpdate(Request $request, $id)
{
    $bank_list = BankList::find($id);

    // If the bank list doesn't exist, return an error response
    if (!$bank_list) {
        return response()->json([
            'message' => 'Bank List not found.',
        ], 404);
    }

    // Update the status
    $bank_list->status = $request->status;

    // Check if the record does not already have an account ledger
    $existingLedger = AccountLedgerName::where('referenceId', $bank_list->id)
        // ->where('type', 'B')
        ->first();

    if (!$existingLedger) {
        // Determine subgroup based on isCash
        $subgroup = $bank_list->isCash == 'true' ? 6 : 7;

        // Calculate the next available code
        $code = AccountLedgerName::whereBetween('code', [1000, 1999])->max('code') ?? 999;
        $code += 1;

        // Add a new account ledger using the existing method
        $this->addCashBankAccount->addAccountLedger(
            $bank_list->shortName . '(' . $bank_list->accountNo . ')',
            $code,
            2, // Group
            $subgroup,
            'Cash Bank Account', // Description
            $bank_list->companyId,
            1, // Is active
            'Debit', // Type
            0, // Opening balance
            0, // Closing balance
            true, // Is visible
            true, // Is sub ledger
            $bank_list->id, // Reference ID
            'B' // Type
        );
    }

    $bank_list->update();
    $this->cacheService->clearAllCache();

    return response()->json([
        'message' => 'Bank List Status changed successfully',
    ], 200);
}


  public function destroy($id)
  {
    $bank_list = BankList::find($id);
    if (!$bank_list) {
      return response()->json(['message' => 'Bank List not found'], 404);
    }
    $bank_list->delete();
    return response()->json([
      'message' => 'Bank List deleted successfully',
    ], 200);
  }

  // public function getBankList()
  // {
  //   $approveBankList = BankList::where('status', 'active')
  //     ->select('id', 'bankName',)
  //     ->get();
  //   return response()->json([
  //     'data' => $approveBankList
  //   ], 200);
  // }

  public function getBankList()
  {
    $approveBankList = BankList::with(['company'])
      ->where('status', 'active')
      ->select('id', 'bankName','shortName', 'companyId')
      ->get();

    $approveBankList = $approveBankList->map(function ($bList) {
      return [
        'id' => $bList->id,
        'bankName' => $bList->bankName,
        'shortName' => $bList->shortName,
        'company' => [
          'id' => $bList->company->id ?? null,
          'nameEn' => $bList->company->nameEn ?? null,
        ],
      ];
    });

    return response()->json([
      'data' => $approveBankList
    ], 200);
  }
}