<?php

namespace App\Http\Resources\Feed;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LabourPaymentResource extends JsonResource
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
            'billStartDate' => $this->billStartDate,
            'billEndDate' => $this->billEndDate,
            'paymentDate' => $this->paymentDate,
            'totalQty' => $this->totalQty,
            'totalAmount' => $this->totalAmount,
            'priceInfo' => $this->priceInfo,
            'note' => $this->note,
            'crBy' => $this->createdBy ? $this->createdBy->name : null,
            'appBy' => $this->approvedBy ? $this->approvedBy->name : null,
            'billStatus' => $this->billStatus,
            'status' => $this->status,
          ];
        }
}