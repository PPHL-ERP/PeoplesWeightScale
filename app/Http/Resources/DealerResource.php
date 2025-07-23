<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DealerResource extends JsonResource
{
  public function toArray($request)
  {
    return [
      'id' => $this->id,
      'dealerCode' => $this->dealerCode,
      'dealerType' => $this->dealerType,
      'tradeName' => $this->tradeName,
      'tradeNameBn' => $this->tradeNameBn,
      'contactPerson' => $this->contactPerson,
      'address' => $this->address,
      'addressBn' => $this->addressBn,
      'shippingAddress' => $this->shippingAddress,
      'zone' => $this->whenLoaded('zone') ? [
        'id' => $this?->zone?->id,
        'zoneName' => $this?->zone?->zoneName,
        ] : null,
      'division' => $this->whenLoaded('division') ? [
        'id' => $this?->division?->id,
        'name' => $this?->division?->name,
        ] : null,

      'district' => $this->whenLoaded('district') ? [
        'id' => $this?->district?->id,
        'name' => $this?->district?->name,
        ] : null,

     'upazila' => $this->whenLoaded('upazila') ? [
        'id' => $this?->upazila?->id,
        'name' => $this?->upazila?->name,
        ] : null,
      'phone' => $this->phone,
      'email' => $this->email,
      'tradeLicenseNo' => $this->tradeLicenseNo,
      'isDueable' => $this->isDueable,
      'dueLimit' => $this->dueLimit,
      'referenceBy' => $this->referenceBy,
      'salesPerson' => $this->salesPerson,
      'openingBalance' => $this->openingBalance,
      'guarantor' => $this->guarantor,
      'guarantorPerson' => $this->guarantorPerson,
      'guarantorByCheck' => json_decode($this->guarantorByCheck, true),
      'dealerGroup' => $this->dealerGroup,
      'crBy' => $this->createdBy ? $this->createdBy->name : null,
      'appBy' => $this->approvedBy ? $this->approvedBy->name : null,
      'status' => $this->status,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
    ];
  }
}