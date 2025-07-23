<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JournalEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'voucherNo' => $this->voucherNo,
            'voucherDate' => $this->voucherDate,
            'approvedDate' => $this->approvedDate ?? null,
            'company' => [
                'id' => $this->company->id ?? null,
                'nameEn' => $this->company->nameEn ?? null,
            ],
            'debitSubGroup' => [
                'id' => $this->debitSubGroup->id ?? null,
                'name' => $this->debitSubGroup->name ?? null,
            ],
            'debitHead' => [
                'id' => $this->debitHead->id ?? null,
                'name' => $this->debitHead->name ?? null,
            ],
           // 'debitSubGroup' =>$this->debitSubGroup->name ?? null,
            //'debitHead' => $this->debitHead->name ?? null,
            'debit' => $this->debit,
            //'creditSubGroup' => $this->creditSubGroup->name ?? null,
            //'creditHead' => $this->creditHead->name ?? null,
            'creditSubGroup' => [
                'id' => $this->creditSubGroup->id ?? null,
                'name' => $this->creditSubGroup->name ?? null,
            ],
            'creditHead' => [
                'id' => $this->creditHead->id ?? null,
                'name' => $this->creditHead->name ?? null,
            ],
            'credit' => $this->credit,
            'checkNo' => $this->checkNo ?? null,
            'checkDate' => $this->checkDate ?? null,
            'trxId' => $this->trxId ?? null,
            'ref' => $this->ref ?? null,
            'note' => $this->note ?? null,
            'createdBy' => $this->createdByUser->name ?? null,
            'modifiedBy' => $this->modifiedByUser->name ?? null,
            'appBy' => $this->appByUser->name ?? null,
            'status' => $this->status
        ];
    }
}