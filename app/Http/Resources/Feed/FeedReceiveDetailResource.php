<?php

namespace App\Http\Resources\Feed;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedReceiveDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $sizeOrWeight = $this->product->sizeOrWeight ?? 0;
        $trQty = $this->trQty;
        $rQty = $this->rQty;
        $deviationQty = $this->deviationQty;

        $bagQty = ($sizeOrWeight > 0) ? round($trQty / $sizeOrWeight, 2) : null;
        $rBagQty = ($sizeOrWeight > 0) ? round($rQty / $sizeOrWeight, 2) : null;
        $dBagQty = ($sizeOrWeight > 0) ? round($deviationQty / $sizeOrWeight, 2) : null;
        return [
            'product' => [
                'id' => $this->product->id ?? null,
                'productName' => $this->product->productName ?? null,
                'productId' => $this->product->productId ?? null,
                'unitId' => $this->product->unit->id ?? null,
                'unitName' => $this->product->unit->name ?? null,
                'sizeOrWeight' => $this->product->sizeOrWeight ?? null,
                'shortName' => $this->product->shortName ?? null,
            ],
            'bagQty' => $bagQty,
            'rBagQty' => $rBagQty,
            'dBagQty' => $dBagQty,
            'trQty' => $this->trQty,
            'rQty' => $this->rQty,
            'deviationQty' => $this->deviationQty,
            'batchNo' => $this->batchNo,
            'note' => $this->note,
        ];
     }
}
