<?php

namespace App\Http\Requests\Feed;

use Illuminate\Foundation\Http\FormRequest;

class FeedSalesReturnRequest extends FormRequest
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
            'returnPurpose' => 'nullable|string|max:255',
            'invoiceDate' => 'date|max:55',
            'returnDate' => 'date|max:55',
            'totalReturnAmount' => 'nullable',
            'discount' => 'nullable',
            'note' => 'nullable|string|max:500',
            'isLabourBill' => 'nullable',
            'status' => 'nullable|string|max:255',
        ];
    }
}