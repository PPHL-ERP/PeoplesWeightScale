<?php

namespace App\Http\Requests\Feed;

use Illuminate\Foundation\Http\FormRequest;

class LabourInfoRequest extends FormRequest
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
            'labourName' => 'required|string|max:255',
            'concernPerson' => 'nullable|string|max:255',
            'contactNo' => 'required',
            'location' => 'nullable',
            'contactDate' => 'date|max:55',
            'expDate' => 'date|max:55',
            'fPrice' => 'nullable',
            'cPrice' => 'nullable',
            'oPrice' => 'nullable',
            'paymentCycle' => 'nullable',
            'paymentType' => 'nullable',
            'paymentInfo' => 'nullable',
            'note' => 'nullable|string|max:500',
            'status' => 'nullable|string|max:255',
        ];
    }
}