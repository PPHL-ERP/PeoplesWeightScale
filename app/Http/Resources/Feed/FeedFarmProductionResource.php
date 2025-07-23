<?php

namespace App\Http\Resources\Feed;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedFarmProductionResource extends JsonResource
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
            'productionId' => $this->productionId,
            'product' => [
                'id' => $this->product->id ?? null,
                'productName' => $this->product->productName ?? null,
            ],
            'sector' => [
                'id' => $this->sector->id ?? null,
                'name' => $this->sector->name ?? null,
            ],
            'productionDate' => $this->productionDate,
            'expDate' => $this->expDate,
            'qty' => $this->qty,
            'batchNo' => $this->batchNo,
            'note' => $this->note,
            'crBy' => $this->createdBy ? $this->createdBy->name : null,
            'appBy' => $this->approvedBy ? $this->approvedBy->name : null,
            'status' => $this->status,

            // // 'productList' => $this->feedProductList,
            // 'productList' => $this->feedProductList->map(function ($item) {
            //     return [
            //         'product' => [
            //             'id' => $item->productId,
            //             'name' => $item->product->productName ?? null
            //         ],
            //         'qty' => $item->qty
            //     ];
            // }),
        ];
    }
}
