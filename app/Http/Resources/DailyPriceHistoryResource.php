<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyPriceHistoryResource extends JsonResource
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
            'dailyPrices' => [
                'id' => $this->dailyPrices->id ?? null,
            ],
            'product' => [
                'id' => $this->product->id ?? null,
                'productName' => $this->product->productName ?? null,
            ],
            'category' => [
                'id' => $this->category->id ?? null,
                'name' => $this->category->name ?? null,
            ],
            'availableQty' => $this->availableQty,
            'newPrice' => $this->newPrice,
            'price' => $this->price,
            'date' => $this->date,
            'time' => $this->time,
            'crBy' => $this->createdBy ? $this->createdBy->name : null,
        ];
    }
}
