<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FarmEggProductionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Allow all authorized users to make these requests
    }

    public function rules(): array
    {
        return [

            'flockId' => 'required|integer', // Make flockId required
            'date' => 'required|date', // Ensure the date is required
            'qty' => 'nullable|numeric', // Validate qty as numeric
            'note' => 'nullable|string|max:500', // Optional note, limited to 500 characters
            'status' => 'nullable|string|in:approved,pending,declined', // Restrict status to certain values
        ];
    }

    public function messages()
    {
        return [

            'flockId.required' => 'Flock ID is required',
            'date.required' => 'Date is required',
            'qty.numeric' => 'Quantity must be a number',
            'note.max' => 'Note must not exceed 500 characters',
            'status.in' => 'Status must be one of the following: approved, pending, declined',
        ];
    }
}
