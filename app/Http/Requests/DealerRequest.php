<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DealerRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Change to false if you need authorization
    }

    public function rules()
    {
        return [
            //'dealerCode' => 'required|string|max:255',
            'dealerType' => 'nullable',
            'tradeName' => 'required|string|max:255',
            'tradeNameBn' => 'nullable|string|max:255',
            'contactPerson' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'addressBn' => 'nullable|string|max:255',
            'shippingAddress' => 'nullable|string|max:255',
            //'zone' => 'nullable|string|max:255',
            // 'division' => 'nullable',
            // 'district' => 'nullable',
            // 'upazila' => 'nullable',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255',
            'tradeLicenseNo' => 'nullable|string|max:255',
            'dueLimit' => 'nullable|numeric',
            'referenceBy' => 'nullable|string|max:255',
            'openingBalance' => 'nullable',
            'guarantor' => 'nullable|string|max:255',
            'guarantorPerson' => 'nullable|string|max:255',
            'dealerGroup' => 'nullable|string|max:255',
            'crBy' => 'nullable|string|max:255',
            'appBy' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
        ];
    }


}
