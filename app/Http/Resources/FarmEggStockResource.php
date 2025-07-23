<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FarmEggStockResource extends JsonResource
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
            'sector' => [
                'id' => $this->sector->id ?? null,
                'name' => $this->sector->name ?? null,
            ],
            'stockDate' => $this->stockDate,
            'dEgg' => $this->dEgg,
            'bEgg' => $this->bEgg,
            'mEgg' => $this->mEgg,
            'smEgg' => $this->smEgg,
            'brokenEgg' => $this->brokenEgg,
            'liqEgg' => $this->liqEgg,
            'wasteEgg' => $this->wasteEgg,
            'adjEgg' => $this->adjEgg,
            'others' => $this->others,
            'note' => $this->note,
            'crBy' => $this->createdBy ? $this->createdBy->name : null,
            'appBy' => $this->approvedBy ? $this->approvedBy->name : null,
            'status' => $this->status,
          ];
        }
}