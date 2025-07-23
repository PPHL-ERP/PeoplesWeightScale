<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentPayableInvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            // 'purchase' => [
            //     'id' => $this->saleInvoice->id ?? null,
            //     'saleId' => $this->sale->saleId ?? null,
            // ],
            'paidDate' => $this->paidDate,
            'dueAmount' => $this->dueAmount,
            'paidAmount' => $this->paidAmount,
        ];
    }
}
