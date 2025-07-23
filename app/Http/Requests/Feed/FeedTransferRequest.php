<?php

namespace App\Http\Requests\Feed;

use Illuminate\Foundation\Http\FormRequest;

class FeedTransferRequest extends FormRequest
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
            'isLabourBill' => 'nullable',
            'note' => 'nullable|string',
            'details' => 'required|array', // Validate details array
            'details.*.productId' => 'required|integer|exists:products,id',
            'details.*.qty' => 'required|numeric|min:1',
            'details.*.transferFor' => 'nullable|string|max:255',
            'details.*.note' => 'nullable|string',
        ];
    }
}
