<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentReceiveInfoRequest extends FormRequest
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
            'payments' => 'required|array|min:1', // Ensure 'payments' is an array and has at least one item
            // 'payments.*.voucherNo' => 'required|string|max:255',
            'payments.*.companyId' => 'required|integer|exists:companies,id', // Assuming 'companyId' refers to a valid company in a 'companies' table
            'payments.*.recType' => 'required|integer|min:1|max:2',
            'payments.*.receiverId' => 'required|integer', // Ensure valid chart of head ID
            'payments.*.amount' => 'required|numeric|min:0', // Amount should be a positive number
            'payments.*.recDate' => 'required|date', // Ensure valid date
            'payments.*.paymentType' => 'required|integer',
            'payments.*.paymentMode' => 'required|integer',
            'payments.*.paymentFor' => 'required|integer',
            'payments.*.note' => 'nullable|string|max:500',
            'payments.*.invoiceType' => 'required|integer|min:1|max:2',
            'payments.*.checkNo' => 'nullable|string|max:50',
            'payments.*.checkDate' => 'nullable|date', // Nullable date for check date
            'payments.*.trxId' => 'nullable|string|max:50',
            'payments.*.ref' => 'nullable|string|max:50',
        ];
    }
    
}