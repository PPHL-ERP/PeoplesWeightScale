<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SmsResource extends JsonResource
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
            'apiKey' => $this->apiKey,
            'gatewayName' => $this->gatewayName,
            'mSenderId' => $this->mSenderId,
            'nmSenderId' => $this->nmSenderId,
            'language' => $this->language,
            'type' => $this->type,
            'url' => $this->url,
            'headerTxtEn' => $this->headerTxtEn,
            'headerTxtBn' => $this->headerTxtBn,
            'footerTxtEn' => $this->footerTxtEn,
            'footerTxtBn' => $this->footerTxtBn,
            'status' => $this->status,
        ];
    }
}