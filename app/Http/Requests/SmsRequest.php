<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SmsRequest extends FormRequest
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
            'apiKey' => 'string|max:255|',
            'gatewayName'  => 'required|string|max:255',
            'mSenderId'  => 'nullable',
            'nmSenderId'  => 'nullable',
            'language'  => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'url' =>  'required|string|max:255',
            'headerTxtEn'  => 'nullable|string|max:255',
            'headerTxtBn'  => 'nullable|string|max:255',
            'footerTxtEn'  => 'nullable|string|max:255',
            'footerTxtBn'  => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
        ];

    }
}