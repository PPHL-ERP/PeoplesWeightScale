<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WCustomerResource extends JsonResource
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
            'cId' => $this->cId,
            'cName' => $this->cName,
            'phone' => $this->phone,
            'address' => $this->address,
            'note' => $this->note,
        ];
    }
}