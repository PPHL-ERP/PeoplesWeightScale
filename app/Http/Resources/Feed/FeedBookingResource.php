<?php

namespace App\Http\Resources\Feed;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedBookingResource extends JsonResource
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
            'subCategory' => [
                'id' => $this->subCategory->id ?? null,
                'subCategoryName' => $this->subCategory->subCategoryName ?? null,
            ],
            'childCategory' => [
                'id' => $this->childCategory->id ?? null,
                'childCategoryName' => $this->childCategory->childCategoryName ?? null,
            ],
            //sector
            'bookingPoint' => [
                'id' => $this->bookingPoint->id ?? null,
                'name' => $this->bookingPoint->name ?? null,
            ],
            //employee name
            'bookingPerson' => $this->bookingPerson,
             //commission
             'commission' => [
                'id' => $this->commission->id ?? null,
                'commissionNo' => $this->commission->commissionNo ?? null,
            ],
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
            'feed_bookings' => FeedBookingDetailsResource::collection($this->feedBookingDetails),
          ];
    }
}