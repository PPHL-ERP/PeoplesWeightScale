<?php

namespace App\Http\Resources\Feed;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedDraftDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $sizeOrWeight = $this->product->sizeOrWeight ?? 0;
        $qty = $this->qty;
        $bagQty = ($sizeOrWeight > 0) ? round($qty / $sizeOrWeight, 2) : null;
        return [
            'id' => $this->id,
            'feed' => [
                'id' => $this->feed->id ?? null,
                'feedId' => $this->feed->feedId ?? null,
            ],
            'product' => [
                'id' => $this->product->id ?? null,
                'productName' => $this->product->productName ?? null,
                'sizeOrWeight' => $this->product->sizeOrWeight ?? null,
                'shortName' => $this->product->shortName ?? null,
            ],
            'unit' => [
                'id' => $this->unit->id ?? null,
                'name' => $this->unit->name ?? null,
            ],
            'bagQty' => $bagQty,
            'tradePrice' => $this->tradePrice,
            'salePrice' => $this->salePrice,
            'qty' => $this->qty,
            'unitBatchNo' => $this->unitBatchNo,
        ];
    }
}
