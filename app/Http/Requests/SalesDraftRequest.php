<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SalesDraftRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'saleType' => 'nullable|string|max:255',
            'salesPerson' => 'nullable|string|max:255',
            'transportType' => 'nullable|string|max:255',
            'dueAmount' => 'nullable',
            'totalAmount' => 'nullable',
            'discount' => 'nullable',
            'discountType' => 'nullable|string|max:255',
            'fDiscount' => 'nullable',
            'vat' => 'nullable',
            'invoiceDate' => 'date|max:55',
            'dueDate' => 'date|max:55',
            'note' => 'nullable|string|max:500',
            'pOverRideBy' => 'nullable',
            'transportCost' => 'nullable',
            'status' => 'nullable|string|max:255',
        ];
    }
}