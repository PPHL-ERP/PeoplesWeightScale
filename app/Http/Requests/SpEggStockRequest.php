<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SpEggStockRequest extends FormRequest
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
            'stockDate' => 'date|max:55',
            'saleDate' => 'date|max:55',
        ];
    }
}