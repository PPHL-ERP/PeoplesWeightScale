<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceWisePaymentReceive extends Model
{
    protected $table = 'invoice_wise_payment_receives';
    protected $fillable = ['paymentReceiveId', 'saleInvoiceId', 'dueAmount', 'paidAmount', 'paidDate', 'status', 'note'];

    public function paymentReceive() : BelongsTo
    {
        return $this->belongsTo(PaymentReceiveInfo::class, 'paymentReceiveId', 'id');
    }

    public function saleInvoice() : BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'saleInvoiceId', 'id');
    }
}
