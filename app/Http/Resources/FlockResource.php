<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FlockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'   => $this->id,
            'flockName' => $this->flockName,
            'flockType' => $this->flockType,
            'sector' => [
                'id' => $this->sector->id ?? null,
                'name' => $this->sector->name ?? null,
                'description' => $this->sector->description ?? null,

            ],
            'flockStartDate' => $this->flockStartDate,
            'note' => $this->note,
            'status' => $this->status,
        ];
   }
}