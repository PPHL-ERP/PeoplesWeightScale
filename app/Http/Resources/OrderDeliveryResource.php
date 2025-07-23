<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDeliveryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            //'saleId'       => $this->sale_id,
            'feedId'       => $this->sale_id,
            'productId'    => $this->product_id,
            'qty'          => $this->qty,
            'dealerCode'   => $this->dealer_code,
            'driverName'   => $this->driver_name,
            'driverPhone'  => $this->driver_phone,
            'vehicleNo'    => $this->vehicle_no,
            'vehicleType'  => $this->vehicle_type,
            'status'       => $this->status,
            'createdAt'    => $this->created_at,
            'updatedAt'    => $this->updated_at,
        ];
    }
}