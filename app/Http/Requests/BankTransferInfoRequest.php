<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BankTransferInfoRequest extends FormRequest
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
           // 'transactionDate' => 'date|max:55',
            // 'transferType' => 'nullable|string|max:255',
            // 'trPurpose' => 'nullable|string|max:255',
            // 'modeOfTransfer' => 'nullable',
            // 'amount' => 'nullable|numeric',
            // 'note' => 'nullable|string|max:500',
            // 'chequeNo' => 'nullable',
            //'chequeDate' => 'date|max:55',
           // 'status' => 'nullable|string|max:255',
        ];
    }
}