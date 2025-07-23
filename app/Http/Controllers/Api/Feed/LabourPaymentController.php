<?php

namespace App\Http\Controllers\Api\Feed;

use App\Http\Controllers\Controller;
use App\Http\Requests\Feed\LabourPaymentRequest;
use App\Http\Resources\Feed\LabourPaymentResource;
use App\Models\LabourPayment;
use App\Models\LabourDetail;
use Illuminate\Http\Request;
use App\Services\PaymentValidationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\AccountLedgerName;
use App\Services\AccountsCreditService;
use App\Services\AccountsDebitService;

class LabourPaymentController extends Controller
{
    protected $accountCredit;
    protected $accountDebit;

    public function __construct(AccountsCreditService $accountCredit, AccountsDebitService $accountDebit)
    {
        $this->accountCredit = $accountCredit;
        $this->accountDebit = $accountDebit;
    }

    public function index(Request $request)
    {
        $oneYearAgo = now()->subYear()->format('Y-m-d');
        $today = today()->format('Y-m-d');

        $labourId = $request->labourId ?? null;
        $startDate = $request->input('startDate', $oneYearAgo);
        $endDate = $request->input('endDate', $today);
        $status = $request->status ?? null;


      $query = LabourPayment::query();

     // Filter by labourId
     if ($labourId) {
        $query->where('labourId', $labourId);
    }

     // Filter paymentDate
     if ($startDate && $endDate) {
        $query->whereBetween('paymentDate', [$startDate, $endDate]);
    }

    // Filter by status
    if ($status) {
        $query->where('status', $status);
    }
      // Fetch labour payment with eager loading of related data
      $lab_payments = $query->latest()->get();

      // Check if any labourPayment found
      if ($lab_payments->isEmpty()) {
        return response()->json(['message' => 'No Labour Payment found', 'data' => []], 200);
      }

      // Use the LabourPaymentResource to transform the data
      $transformedLabourPayments = LabourPaymentResource::collection($lab_payments);

      // Return labourInfo transformed with the resource
      return response()->json([
        'message' => 'Success!',
        'data' => $transformedLabourPayments
      ], 200);
    }


    public function store(LabourPaymentRequest $request)
    {
        try {
            $lab_payment = new LabourPayment();

            $lab_payment->labourId = $request->labourId;
            $lab_payment->billStartDate = $request->billStartDate;
            $lab_payment->billEndDate = $request->billEndDate;
            $lab_payment->paymentDate = $request->paymentDate;
            $lab_payment->totalQty = $request->totalQty;
            $lab_payment->totalAmount = $request->totalAmount;
            $lab_payment->priceInfo = $request->priceInfo;
            $lab_payment->note = $request->note;
            $lab_payment->crBy = auth()->id();
            $lab_payment->billStatus = 'Due';
            $lab_payment->status = 'pending';
            $lab_payment->save();
            return response()->json([
              'message' => 'Labour payment created successfully',
              'data' => new LabourPaymentResource($lab_payment),
            ], 200);
          } catch (\Exception $e) {
            // Handle the exception here
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
          }
      }
    public function show($id)
    {
      $lab_payment = LabourPayment::find($id);

      if (!$lab_payment) {
        return response()->json(['message' => 'Labour payment not found'], 404);
      }
      return new LabourPaymentResource($lab_payment);
    }


    public function update(LabourPaymentRequest $request, $id)
    {
        try {

            $lab_payment = LabourPayment::find($id);

            if (!$lab_payment) {
              return $this->sendError('Labour payment not found.');
            }

            // Prevent update if status is already approved
        if ($lab_payment->status === 'approved') {
            return response()->json(['message' => 'Cannot update. Labour payment is already approved.'], 403);
        }


            $lab_payment->labourId = $request->labourId;
            $lab_payment->billStartDate = $request->billStartDate;
            $lab_payment->billEndDate = $request->billEndDate;
            $lab_payment->paymentDate = $request->paymentDate;
            $lab_payment->totalQty = $request->totalQty;
            $lab_payment->totalAmount = $request->totalAmount;
            $lab_payment->priceInfo = $request->priceInfo;
            $lab_payment->note = $request->note;
            $lab_payment->billStatus = $request->billStatus;
            $lab_payment->status = 'pending';

            $lab_payment->update();


            return response()->json([
              'message' => 'labour payment Maps Updated successfully',
              'data' => new LabourPaymentResource($lab_payment),
            ], 200);
          } catch (\Exception $e) {
            // Handle the exception here
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
          }
    }

    public function statusUpdateold(Request $request, $id)
    {
        $lab_payment = LabourPayment::find($id);

        if (!$lab_payment) {
            return response()->json(['message' => 'Labour payment not found'], 404);
        }

        // Prevent status change if already approved
        if ($lab_payment->status === 'approved') {
            return response()->json(['message' => 'Status is already approved and cannot be changed.'], 403);
        }

        $lab_payment->status = $request->status;
        $lab_payment->appBy = auth()->id();

        // Automatically set billStatus to 'Paid' and update payStatus in labour_details
        if ($request->status === 'approved') {
            $lab_payment->billStatus = 'Paid';

            // Update payStatus in labour_details where labourId and date range match
            DB::table('labour_details')
                ->where('labourId', $lab_payment->labourId)
                ->whereBetween('tDate', [$lab_payment->billStartDate, $lab_payment->billEndDate])
                ->update(['payStatus' => 'Paid']);
        }

        $lab_payment->update();

        return response()->json([
            'message' => 'Labour payment status updated successfully',
            'data' => new LabourPaymentResource($lab_payment),
        ], 200);
    }

    public function statusUpdate(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $lab_payment = LabourPayment::find($id);

            if ($lab_payment->status === 'approved') {
                return response()->json(['message' => 'Status is already approved and cannot be changed.'], 403);
            }
            $lab_payment->status = $request->status;
            $lab_payment->appBy = auth()->id();

            if ($request->status === 'approved') {
                $lab_payment->billStatus = 'Paid';

                DB::table('labour_details')
                    ->where('labourId', $lab_payment->labourId)
                    ->whereBetween('tDate', [$lab_payment->billStartDate, $lab_payment->billEndDate])
                    ->update(['payStatus' => 'Paid']);


                $voucherNo = 'LBP-' . $lab_payment->id;
                $voucherType = 'LabourPayment';
                $voucherDate = $lab_payment->paymentDate;
                $companyId = 2;
                $labourAmount = (float) $lab_payment->totalAmount;
                $labourLedger = AccountLedgerName::where([
                    'partyId' => $lab_payment->labourId,
                    'partyType' => 'L', // Labour
                ])->first();

                $this->accountCredit->setCreditData(
                    chartOfHeadId: $labourLedger->id,
                    companyId: $companyId,
                    voucherNo: $voucherNo,
                    voucherType: $voucherType,
                    voucherDate: $voucherDate,
                    note: "Labour payment  {$lab_payment->billStartDate} To {$lab_payment->billEndDate}",
                    credit: $labourAmount
                );


                if ($labourLedger) {
                    $labourLedger->update([
                        'current_balance' => $labourLedger->current_balance + $labourAmount
                    ]);
                }

            }

            $lab_payment->update();

            DB::commit();

            return response()->json([
                'message' => 'Labour payment status updated successfully',
                'data' => new LabourPaymentResource($lab_payment),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }


    public function destroy($id)
      {
        $lab_payment = LabourPayment::find($id);
        if (!$lab_payment) {
          return response()->json(['message' => 'Labour payment not found'], 404);
        }
        $lab_payment->delete();
        return response()->json([
          'message' => 'Labour payment deleted successfully',
        ], 200);
      }


      //
      public function generatePayment(Request $request)
      {
          try {
              $dateFrom = $request->input('dateFrom');
              $dateTo = $request->input('dateTo');

              $labourIds = is_array($request->input('labourIds'))
                  ? $request->input('labourIds')
                  : explode(',', $request->input('labourIds', ''));

              $payments = LabourDetail::with(['depot', 'unit'])
                  ->whereIn('labourId', $labourIds)
                  ->whereBetween('tDate', [$dateFrom, $dateTo])
                  ->orderBy('tDate', 'asc')
                  ->get();

              // Check if any payment in the date range has status 'paid'
              if ($payments->contains('status', 'paid')) {
                  return response()->json([
                      'message' => 'Payment cannot be generated because there are already paid records in the selected date range.',
                      'paymentStatus' => 'paid'
                  ], 400);
              }

              // Check if any labourId with billStartDate and billEndDate already exists in labour_payments
              $existingPayments = LabourPayment::whereIn('labourId', $labourIds)
                  ->where(function ($query) use ($dateFrom, $dateTo) {
                      $query->whereBetween('billStartDate', [$dateFrom, $dateTo])
                            ->orWhereBetween('billEndDate', [$dateFrom, $dateTo]);
                  })
                  ->exists();

              if ($existingPayments) {
                  return response()->json([
                      'message' => 'Payment already created in the selected date range.',
                      'paymentStatus' => 'exists'
                  ], 400);
              }

              $groupedPayments = $payments->where('status', 'approved')->groupBy(['tDate', 'labourId']);

              $response = [];
              $totals = ['totalQty' => 0, 'totalAmount' => 0];

              foreach ($groupedPayments as $date => $labours) {
                  $dailyDetails = [];
                  $dailyQtyTotal = 0;
                  $dailyAmountTotal = 0;

                  foreach ($labours as $labourId => $records) {
                      $dailyQty = $records->sum('qty');
                      $dailyAmount = $records->sum('bAmount');

                      $dailyRecords = $records->map(function ($record) {
                          return [
                              'id' => $record->id,
                              'labourId' => $record->labourId,
                              'depotId' => $record->depotId,
                              'depotName' => $record->depot ? $record->depot->name : null,
                              'unitId' => $record->unitId,
                              'unitName' => $record->unit ? $record->unit->name : null,
                              'transactionId' => $record->transactionId,
                              'transactionType' => $record->transactionType,
                              'workType' => $record->workType,
                              'tDate' => $record->tDate,
                              'qty' => $record->qty,
                              'bAmount' => $record->bAmount,
                              'crBy' => $record->crBy,
                              'appBy' => $record->appBy,
                              'status' => $record->status,
                              'created_at' => $record->created_at,
                              'updated_at' => $record->updated_at,
                          ];
                      });

                      $dailyDetails[] = [
                          'labourId' => $labourId,
                          'qty' => $dailyQty,
                          'amount' => $dailyAmount,
                          'records' => $dailyRecords
                      ];

                      $dailyQtyTotal += $dailyQty;
                      $dailyAmountTotal += $dailyAmount;
                  }

                  $response[] = [
                      'date' => $date,
                      'details' => $dailyDetails,
                      'dailyTotalQty' => $dailyQtyTotal,
                      'dailyTotalAmount' => $dailyAmountTotal,
                  ];

                  $totals['totalQty'] += $dailyQtyTotal;
                  $totals['totalAmount'] += $dailyAmountTotal;
              }

              return response()->json([
                  'message' => 'Payment details generated successfully',
                  'data' => $response,
                  'totals' => $totals,
                  'paymentStatus' => 'unpaid'
              ], 200);
          } catch (\Exception $e) {
              Log::error('Error generating payment: ' . $e->getMessage());
              return response()->json([
                  'message' => 'Internal server error!',
                  'error' => $e->getMessage()
              ], 500);
          }
      }



}
