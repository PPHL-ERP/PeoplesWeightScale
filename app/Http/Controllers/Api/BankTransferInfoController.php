<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BankTransferInfoRequest;
use App\Http\Resources\BankTransferInfoResource;
use App\Models\AccountLedgerName;
use App\Models\BankTransferInfo;
use App\Services\AccountsCreditService;
use App\Services\AccountsDebitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BankTransferInfoController extends Controller
{

  private $accountCreditService, $accountDebitService;

  public function __construct(AccountsCreditService $accountCredit, AccountsDebitService $accountDebit)
  {
    $this->accountCreditService = $accountCredit;
    $this->accountDebitService = $accountDebit;
  }


  public function index(Request $request)
  {
    $oneYearAgo = now()->subYear()->format('Y-m-d');
    $today = today()->format('Y-m-d');

    $btrId = $request->btrId ?? null;
    $modeOfTransfer = $request->modeOfTransfer ?? null;
    $startDate = $request->input('startDate', $oneYearAgo);
    $endDate = $request->input('endDate', $today);
    $status = $request->status ?? null;

    $query = BankTransferInfo::query();

    // Filter by btrId
    if ($btrId) {
        $query->where('btrId', 'LIKE', '%' . $btrId . '%');
      }

    // Filter by transferMode
    if ($modeOfTransfer) {
      $query->where('modeOfTransfer', operator: $modeOfTransfer);
    }

    //filter transactionDate
    if ($startDate && $endDate) {
      $query->whereBetween('transactionDate', [$startDate, $endDate]);
    }

    // Filter by status
    if ($status) {
      $query->where('status', $status);
    }

    // Fetch bank_transfers with eager loading of related data
    $bt_infos = $query->latest()->get();

    // Check if any bank_transfers found
    if ($bt_infos->isEmpty()) {
      return response()->json(['message' => 'No Bank Transfers found', 'data' => []], 200);
    }

    // Use the BankTransferInfoResource to transform the data
    $transformedBankTransfers = BankTransferInfoResource::collection($bt_infos);

    // Return bank Transfers transformed with the resource
    return response()->json([
      'message' => 'Success!',
      'data' => $transformedBankTransfers
    ], 200);
  }

  // public function store(Request $request)
  // {
  //     $data = $request->all();

  //     // Validate the incoming payload
  //     $validator = Validator::make($data, [
  //         '*.bankTransferId' => 'required|string',
  //         '*.company' => 'required|integer',
  //         '*.sector' => 'required|integer',
  //         '*.head' => 'required|integer',
  //         '*.transactionId' => 'nullable|string',
  //         '*.fromBankId' => 'required|integer',
  //         '*.toBankId' => 'required|integer',
  //         '*.transactionDate' => 'required|date',
  //         '*.transferPurpose' => 'nullable|string',
  //         '*.transferMode' => 'nullable|string',
  //         '*.transferType' => 'nullable|string',
  //         '*.note' => 'nullable|string',
  //         '*.chequeNo' => 'nullable|string',
  //         '*.chequeDate' => 'nullable|date',
  //         '*.amount' => 'required|numeric|min:0',
  //     ]);

  //     if ($validator->fails()) {
  //         return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
  //     }

  //     try {
  //         $records = [];

  //         foreach ($data as $item) {
  //             $bt_info = new BankTransferInfo();
  //             $bt_info->btrId = $item['bankTransferId'];
  //             $bt_info->companyId = $item['company'];
  //             $bt_info->sectorId = $item['sector'];
  //             $bt_info->headId = $item['head'];
  //             $bt_info->transactionId = $item['transactionId'];
  //             $bt_info->bankIdFrom = $item['fromBankId'];
  //             $bt_info->bankIdTo = $item['toBankId'];
  //             $bt_info->transactionDate = $item['transactionDate'];
  //             $bt_info->transferType = $item['transferType'];
  //             $bt_info->trPurpose = $item['transferPurpose'];
  //             $bt_info->modeOfTransfer = $item['transferMode'];
  //             $bt_info->amount = $item['amount'];
  //             $bt_info->chequeNo = $item['chequeNo'];
  //             $bt_info->chequeDate = $item['chequeDate'];
  //             $bt_info->note = $item['note'];
  //             $bt_info->entryBy = auth()->id();
  //             $bt_info->status = 'pending';

  //             $bt_info->save();

  //             // Collect saved records for response
  //             $records[] = new BankTransferInfoResource($bt_info);
  //         }

  //         return response()->json([
  //             'message' => 'Bank transfer records created successfully',
  //             'data' => $records,
  //         ], 201);
  //     } catch (\Exception $e) {
  //         return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
  //     }
  // }



  public function store(Request $request)
  {
    $data = $request->all();

    $validator = Validator::make(['transfers' => $data], [
      'transfers.*.bankTransferId' => 'nullable|string',
      // 'transfers.*.company' => 'required|integer',
      // 'transfers.*.sector' => 'required|integer',
      // 'transfers.*.head' => 'required|integer',
      'transfers.*.company' => 'nullable',
      'transfers.*.sector' => 'nullable',
      'transfers.*.head' => 'nullable',
      'transfers.*.transactionId' => 'nullable|string',
      'transfers.*.fromBankId' => 'required|integer',
      'transfers.*.toBankId' => 'required|integer',
      'transfers.*.transactionDate' => 'required|date',
      'transfers.*.transferPurpose' => 'nullable|string',
      'transfers.*.transferMode' => 'nullable|string',
      'transfers.*.transferType' => 'nullable|string',
      'transfers.*.note' => 'nullable|string',
      'transfers.*.chequeNo' => 'nullable|string',
      'transfers.*.chequeDate' => 'nullable|date',
      'transfers.*.amount' => 'required|numeric|min:0',
    ]);

    if ($validator->fails()) {
      return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
    }

    try {
      $records = [];

      foreach ($data as $item) {
        $bt_info = new BankTransferInfo();
        $bt_info->btrId = $item['bankTransferId'] ?? null;
        $bt_info->companyId = $item['company'];
        $bt_info->sectorId = $item['sector'];
        $bt_info->headId = $item['head'];
        $bt_info->transactionId = $item['transactionId'] ?? null;
        $bt_info->bankIdFrom = $item['fromBankId'];
        $bt_info->bankIdTo = $item['toBankId'];
        $bt_info->transactionDate = $item['transactionDate'];
        $bt_info->transferType = $item['transferType'] ?? null;
        $bt_info->trPurpose = $item['transferPurpose'] ?? null;
        $bt_info->modeOfTransfer = $item['transferMode'] ?? null;
        $bt_info->amount = $item['amount'];
        $bt_info->chequeNo = $item['chequeNo'] ?? null;
        $bt_info->chequeDate = $item['chequeDate'] ?? null;
        $bt_info->note = $item['note'] ?? null;
        $bt_info->entryBy = auth()->id();
        $bt_info->status = 'pending';

        $bt_info->save();
        $records[] = new BankTransferInfoResource($bt_info);
      }

      return response()->json([
        'message' => 'Bank transfer records created successfully',
        'data' => $records,
      ], 201);
    } catch (\Exception $e) {
      return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
    }
  }

  public function show($id)
  {
    $bt_info = BankTransferInfo::find($id);

    if (!$bt_info) {
      return response()->json(['message' => 'BankTransferInfo not found'], 404);
    }
    return new BankTransferInfoResource($bt_info);
  }

  public function update(BankTransferInfoRequest $request, $id)
  {
    try {

      $bt_info = BankTransferInfo::find($id);

      if (!$bt_info) {
        return $this->sendError('BankTransferInfo not found.');
      }

      // Validation status is 'approved'
      if ($bt_info->status === 'approved') {
        return response()->json([
          'message' => 'Updates are not allowed BankTransferInfo all ready approved.'
        ], 403);
      }

      $bt_info->companyId = $request->companyId;
      $bt_info->sectorId = $request->sectorId;
      $bt_info->headId = $request->headId;
      $bt_info->transactionId = $request->transactionId;
      $bt_info->bankIdFrom = $request->bankIdFrom;
      $bt_info->bankIdTo = $request->bankIdTo;
      $bt_info->transactionDate = $request->transactionDate;
      $bt_info->transferType = $request->transferType;
      $bt_info->trPurpose = $request->trPurpose;
      $bt_info->modeOfTransfer = $request->modeOfTransfer;
      $bt_info->amount = $request->amount;
      $bt_info->chequeNo = $request->chequeNo;
      $bt_info->chequeDate = $request->chequeDate;
      $bt_info->note = $request->note;
      $bt_info->status = 'pending';

      $bt_info->update();

      return response()->json([
        'message' => 'BankTransferInfo Updated successfully',
        'data' => new BankTransferInfoResource($bt_info),
      ], 200);
    } catch (\Exception $e) {
      // Handle the exception here
      return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
    }
  }

  public function statusUpdate(Request $request, $id)
  {
    $bt_info = BankTransferInfo::find($id);

    if (!$bt_info) {
      return response()->json([
        'message' => 'BankTransferInfo not found.'
      ], 404);
    }

    // Validation status is 'approved'
    if ($bt_info->status === 'approved') {
      return response()->json([
        'message' => 'Status cannot be changed BankTransferInfo all ready approved.'
      ], 403);
    }

    if ($request->status === 'approved') {
      // $bt_info->bankIdFrom = $request->bankIdFrom;
      // $bt_info->bankIdTo = $request->bankIdTo;

      $fromBankLedger = AccountLedgerName::where(['partyType' => 'B', 'partyId' => $bt_info->bankIdFrom])->first();
      $toBankLedger = AccountLedgerName::where(['partyType' => 'B', 'partyId' => $bt_info->bankIdTo])->first();

      // Debit Logic
      $this->accountDebitService->setDebitData(
        chartOfHeadId: $toBankLedger->id,
        companyId: $bt_info->companyId ?? 0,
        voucherNo: $bt_info->btrId,
        voucherType: 'BankTransfer',
        voucherDate: $bt_info->transactionDate,
        note: 'Bank Transfer approved debit entry',
        debit: $bt_info->amount
      );

      // Credit Logic
      $this->accountCreditService->setCreditData(
        chartOfHeadId: $fromBankLedger->id,
        companyId: $bt_info->companyId ?? 0,
        voucherNo: $bt_info->btrId,
        voucherType: 'BankTransfer',
        voucherDate: $bt_info->transactionDate,
        note: 'Bank Transfer approved credit entry',
        credit: $bt_info->amount
      );

      $fromBankLedger->current_balance = $fromBankLedger->current_balance + $bt_info->amount;

      $toBankLedger->current_balance = $toBankLedger->current_balance - $bt_info->amount;

      $fromBankLedger->update();
      $toBankLedger->update();
    }

    $bt_info->status = $request->status;
    $bt_info->appBy = auth()->id();

    $bt_info->update();
    return response()->json([
      'message' => 'Bank Transfer Info Status change successfully',
    ], 200);
  }
  public function destroy($id)
  {
    $bt_info = BankTransferInfo::find($id);
    if (!$bt_info) {
      return response()->json(['message' => 'BankTransferInfo not found'], 404);
    }
    $bt_info->delete();
    return response()->json([
      'message' => 'BankTransferInfo deleted successfully',
    ], 200);
  }

  public function autoGenerateNextBtrId()
  {
    try {
      $latestId = BankTransferInfo::max('id');
      $nextId = $latestId ? $latestId + 1 : 1;
      $newBtrId = 'BTR' . date('y') . date('m') . str_pad($nextId, 4, '0', STR_PAD_LEFT);

      return response()->json(['btrId' => $newBtrId], 200);
    } catch (\Exception $e) {
      return response()->json(['message' => 'Failed to generate BTR ID: ' . $e->getMessage()], 500);
    }
  }
}