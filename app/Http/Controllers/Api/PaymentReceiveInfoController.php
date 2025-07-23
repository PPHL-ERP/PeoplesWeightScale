<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentReceiveFormUpdateRequest;
use App\Http\Requests\PaymentReceiveInfoRequest;
use App\Http\Resources\PaymentReceiveInfoResource;
use App\Models\AccountLedgerName;
use App\Models\InvoiceWisePaymentReceive;
use App\Models\PaymentReceiveInfo;
use App\Models\SalesOrder;
use App\Services\AccountsCreditService;
use App\Services\AccountsDebitService;
use App\Services\InvoiceWisePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\FeedOrder;
use App\Models\FeedOrderDetails;
use App\Services\CacheService;

class PaymentReceiveInfoController extends Controller
{
  private $accountDebit;
  private $accountCredit;
  protected $invoiceWisePayment;
  protected $cacheService;
  public function __construct(AccountsDebitService $accountDebit, AccountsCreditService $accountCredit, InvoiceWisePaymentService $invoiceWisePayment,CacheService $cacheService)
  {
    $this->accountDebit = $accountDebit;
    $this->accountCredit = $accountCredit;
    $this->invoiceWisePayment = $invoiceWisePayment;
    $this->cacheService = $cacheService;

  }
  public function indexOld(Request $request)
  {
    $oneYearAgo = now()->subYear()->format('Y-m-d');
    $today = today()->format('Y-m-d');

    $voucherNo = $request->voucherNo ?? null;
    $chartOfHeadId = $request->chartOfHeadId ?? null;
    $companyId = $request->companyId ?? null;
    $paymentFor = $request->paymentFor ?? null;
    // $bankId = $request->bankId ?? null;
    $paymentType = $request->paymentType ?? null;
    $startDate = $request->input('startDate', $oneYearAgo);
    $endDate = $request->input('endDate', $today);
    $status = $request->status ?? null;
    $query = PaymentReceiveInfo::query();

    // Filter by voucherNo
    if ($voucherNo) {
      $query->where('voucherNo', 'LIKE', '%' . $voucherNo . '%');
    }

    // Filter by chartOfHeadId
    if ($chartOfHeadId) {
      $query->where('chartOfHeadId', operator: $chartOfHeadId);
    }

    // Filter by companyId
    if ($companyId) {
        $query->where('companyId', operator: $companyId);
    }

     // Filter by paymentFor
     if ($paymentFor) {
        $query->orWhere('paymentFor', operator: $paymentFor);
      }

    // Filter by paymentType
    if ($paymentType) {
      $query->orWhere('paymentType', operator: $paymentType);
    }

    //filter recDate
    if ($startDate && $endDate) {
      $query->whereBetween('recDate', [$startDate, $endDate]);
    }

     // Filter by status
     if (!is_null($status)) {  // Ensure status can be 0
        $query->where('status', $status);
    }

    // Fetch pr_infos with eager loading of related data
    //$pr_infos = $query->latest()->get();
    $pr_infos = $query->with(['company', 'dealer', 'employee', 'bank', 'pFor', 'invoicePaymentReceivesList.saleInvoice'])->latest()->get();

    // Check if any pr_infos found
    if ($pr_infos->isEmpty()) {
      return response()->json(['message' => 'No Payment Receive Info found', 'data' => []], 200);
    }

    // Use the PaymentReceiveInfoResource to transform the data
    $transformedPaymentReceives = PaymentReceiveInfoResource::collection($pr_infos);

    // Return Payment receive transformed with the resource
    return response()->json([
      'message' => 'Success!',
      'data' => $transformedPaymentReceives
    ], 200);
  }

  public function index(Request $request)
  {
    $oneYearAgo = now()->subYear()->format('Y-m-d');
    $today = today()->format('Y-m-d');

    // Filters
    $voucherNo       = $request->voucherNo;
    $chartOfHeadId   = $request->chartOfHeadId;
    $companyId       = $request->companyId;
    $paymentFor      = $request->paymentFor;
    $paymentType     = $request->paymentType;
    $startDate       = $request->input('startDate', $oneYearAgo);
    $endDate         = $request->input('endDate', $today);
    $status          = $request->status;
    $limit           = $request->input('limit', 100); // Default 100

    $query = PaymentReceiveInfo::query();

    // Filter by voucherNo
    if ($voucherNo) {
      $query->where('voucherNo', 'LIKE', '%' . $voucherNo . '%');
    }

    // Filter by chartOfHeadId
    if ($chartOfHeadId) {
      $query->where('chartOfHeadId', operator: $chartOfHeadId);
    }

    // Filter by companyId
    if ($companyId) {
        $query->where('companyId', operator: $companyId);
    }

     // Filter by paymentFor
     if ($paymentFor) {
        $query->where('paymentFor', operator: $paymentFor);
      }

    // Filter by paymentType
    if ($paymentType) {
      $query->where('paymentType', operator: $paymentType);
    }

    //filter recDate
    if ($startDate && $endDate) {
      $query->whereBetween('recDate', [$startDate, $endDate]);
    }

     // Filter by status
     if (!is_null($status)) {  // Ensure status can be 0
        $query->where('status', $status);
    }

    // Fetch pr_infos with eager loading of related data
    //$pr_infos = $query->latest()->get();
    $pr_infos = $query->with(['company', 'dealer', 'employee', 'bank', 'pFor', 'invoicePaymentReceivesList.saleInvoice'])->latest()->paginate($limit);

    // Return paginated response
    return response()->json([
        'message' => 'Success!',
        'data' => PaymentReceiveInfoResource::collection($pr_infos),
        'meta' => [
            'current_page' => $pr_infos->currentPage(),
            'last_page' => $pr_infos->lastPage(),
            'per_page' => $pr_infos->perPage(),
            'total' => $pr_infos->total(),
        ]
    ], 200);
  }

  public function store(PaymentReceiveInfoRequest $request)
  {
    $dataArray = [];
    DB::beginTransaction();
    try {
      foreach ($request->payments as $key => $singlePayment) {
        $pr_infos = new PaymentReceiveInfo();
        $pr_infos->voucherNo = $singlePayment['voucherNo'] ?? null;
        $pr_infos->companyId = $singlePayment['companyId'];
        $pr_infos->recType = $singlePayment['recType'];
        $pr_infos->chartOfHeadId = $singlePayment['receiverId'];
        $pr_infos->amount = $singlePayment['amount'];
        $pr_infos->recDate = $singlePayment['recDate'];
        $pr_infos->paymentType = $singlePayment['paymentType'];
        $pr_infos->paymentMode = $singlePayment['paymentMode'];
        $pr_infos->paymentFor = $singlePayment['paymentFor'];
        $pr_infos->note = $singlePayment['note'];
        $pr_infos->invoiceType = $singlePayment['invoiceType'];
        $pr_infos->checkNo = $singlePayment['checkNo'];
        $pr_infos->checkDate = $singlePayment['checkDate'];
        $pr_infos->trxId = $singlePayment['trxId'];
        $pr_infos->ref = $singlePayment['ref'];
        $pr_infos->createdBy = auth()->user()->id;
        $pr_infos->save();
        $paidAmount = $singlePayment['amount'];
        $paymentReceiveId = $pr_infos->id;
        if (isset($singlePayment['sale_order']) && is_array($singlePayment['sale_order']) && count($singlePayment['sale_order']) > 0) {
          foreach ($singlePayment['sale_order'] as $detail) {
            $saleId = $detail['id'] ?? null;
            $dueAmount = $detail['dueAmount'] ?? 0;
            $tAmount = $detail['totalAmount'] ?? 0;
            // $dueAmount = $tAmount - $paidAmount;

            if ($saleId) {
              $dbDetail = new InvoiceWisePaymentReceive();
              $dbDetail->paymentReceiveId = $paymentReceiveId;
              $dbDetail->saleInvoiceId = $saleId;
              $dbDetail->dueAmount = $dueAmount;
              $dbDetail->paidAmount = min($paidAmount, $dueAmount);
              $dbDetail->paidDate = $pr_infos->recDate;
              $dbDetail->save();

              $paidAmount -= $dueAmount;
              if ($paidAmount <= 0) {
                break;
              }
            } else {
              // \Log::warning('Missing sale ID in sale order detail:', $detail);
            }
          }
        } else {
          // \Log::warning('Sale order is missing or invalid for payment index ' . $key);
        }
        $dataArray[] = new PaymentReceiveInfoResource($pr_infos);
      }
      DB::commit();
      return response()->json([
        'message' => 'Payment Receive Info created successfully',
        'data' => $dataArray,
      ], 200);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
    }
  }


  public function show($id)
  {
    $pr_info = PaymentReceiveInfo::with('invoicePaymentReceivesList')->find($id);

    if (!$pr_info) {
      return response()->json(['message' => 'Payment Receive Info not found'], 404);
    }
    return new PaymentReceiveInfoResource($pr_info);
  }


  //   public function update(PaymentReceiveInfoRequest $request, $id)
  //   {
  //     try {

  //       $pr_infos = PaymentReceiveInfo::find($id);

  //       if (!$pr_infos) {
  //         return $this->sendError('Payment Receive Info not found.');
  //       }

  //       $pr_infos->companyId = $request->companyId;
  //       $pr_infos->dealerId = $request->dealerId;
  //       $pr_infos->bankId = $request->bankId;
  //       $pr_infos->accountHead = $request->accountHead;
  //       $pr_infos->amount = $request->amount;
  //       $pr_infos->recDate = $request->recDate;
  //       $pr_infos->recType = $request->recType;
  //       $pr_infos->paymentType = $request->paymentType;
  //       $pr_infos->paymentMode = $request->paymentMode;
  //       $pr_infos->paymentFor = $request->paymentFor;
  //       $pr_infos->note = $request->note;
  //       $pr_infos->appBy = $request->appBy;

  //       $pr_infos->update();

  //       return response()->json([
  //         'message' => 'Payment Receive Updated successfully',
  //         'data' => new PaymentReceiveInfoResource($pr_infos),
  //       ], 200);
  //     } catch (\Exception $e) {
  //       // Handle the exception here
  //       return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
  //     }
  //   }


  public function update(PaymentReceiveFormUpdateRequest $request, $id)
  {
    DB::beginTransaction();
    try {
      $pr_infos = PaymentReceiveInfo::find($id);
      if (!$pr_infos) {
        return $this->sendError('Payment Receive Info not found.');
      }

      // Check if the status is 'Approved' (integer value 1)
      if ($pr_infos->status === 1) {
        return response()->json([
          'message' => 'Updates are not allowed. Payment Receive Info is already approved.'
        ], 403);
      }


      $pr_infos->voucherNo = $request->voucherNo;
      $pr_infos->companyId = $request->companyId;
      $pr_infos->recType = $request->recType;
      $pr_infos->chartOfHeadId = $request->receiverId;
      $pr_infos->amount = $request->amount;
      $pr_infos->recDate = $request->recDate;
      $pr_infos->paymentType = $request->paymentType;
      $pr_infos->paymentMode = $request->paymentMode;
      $pr_infos->paymentFor = $request->paymentFor;
      $pr_infos->note = $request->note;
      $pr_infos->invoiceType = $request->invoiceType;
      $pr_infos->checkNo = $request->checkNo;
      $pr_infos->checkDate = $request->checkDate;
      $pr_infos->trxId = $request->trxId;
      $pr_infos->ref = $request->ref;
      $pr_infos->modifiedBy = auth()->user()->id;

      $pr_infos->save();
      $paidAmount = $request->amount;
      InvoiceWisePaymentReceive::where('paymentReceiveId', $id)->delete();
      if (isset($request->sale_order) && count($request->sale_order) > 0 && $request->invoiceType == 1) {
        foreach ($request->sale_order as $detail) {
          $saleId = $detail['id'] ?? null;
          $dueAmount = $detail['dueAmount'] ?? 0;
          $tAmount = $detail['totalAmount'] ?? 0;

          if ($saleId) {
            $dbDetail = new InvoiceWisePaymentReceive();
            $dbDetail->paymentReceiveId = $id;
            $dbDetail->saleInvoiceId = $saleId;
            $dbDetail->dueAmount = $dueAmount;
            $dbDetail->paidAmount = min($paidAmount, $dueAmount);
            $dbDetail->paidDate = $pr_infos->recDate;
            $dbDetail->save();

            $paidAmount -= $dueAmount;
            if ($paidAmount <= 0) {
              break;
            }
          } else {
            // \Log::warning('Missing sale ID in sale order detail:', $detail);
          }
        }
      }

      DB::commit();
      return response()->json([
        'message' => 'Payment Receive Updated successfully',
        'data' => new PaymentReceiveInfoResource($pr_infos),
      ], 200);
    } catch (\Exception $e) {
      DB::rollBack();
      // Handle the exception here
      return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
    }
  }

  public function destroy($id)
  {
    $pr_infos = PaymentReceiveInfo::find($id);
    if (!$pr_infos) {
      return response()->json(['message' => 'Payment Receive not found'], 404);
    }

    $pr_infos->delete();
    return response()->json([
      'message' => 'Payment Receive deleted successfully',
    ], 200);
  }


  //with validation
  public function updateStatusold(Request $request, $id)
  {
    DB::beginTransaction();
    try {
      $pr_infos = PaymentReceiveInfo::with('invoicePaymentReceivesList', 'invoicePaymentReceivesList.saleInvoice')->find($id);
      // return $pr_infos;
      if (!$pr_infos) {
        return response()->json(['message' => 'Payment Receive not found'], 404);
      }

      // Validation status is 'approved'
      if ($pr_infos->status == 'approved' || $pr_infos->status == 1) {
        return response()->json(['message' => 'Status change is not allowed for already approved records.'], 403);
      }

      $pr_infos->status = $request->status;

      if ($request->status == 1) {
        // Add Credit and Debit Accounting Logic
        $voucherNo = $pr_infos->voucherNo;
        $voucherType = 'PaymentRecieve';
        $voucherDate = $pr_infos->recDate;
        $companyId = $pr_infos->companyId;
        // $chartOfHeadId = $sales_order->chartOfHeadId;
        if ($pr_infos->recType == 1) {
          $dealerHead = AccountLedgerName::where(['partyId' => $pr_infos->chartOfHeadId, 'partyType' => 'D'])->first();

          $paymentHead = AccountLedgerName::where(['partyId' => $pr_infos->paymentType, 'partyType' => 'B'])->first();
        }

        // Credit Logic
        $this->accountCredit->setCreditData(
          chartOfHeadId: $dealerHead->id, // Example chart of head for credit (replace with correct ID)
          companyId: $companyId,
          voucherNo: $voucherNo,
          voucherType: $voucherType,
          voucherDate: $voucherDate,
          note: 'Payment Receive approved credit entry',
          credit: $pr_infos->amount
        );

        // Debit Logic is on hold
        $this->accountDebit->setDebitData(
          chartOfHeadId: $paymentHead->id,
          companyId: $companyId,
          voucherNo: $voucherNo,
          voucherType: $voucherType,
          voucherDate: $voucherDate,
          note: 'Payment Receive approved debit entry',
          debit: $pr_infos->amount
        );

        $dealerHead->update([
          'current_balance' => $dealerHead->current_balance - $pr_infos->amount
        ]);
        $paymentHead->update([
          'current_balance' => $paymentHead->current_balance + $pr_infos->amount
        ]);
      }

      if (isset($pr_infos->invoicePaymentReceivesList) && count($pr_infos->invoicePaymentReceivesList) > 0) {
        foreach ($pr_infos->invoicePaymentReceivesList as $invoicePaymentReceive) {
          $newDueAmount = $invoicePaymentReceive->saleInvoice->dueAmount - $invoicePaymentReceive->paidAmount;
          $paymentStatus = $newDueAmount == 0 ? 'Paid' : 'Partial';
          $invoicePaymentReceive->saleInvoice->update([
            'dueAmount' => $newDueAmount,
            'paymentStatus' => $paymentStatus
          ]);
        }
      } else {
        $sales_orders = SalesOrder::where(['companyId' => $pr_infos->companyId, 'dealerId' => $pr_infos->chartOfHeadId])->where('dueAmount', '>', 0)->where('status', 'approved')->where('paymentStatus', '!=', 'Paid')->get();

        // return $sales_orders;

        $totalAmount = $pr_infos->amount;
        $lastTotal = $totalAmount;

        foreach ($sales_orders as $sales_order) {
          if ($lastTotal <= 0) {
            break;
          }
          $dueAmount = $sales_order->dueAmount;
          $totalAmount = $totalAmount - $dueAmount;
          if ($totalAmount >= 0) {
            $sales_order->update([
              'dueAmount' => 0,
              'paymentStatus' => 'Paid'
            ]);
            $invoiceStatus = 'Paid';
            $invoiceNote = 'Paid in full';
            $paidAmount = $dueAmount;
            $invoicedueAmount = 0;
            $lastTotal = $totalAmount;
          } else {
            $sales_order->update([
              'dueAmount' => $dueAmount - $lastTotal,
              'paymentStatus' => 'Partial'
            ]);
            $invoiceStatus = 'Partial';
            $invoiceNote = 'Partially paid';
            $paidAmount = $lastTotal;
            $invoicedueAmount = $dueAmount - $lastTotal;
            $lastTotal = -1;
          }
          $this->invoiceWisePayment->setPaymentInvoice(
            saleInvoiceId: $sales_order->id,
            dueAmount: $invoicedueAmount,
            paidAmount: $paidAmount,
            paidDate: $voucherDate,
            note: $invoiceNote,
            status: $invoiceStatus
          );
          $sales_order->paymentStatus = $invoiceStatus;
          $sales_order->dueAmount = $invoicedueAmount;
          $sales_order->update();
        }
      }
      $pr_infos->appBy = auth()->id();
      $pr_infos->save();
      DB::commit();
      return response()->json([
        'message' => 'Payment Receive Status change successfully',
      ], 200);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
    }
  }

  public function updateStatus(Request $request, $id)
  {
      DB::beginTransaction();
      try {
          $pr_infos = PaymentReceiveInfo::with('invoicePaymentReceivesList', 'invoicePaymentReceivesList.saleInvoice')->find($id);

          if (!$pr_infos) {
              return response()->json(['message' => 'Payment Receive not found'], 404);
          }

          // Validation: already approved can't change
          if ($pr_infos->status == 'approved' || $pr_infos->status == 1) {
              return response()->json(['message' => 'Status change is not allowed for already approved records.'], 403);
          }

          $pr_infos->status = $request->status;

          if ($request->status == 1) {
              // Voucher Info
              $voucherNo = $pr_infos->voucherNo;
              $voucherType = 'PaymentRecieve';
              $voucherDate = $pr_infos->recDate;
              $companyId = $pr_infos->companyId;

              // Get ledger heads
              if ($pr_infos->recType == 1) {
                  $dealerHead = AccountLedgerName::where(['partyId' => $pr_infos->chartOfHeadId, 'partyType' => 'D'])->first();
                  $paymentHead = AccountLedgerName::where(['partyId' => $pr_infos->paymentType, 'partyType' => 'B'])->first();
              }

              // Ledger entry Credit: dealer
              $this->accountCredit->setCreditData(
                  chartOfHeadId: $dealerHead->id,
                  companyId: $companyId,
                  voucherNo: $voucherNo,
                  voucherType: $voucherType,
                  voucherDate: $voucherDate,
                  note: 'Payment Receive approved credit entry',
                  credit: $pr_infos->amount
              );

              // Ledger entry Debit: payment type (bank/cash)
              $this->accountDebit->setDebitData(
                  chartOfHeadId: $paymentHead->id,
                  companyId: $companyId,
                  voucherNo: $voucherNo,
                  voucherType: $voucherType,
                  voucherDate: $voucherDate,
                  note: 'Payment Receive approved debit entry',
                  debit: $pr_infos->amount
              );

              // Update ledger balances
              $dealerHead->update([
                  'current_balance' => $dealerHead->current_balance - $pr_infos->amount
              ]);

              $paymentHead->update([
                  'current_balance' => $paymentHead->current_balance + $pr_infos->amount
              ]);
          }


        if ($pr_infos->paymentFor != 14) {
            if (isset($pr_infos->invoicePaymentReceivesList) && count($pr_infos->invoicePaymentReceivesList) > 0) {
                foreach ($pr_infos->invoicePaymentReceivesList as $invoicePaymentReceive) {
                    $saleInvoice = $invoicePaymentReceive->saleInvoice;

                    $newDueAmount = $saleInvoice->dueAmount - $invoicePaymentReceive->paidAmount;
                    $paymentStatus = $newDueAmount == 0 ? 'Paid' : 'Partial';

                    $saleInvoice->update([
                        'dueAmount' => $newDueAmount,
                        'paymentStatus' => $paymentStatus
                    ]);
                }
            } else {
                // --- this part updated for FeedOrder/SalesOrder switching ---
                if ($pr_infos->paymentFor == 5) {
                    $orderModel = SalesOrder::class;
                } elseif ($pr_infos->paymentFor == 1 && $pr_infos->companyId == 2) {
                    $orderModel = FeedOrder::class;
                } else {
                    throw new \Exception('Unsupported paymentFor or companyId combination.');
                }

                $sales_orders = $orderModel::where('companyId', $pr_infos->companyId)
                    ->where('dealerId', $pr_infos->chartOfHeadId)
                    ->where('dueAmount', '>', 0)
                    ->where('status', 'approved')
                    ->where('paymentStatus', '!=', 'Paid')
                    ->orderBy('invoiceDate', 'asc')
                    ->get();

                $totalAmount = $pr_infos->amount;
                $lastTotal = $totalAmount;

                foreach ($sales_orders as $sales_order) {
                    if ($lastTotal <= 0) {
                        break;
                    }

                    $dueAmount = $sales_order->dueAmount;
                    $totalAmount = $totalAmount - $dueAmount;

                    if ($totalAmount >= 0) {
                        $sales_order->update([
                            'dueAmount' => 0,
                            'paymentStatus' => 'Paid'
                        ]);
                        $invoiceStatus = 'Paid';
                        $invoiceNote = 'Paid in full';
                        $paidAmount = $dueAmount;
                        $invoicedueAmount = 0;
                        $lastTotal = $totalAmount;
                    } else {
                        $sales_order->update([
                            'dueAmount' => $dueAmount - $lastTotal,
                            'paymentStatus' => 'Partial'
                        ]);
                        $invoiceStatus = 'Partial';
                        $invoiceNote = 'Partially paid';
                        $paidAmount = $lastTotal;
                        $invoicedueAmount = $dueAmount - $lastTotal;
                        $lastTotal = -1;
                    }

                    $this->invoiceWisePayment->setPaymentInvoice(
                        saleInvoiceId: $sales_order->id,
                        dueAmount: $invoicedueAmount,
                        paidAmount: $paidAmount,
                        paidDate: $voucherDate,
                        note: $invoiceNote,
                        status: $invoiceStatus
                    );
                }
            }
        }


          $pr_infos->appBy = auth()->id();
          $pr_infos->save();

          DB::commit();
          return response()->json([
              'message' => 'Payment Receive Status changed successfully',
          ], 200);
      } catch (\Exception $e) {
          DB::rollBack();
          return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
      }
  }


  public function autoGenerateNextVouNo()
  {
    try {
      $latestId = PaymentReceiveInfo::max('id');
      $nextId = $latestId ? $latestId + 1 : 1;
      $newVouNo = 'PRI' . date('y') . date('m') . str_pad($nextId, 4, '0', STR_PAD_LEFT);

      return response()->json(['voucherNo' => $newVouNo], 200);
    } catch (\Exception $e) {
      return response()->json(['message' => 'Failed to generate PRI ID: ' . $e->getMessage()], 500);
    }
  }


public function updateMultiStatus(Request $request)
{
    try {
        $request->validate([
            'status' => 'required|integer',
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:payment_receive_infos,id'
        ]);

        $ids = $request->input('ids');
        $unchangedIds = [];
        $dateSet = [];
        $invalidDateIds = [];

        // Prevent updates to already approved records
        $alreadyApproved = PaymentReceiveInfo::whereIn('id', $ids)
            ->where('status', 1)
            ->pluck('id')
            ->toArray();

        if (!empty($alreadyApproved)) {
            return response()->json([
                'status' => 'error',
                'message' => 'One or more records are already approved and cannot be updated.',
                'unchanged' => $alreadyApproved,
            ], 422);
        }

        DB::beginTransaction();

        foreach ($ids as $id) {
            $pr_infos = PaymentReceiveInfo::with('invoicePaymentReceivesList', 'invoicePaymentReceivesList.saleInvoice')->findOrFail($id);

            if (is_null($pr_infos->recDate)) {
                $invalidDateIds[] = $pr_infos->id;
                continue;
            }

            if ((int)$request->status === 1) {
                $dateSet[$pr_infos->recDate] = true;

                if (count($dateSet) > 1) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Multiple productions with different dates cannot be approved together.',
                        'conflicting_date' => $pr_infos->recDate,
                    ], 422);
                }

                $pr_infos->status = 1;

                $voucherNo = $pr_infos->voucherNo;
                $voucherType = 'PaymentRecieve';
                $voucherDate = $pr_infos->recDate;
                $companyId = $pr_infos->companyId;

                if ($pr_infos->recType == 1) {
                    $dealerHead = AccountLedgerName::where(['partyId' => $pr_infos->chartOfHeadId, 'partyType' => 'D'])->first();
                    $paymentHead = AccountLedgerName::where(['partyId' => $pr_infos->paymentType, 'partyType' => 'B'])->first();

                    $this->accountCredit->setCreditData(
                        chartOfHeadId: $dealerHead->id,
                        companyId: $companyId,
                        voucherNo: $voucherNo,
                        voucherType: $voucherType,
                        voucherDate: $voucherDate,
                        note: 'Payment Receive approved credit entry',
                        credit: $pr_infos->amount
                    );

                    $this->accountDebit->setDebitData(
                        chartOfHeadId: $paymentHead->id,
                        companyId: $companyId,
                        voucherNo: $voucherNo,
                        voucherType: $voucherType,
                        voucherDate: $voucherDate,
                        note: 'Payment Receive approved debit entry',
                        debit: $pr_infos->amount
                    );

                    $dealerHead->update([
                        'current_balance' => $dealerHead->current_balance - $pr_infos->amount
                    ]);
                    $paymentHead->update([
                        'current_balance' => $paymentHead->current_balance + $pr_infos->amount
                    ]);
                }
                if ($pr_infos->paymentFor != 14) {
                if (isset($pr_infos->invoicePaymentReceivesList) && count($pr_infos->invoicePaymentReceivesList) > 0) {
                    foreach ($pr_infos->invoicePaymentReceivesList as $invoicePaymentReceive) {
                        $saleInvoice = $invoicePaymentReceive->saleInvoice;
                        $newDueAmount = $saleInvoice->dueAmount - $invoicePaymentReceive->paidAmount;
                        $paymentStatus = $newDueAmount == 0 ? 'Paid' : 'Partial';

                        $saleInvoice->update([
                            'dueAmount' => $newDueAmount,
                            'paymentStatus' => $paymentStatus
                        ]);
                    }
                } else {
                    if ($pr_infos->paymentFor == 5) {
                        $orderModel = SalesOrder::class;
                    } elseif ($pr_infos->paymentFor == 1 && $pr_infos->companyId == 2) {
                        $orderModel = FeedOrder::class;
                    } else {
                        throw new \Exception('Unsupported paymentFor or companyId combination.');
                    }

                    $sales_orders = $orderModel::where('companyId', $pr_infos->companyId)
                        ->where('dealerId', $pr_infos->chartOfHeadId)
                        ->where('dueAmount', '>', 0)
                        ->where('status', 1)
                        ->where('paymentStatus', '!=', 'Paid')
                        ->orderBy('invoiceDate', 'asc')
                        ->get();

                    $totalAmount = $pr_infos->amount;
                    $lastTotal = $totalAmount;

                    foreach ($sales_orders as $sales_order) {
                        if ($lastTotal <= 0) break;

                        $dueAmount = $sales_order->dueAmount;
                        $totalAmount -= $dueAmount;

                        if ($totalAmount >= 0) {
                            $sales_order->update([
                                'dueAmount' => 0,
                                'paymentStatus' => 'Paid'
                            ]);
                            $invoiceStatus = 'Paid';
                            $invoiceNote = 'Paid in full';
                            $paidAmount = $dueAmount;
                            $invoicedueAmount = 0;
                            $lastTotal = $totalAmount;
                        } else {
                            $sales_order->update([
                                'dueAmount' => $dueAmount - $lastTotal,
                                'paymentStatus' => 'Partial'
                            ]);
                            $invoiceStatus = 'Partial';
                            $invoiceNote = 'Partially paid';
                            $paidAmount = $lastTotal;
                            $invoicedueAmount = $dueAmount - $lastTotal;
                            $lastTotal = -1;
                        }

                        $this->invoiceWisePayment->setPaymentInvoice(
                            saleInvoiceId: $sales_order->id,
                            dueAmount: $invoicedueAmount,
                            paidAmount: $paidAmount,
                            paidDate: $voucherDate,
                            note: $invoiceNote,
                            status: $invoiceStatus
                        );
                    }
                }
            }
            }

            $pr_infos->appBy = auth()->id();
            $pr_infos->save();
        }

        $this->cacheService->clearAllCache();

        DB::commit();

        return response()->json([
            'status' => 'success',
            'message' => 'Status updated successfully for ' . count($ids) . ' record(s)',
            'unchanged' => $unchangedIds,
            'invalid_dates' => $invalidDateIds,
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'status' => 'error',
            'message' => 'An error occurred while updating the status and creating ledger entries',
            'error' => $e->getMessage(),
        ], 500);
    }
}


}
