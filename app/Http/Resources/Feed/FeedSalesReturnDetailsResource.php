<?php

namespace App\Http\Resources\Feed;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedSalesReturnDetailsResource extends JsonResource
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
        $rQty = $this->rQty;

        $bagQty = ($sizeOrWeight > 0) ? round($qty / $sizeOrWeight, 2) : null;
        $rBagQty = ($sizeOrWeight > 0) ? round($rQty / $sizeOrWeight, 2) : null;
        return [
            'id' => $this->id,
            'saleReturn' => [
                'id' => $this->saleReturn->id ?? null,
                'saleReturnId' => $this->saleReturn->saleReturnId ?? null,
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
            'rBagQty' => $rBagQty,
            'tradePrice' => $this->tradePrice,
            'salePrice' => $this->salePrice,
            'qty' => $this->qty,
            'rQty' => $this->rQty,
            'note' => $this->note,
        ];
    }
}
