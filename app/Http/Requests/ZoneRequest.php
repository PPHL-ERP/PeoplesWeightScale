<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ZoneRequest extends FormRequest
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
            'zoneName' => 'required|string|max:255',
            'zonalInCharge' => 'nullable|string|max:255',
            //'districtId' => 'required|integer|exists:districts,id',
            'note' => 'nullable|string',
            // 'districtIds' => 'required|array',
            // 'districtIds.*' => 'integer|exists:districts,id',
        ];
    }
}
