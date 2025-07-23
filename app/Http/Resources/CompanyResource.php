<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return[
            'id' => $this->id,
            'nameEn' => $this->nameEn,
            'nameBn' => $this->nameBn,
            'sloganEn' => $this->sloganEn,
            'sloganBn' => $this->sloganBn,
            'mobile' => $this->mobile,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'image' =>config('app.url')."/". config('imagepath.company').$this->image,
            'tin' => $this->tin,
            'bin' => $this->bin,
            'addressEn' => $this->addressEn,
            'addressBn' => $this->addressBn,
            'comEx' => $this->comEx,
            'status' => $this->status,

        ];    }
}