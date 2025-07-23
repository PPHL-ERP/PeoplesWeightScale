<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountLedgerNameResource extends JsonResource
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
            'company' => [
                'id' => $this->company->id ?? null,
                'nameEn' => $this->company->nameEn ?? null,
            ],
            'name' => $this->name,
            'code' => $this->code,
            'accountClass' => [
                'id' => $this->accountClass->id ?? null,
                'name' => $this->accountClass->name ?? null,
            ],
            'accountGroup' => [
                'id' => $this->accountGroup->id ?? null,
                'name' => $this->accountGroup->name ?? null,
            ],
            'accountSubGroup' => [
                'id' => $this->accountSubGroup->id ?? null,
                'name' => $this->accountSubGroup->name ?? null,
            ],
            'nature' => $this->nature,
            'opening_balance' => $this->opening_balance,
            'current_balance' => $this->current_balance,
            'is_active' => $this->is_active,
            'is_posting_allowed' => $this->is_posting_allowed,
            'description' => $this->description,
            'partyId' => $this->partyId,
            'partyType' => $this->partyType,
        ];
    }
}