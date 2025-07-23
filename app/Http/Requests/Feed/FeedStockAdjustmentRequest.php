<?php

namespace App\Http\Requests\Feed;

use Illuminate\Foundation\Http\FormRequest;

class FeedStockAdjustmentRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'adjCategory' => 'required|string',
            'date' => 'date|max:55',
            'initialQty' => 'nullable|numeric',
            'adjQty' => 'nullable|numeric',
            'finalQty' => 'nullable|numeric',
            'note' => 'nullable|string|max:500',
            'status' => 'nullable|string|max:255',
        ];
    }
}