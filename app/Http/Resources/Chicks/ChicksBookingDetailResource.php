<?php

namespace App\Http\Resources\Chicks;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChicksBookingDetailResource extends JsonResource
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
            'chicksBooking' => [
                'id' => $this->chicksBooking->id ?? null,
                'cBookingId' => $this->chicksBooking->cBookingId ?? null,
            ],
            'product' => [
                'id' => $this->product->id ?? null,
                'productName' => $this->product->productName ?? null,
                'sizeOrWeight' => $this->product->sizeOrWeight ?? null,
                'shortName' => $this->product->shortName ?? null,
                'batchNo' => $this->product->batchNo ?? null,
            ],
            'cdPrice' => [
                'id' => $this->cdPrice->id ?? null,
            ],
            'unit' => [
                'id' => $this->unit->id ?? null,
                'name' => $this->unit->name ?? null,
            ],
            'settingId' => json_decode($this->settingId, true),
            'flockId' => json_decode($this->flockId, true),
            'bQty' => $this->bQty,
            'salePrice' => $this->salePrice,
            'mrp' => $this->mrp,
            'note' => $this->note,
        ];
    }
}