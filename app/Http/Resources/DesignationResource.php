<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DesignationResource extends JsonResource
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
            'name' => $this->name,
            'leaveCount' => $this->leaveCount,
            'description' => $this->description,
            'department' => [
                'id' => $this->department->id ?? null,
                'name' => $this->department->name ?? null,
            ],
            'status' => $this->status,

        ];
    }
}