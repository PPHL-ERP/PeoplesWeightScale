<?php

namespace App\Http\Resources\Feed;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LabourDetailResource extends JsonResource
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
            'labInfo' => [
                'id' => $this->labInfo->id ?? null,
                'labourName' => $this->labInfo->labourName ?? null,
            ],
            'depot' => [
                'id' => $this->depot->id ?? null,
                'name' => $this->depot->name ?? null,
            ],
            'unit' => [
                'id' => $this->unit->id ?? null,
                'name' => $this->unit->name ?? null,
            ],
            'transactionId' => $this->transactionId,
            'transactionType' => $this->transactionType,
            'workType' => $this->workType,
            'tDate' => $this->tDate,
            'qty' => $this->qty,
            'bAmount' => $this->bAmount,
            'crBy' => $this->createdBy ? $this->createdBy->name : null,
            'appBy' => $this->approvedBy ? $this->approvedBy->name : null,
            'payStatus' => $this->payStatus,
            'status' => $this->status,
          ];
        }
}