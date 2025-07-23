<?php

namespace App\Http\Resources\Feed;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedReceiveResource extends JsonResource
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
            'recId' => $this->recId,
            'feedTransfers' => [
                'id' => $this->feedTransfers->id ?? null,
                'trId' => $this->feedTransfers->trId ?? null,
                'fromStore' => $this->feedTransfers->fromStore ?? null,
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
            //'unLoadBy' => $this->unLoadBy,
            'labInfo' => [
                'id' => $this->labInfo->id ?? null,
                'labourName' => $this->labInfo->labourName ?? null,
                'concernPerson' => $this->labInfo->concernPerson ?? null,
                'name' => $this->labInfo->depot->name ?? null,
            ],
            'labourGroupId' => $this->labourGroupId,
            'labourBill' => $this->labourBill,
            'isLabourBill' => $this->isLabourBill,
            'remarks' => $this->remarks,
            'status' => $this->status,
            'crBy' => $this->createdBy ? $this->createdBy->name : null,
            'appBy' => $this->approvedBy ? $this->approvedBy->name : null,
            'details' => FeedReceiveDetailResource::collection($this->details),
        ];
    }
}