<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesAddressMapResource extends JsonResource
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
            'transportId' => $this->transportId,
            'address' => $this->address,
            'note' => $this->note,
        ];
      }
}
