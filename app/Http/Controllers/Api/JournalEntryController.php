<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\JournalEntryRequest;
use App\Http\Requests\JournalEntryUpdateFormRequest;
use App\Http\Resources\JournalEntryResource;
use App\Models\AccountLedgerName;
use App\Models\JournalEntry;
use App\Services\AccountsCreditService;
use App\Services\AccountsDebitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JournalEntryController extends Controller
{
  private $accountDebitService, $accountCreditService;

  public function __construct(AccountsDebitService $accountDebit, AccountsCreditService $accountCredit)
  {
    $this->accountDebitService = $accountDebit;
    $this->accountCreditService = $accountCredit;
  }
  public function index(Request $request)
  {
    $oneYearAgo = now()->subYear()->format('Y-m-d');
    $today = today()->format('Y-m-d');

    $voucherNo = $request->voucherNo ?? null;
    $companyId = $request->companyId ?? null;
    $debitHeadId = $request->debitHeadId ?? null;
    $creditHeadId = $request->creditHeadId ?? null;
    $startDate = $request->input('startDate', $oneYearAgo);
    $endDate = $request->input('endDate', $today);
    $status = $request->status ?? null;

    $query = JournalEntry::query();

    // Filter By Voucher No
    if ($voucherNo) {
        $query->where('voucherNo', 'LIKE', '%' . $voucherNo . '%');
      }

    //Filter By Debit Head
    if ($debitHeadId) {
      $query->where('debitHeadId', $debitHeadId);
    }
    //Filter By Credit Head
    if ($creditHeadId) {
      $query->where('creditHeadId', $creditHeadId);
    }
    // Filter by companyId
    if ($companyId) {
      $query->where('companyId', $companyId);
    }

    //filter trDate
    if ($startDate && $endDate) {
      $query->whereBetween('voucherDate', [$startDate, $endDate]);
    }

    // Filter by status
    if (!is_null($status)) {  // Ensure status can be 0
        $query->where('status', $status);
    }

    // Fetch j_entries with eager loading of related data
    $j_entries = $query->latest()->get();

    // Check if any j_entries found
    if ($j_entries->isEmpty()) {
      return response()->json(['message' => 'No Journal Entry found', 'data' => []], 200);
    }

    // Use the JournalEntryResource to transform the data
    $transformedJournalEntries = JournalEntryResource::collection($j_entries);

    // Return Journal Entry transformed with the resource
    return response()->json([
      'message' => 'Success!',
      'data' => $transformedJournalEntries
    ], 200);
  }

  public function store(JournalEntryRequest $request)
  {
    DB::beginTransaction();
    try {
      $dataArray = [];
      foreach ($request->entries as $entry) {
        $journalEntry = new JournalEntry();
        //$journalEntry->voucherNo = $entry['voucherNo'];
        $journalEntry->voucherNo = $entry['voucherNo'] ?? null;
        $journalEntry->voucherDate = $entry['voucherDate'];
        $journalEntry->companyId = $entry['companyId'];
        $journalEntry->debitSubGroupId = $entry['debitSubGroupId'];
        $journalEntry->debitHeadId = $entry['debitHeadId'];
        $journalEntry->debit = $entry['debit'];
        $journalEntry->creditSubGroupId = $entry['creditSubGroupId'];
        $journalEntry->creditHeadId = $entry['creditHeadId'];
        $journalEntry->credit = $entry['credit'];
        $journalEntry->checkNo = $entry['checkNo'];
        $journalEntry->checkDate = $entry['checkDate'];
        $journalEntry->trxId = $entry['trxId'];
        $journalEntry->ref = $entry['ref'];
        $journalEntry->createdBy = auth()->user()->id;
        $journalEntry->note = $entry['note'];
        $journalEntry->save();
        $dataArray[] = new JournalEntryResource($journalEntry);
      }
      DB::commit();
      return response()->json(['message' => 'Journal Entry created successfully', 'data' => $dataArray], 201);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(['message' => $e->getMessage()], 500);
    }
  }


  public function show($id)
  {
    $journal = JournalEntry::find($id);

    if (!$journal) {
      return response()->json(['message' => 'Journal Entry not found'], 404);
    }
    return new JournalEntryResource($journal);
  }

  public function edit($id)
  {
    $journal = JournalEntry::find($id);

    if (!$journal) {
      return response()->json(['message' => 'Journal Entry not found'], 404);
    }
    return response()->json($journal);
  }

  public function update(JournalEntryUpdateFormRequest $request, $id)
  {
    $journal = JournalEntry::find($id);
    if (!$journal) {
      return response()->json(['message' => 'Journal Entry not found'], 404);
    }
    if ($journal->status == 1) {
      return response()->json(['message' => 'Journal Entry already approved'], 400);
    }
    DB::beginTransaction();
    try {
      $journal->voucherDate = $request->voucherDate;
      $journal->companyId = $request->companyId;
      $journal->debitSubGroupId = $request->debitSubGroupId;
      $journal->debitHeadId = $request->debitHeadId;
      $journal->debit = $request->debit;
      $journal->creditSubGroupId = $request->creditSubGroupId;
      $journal->creditHeadId = $request->creditHeadId;
      $journal->credit = $request->credit;
      $journal->checkNo = $request->checkNo;
      $journal->checkDate = $request->checkDate;
      $journal->trxId = $request->trxId;
      $journal->ref = $request->ref;
      $journal->modifiedBy = auth()->user()->id;
      $journal->note = $request->note;
      $journal->save();
      DB::commit();
      return response()->json(['message' => 'Journal Entry updated successfully', 'data' => new JournalEntryResource($journal)], 200);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(['message' => $e->getMessage()], 500);
    }
  }


  public function destroy($id)
  {
    $journal = JournalEntry::find($id);

    if (!$journal) {
      return response()->json(['message' => 'Journal Entry not found'], 404);
    }

    DB::beginTransaction();
    try {
      $journal->deletedBy = auth()->user()->id;
      $journal->save();
      $journal->delete();
      DB::commit();
      return response()->json(['message' => 'Journal Entry deleted successfully'], 200);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(['message' => $e->getMessage()], 500);
    }
  }

  public function updateStatus(Request $request)
  {
    $journal = JournalEntry::find($request->journalId);
    if (!$journal) {
      return response()->json(['message' => 'Journal Entry not found'], 404);
    }
    if ($journal->status == 1) {
      return response()->json(['message' => 'Journal Entry already approved'], 400);
    }
    DB::beginTransaction();
    try {
      $journal->appBy = auth()->user()->id;
      $journal->status = $request->status;
      if ($request->status == 1) {
        $voucherType = 'JournalEntry';
        $voucherDate = $journal->voucherDate;
        $companyId = $journal->companyId;
        $voucherNo = $journal->voucherNo;
        // Debit Logic
        $this->accountDebitService->setDebitData(
          chartOfHeadId: $journal->debitHeadId,
          companyId: $companyId,
          voucherNo: $voucherNo,
          voucherType: $voucherType,
          voucherDate: $voucherDate,
          note: $journal->note,
          debit: $journal->debit,
        );

        // Credit Logic
        $this->accountCreditService->setCreditData(
          chartOfHeadId: $journal->creditHeadId,
          companyId: $companyId,
          voucherNo: $voucherNo,
          voucherType: $voucherType,
          voucherDate: $voucherDate,
          note: $journal->note,
          credit: $journal->credit,
        );

        $debitHead = AccountLedgerName::find($journal->debitHeadId);
        $debitHead->current_balance = $debitHead->current_balance + $journal->debit;
        $debitHead->save();

        $creditHead = AccountLedgerName::find($journal->creditHeadId);
        $creditHead->current_balance = $creditHead->current_balance - $journal->credit;
        $creditHead->save();
      }
      $journal->save();
      DB::commit();
      return response()->json(['message' => 'Journal Entry updated successfully'], 200);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(['message' => $e->getMessage()], 500);
    }
  }


  public function autoGenerateJournalVouNo()
  {
    try {
      $latestId = JournalEntry::max('id');
      $nextId = $latestId ? $latestId + 1 : 1;
      $newVouNo = 'JEI' . date('y') . date('m') . str_pad($nextId, 4, '0', STR_PAD_LEFT);

      return response()->json(['voucherNo' => $newVouNo], 200);
    } catch (\Exception $e) {
      return response()->json(['message' => 'Failed to generate JEI ID: ' . $e->getMessage()], 500);
    }
  }

}
