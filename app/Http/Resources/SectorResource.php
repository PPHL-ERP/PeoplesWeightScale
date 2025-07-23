<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SectorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'company' => $this->company ? [
                'id' => $this->company->id ?? null,
                'nameEn' => $this->company->nameEn ?? null,
            ] : null,
            'name' => $this->name,
            'isFarm' => $this->isFarm,
            'isSalesPoint' => $this->isSalesPoint,
            'salesPointName' => $this->salesPointName,
            'feedDepotCost' => $this->feedDepotCost,
            'chicksDepotCost' => $this->chicksDepotCost,
            'sectorType' => $this->sectorType,
            'inchargeName' => $this->inchargeName,
            'inchargePhone' => $this->inchargePhone,
            'inchargeAddress' => $this->inchargeAddress,
            'description' => $this->description,
            'status' => $this->status,
        ];
    }
}
