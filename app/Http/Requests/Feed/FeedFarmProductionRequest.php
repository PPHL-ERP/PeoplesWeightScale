<?php

namespace App\Http\Requests\Feed;

use Illuminate\Foundation\Http\FormRequest;

class FeedFarmProductionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sectorId' => 'required|integer|exists:sectors,id',

            //'batchNo' => 'required|string|max:255',

            'rows' => 'required|array|min:1',

            'rows.*.productId' => 'required|integer|exists:products,id',

            'rows.*.qty' => 'required|numeric|min:1',

            'productionDate' => 'required|date', // Ensure the date is required
            'expDate' => 'required|date', // Ensure the date is required
            'note' => 'nullable|string|max:500', // Optional note, limited to 500 characters values
        ];
    }

    public function messages()
    {
        return [
            // 'sectorId.required' => 'The sector field is mandatory.',
            // 'sectorId.integer' => 'The sector ID must be a valid number.',
            // 'sectorId.exists' => 'The selected sector does not exist.',
            // 'totalProduct.required' => 'Total product quantity is required.',
            // 'totalProduct.numeric' => 'Total product must be a number.',
            // 'totalProduct.min' => 'Total product must be at least 1.',
            // 'productionDate.required' => 'The production date is required.',
            // 'productionDate.date' => 'The production date must be a valid date.',
            // 'rows.required' => 'At least one product row is required.',
            // 'rows.array' => 'The rows field must be an array.',
            // 'rows.*.productId.required' => 'Product ID is required for each row.',
            // 'rows.*.productId.integer' => 'Product ID must be a valid number.',
            // 'rows.*.productId.exists' => 'The selected product does not exist.',
            // 'rows.*.qty.required' => 'Quantity is required for each row.',
            // 'rows.*.qty.numeric' => 'Quantity must be a number.',
            // 'rows.*.qty.min' => 'Quantity must be at least 1.',

            // 'flockId.required' => 'Flock ID is required',
            'productionDate.required' => 'Date is required',
            'expDate.required' => 'Date is required',
            'qty.numeric' => 'Quantity must be a number',
            'note.max' => 'Note must not exceed 500 characters',
            'status.in' => 'Status must be one of the following: approved, pending, declined',

        ];
    }
}
