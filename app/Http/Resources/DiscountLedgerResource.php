<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiscountLedgerResource extends JsonResource
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
            'sale' => [
                'id' => $this->sale->id ?? null,
                'saleId' => $this->sale->saleId ?? null,
            ],
            'saleCategory' => [
                'id' => $this->saleCategory->id ?? null,
                'name' => $this->saleCategory->name ?? null,
            ],
             'dealer' => [
                'id' => $this->dealer->id ?? null,
                'dealerCode' => $this->dealer->dealerCode ?? null,
            ],
            'saleDetails' => json_decode($this->saleDetails, true),
            'totalPrice' => $this->totalPrice,
            'discountPrice' => $this->discountPrice,
            'date' => $this->date,
            'appBy' => $this->approvedBy ? $this->approvedBy->name : null,
          ];
     }
}