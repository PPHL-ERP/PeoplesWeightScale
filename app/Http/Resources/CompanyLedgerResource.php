<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyLedgerResource extends JsonResource
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
            'trId' => $this->trId,
            'typeId' => $this->typeId,
            'company' => [
                'id' => $this->company->id ?? null,
                'nameEn' => $this->company->nameEn ?? null,
            ],
            'accountHead' => $this->accountHead,
            'transactionDate' => $this->transactionDate,
            'trType' => $this->trType,
            'amount' => $this->amount,
            'balance' => $this->balance,
            'particular' => $this->particular,
            'appBy' => $this->approvedBy ? $this->approvedBy->name : null,

        ];
    }
}