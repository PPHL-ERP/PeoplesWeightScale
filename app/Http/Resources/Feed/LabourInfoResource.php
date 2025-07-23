<?php

namespace App\Http\Resources\Feed;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LabourInfoResource extends JsonResource
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
            'labourName' => $this->labourName,
            'concernPerson' => $this->concernPerson,
            'contactNo' => $this->contactNo,
            'location' => $this->location,
            'depot' => [
                'id' => $this->depot->id ?? null,
                'name' => $this->depot->name ?? null,
            ],
            'unit' => [
                'id' => $this->unit->id ?? null,
                'name' => $this->unit->name ?? null,
            ],
            'contactDate' => $this->contactDate,
            'expDate' => $this->expDate,
            'fPrice' => $this->fPrice,
            'cPrice' => $this->cPrice,
            'oPrice' => $this->oPrice,
            'paymentCycle' => $this->paymentCycle,
            'paymentType' => $this->paymentType,
            'paymentInfo' => $this->paymentInfo,
            'note' => $this->note,
            'crBy' => $this->createdBy ? $this->createdBy->name : null,
            'appBy' => $this->approvedBy ? $this->approvedBy->name : null,
            'status' => $this->status,
          ];
        }
}