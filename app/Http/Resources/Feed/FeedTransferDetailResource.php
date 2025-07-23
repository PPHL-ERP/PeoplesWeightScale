<?php

namespace App\Http\Resources\Feed;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedTransferDetailResource extends JsonResource
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
            'transfer' => [
                'id' => $this->transfer->id ?? null,
                'trId' => $this->transfer->trId ?? null,
            ],
            'product' => [
                'id' => $this->product->id ?? null,
                'productName' => $this->product->productName ?? null,
                'unitId' => $this->product->unit->id ?? null,  // Add unitId
                'unitName' => $this->product->unit->name ?? null,  // Add unitName
                'sizeOrWeight' => $this->product->sizeOrWeight ?? null,
                'shortName' => $this->product->shortName ?? null,
            ],
            'bagQty' => $bagQty,
            'qty' => $this->qty,
            'transferFor' => $this->transferFor,
            'note' => $this->note,
          ];
        }
}