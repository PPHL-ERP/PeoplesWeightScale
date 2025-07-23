<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DebitVoucherResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'voucherNo' => $this->voucherNo,
            'company' => [
                'id' => $this->company->id ?? null,
                'name' => $this->company->nameEn ?? null,
            ],
            'voucherDate' => $this->voucherDate,
            'creditHead' => $this->creditHead->name ?? null,
            'amount' => $this->amount,
            'checkNo' => $this->checkNo ?? null,
            'checkDate' => $this->checkDate ?? null,
            'trxId' => $this->trxId ?? null,
            'ref' => $this->ref ?? null,
            'note' => $this->note,
            'status' => $this->status,
            'createdBy' => $this->createdBy ?? null,
            'modifiedBy' => $this->modifiedBy ?? null,
            'appBy' => $this->approvedBy->name ?? null,
            'voucherItems' => $this->itemList
                ? DebitVoucherItemResource::collection($this->itemList)
                : null,
        ];
    }
}
