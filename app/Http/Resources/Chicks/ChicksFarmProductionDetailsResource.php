<?php

namespace App\Http\Resources\Chicks;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChicksFarmProductionDetailsResource extends JsonResource
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
            //'pId' => $this->pId,
            'production' => [
                'id' => $this->production->id ?? null,
                'productionId' => $this->production->productionId ?? null,
            ],
            'product' => [
                'id' => $this->product->id ?? null,
                'productName' => $this->product->productName ?? null,
                'shortName' => $this->product->shortName ?? null,
                'batchNo' => $this->product->batchNo ?? null,
            ],
            'settingId' => $this->settingId,
            //'breedId' => $this->breedId,
            'breed' => [
                'id' => $this->breed->id ?? null,
                'breedName' => $this->breed->breedName ?? null,
            ],
            'chicksType' => $this->chicksType,
            'batchNo' => $this->batchNo,
            'grade' => $this->grade,
            'approxQty' => $this->approxQty,
            'finalQty' => $this->finalQty,

        ];

 }
}
