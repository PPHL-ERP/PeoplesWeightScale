<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountGroupResource extends JsonResource
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
            'accountClass' => [
                'id' => $this->accountClass->id ?? null,
                'name' => $this->accountClass->name ?? null,
            ],
            'description' => $this->description,
        ];
    }
}