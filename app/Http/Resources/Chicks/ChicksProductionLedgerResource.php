<?php

namespace App\Http\Resources\Chicks;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChicksProductionLedgerResource extends JsonResource
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
            'hatchery' => [
                'id' => $this->hatchery->id ?? null,
                'name' => $this->hatchery->name ?? null,
            ],
            'product' => [
                'id' => $this->product->id ?? null,
                'productName' => $this->product->productName ?? null,
            ],
            'transactionId' => $this->transactionId ?? 'null',
            'breed' => [
                'id' => $this->breed->id ?? null,
                'breedName' => $this->breed->breedName ?? null,
            ],
            'trType' => $this->trType ?? 'null',
            'date' => $this->date ?? 'null',
            'approxQty' => $this->approxQty ?? 'null',
            'finalQty' => $this->finalQty ?? 'null',
            'batchNo' => $this->batchNo ?? 'null',
            'remarks' => $this->remarks ?? 'null',
            'appBy' => $this->approvedBy ? $this->approvedBy->name : null,
        ];
    }
}