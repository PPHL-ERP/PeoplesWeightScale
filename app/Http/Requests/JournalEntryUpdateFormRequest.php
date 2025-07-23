<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JournalEntryUpdateFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'voucherDate' => 'required|date',
            'companyId' => 'required|integer|exists:companies,id',
            'debitSubGroupId' => 'required|integer',
            'debitHeadId' => 'required|integer|exists:account_ledger_name,id',
            'debit' => 'required|numeric|min:0',
            'creditSubGroupId' => 'required|integer',
            'creditHeadId' => 'required|integer|exists:account_ledger_name,id',
            'credit' => 'required|numeric|min:0',
            'checkNo' => 'nullable|string|max:255',
            'checkDate' => 'nullable|date',
            'trxId' => 'nullable|string|max:255',
            'ref' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array  
    {
        return [
            'voucherNo.required' => 'Voucher number is required for each entry.',
            'voucherDate.required' => 'Voucher date is required for each entry.',
            'companyId.exists' => 'The selected company does not exist.',
            'debitSubGroupId.exists' => 'The selected debit sub-group does not exist.',
            'debitHeadId.exists' => 'The selected debit head does not exist.',
            'creditSubGroupId.exists' => 'The selected credit sub-group does not exist.',
            'creditHeadId.exists' => 'The selected credit head does not exist.',
            'debit.min' => 'Debit amount must be at least 0.',
            'credit.min' => 'Credit amount must be at least 0.',
        ];
    }
}
