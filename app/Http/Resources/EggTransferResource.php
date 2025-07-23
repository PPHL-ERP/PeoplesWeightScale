<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EggTransferResource extends JsonResource
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
            'transferHead' => $this->transferHead,
            'trType' => $this->trType,
           'fromStore' => [
            'id' => $this->fStore->id ?? null,
            'name' => $this->fStore->name ?? null,
            'salesPointName' => $this->fStore->salesPointName ?? null,
            'description' => $this->fStore->description ?? null,
        ],
           'tStore' => [
            'id' => $this->tStore->id ?? null,
            'name' => $this->tStore->name ?? null,
            'salesPointName' => $this->tStore->salesPointName ?? null,
            'description' => $this->tStore->description ?? null,
        ],
            'transportType' => $this->transportType,
            'driverName' => $this->driverName,
            'mobile' => $this->mobile,
            'vehicleNo' => $this->vehicleNo,
            'date' => $this->date,
            'loadBy' => $this->loadBy,
            'labourGroupId' => $this->labourGroupId,
            'labourBill' => $this->labourBill,
            'note' => $this->note,
            'crBy' => $this->createdBy ? $this->createdBy->name : null,
            'appBy' => $this->approvedBy ? $this->approvedBy->name : null,
            'status' => $this->status,
            'details' => EggTransferDetailsResource::collection($this->details),
          ];
      }
}
