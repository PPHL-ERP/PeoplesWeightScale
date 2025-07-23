<?php

namespace App\Http\Resources\Feed;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedOrderResource extends JsonResource
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
            'feedId' => $this->feedId,
            'booking' => [
                'id' => $this->booking->id ?? null,
                'bookingId' => $this->booking->bookingId ?? null,
                'dealerCode' => $this->booking->dealer->dealerCode ?? null,
                'tradeName' => $this->booking->dealer->tradeName ?? null,
                //'contactPerson' => $this->booking->dealer->contactPerson ?? null,
                //'phone' => $this->booking->dealer->phone ?? null,
                'zoneName' => $this->booking->dealer->zone->zoneName ?? null,
                'advanceAmount' => $this->booking->advanceAmount ?? null,
            ],
            'saleCategory' => [
                'id' => $this->saleCategory->id ?? null,
                'name' => $this->saleCategory->name ?? null,
                'companyId' => $this->saleCategory->companyId ?? null,
            ],
            'subCategory' => [
                'id' => $this->subCategory->id ?? null,
                'subCategoryName' => $this->subCategory->subCategoryName ?? null,
            ],
            'childCategory' => [
                'id' => $this->childCategory->id ?? null,
                'childCategoryName' => $this->childCategory->childCategoryName ?? null,
            ],
            'dealer' => [
                'id' => $this->dealer->id ?? null,
                'tradeName' => $this->dealer->tradeName ?? null,
                'dealerCode' => $this->dealer->dealerCode ?? null,
                'contactPerson' => $this->dealer->contactPerson ?? null,
                'phone' => $this->dealer->phone ?? null,
                'zoneName' => $this->dealer->zone->zoneName ?? null,
            ],
            'sector' => [
                'id' => $this->sector->id ?? null,
                'name' => $this->sector->name ?? null,
                'description' => $this->sector->description ?? null,

            ],
            'feedDraft' => [
                'id' => $this->feedDraft->id ?? null,
                'feedId' => $this->feedDraft->feedId ?? null,
            ],
            'company' => [
                'id' => $this->company->id ?? null,
                'nameEn' => $this->company->nameEn ?? null,
            ],
            'accountLedgerName' => [
                'id' => $this->accountLedgerName->id ?? null,
                'name' => $this->accountLedgerName->name ?? null,
            ],
            'commission' => [
                'id' => $this->commission->id ?? null,
                'commissionNo' => $this->commission->commissionNo ?? null,
            ],
            'saleType' => $this->saleType,
            'salesPerson' => $this->salesPerson,
            'transportType' => $this->transportType,
            //'loadBy' => $this->loadBy,
            'labInfo' => [
                'id' => $this->labInfo->id ?? null,
                'labourName' => $this->labInfo->labourName ?? null,
                'concernPerson' => $this->labInfo->concernPerson ?? null,
                'name' => $this->labInfo->depot->name ?? null,
            ],
            'isLabourBill' => $this->isLabourBill,
            'transportBy' => $this->transportBy,
            'outTransportInfo' => json_decode($this->outTransportInfo, true),
            'subTotal' => $this->subTotal,
            'dueAmount' => $this->dueAmount,
            'totalAmount' => $this->totalAmount,
            'discount' => $this->discount,
            'discountType' => $this->discountType,
            'fDiscount' => $this->fDiscount,
            'vat' => $this->vat,
            'invoiceDate' => $this->invoiceDate,
            'dueDate' => $this->dueDate,
            'note' => $this->note,
            'pOverRideBy' => $this->pOverRideBy,
            'transportCost' => $this->transportCost,
            'othersCost' => json_decode($this->othersCost, true),
            'crBy' => $this->createdBy ? $this->createdBy->name : null,
            'appBy' => $this->approvedBy ? $this->approvedBy->name : null,
            'status' => $this->status,
            'depotCost' => $this->depotCost,
            'paymentStatus' => $this->paymentStatus,
            'billingAddress' => $this->billingAddress,
            'deliveryAddress' => $this->deliveryAddress,
            'feed_details' => FeedOrderDetailsResource::collection($this->feedDetails),

            'feed_returns' => $this->returnDetails->map(function ($item) {
                $size = (float) ($item->product->sizeOrWeight ?? 0);
                $returnQty = (float) $item->rQty;
                $bagQty = $size > 0 ? round($returnQty / $size, 2) : 0;
                $salePrice = (float) $item->salePrice;

                return [
                    'product' => [
                        'id'            => $item->product->id ?? null,
                        'productName'   => $item->product->productName ?? null,
                        'shortName'     => $item->product->shortName ?? null,
                        'sizeOrWeight'  => $item->product->sizeOrWeight ?? null,
                    ],
                    'return_qty'      => $returnQty,
                    'return_bag_qty' => $bagQty,
                    'sale_price'     => $salePrice,
                    'return_amount'  => round($bagQty * $salePrice, 2),
                ];
            })->values(),


          ];
    }
}