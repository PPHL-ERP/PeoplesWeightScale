<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyPriceResource extends JsonResource
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
            'product' => [
                'id' => $this->product->id ?? null,
                'productName' => $this->product->productName ?? null,
                'basePrice' => $this->product->basePrice ?? null,
                'shortName' => $this->product->shortName ?? null,
            ],
            'category' => [
                'id' => $this->category->id ?? null,
                'name' => $this->category->name ?? null,
            ],
            'availableQty' => $this->availableQty,
            'oldPrice' => $this->oldPrice,
            'currentPrice' => $this->currentPrice,
            'date' => $this->date,
            'note' => $this->note,
            'crBy' => $this->createdBy ? $this->createdBy->name : null,
            'appBy' => $this->approvedBy ? $this->approvedBy->name : null,
            'status' => $this->status,
            'daily_price_histories' => DailyPriceHistoryResource::collection($this->dailyPriceHistory),
        ];
    }
}