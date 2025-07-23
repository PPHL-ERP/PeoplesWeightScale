<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EggReceiveResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'recId' => $this->recId,
            'eggTransfers' => [
                'id' => $this->eggTransfers->id ?? null,
                'trId' => $this->eggTransfers->trId ?? null,
                'fromStore' => $this->eggTransfers->fromStore ?? null,
            ],
            'transferFrom' => [
                'id' => $this->transferFromSector->id ?? null,
                'name' => $this->transferFromSector->name ?? null,
                'salesPointName' => $this->transferFromSector->salesPointName ?? null,
                'description' => $this->transferFromSector->description ?? null,
            ],
            'recStore' => [
                'id' => $this->recStoreSector->id ?? null,
                'name' => $this->recStoreSector->name ?? null,
                'salesPointName' => $this->recStoreSector->salesPointName ?? null,
                'description' => $this->recStoreSector->description ?? null,
            ],
            'recHead' => $this->recHead,
            'chalanNo' => $this->chalanNo,
            'date' => $this->date,
            'unLoadBy' => $this->unLoadBy,
            'labourGroupId' => $this->labourGroupId,
            'labourBill' => $this->labourBill,
            'remarks' => $this->remarks,
            'status' => $this->status,
            'crBy' => $this->createdBy ? $this->createdBy->name : null,
            'appBy' => $this->approvedBy ? $this->approvedBy->name : null,
            'details' => EggReceiveDetailResource::collection($this->details),
        ];
    }
}
