<?php

namespace App\Http\Resources\Feed;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedProductionLedgerResource extends JsonResource
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
            'product' => [
                'id' => $this->product->id ?? null,
                'productName' => $this->product->productName ?? null,
            ],
            'transactionId' => $this->transactionId ?? 'null',
            'trType' => $this->trType ?? 'null',
            'date' => $this->date ?? 'null',
            'qty' => $this->qty ?? 'null',
            'lockQty' => $this->lockQty ?? 'null',
            'closingBalance' => $this->closingBalance ?? 'null',
            'remarks' => $this->remarks ?? 'null',
            'appBy' => $this->approvedBy ? $this->approvedBy->name : null,
            'status' => $this->status ?? 'null',
        ];
      }
}