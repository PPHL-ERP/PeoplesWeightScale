<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'productId' => $this->productId,
            'productName' => $this->productName,
            'productType' => $this->productType,
            'sn' => $this->sn,
            'qrCode' => $this->qrCode,
            'batchNo' => $this->batchNo,
            'company' => [
                'id' => $this->company->id ?? null,
                'nameEn' => $this->company->nameEn ?? null,
            ],
            'category' => [
                'id' => $this->category->id ?? null,
                'name' => $this->category->name ?? null,
            ],
            'subCategory' => [
                'id' => $this->subCategory->id ?? null,
                'subCategoryName' => $this->subCategory->subCategoryName ?? null,
            ],
            'childCategory' => [
                'id' => $this->childCategory->id ?? null,
                'childCategoryName' => $this->childCategory->childCategoryName ?? null,
            ],
            'unit' => [
                'id' => $this->unit->id ?? null,
                'name' => $this->unit->name ?? null,
            ],
            'image' =>$this->image ? config('app.url') . "/" . config('imagepath.product') . $this->image : asset('images/blank-image.png'),
            'basePrice' => $this->basePrice,
            'sizeOrWeight' => $this->sizeOrWeight,
            'shortName' => $this->shortName,
            'productForm' => $this->productForm,
            'warranty' => $this->warranty,
            'minStock' => $this->minStock,
            'description' => $this->description,
            'crBy' => $this->createdBy ? $this->createdBy->name : null,
            'appBy' => $this->approvedBy ? $this->approvedBy->name : null,
            'status' => $this->status,

        ];
    }
}