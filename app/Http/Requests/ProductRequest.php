<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
            'productName' => 'required|string|max:255',
            'productType' => 'nullable|string|max:255',
            'sn' => 'nullable',
            'qrCode' => 'nullable',
            'batchNo' => 'nullable',
            'basePrice' => 'required',
            'sizeOrWeight' => 'nullable',
            'shortName' => 'nullable',
            'productForm' => 'nullable',
            'warranty' => 'nullable',
            'minStock' => 'nullable',
            'description' => 'nullable|string|max:500',
            'status' => 'nullable|string|max:255',
        ];
    }
}