<?php

namespace App\Http\Resources\Chicks;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChicksDailyPriceHistoryResource extends JsonResource
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
            'chicksDailyPrices' => [
                'id' => $this->chicksDailyPrices->id ?? null,
            ],
            'date' => $this->date,
            'changeType' => $this->changeType,
            'product' => [
                'id' => $this->product->id ?? null,
                'productName' => $this->product->productName ?? null,
                'sizeOrWeight' => $this->product->sizeOrWeight ?? null,
                'shortName' => $this->product->shortName ?? null,
                'batchNo' => $this->product->batchNo ?? null,
            ],
            'cZoneId' => $this->cZoneId,
            'pCost' => $this->pCost,
            'mrp' => $this->mrp,
            'salePrice' => $this->salePrice,
            'crBy' => $this->createdBy ? $this->createdBy->name : null,
            'appBy' => $this->approvedBy ? $this->approvedBy->name : null,
            'status' => $this->status,
        ];
    }
}