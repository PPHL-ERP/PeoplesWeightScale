<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankTransferInfoResource extends JsonResource
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
            'btrId' => $this->btrId,
            'company' => [
                'id' => $this->company->id ?? null,
                'nameEn' => $this->company->nameEn ?? null,
            ],
            'sector' => [
                'id' => $this->sector->id ?? null,
                'name' => $this->sector->name ?? null,
            ],
            'accountLedgerName' => [
                'id' => $this->accountLedgerName->id ?? null,
                'name' => $this->accountLedgerName->name ?? null,
            ],
            'transactionId' => $this->transactionId,
            'bankFrom' => [
                'id' => $this->bankFrom->id ?? null,
                'bankName' => $this->bankFrom->bankName ?? null,
                'accountNo' => $this->bankFrom->accountNo ?? null,
            ],
            'bankTo' => [
                'id' => $this->bankTo->id ?? null,
                'bankName' => $this->bankTo->bankName ?? null,
                'accountNo' => $this->bankTo->accountNo ?? null,
            ],
            'transactionDate' => $this->transactionDate,
            'transferType' => $this->transferType,
            'trPurpose' => $this->trPurpose,
            'modeOfTransfer' => $this->modeOfTransfer,
            'amount' => $this->amount,
            'chequeNo' => $this->chequeNo,
            'chequeDate' => $this->chequeDate,
            'note' => $this->note,
            'entryBy' => $this->entBy ? $this->entBy->name : null,
            'appBy' => $this->approvedBy ? $this->approvedBy->name : null,
            'status' => $this->status,

        ];
    }
}
