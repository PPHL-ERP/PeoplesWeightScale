<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EggTransferDetailsResource extends JsonResource
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
            'transfer' => [
                'id' => $this->transfer->id ?? null,
                'trId' => $this->transfer->trId ?? null,
            ],
            'product' => [
                'id' => $this->product->id ?? null,
                'productName' => $this->product->productName ?? null,
                'shortName' => $this->product->shortName ?? null,
                'unitId' => $this->product->unit->id ?? null,  // Add unitId
                'unitName' => $this->product->unit->name ?? null,  // Add unitName
            ],
            'qty' => $this->qty,
            'transferFor' => $this->transferFor,
            'note' => $this->note,
          ];
    }
}