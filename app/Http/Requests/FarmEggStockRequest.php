<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FarmEggStockRequest extends FormRequest
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
            'dEgg' => 'nullable|string|max:255',
            'bEgg' => 'nullable|string|max:255',
            'mEgg' => 'nullable|string|max:255',
            'smEgg' => 'nullable|string|max:255',
            'brokenEgg' => 'nullable|string|max:255',
            'liqEgg' => 'nullable|string|max:255',
            'wasteEgg' => 'nullable|string|max:255',
            'adjEgg' => 'nullable|string|max:255',
            'others' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:500',
            'status' => 'nullable|string|max:255',
        ];
    }
}