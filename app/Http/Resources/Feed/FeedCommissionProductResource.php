<?php

namespace App\Http\Resources\Feed;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedCommissionProductResource extends JsonResource
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
            'commission' => [
                'id' => $this->commission->id ?? null,
                'commissionNo' => $this->commission->commissionNo ?? null,
            ],
            'product' => [
                'id' => $this->product->id ?? null,
                'productName' => $this->product->productName ?? null,
                'shortName' => $this->product->shortName ?? null,
                'basePrice' => $this->product->basePrice ?? null,

            ],

            'generalCommissionPercentagePerBag' => $this->generalCommissionPercentagePerBag,
            'cashIncentivePerBag' => $this->cashIncentivePerBag,
            'monthlyTargetQuantity' => $this->monthlyTargetQuantity,
            'monthlyTargetPerBagCashAmount' => $this->monthlyTargetPerBagCashAmount,
            'yearlyTargetQuantity' => $this->yearlyTargetQuantity,
            'yearlyTargetPerBagCashAmount' => $this->yearlyTargetPerBagCashAmount,
            'perBagTransportDiscountAmount' => $this->perBagTransportDiscountAmount,
            'specialTargetQuantity' => $this->specialTargetQuantity,
            'specialTargetPerBagCashAmount' => $this->specialTargetPerBagCashAmount,
            'incentiveCashBack' => $this->incentiveCashBack,
            'currentProductAmount' => $this->currentProductAmount,
            'productStatus' => $this->productStatus,
        ];
    }
}
