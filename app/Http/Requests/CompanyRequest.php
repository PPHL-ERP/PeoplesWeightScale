<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyRequest extends FormRequest
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
        return  [
            'nameEn' => 'required|string|max:255',
            'nameBn'  => 'nullable|string|max:255',
            'sloganEn' => 'required|string|max:255',
            'sloganBn'  => 'nullable|string|max:255',
            'mobile' => 'string|max:14',
            'phone' => 'string|max:14',
            'email'      => 'required|email',
            'website' => 'required|string|max:55',
            'image'      => 'image',
            'tin' => 'string|max:55',
            'bin' => 'string|max:55',
            'addressEn' => 'string|max:255',
            'addressBn' => 'nullable|max:255',
            'comEx' => 'nullable|max:255',
           'status' => 'nullable|string|max:55',
        ];
    }
}
