<?php

namespace App\Services;

use App\Models\AccountReceivable;
use App\Models\AccountReceivableDetail;
use App\Models\PaymentReceiveInfo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountReceivableService
{
    public function getAll($request)
    {
        // Optional: Implement filters as needed
        return AccountReceivable::with('details')->orderBy('id', 'desc')->get();
    }

    public function getById($id)
    {
        return AccountReceivable::with('details')->find($id);
    }

    /**
     * Create a new accounts receivable record with details.
     * Status will be pending until approval.
     */
    public function createWithDetails(array $data)
    {
        Log::info('Received payload:', $data);
    
        // Wrap single payment object as an array if necessary
        $payments = isset($data['payments']) && is_array($data['payments'])
            ? $data['payments']
            : [$data];
    
        return DB::transaction(function () use ($payments) {
            foreach ($payments as $payment) {
                // Validate and extract fields
                $companyId = $payment['company'] ?? null;
    
                if (!$companyId) {
                    throw new \InvalidArgumentException('Company ID is required for each payment.');
                }
    
                // Create Account Receivable
                $ar = AccountReceivable::create([
                    'company_id'      => $companyId,
                    'invoice_number'  => $payment['voucher'],
                    'customer_name'   => $payment['receiverName'] ?? null,
                    'transaction_date'=> $payment['voucherDate'],
                    'payment_term'    => $payment['paymentMode'],
                    'amount'          => $payment['totalAmount'],
                    'balance'         => $payment['totalAmount'],
                    'particular'      => $payment['note'] ?? null,
                    'approved_by'     => null,
                    'status'          => 'Pending',
                ]);
    
                // Create Payment Receive Info
                PaymentReceiveInfo::create([
                    'prInfoId'     => $payment['voucher'],
                    'companyId'    => $companyId,
                    'dealerId'     => $payment['receiverId'] ?? null,
                    'bankId'       => $payment['paymentTypeId'] ?? null,
                    'accountHead'  => null, // Needs further clarification
                    'amount'       => $payment['totalAmount'],
                    'recDate'      => $payment['voucherDate'],
                    'recType'      => $payment['receiverType'],
                    'paymentType'  => $payment['paymentTypeName'] ?? null,
                    'paymentMode'  => $payment['paymentMode'],
                    'paymentFor'   => $payment['paymentFor'],
                    'transactionId'=> $payment['transactionId'] ?? null,
                    'checkNo'      => $payment['checkNo'] ?? null,
                    'checkDate'    => $payment['checkDate'] ?? null,
                    'ref'          => $payment['ref'],
                    'note'         => $payment['note'] ?? null,
                ]);
    
                // Add Invoice Data
                if (!empty($payment['invoiceData'])) {
                    foreach ($payment['invoiceData'] as $line) {
                        AccountReceivableDetail::create([
                            'account_receivable_id' => $ar->id,
                            'invoice_id'            => $line['id'] ?? null,
                            'sale_id'               => $line['saleId'] ?? null,
                            'applied_amount'        => $line['totalAmount'] ?? 0,
                        ]);
                    }
                }
            }
        });
    }
            
    public function update($id, array $data)
    {
        $ar = AccountReceivable::find($id);
        if (!$ar) {
            return false;
        }

        // For example, handling an approval update
        if (isset($data['status'])) {
            $ar->status = $data['status'];
            // if status = Approved, set appBy from authenticated user if you have auth
            // e.g., $ar->appBy = auth()->id();
            $ar->save();
        }

        return $ar->fresh('details');
    }

    public function delete($id)
    {
        $ar = AccountReceivable::find($id);
        if (!$ar) {
            return false;
        }
        $ar->delete();
        return true;
    }
}
