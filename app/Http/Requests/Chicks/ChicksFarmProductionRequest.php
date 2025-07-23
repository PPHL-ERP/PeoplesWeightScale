<?php

namespace App\Http\Requests\Chicks;

use Illuminate\Foundation\Http\FormRequest;

class ChicksFarmProductionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hatcheryId' => 'nullable|integer|exists:sectors,id',
            // 'batchNo' => 'required|string|max:255',
            // 'rows' => 'required|array|min:1',
            // 'rows.*.productId' => 'required|integer|exists:products,id',
            // 'rows.*.totalEggQty' => 'required|numeric|min:1',
            // 'rows.*.avgWeight' => 'required|numeric|min:1',
            'hatchDate' => 'nullable|date', // Ensure the date is required
            'note' => 'nullable|string|max:500',
        ];
    }

    public function messages()
    {
        return [
            'hatchDate.nullable' => 'Date is required',
            'totalEggQty.numeric' => 'Quantity must be a number',
            'note.max' => 'Note must not exceed 500 characters',
            // 'status.in' => 'Status must be one of the following: approved, pending, declined',

        ];
    }
}