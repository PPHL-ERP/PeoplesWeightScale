<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ZoneResource extends JsonResource
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
            'zoneName' => $this->zoneName,
            'zonalInCharge' => $this->zonalInCharge,
            //'districts' => $this->whenLoaded('districts'),
            'districts' => $this->districts->map(function ($district) {
                return [
                    'id' => $district->id,
                    'name' => $district->name,
                ];
            }),
            'note' => $this->note,
        ];
    }
}
