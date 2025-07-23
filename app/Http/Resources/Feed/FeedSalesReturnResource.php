<?php

namespace App\Http\Resources\Feed;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedSalesReturnResource extends JsonResource
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
            'saleReturnId' => $this->saleReturnId,
            'feed' => [
                'id' => $this->feed->id ?? null,
                'feedId' => $this->feed->feedId ?? null,
                'dealerCode' => $this->feed->dealer->dealerCode ?? null,
                'tradeName' => $this->feed->dealer->tradeName ?? null,
                'contactPerson' => $this->feed->dealer->contactPerson ?? null,
                'phone' => $this->feed->dealer->phone ?? null,
                'zoneName' => $this->feed->dealer->zone->zoneName ?? null,
            ],
            'dealer' => [
                'id' => $this->dealer->id ?? null,
                'tradeName' => $this->dealer->tradeName ?? null,
                'dealerCode' => $this->dealer->dealerCode ?? null,
                'contactPerson' => $this->dealer->contactPerson ?? null,
                'phone' => $this->dealer->phone ?? null,
                'zoneName' => $this->dealer->zone->zoneName ?? null,
            ],
            'returnPurpose' => $this->returnPurpose,
            'invoiceDate' => $this->invoiceDate,
            'returnDate' => $this->returnDate,
            'totalReturnAmount' => $this->totalReturnAmount,
            'discount' => $this->discount,
            'note' => $this->note,
            'isLabourBill' => $this->isLabourBill,
            'crBy' => $this->createdBy ? $this->createdBy->name : null,
            'appBy' => $this->approvedBy ? $this->approvedBy->name : null,
            'status' => $this->status,
            'sales_return_details' => FeedSalesReturnDetailsResource::collection($this->saleReturnDetails),
          ];
     }
}