<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WVendorResource extends JsonResource
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
            'vId' => $this->vId,
            'vName' => $this->vName,
            'phone' => $this->phone,
            'address' => $this->address,
            'note' => $this->note,
        ];
    }
}