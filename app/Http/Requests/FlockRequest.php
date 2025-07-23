<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FlockRequest extends FormRequest
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
            'flockName' => 'required|string|max:255',
            'flockType' => 'nullable',
            'stockDate' => 'date|max:55',
            'note' => 'nullable|string|max:500',
            'status' => 'nullable|string|max:255',
        ];
    }
}
