<?php

namespace App\Http\Resources\Chicks;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChicksPriceResource extends JsonResource
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
            'dealer' => [
                'id' => $this->dealer->id ?? null,
                'tradeName' => $this->dealer->tradeName ?? null,
                'dealerCode' => $this->dealer->dealerCode ?? null,
                'contactPerson' => $this->dealer->contactPerson ?? null,
                'phone' => $this->dealer->phone ?? null,
                'zoneName' => $this->dealer->zone->zoneName ?? null,
            ],

            'empId' => $this->employee ? $this->employee->name : null,
            'outDealerName' => $this->outDealerName,
            'phone' => $this->phone,
            'date' => $this->date,
            'validityDate' => $this->validityDate,
            'note' => $this->note,
            'crBy' => $this->createdBy ? $this->createdBy->name : null,
            'appBy' => $this->approvedBy ? $this->approvedBy->name : null,
            'status' => $this->status,
            'price_details' => ChicksPriceDetailResource::collection($this->priceDetails),
          ];
        }
}