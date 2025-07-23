<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommissionUpdateFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'companyId' => 'required|integer|exists:companies,id',
            'commissionDate' => 'required|date',
            'commissionType' => 'required|numeric|in:1,2,3',
            'categoryId' => 'nullable|integer',
            'dealerId' => 'nullable|integer|exists:dealers,id',
            'zoneId' => 'nullable|integer|exists:zones,id',
            'note' => 'nullable|string|max:255',
            'products' => 'required|array',
            'products.*.productId' => 'required|integer|exists:products,id',
            'products.*.generalCommissionPercentagePerBag' => 'required|numeric|min:0',
            'products.*.cashIncentivePerBag' => 'required|numeric|min:0',
            'products.*.monthlyTargetQuantity' => 'required|integer|min:0',
            'products.*.monthlyTargetPerBagCashAmount' => 'required|numeric|min:0',
            'products.*.yearlyTargetQuantity' => 'required|integer|min:0',
            'products.*.yearlyTargetPerBagCashAmount' => 'required|numeric|min:0',
            'products.*.perBagTransportDiscountAmount' => 'required|numeric|min:0',
            'products.*.specialTargetQuantity' => 'nullable|integer|min:0',
            'products.*.specialTargetPerBagCashAmount' => 'nullable|numeric|min:0',
            'products.*.incentiveCashBack' => 'nullable|numeric|min:0',
        ];
    }
}
