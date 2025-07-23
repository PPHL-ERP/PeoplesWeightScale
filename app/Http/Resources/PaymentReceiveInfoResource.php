<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentReceiveInfoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'voucherNo' => $this->voucherNo,
            'company' => [
                'id' => $this->company->id ?? null,
                'nameEn' => $this->company->nameEn ?? null,
            ],
            'recType' => $this->recType == 1 ? 'Dealer' : 'Employee',
            'dealer' => $this->recType == 1 ? [
                'id' => $this->dealer->id ?? null,
                'tradeName' => $this->dealer->tradeName ?? null,
                'dealerCode' => $this->dealer->dealerCode ?? null,
                'contactPerson' => $this->dealer->contactPerson ?? null,
                'phone' => $this->dealer->phone ?? null,
                'zoneName' => $this->dealer->zone->zoneName ?? null,
                'address' => $this->dealer->address ?? null,
            ] : null,
            'employee' => $this->recType == 2 ? [
                'id' => $this->employee->id ?? null,
                'name' => ($this->employee->first_name ?? '') . ($this->employee->last_name ?? ''),
                'code' => $this->employee->emp_id ?? null,
                'email' => $this->employee->email ?? null,
                'phone' => $this->employee->phone_number ?? null,
            ] : null,
            'amount' => $this->amount,
            'recDate' => $this->recDate,
            //'paymentType' => $this->paymentType,
            'bank' => [
                'id' => $this->bank->id ?? null,
                'bankName' => $this->bank->bankName ?? null,
                'accountNo' => $this->bank->accountNo ?? null,
                'shortName' => $this->bank->shortName ?? null,
            ],
            'paymentMode' => $this->paymentMode,
            //'paymentFor' => $this->paymentFor,
            'pFor' => [
                'id' => $this->pFor->id ?? null,
                'name' => $this->pFor->name ?? null,
            ],
            'invoiceType' => $this->invoiceType == 1 ? 'With Voucher' : 'Without Voucher',
            'checkNo' => $this->checkNo ?? null,
            'checkDate' => $this->checkDate ?? null,
            'trxId' => $this->trxId ?? null,
            'ref' => $this->ref ?? null,
            'note' => $this->note,
            'status' => $this->status,
            'createdBy' => $this->createBy->name ?? null,
            'appBy' => $this->approvedBy->name ?? null,
            // 'payment_receive_invoice_list' => $this->invoiceType == 1 && $this->paymentReceiveInvoiceList
            //     ? PaymentReceiveInvoiceResource::collection($this->paymentReceiveInvoiceList)
            //     : null,
            'payment_receive_invoice_list' => $this->invoicePaymentReceivesList->map(function ($item) {
                return [
                    'saleInvoiceId' => $item->saleInvoiceId,
                    'dueAmount' => $item->dueAmount,
                    'paidAmount' => $item->paidAmount,
                    'paidDate' => $item->paidDate,
                    'saleInvoiceDetails' => $item->saleInvoice ? [
                        'saleId' => $item->saleInvoice->saleId ?? null,
                        'invoiceDate' => $item->saleInvoice->invoiceDate ?? null,
                        'dueDate' => $item->saleInvoice->invoiceDate ?? null,
                        'totalAmount' => $item->saleInvoice->totalAmount ?? null,
                    ] : null,
                ];
            }),
        ];
    }
}