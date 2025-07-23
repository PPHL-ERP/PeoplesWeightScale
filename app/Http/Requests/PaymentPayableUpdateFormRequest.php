<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentPayableUpdateFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'companyId' => 'required|integer|exists:companies,id', 
            'paidType' => 'required|integer|min:1|max:2',
            'payableId' => 'required|integer', // Ensure valid chart of head ID
            'amount' => 'required|numeric|min:0', // Amount should be a positive number
            'paidDate' => 'required|date', // Ensure valid date
            'paymentType' => 'required|integer',
            'paymentMode' => 'required|integer',
            'paymentFor' => 'required|integer',
            'note' => 'nullable|string|max:500',
            'invoiceType' => 'required|integer|min:1|max:2',
            'checkNo' => 'nullable|string|max:50',
            'checkDate' => 'nullable|date', // Nullable date for check date
            'trxId' => 'nullable|string|max:50',
            'ref' => 'nullable|string|max:50',
        ];
    }
}
