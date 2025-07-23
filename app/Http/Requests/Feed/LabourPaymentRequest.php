<?php

namespace App\Http\Requests\Feed;

use Illuminate\Foundation\Http\FormRequest;

class LabourPaymentRequest extends FormRequest
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
            'transactionType' => 'nullable',
            'workType' => 'nullable',
            'billStartDate' => 'date|max:55',
            'billEndDate' => 'date|max:55',
            'paymentDate' => 'date|max:55',
            'totalQty' => 'nullable',
            'totalAmount' => 'nullable',
            'priceInfo' => 'nullable',
            'note' => 'nullable|string|max:500',
            'billStatus' => 'nullable',
            'status' => 'nullable|string|max:255',
        ];
    }
}
