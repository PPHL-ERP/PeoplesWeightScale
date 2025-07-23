<?php

namespace App\Http\Requests\Chicks;

use Illuminate\Foundation\Http\FormRequest;

class ChicksDailyPriceRequest extends FormRequest
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
            'date' => 'required|date',
            'pCost' => 'nullable|numeric',
            'mrp' => 'nullable|numeric',
            'salePrice' => 'nullable|numeric',
            'status' => 'nullable|string',
        ];
    }
}