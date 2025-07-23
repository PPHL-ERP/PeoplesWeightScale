<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EggReceiveDetailResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            // 'productId' => $this->productId,
            // 'productName' => $this->product->productName ?? null, // Load the product name
            // 'productFid' => $this->product->productId ?? null, // Load the product name
            'product' => [
                'id' => $this->product->id ?? null,
                'productName' => $this->product->productName ?? null,
                'productId' => $this->product->productId ?? null,
                'unitId' => $this->product->unit->id ?? null,
                'unitName' => $this->product->unit->name ?? null,
            ],
            'trQty' => $this->trQty,
            'rQty' => $this->rQty,
            'deviationQty' => $this->deviationQty,
            'batchNo' => $this->batchNo,
            'note' => $this->note,
        ];
    }
}
