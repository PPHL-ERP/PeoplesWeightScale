<?php

namespace App\Services;

use App\Models\InvoiceWisePaymentReceive;

class InvoiceWisePaymentService
{

    public function setPaymentInvoice(int $saleInvoiceId,float $dueAmount,float $paidAmount, string $paidDate, string $status, string $note)
    {
        InvoiceWisePaymentReceive::create([
            'saleInvoiceId' => $saleInvoiceId,
            'dueAmount' => $dueAmount,
            'paidAmount' => $paidAmount,
            'paidDate' => $paidDate,
            'note' => $note,
            'status' => $status
        ]);
    }
}
