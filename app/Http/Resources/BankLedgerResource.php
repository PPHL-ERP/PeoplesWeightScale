<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankLedgerResource extends JsonResource
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
            'sector' => [
                'id' => $this->sector->id ?? null,
                'name' => $this->sector->name ?? null,
            ],
            //'bankId' => $this->bankId,
            'bank' => [
                'id' => $this->bank->id ?? null,
                'bankName' => $this->bank->bankName ?? null,
                'accountNo' => $this->bank->accountNo ?? null,
            ],
            'trId' => $this->trId,
            'trType' => $this->trType,
            'trDate' => $this->trDate,
            'companyBalance' => $this->companyBalance,
            'sectorBalance' => $this->sectorBalance,
            'amount' => $this->amount,
            'balance' => $this->balance,
            'particular' => $this->particular,
            'note' => $this->note,
            'appBy' => $this->approvedBy ? $this->approvedBy->name : null,
        ];
    }
}
