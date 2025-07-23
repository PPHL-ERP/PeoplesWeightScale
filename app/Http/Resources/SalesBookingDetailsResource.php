<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesBookingDetailsResource extends JsonResource
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
            'booking' => [
                'id' => $this->booking->id ?? null,
                'bookingId' => $this->booking->bookingId ?? null,
            ],
            'product' => [
                'id' => $this->product->id ?? null,
                'productName' => $this->product->productName ?? null,
            ],
            'unit' => [
                'id' => $this->unit->id ?? null,
                'name' => $this->unit->name ?? null,
            ],
            'bookingQty' => $this->bookingQty,
            'bookingPrice' => $this->bookingPrice,
            'noteDetails' => $this->noteDetails,
          ];

        }
}
