<?php

namespace App\Http\Requests\Feed;

use Illuminate\Foundation\Http\FormRequest;

class LabourDetailRequest extends FormRequest
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
            'transactionType' => 'nullable',
            'workType' => 'nullable',
            'tDate' => 'date|max:55',
            'qty' => 'nullable',
            'bAmount' => 'nullable',
            'payStatus' => 'nullable',
            'status' => 'nullable|string|max:255',
        ];
    }
}