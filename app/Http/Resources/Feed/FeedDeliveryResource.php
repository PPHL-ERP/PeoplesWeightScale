<?php

namespace App\Http\Resources\Feed;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedDeliveryResource extends JsonResource
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
            'salesPerson' => $this->salesPerson,
            'deliveryPointDetails' => $this->deliveryPointDetails,
            'deliveryPersonDetails' => json_decode($this->deliveryPersonDetails, true),
            'deliveryDate' => $this->deliveryDate,
            'transportType' => $this->transportType,
            'roadInfo' => $this->roadInfo,
            'driverName' => $this->driverName,
            'mobile' => $this->mobile,
            'vehicleNo' => $this->vehicleNo,
            'note' => $this->note,
            'appBy' => $this->approvedBy ? $this->approvedBy->name : null,
            'status' => $this->status,

            'delivery_details' => FeedDeliveryResource::collection($this->deliveryDetails),
          ];
        }
}