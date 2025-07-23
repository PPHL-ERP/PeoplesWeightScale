<?php

namespace App\Http\Resources\Chicks;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChicksStockAdjustmentResource extends JsonResource
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
            'adjId' => $this->adjId,
            'sector' => [
                'id' => $this->sector->id ?? null,
                'name' => $this->sector->name ?? null,
            ],
            'product' => [
                'id' => $this->product->id ?? null,
                'productName' => $this->product->productName ?? null,
                'shortName' => $this->product->shortName ?? null,
                'batchNo' => $this->product->batchNo ?? null,
            ],
            'breed' => [
                'id' => $this->breed->id ?? null,
                'breedName' => $this->breed->breedName ?? null,
            ],
            'date' => $this->date,
            'initialQty' => $this->initialQty,
            'adjQty' => $this->adjQty,
            'finalQty' => $this->finalQty,
            'adjType' => $this->adjType,
            'adjCategory' => $this->adjCategory,
            'referenceId' => $this->referenceId,
            'referenceType' => $this->referenceType,
            'batchNo' => $this->batchNo,
            'image' =>$this->image ? config('app.url') . "/" . config('imagepath.adjustment') . $this->image : asset('images/blank-image.png'),
            'note' => $this->note,
            'crBy' => $this->createdBy ? $this->createdBy->name : null,
            'appBy' => $this->approvedBy ? $this->approvedBy->name : null,
            'status' => $this->status,
        ];
    }
}
