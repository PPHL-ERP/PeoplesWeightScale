<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankListResource extends JsonResource
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
            'bankName' => $this->bankName,
            'bankBranch' => $this->bankBranch,
            'accountHolder' => $this->accountHolder,
            'bankaAccountType' => $this->bankaAccountType,
            'accountNo' => $this->accountNo,
            'routingNo' => $this->routingNo,
            'isMobileBanking' => $this->isMobileBanking,
            'isCash' => $this->isCash,
            'contactNo' => $this->contactNo,
            'bankAddress' => $this->bankAddress,
            'openingBalance' => $this->openingBalance,
            'company' => [
                'id' => $this->company->id ?? null,
                'nameEn' => $this->company->nameEn ?? null,
            ],
            'shortName' => $this->shortName,
            'crBy' => $this->createdBy ? $this->createdBy->name : null,
            'note' => $this->note,
            'status' => $this->status,

            'partyName' => $this->party_name, // Added from account_ledger_name
            'partyCurrentBalance' => $this->party_current_balance, // Added from account_ledger_name
        ];
    }
}
