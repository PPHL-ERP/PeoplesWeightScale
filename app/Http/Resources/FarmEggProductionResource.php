<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FarmEggProductionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'productionId' => $this->productionId,
           // 'productId' => $this->productId,
            'product' => [
                'id' => $this->product->id ?? null,
                'productName' => $this->product->productName ?? null,
            ],
            'sector' => [
                'id' => $this->sector->id ?? null,
                'name' => $this->sector->name ?? null,
            ],
            //'flockId' => $this->flockId,
            'flock' => [
                'id' => $this->flock->id ?? null,
                'flockName' => $this->flock->flockName ?? null,
            ],
            'flockTotal' => $this->flockTotal,
            'date' => $this->date,
            'qty' => $this->qty,
            'note' => $this->note,
            'crBy' => $this->createdBy ? $this->createdBy->name : null,
            'appBy' => $this->approvedBy ? $this->approvedBy->name : null,
            'status' => $this->status,
        ];
    }
}