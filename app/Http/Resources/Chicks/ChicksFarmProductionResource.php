<?php

namespace App\Http\Resources\Chicks;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChicksFarmProductionResource extends JsonResource
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
            'productionId' => $this->productionId,
            'hatchery' => [
                'id' => $this->hatchery->id ?? null,
                'name' => $this->hatchery->name ?? null,
            ],
            'settingId' => $this->settingId,
            'eggSource' => $this->eggSource,
            'settingDate' => $this->settingDate,
            'hatchDate' => $this->hatchDate,
            //'breedId' => $this->breedId,
            'breed' => [
                'id' => $this->breed->id ?? null,
                'breedName' => $this->breed->breedName ?? null,
            ],
            'flock' => [
                'id' => $this->flock->id ?? null,
                'flockName' => $this->flock->flockName ?? null,
            ],
            'color' => $this->color,
            'totalEggSetting' => $this->totalEggSetting,
            'note' => $this->note,
            'crBy' => $this->createdBy ? $this->createdBy->name : null,
            'appBy' => $this->approvedBy ? $this->approvedBy->name : null,
            'status' => $this->status,
            'details' => ChicksFarmProductionDetailsResource::collection($this->whenLoaded('details')),
            //
            'productSummaries' => $this->details
            ? $this->details
                ->groupBy('productId')
                ->map(function ($group) {
                    $product = $group->first()->product;
                    return [
                        'productId' => $group->first()->productId,
                        'productName' => $product?->productName ?? 'Unknown Product',
                        'totalApproxQty' => $group->sum('approxQty'),
                        'totalFinalQty' => $group->sum('finalQty'),
                    ];
                })->values()
            : [],



        ];


    }
}