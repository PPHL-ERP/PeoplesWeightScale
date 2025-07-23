<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesHasPaymentsResource extends JsonResource
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
            'sale' => [
                'id' => $this->sale->id ?? null,
                'saleId' => $this->sale->saleId ?? null,
            ],
            'payment' => [
                'id' => $this->payment->id ?? null,
                'name' => $this->payment->name ?? null,
            ],
            'bank' => [
                'id' => $this->bank->id ?? null,
                'bankName' => $this->bank->bankName ?? null,
            ],
            'cashInfo' => $this->cashInfo,
            'checkInfo' => $this->checkInfo,
        ];
    }
}
