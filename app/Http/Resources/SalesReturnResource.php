<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesReturnResource extends JsonResource
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
            'saleReturnId' => $this->saleReturnId,
            'sale' => [
                'id' => $this->sale->id ?? null,
                'saleId' => $this->sale->saleId ?? null,
                'dealerCode' => $this->sale->dealer->dealerCode ?? null,
                'tradeName' => $this->sale->dealer->tradeName ?? null,
                'contactPerson' => $this->sale->dealer->contactPerson ?? null,
                'phone' => $this->sale->dealer->phone ?? null,
                'zoneName' => $this->sale->dealer->zone->zoneName ?? null,
            ],
            'dealer' => [
                'id' => $this->dealer->id ?? null,
                'tradeName' => $this->dealer->tradeName ?? null,
                'dealerCode' => $this->dealer->dealerCode ?? null,
                'contactPerson' => $this->dealer->contactPerson ?? null,
                'phone' => $this->dealer->phone ?? null,
                'zoneName' => $this->dealer->zone->zoneName ?? null,
            ],
            'returnPurpose' => $this->returnPurpose,
            'invoiceDate' => $this->invoiceDate,
            'returnDate' => $this->returnDate,
            'totalReturnAmount' => $this->totalReturnAmount,
            'discount' => $this->discount,
            'note' => $this->note,
            'crBy' => $this->createdBy ? $this->createdBy->name : null,
            'appBy' => $this->approvedBy ? $this->approvedBy->name : null,
            'status' => $this->status,
            'sales_return_details' => SalesReturnDetailsResource::collection($this->saleReturnDetails),
          ];
    }
}
