<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesDraftResource extends JsonResource
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
            'saleId' => $this->saleId,
            'booking' => [
                'id' => $this->booking->id ?? null,
                'bookingId' => $this->booking->bookingId ?? null,
                'dealerCode' => $this->booking->dealer->dealerCode ?? null,
                'tradeName' => $this->booking->dealer->tradeName ?? null,
                //'contactPerson' => $this->booking->dealer->contactPerson ?? null,
                //'phone' => $this->booking->dealer->phone ?? null,
                'zoneName' => $this->booking->dealer->zone->zoneName ?? null,
            ],
            'saleCategory' => [
                'id' => $this->saleCategory->id ?? null,
                'name' => $this->saleCategory->name ?? null,
                'companyId' => $this->saleCategory->companyId ?? null,
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
            ],
            'company' => [
                'id' => $this->company->id ?? null,
                'nameEn' => $this->company->nameEn ?? null,
            ],
            'accountLedgerName' => [
                'id' => $this->accountLedgerName->id ?? null,
                'name' => $this->accountLedgerName->name ?? null,
            ],
            'saleType' => $this->saleType,
            'salesPerson' => $this->salesPerson,
            'transportType' => $this->transportType,
            'outTransportInfo' => json_decode($this->outTransportInfo, true),
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
            'sales_draft_details' => SalesDraftDetailsResource::collection($this->salesDraftDetails),
          ];
        }
}