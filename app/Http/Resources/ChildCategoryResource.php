<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChildCategoryResource extends JsonResource
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
            'childCategoryName' => $this->childCategoryName,
            'subCategory' => [
                'id' => $this->subCategory->id ?? null,
                'subCategoryName' => $this->subCategory->subCategoryName ?? null,
                'name' => $this->subCategory->category->name ?? null,
            ],
            'description' => $this->description,
            'crBy' => $this->createdBy ? $this->createdBy->name : null,
            'appBy' => $this->approvedBy ? $this->approvedBy->name : null,
            'status' => $this->status,
        ];
    }
}
