<?php

namespace App\Http\Resources\Feed;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedBookingDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $sizeOrWeight = $this->product->sizeOrWeight ?? 0;
        $bookingQty = $this->bookingQty;
        $bagQty = ($sizeOrWeight > 0) ? round($bookingQty / $sizeOrWeight, 2) : null;
        return [
            'id' => $this->id,
            'booking' => [
                'id' => $this->booking->id ?? null,
                'bookingId' => $this->booking->bookingId ?? null,
            ],
            'product' => [
                'id' => $this->product->id ?? null,
                'productName' => $this->product->productName ?? null,
                'sizeOrWeight' => $this->product->sizeOrWeight ?? null,
                'shortName' => $this->product->shortName ?? null,
            ],
            'unit' => [
                'id' => $this->unit->id ?? null,
                'name' => $this->unit->name ?? null,
            ],
            'bagQty' => $bagQty,
            'bookingQty' => $this->bookingQty,
            'bookingPrice' => $this->bookingPrice,
            'noteDetails' => $this->noteDetails,
          ];
        }
}
