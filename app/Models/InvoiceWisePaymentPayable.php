<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class InvoiceWisePaymentPayable extends Model
{
    use HasFactory;
    protected $fillable = [
        'paymentPayableId',
        'purchaseInvoiceId',
        'paidDate',
        'dueAmount',
        'paidAmount',
    ];
    protected $table = 'invoice_wise_payment_payables';

    public function paymentPayable() : BelongsTo
    {
        return $this->belongsTo(PaymentPayableInfo::class, 'paymentPayableId', 'id');
    }

    // public function saleInvoice() : BelongsTo
    // {
    //     return $this->belongsTo(SalesOrder::class, 'purchaseInvoiceId', 'id');
    // }


}