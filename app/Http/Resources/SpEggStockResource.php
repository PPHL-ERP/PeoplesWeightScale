<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SpEggStockResource extends JsonResource
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
            'spId' => $this->spId,
            'stock' => [
                'id' => $this->stock->id,
                'stockId' => $this->stock->stockId,
            ],
             'farmStock' => [
                'id' => $this->farmStock->id ?? null,
            ],
            'stockDate' => $this->stockDate,
            'saleDate' => $this->saleDate,
          ];
    }
}