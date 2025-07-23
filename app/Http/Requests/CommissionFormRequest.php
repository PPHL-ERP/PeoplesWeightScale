<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommissionFormRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fields' => 'required|array',
            'fields.*.companyId' => 'required|integer|exists:companies,id',
            'fields.*.commissionDate' => 'required|date',
            'fields.*.commissionType' => 'required|numeric|in:1,2,3',
            'fields.*.categoryId' => 'nullable|integer',
            'fields.*.dealerId' => 'nullable|integer|exists:dealers,id',
            'fields.*.zoneId' => 'nullable|integer|exists:zones,id',
            'fields.*.note' => 'nullable|string|max:255',
            'fields.*.products' => 'required|array',
            'fields.*.products.*.productId' => 'required|integer|exists:products,id',
            'fields.*.products.*.generalCommissionPercentagePerBag' => 'required|numeric|min:0',
            'fields.*.products.*.cashIncentivePerBag' => 'required|numeric|min:0',
            'fields.*.products.*.monthlyTargetQuantity' => 'required|integer|min:0',
            'fields.*.products.*.monthlyTargetPerBagCashAmount' => 'required|numeric|min:0',
            'fields.*.products.*.yearlyTargetQuantity' => 'required|integer|min:0',
            'fields.*.products.*.yearlyTargetPerBagCashAmount' => 'required|numeric|min:0',
            'fields.*.products.*.perBagTransportDiscountAmount' => 'required|numeric|min:0',
            'fields.*.products.*.specialTargetQuantity' => 'nullable|integer|min:0',
            'fields.*.products.*.specialTargetPerBagCashAmount' => 'nullable|numeric|min:0',
            'fields.*.products.*.incentiveCashBack' => 'nullable|numeric|min:0',
        ];
    }
}
