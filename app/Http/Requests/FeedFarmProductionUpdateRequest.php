<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FeedFarmProductionUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sectorId' => 'required|integer|exists:sectors,id',

            'batchNo' => 'required|string|max:255',

            'productId' => 'required|integer|exists:products,id',

            'qty' => 'required|numeric|min:1',

            'productionDate' => 'required|date', // Ensure the date is required
            'note' => 'nullable|string|max:500', // Optional note, limited to 500 characters values
        ];
    }
}
