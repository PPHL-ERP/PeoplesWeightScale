<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EggTransferRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Set to false if authorization logic is needed
    }

    public function rules()
    {
        return [

            'transferHead' => 'required|string|max:255',
            'trType' => 'required|string|max:255',
            'fromStore' => 'required|integer', // Ensure the fromStore exists in the stores table
            'toStore' => 'required|integer',
            'transportType' => 'nullable|string|max:255',
            'driverName' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'vehicleNo' => 'nullable|string|max:255',
            'date' => 'required|date',
            'loadBy' => 'nullable|string|max:255',
            'labourGroupId' => 'nullable',
            'labourBill' => 'nullable',
            'note' => 'nullable|string',
            'details' => 'required|array', // Validate details array
            'details.*.productId' => 'required|integer|exists:products,id',
            'details.*.qty' => 'required|numeric|min:1',
            'details.*.transferFor' => 'nullable|string|max:255',
            'details.*.note' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'trId.required' => 'Transfer ID is required.',
            'details.*.productId.required' => 'Product ID is required for each detail.',
            'details.*.qty.required' => 'Quantity is required for each product.',
            'details.*.qty.min' => 'Quantity must be at least 1.',
        ];
    }
}