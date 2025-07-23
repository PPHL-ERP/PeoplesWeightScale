<?php

namespace App\Http\Resources\Chicks;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChicksStockResource extends JsonResource
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
            // 'sector' => [
            //     'id' => $this->sector->id ?? null,
            //     'name' => $this->sector->name ?? null,
            //     'salesPointName' => $this->sector->salesPointName ?? null,
            // ],
            // 'product' => [
            //     'id' => $this->product->id ?? null,
            //     'productName' => $this->product->productName ?? null,
            //     'sizeOrWeight' => $this->product->sizeOrWeight ?? null,
            //     'batchNo' => $this->product->batchNo ?? null,
            // ],
            //  'breed' => [
            //     'id' => $this->breed->id ?? null,
            //     'breedName' => $this->breed->breedName ?? null,
            // ],
            'sectorId' => $this->sectorId,
            'sectorName' => $this->sectorname,
            'productId' => $this->productId,
            'productName' => $this->productname,
            'breedId' => $this->breedId,
            'breedName' => $this->breedname,
            'stockType' => $this->stockType,
            'batchNo' => $this->batchNo,
            'stockDate' => $this->stockDate,
            'approxQty' => $this->approxQty,
            'finalQty' => $this->finalQty,
            'closing' => $this->closing,
        ];
     }
}