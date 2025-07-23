<?php

namespace App\Http\Resources\Chicks;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChicksDailyPriceResource extends JsonResource
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
            'date' => $this->date,
            'product' => [
                'id' => $this->product->id ?? null,
                'productName' => $this->product->productName ?? null,
                'sizeOrWeight' => $this->product->sizeOrWeight ?? null,
                'shortName' => $this->product->shortName ?? null,
                'batchNo' => $this->product->batchNo ?? null,
            ],
            'cZoneId' => $this->cZoneId,
            'pCost' => $this->pCost,
            'mrp' => $this->mrp,
            'salePrice' => $this->salePrice,
            'category' => [
                'id' => $this->category->id ?? null,
                'name' => $this->category->name ?? null,
                'companyId' => $this->category->companyId ?? null,
            ],
            'subCategory' => [
                'id' => $this->subCategory->id ?? null,
                'subCategoryName' => $this->subCategory->subCategoryName ?? null,
            ],
            'childCategory' => [
                'id' => $this->childCategory->id ?? null,
                'childCategoryName' => $this->childCategory->childCategoryName ?? null,
            ],
            'crBy' => $this->createdBy ? $this->createdBy->name : null,
            'appBy' => $this->approvedBy ? $this->approvedBy->name : null,
            'status' => $this->status,
            'chicks_dp_histories' => ChicksDailyPriceHistoryResource::collection($this->chicksDpHistory),

        ];
    }
}