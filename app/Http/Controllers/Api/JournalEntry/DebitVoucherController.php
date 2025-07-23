<?php

namespace App\Http\Controllers\Api\JournalEntry;

use App\Http\Controllers\Controller;
use App\Http\Resources\DebitVoucherItemResource;
use App\Http\Resources\DebitVoucherResource;
use App\Models\AccountLedgerName;
use App\Models\DebitVoucher;
use App\Models\DebitVoucheritem;
use App\Services\AccountsCreditService;
use App\Services\AccountsDebitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DebitVoucherController extends Controller
{
    private $accountDebit;
    private $accountCredit;
    public function __construct(AccountsDebitService $accountDebit, AccountsCreditService $accountCredit)
    {
        $this->accountDebit = $accountDebit;
        $this->accountCredit = $accountCredit;
    }
    public function index()
    {
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $dV = new DebitVoucher();
            $dV->voucherNo = $request->voucherNo;
            $dV->voucherDate = $request->voucherDate;
            $dV->companyId = $request->companyId;
            $dV->creditHeadId = $request->creditHeadId;
            $dV->amount = $request->amount;

            $dV->checkNo = $request->checkNo;
            $dV->checkDate = $request->checkDate;
            $dV->trxId = $request->trxId;
            $dV->ref = $request->ref;
            $dV->note = $request->note;
            $dV->createdBy = auth()->user()->id;
            $dV->save();
            $voucherId = $dV->id;
            if (isset($request->debitItem) && is_array($request->debitItem) && count($request->debitItem) > 0) {
                foreach ($request->debitItem as $detail) {
                    if (isset($detail['itemHeadId']) && isset($detail['amount'])) {
                        $dVItem = new DebitVoucheritem();
                        $dVItem->debitVoucherId = $voucherId;
                        $dVItem->itemHeadId = $detail['itemHeadId'];
                        $dVItem->amount = $detail['amount'];
                        $dVItem->save();
                    } else {
                        // \Log::warning('Missing sale ID in sale order detail:', $detail);
                    }
                }
            } else {
                // \Log::warning('Sale order is missing or invalid for payment index ' . $key);
            }
            DB::commit();
            return response()->json([
                'message' => 'Payment Receive Info created successfully',
                'data' => new DebitVoucherResource($dV),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $dV = DebitVoucher::find($id);
            $dV->voucherNo = $request->voucherNo;
            $dV->voucherDate = $request->voucherDate;
            $dV->companyId = $request->companyId;
            $dV->creditHeadId = $request->creditHeadId;
            $dV->amount = $request->amount;

            $dV->checkNo = $request->checkNo;
            $dV->checkDate = $request->checkDate;
            $dV->trxId = $request->trxId;
            $dV->ref = $request->ref;
            $dV->note = $request->note;
            $dV->modifiedBy = auth()->user()->id;
            $dV->save();
            $voucherId = $id;
            DebitVoucheritem::where('debitVoucherId', $voucherId)->delete();
            if (isset($request->debitItem) && is_array($request->debitItem) && count($request->debitItem) > 0) {
                foreach ($request->debitItem as $detail) {
                    if (isset($detail['itemHeadId']) && isset($detail['amount'])) {
                        $dVItem = new DebitVoucheritem();
                        $dVItem->debitVoucherId = $voucherId;
                        $dVItem->itemHeadId = $detail['itemHeadId'];
                        $dVItem->amount = $detail['amount'];
                        $dVItem->save();
                    } else {
                        // \Log::warning('Missing sale ID in sale order detail:', $detail);
                    }
                }
            } else {
                // \Log::warning('Sale order is missing or invalid for payment index ' . $key);
            }
            DB::commit();
            return response()->json([
                'message' => 'Payment Receive Info updated successfully',
                'data' => 'not tested',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $dv = DebitVoucher::with('itemList')->find($id);
        if (!$dv) {
            return response()->json(['message' => 'Data not found'], 404);
        }
        if ($dv->status == 1) {
            return response()->json(['message' => 'Approved voucher can not be updated'], 400);
        }
        $dv->status = $request->status;
        if ($request->status == 1) {
            // Credit Logic
            $this->accountCredit->setCreditData(
                chartOfHeadId: $dv->creditHeadId, // Example chart of head for credit (replace with correct ID)
                companyId: $dv->companyId,
                voucherNo: $dv->voucherNo,
                voucherType: 'DebitVoucher',
                voucherDate: $dv->voucherDate,
                note: 'Debit Voucher approved credit entry',
                credit: $dv->amount
            );

            $paymentHead = AccountLedgerName::find($dv->creditHeadId);
            //Debit Logic
            foreach ($dv->itemList as $item) {
                $companyId = $dv->companyId;
                $voucherNo = $dv->voucherNo;
                $voucherType = 'DebitVoucher';
                $voucherDate = $dv->voucherDate;
                $this->accountDebit->setDebitData(
                    chartOfHeadId: $item->itemHeadId,
                    companyId: $companyId,
                    voucherNo: $voucherNo,
                    voucherType: $voucherType,
                    voucherDate: $voucherDate,
                    note: 'Debit Voucher approved debit entry',
                    debit: $item->amount
                );

            }
            
            $paymentHead->update([
                'current_balance' => $paymentHead->current_balance - $dv->amount
            ]);
        }
        $dv->appBy = auth()->user()->id;
        $dv->save();
        return response()->json(['message' => 'Data updated successfully', 'data' => new DebitVoucherResource($dv)], 200);
    }

    public function destroy(Request $request, $id)
    {
        $dv = DebitVoucher::with('itemList')->find($id);
        if (!$dv) {
            return response()->json(['message' => 'Data not found'], 404);
        }
        if ($dv->status == 1) {
            return response()->json(['message' => 'Approved voucher can not be deleted'], 400);
        }
        DB::beginTransaction();
        try {
            $dv->deletedBy = auth()->user()->id;
            $dv->itemList()->delete();
            $dv->save();
            $dv->delete();
            DB::commit();
            return response()->json(['message' => 'Voucher deleted successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}
