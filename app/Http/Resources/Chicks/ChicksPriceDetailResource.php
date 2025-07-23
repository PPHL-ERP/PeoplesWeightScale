<?php

namespace App\Http\Resources\Chicks;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChicksPriceDetailResource extends JsonResource
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
            'cPrice' => [
                'id' => $this->cPrice->id ?? null,
            ],
            'product' => [
                'id' => $this->product->id ?? null,
                'productName' => $this->product->productName ?? null,
                'sizeOrWeight' => $this->product->sizeOrWeight ?? null,
                'shortName' => $this->product->shortName ?? null,
                'batchNo' => $this->product->batchNo ?? null,
            ],
            'qty' => $this->qty,
            // 'cDailyPrice' => [
            //     'id' => $this->cDailyPrice->id ?? null,
            //     'dPrice' => $this->cDailyPrice->dPrice ?? null,
            // ],
            'dailyPriceId' => $this->dailyPriceId,
            'dPrice' => $this->dPrice,
            'cPrice' => $this->cPrice,

        ];
    }
}