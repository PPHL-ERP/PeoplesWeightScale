<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuantityTypeRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'codeName' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:500',
        ];
    }
}