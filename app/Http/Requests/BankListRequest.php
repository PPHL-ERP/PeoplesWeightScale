<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BankListRequest extends FormRequest
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
            'bankName' => 'required|string|max:255',
            'bankBranch' => 'nullable',
            'accountHolder' => 'required',
            'bankaAccountType' => 'required',
            'accountNo' => 'required|string|max:255',
            'routingNo' => 'nullable',
            'isMobileBanking' => 'nullable',
            'isCash' => 'nullable',
            'contactNo' => 'required',
            'bankAddress' => 'nullable',
            'openingBalance' => 'nullable',
            'shortName' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:500',
            'status' => 'nullable|string|max:55',
         ];
    }
}
