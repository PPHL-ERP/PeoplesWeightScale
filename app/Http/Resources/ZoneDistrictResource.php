<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ZoneDistrictResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, // Assuming resourceId is the ID of the resource
            'zone' => [
                'id' => $this->zoneId,
                'name' => $this->zoneName,
            ],
            'districts' => $this->districts->map(function ($district) {
                return [
                    'id' => $district['districtId'],
                    'name' => $district['name'],
                ];
            }),
        ];    }
}