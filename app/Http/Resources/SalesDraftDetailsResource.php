<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesDraftDetailsResource extends JsonResource
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
            'product' => [
                'id' => $this->product->id ?? null,
                'productName' => $this->product->productName ?? null,
            ],
            'unit' => [
                'id' => $this->unit->id ?? null,
                'name' => $this->unit->name ?? null,
            ],
            'tradePrice' => $this->tradePrice,
            'salePrice' => $this->salePrice,
            'qty' => $this->qty,
            'unitBatchNo' => $this->unitBatchNo,
        ];
    }
}