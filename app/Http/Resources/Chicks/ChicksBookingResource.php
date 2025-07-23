<?php

namespace App\Http\Resources\Chicks;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChicksBookingResource extends JsonResource
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
            'cBookingId' => $this->cBookingId,
            'dealer' => [
                'id' => $this->dealer->id ?? null,
                'tradeName' => $this->dealer->tradeName ?? null,
                'dealerCode' => $this->dealer->dealerCode ?? null,
                'contactPerson' => $this->dealer->contactPerson ?? null,
                'phone' => $this->dealer->phone ?? null,
                'zoneName' => $this->dealer->zone->zoneName ?? null,
            ],
            'category' => [
                'id' => $this->category->id ?? null,
                'name' => $this->category->name ?? null,
                'companyId' => $this->category->companyId ?? null,
            ],
            'subCategory' => [
                'id' => $this->subCategory->id ?? null,
                'subCategoryName' => $this->subCategory->subCategoryName ?? null,
            ],
            'childCategory' => [
                'id' => $this->childCategory->id ?? null,
                'childCategoryName' => $this->childCategory->childCategoryName ?? null,
            ],
            'commission' => [
                'id' => $this->commission->id ?? null,
                'commissionNo' => $this->commission->commissionNo ?? null,
            ],
            'bookingPoint' => [
                'id' => $this->bookingPoint->id ?? null,
                'name' => $this->bookingPoint->name ?? null,
            ],
            'cPrice' => [
                'id' => $this->cPrice->id ?? null,
            ],
            'bookingPerson' => $this->bookingPerson,
            'bookingType' => $this->bookingType,
            'isBookingMoney' => $this->isBookingMoney,
            'isMultiDelivery' => $this->isMultiDelivery,
            'deliveryDetails' => json_decode($this->deliveryDetails, true),
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
            'chicks_bookings' => ChicksBookingDetailResource::collection($this->chicksBookingDetails),
        ];
    }
}