<?php

namespace App\Http\Requests\Chicks;

use Illuminate\Foundation\Http\FormRequest;

class ChicksStockAdjustmentRequest extends FormRequest
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
            'adjCategory' => 'required|string',
            'date' => 'date|max:55',
            'initialQty' => 'nullable|numeric',
            'adjQty' => 'nullable|numeric',
            'finalQty' => 'nullable|numeric',
            'batchNo' => 'nullable',
            'referenceId' => 'nullable',
            'referenceType' => 'nullable',
            'note' => 'nullable|string|max:500',
            'status' => 'nullable|string|max:255',
        ];
    }
}
