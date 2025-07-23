<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesBookingResource extends JsonResource
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
            'bookingId' => $this->bookingId,
            'dealer' => [
                'id' => $this->dealer->id ?? null,
                'tradeName' => $this->dealer->tradeName ?? null,
                'dealerCode' => $this->dealer->dealerCode ?? null,
                'contactPerson' => $this->dealer->contactPerson ?? null,
                'phone' => $this->dealer->phone ?? null,
                'zoneName' => $this->dealer->zone->zoneName ?? null,
            ],
            'saleCategory' => [
                'id' => $this->saleCategory->id ?? null,
                'name' => $this->saleCategory->name ?? null,
                'companyId' => $this->saleCategory->companyId ?? null,
            ],
            //sector
            'bookingPoint' => [
                'id' => $this->bookingPoint->id ?? null,
                'name' => $this->bookingPoint->name ?? null,
            ],
            //employee name
            'bookingPerson' => $this->bookingPerson,
            'bookingType' => $this->bookingType,
            'isBookingMoney' => $this->isBookingMoney,
            'discount' => $this->discount,
            'discountType' => $this->discountType,
            'advanceAmount' => $this->advanceAmount,
            'totalAmount' => $this->totalAmount,
            'bookingDate' => $this->bookingDate,
            'invoiceDate' => $this->invoiceDate,
            'note' => $this->note,
            'crBy' => $this->createdBy ? $this->createdBy->name : null,
            'appBy' => $this->approvedBy ? $this->approvedBy->name : null,
            'status' => $this->status,
            'sales_bookings' => SalesBookingDetailsResource::collection($this->salesBookingDetails),
          ];
    }
}
