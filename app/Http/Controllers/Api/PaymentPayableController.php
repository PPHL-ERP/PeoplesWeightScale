<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentPayableFormRequest;
use App\Http\Requests\PaymentPayableUpdateFormRequest;
use App\Http\Resources\PaymentPayableInfoResource;
use App\Models\AccountLedgerName;
use App\Models\InvoiceWisePaymentPayable;
use App\Models\PaymentPayableInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\InvoiceWisePaymentReceive;
use App\Services\AccountsCreditService;
use App\Services\AccountsDebitService;

class PaymentPayableController extends Controller
{
    private $accountDebit;
    private $accountCredit;
    public function __construct(AccountsDebitService $accountDebit, AccountsCreditService $accountCredit)
    {
        $this->accountDebit = $accountDebit;
        $this->accountCredit = $accountCredit;
    }
    public function store(PaymentPayableFormRequest $request)
    {
        $dataArray = [];
        DB::beginTransaction();
        try {
            foreach ($request->payments as $key => $singlePayment) {
                $pp_info = new PaymentPayableInfo();
                $pp_info->voucherNo = $singlePayment['voucherNo'];
                $pp_info->companyId = $singlePayment['companyId'];
                $pp_info->paidType = $singlePayment['paidType'];
                $pp_info->chartOfHeadId = $singlePayment['payableId'];
                $pp_info->amount = $singlePayment['amount'];
                $pp_info->paidDate = $singlePayment['paidDate'];
                $pp_info->paymentType = $singlePayment['paymentType'];
                $pp_info->paymentMode = $singlePayment['paymentMode'];
                $pp_info->paymentFor = $singlePayment['paymentFor'];
                $pp_info->note = $singlePayment['note'];
                $pp_info->invoiceType = $singlePayment['invoiceType'];
                $pp_info->checkNo = $singlePayment['checkNo'];
                $pp_info->checkDate = $singlePayment['checkDate'];
                $pp_info->trxId = $singlePayment['trxId'];
                $pp_info->ref = $singlePayment['ref'];
                $pp_info->createdBy = auth()->user()->id;
                $pp_info->save();
                $paymentPayableId = $pp_info->id;

                $paidAmount = $singlePayment['amount'];
                if (isset($singlePayment['purcchaseOrder']) && is_array($singlePayment['purcchaseOrder']) && count($singlePayment['purcchaseOrder']) > 0) {
                  foreach ($singlePayment['purcchaseOrder'] as $detail) {
                    $purchaseId = $detail['id'] ?? null;
                    $dueAmount = $detail['dueAmount'] ?? 0;
                    $tAmount = $detail['totalAmount'] ?? 0;

                    if ($purchaseId) {
                      $dbDetail = new InvoiceWisePaymentPayable();
                      $dbDetail->paymentPayableId = $paymentPayableId;
                      $dbDetail->purchaseInvoiceId = $purchaseId;
                      $dbDetail->dueAmount = $dueAmount;
                      $dbDetail->paidAmount = min($paidAmount, $dueAmount);
                      $dbDetail->paidDate = $pp_info->paidDate;
                      $dbDetail->save();

                      $paidAmount -= $dueAmount;
                      if ($paidAmount <= 0) {
                        break;
                      }
                    } else {
                      // \Log::warning('Missing Purchase ID in purchase order detail:', $detail);
                    }
                  }
                } else {
                  // \Log::warning('Purchase order is missing or invalid for payment index ' . $key);
                }

                $dataArray[] = new PaymentPayableInfoResource($pp_info);
            }
            DB::commit();
            return response()->json([
                'message' => 'Payment Paid successfully',
                'data' => $dataArray,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function update(PaymentPayableUpdateFormRequest $request, $id)
    {
        $pp_info = PaymentPayableInfo::find($id);
        if (!$pp_info) {
            return response()->json(['message' => 'Payment Payable Info not found'], 404);
        }
        DB::beginTransaction();
        try {
            $pp_info->companyId = $request->companyId;
            $pp_info->paidType = $request->paidType;
            $pp_info->chartOfHeadId = $request->payableId;
            $pp_info->amount = $request->amount;
            $pp_info->paidDate = $request->paidDate;
            $pp_info->paymentType = $request->paymentType;
            $pp_info->paymentMode = $request->paymentMode;
            $pp_info->paymentFor = $request->paymentFor;
            $pp_info->note = $request->note;
            $pp_info->invoiceType = $request->invoiceType;
            $pp_info->checkNo = $request->checkNo;
            $pp_info->checkDate = $request->checkDate;
            $pp_info->trxId = $request->trxId;
            $pp_info->ref = $request->ref;
            $pp_info->modifiedBy = auth()->user()->id;
            $pp_info->save();
            $paidAmount = $request->amount;
            InvoiceWisePaymentPayable::where('paymentPayableId', $id)->delete();
            if (isset($request->purcchaseOrder) && is_array($request->purcchaseOrder) && count($request->purcchaseOrder) > 0) {
                foreach ($request->purcchaseOrder as $detail) {
                  $purchaseId = $detail['id'] ?? null;
                  $dueAmount = $detail['dueAmount'] ?? 0;
                  $tAmount = $detail['totalAmount'] ?? 0;

                  if ($purchaseId) {
                    $dbDetail = new InvoiceWisePaymentPayable();
                    $dbDetail->paymentPayableId = $id;
                    $dbDetail->purchaseInvoiceId = $purchaseId;
                    $dbDetail->dueAmount = $dueAmount;
                    $dbDetail->paidAmount = min($paidAmount, $dueAmount);
                    $dbDetail->paidDate = $pp_info->paidDate;
                    $dbDetail->save();

                    $paidAmount -= $dueAmount;
                    if ($paidAmount <= 0) {
                      break;
                    }
                  } else {
                    // \Log::warning('Missing Purchase ID in purchase order detail:', $detail);
                  }
                }
              } else {
                // \Log::warning('Purchase order is missing or invalid for payment index ' . $key);
              }
            DB::commit();
            return response()->json([
                'message' => 'Payment Payable Info updated successfully',
                'data' => new PaymentPayableInfoResource($pp_info),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $pp_info = PaymentPayableInfo::find($id);
        if (!$pp_info) {
            return response()->json(['message' => 'Payment Payable Info not found'], 404);
        }
        DB::beginTransaction();
        try {
            $pp_info->status = $request->status;
            $pp_info->modifiedBy = auth()->user()->id;
            if ($request->status == 1) {
                $voucherNo = $pp_info->voucherNo;
                $voucherType = 'PaymentPayable';
                $voucherDate = $pp_info->paidDate;
                $companyId = $pp_info->companyId;
                if ($pp_info->paidType == 1) {
                    $dealerHead = AccountLedgerName::where(['partyId' => $pp_info->chartOfHeadId, 'partyType' => 'D'])->first();

                    $paymentHead = AccountLedgerName::where(['partyId' => $pp_info->paymentType, 'partyType' => 'B'])->first();
                }

                // Debit Logic
                $this->accountDebit->setDebitData(
                    chartOfHeadId: $dealerHead->id,
                    companyId: $companyId,
                    voucherNo: $voucherNo,
                    voucherType: $voucherType,
                    voucherDate: $voucherDate,
                    note: 'Payment Payable approved debit entry',
                    debit: $pp_info->amount,
                );

                // Credit Logic
                $this->accountCredit->setCreditData(
                    chartOfHeadId: $paymentHead->id,
                    companyId: $companyId,
                    voucherNo: $voucherNo,
                    voucherType: $voucherType,
                    voucherDate: $voucherDate,
                    note: 'Payment Payable approved Credit entry',
                    credit: $pp_info->amount
                );

                $dealerHead->update([
                    'current_balance' => $dealerHead->current_balance + $pp_info->amount
                ]);
                $paymentHead->update([
                    'current_balance' => $paymentHead->current_balance - $pp_info->amount
                ]);
            }
            $pp_info->save();
            DB::commit();
            return response()->json([
                'message' => 'Payment Payable Info status updated successfully',
                'data' => new PaymentPayableInfoResource($pp_info),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
    //
    public function index(Request $request)
    {
        $oneYearAgo = now()->subYear()->format('Y-m-d');
        $today = today()->format('Y-m-d');

        $voucherNo = $request->voucherNo ?? null;
        $chartOfHeadId = $request->chartOfHeadId ?? null;
        $paymentType = $request->paymentType ?? null;
        $startDate = $request->input('startDate', $oneYearAgo);
        $endDate = $request->input('endDate', $today);
        $status = $request->status ?? null;

        $query = PaymentPayableInfo::query();

        // Filter by voucherNo
        if ($voucherNo) {
            $query->where('voucherNo', 'LIKE', '%' . $voucherNo . '%');
        }

        // Filter by dealerId
        if ($chartOfHeadId) {
            $query->where('chartOfHeadId', operator: $chartOfHeadId);
        }

        // Filter by paymentType
        if ($paymentType) {
            $query->where('paymentType', operator: $paymentType);
        }

        //filter paidDate
        if ($startDate && $endDate) {
            $query->whereBetween('paidDate', [$startDate, $endDate]);
        }

         // Filter by status
     if (!is_null($status)) {  // Ensure status can be 0
        $query->where('status', $status);
    }

        // Fetch pp_infos with eager loading of related data
        $pp_infos = $query->latest()->get();

        // Check if any pr_infos found
        if ($pp_infos->isEmpty()) {
            return response()->json(['message' => 'No Payment Payable Info found', 'data' => []], 200);
        }

        // Use the PaymentPayableResource to transform the data
        $transformedPaymentPayable = PaymentPayableInfoResource::collection($pp_infos);

        // Return Payment receive transformed with the resource
        return response()->json([
            'message' => 'Success!',
            'data' => $transformedPaymentPayable
        ], 200);
    }


    public function show($id)
    {
        $pp_info = PaymentPayableInfo::find($id);

        if (!$pp_info) {
            return response()->json(['message' => 'Payment Payable Info not found'], 404);
        }
        return new PaymentPayableInfoResource($pp_info);
    }

    public function autoGeneratePayVouNo()
    {
        try {
            $latestId = PaymentPayableInfo::max('id');
            $nextId = $latestId ? $latestId + 1 : 1;
            $newVouNo = 'PPI' . date('y') . date('m') . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            return response()->json(['voucherNo' => $newVouNo], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to generate PPI ID: ' . $e->getMessage()], 500);
        }
    }

    public function delete(Request $request,$id)
    {
        $pp_info = PaymentPayableInfo::with('invoiceWisePaymentPayable')->find($id);

        if (!$pp_info) {
            return response()->json(['message' => 'Payment Payable Info not found'], 404);
        }
        DB::beginTransaction();
        try {
            $pp_info->deletedBy = auth()->user()->id;
            $pp_info->invoiceWisePaymentPayable->delete();
            $pp_info->save();
            $pp_info->delete();
            DB::commit();
            return response()->json(['message' => 'Payment Payable Info deleted successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}