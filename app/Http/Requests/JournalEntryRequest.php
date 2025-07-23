<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entries' => 'required|array',
            'entries.*.voucherNo' => 'required|string|max:255',
            'entries.*.voucherDate' => 'required|date',
            'entries.*.companyId' => 'required|integer|exists:companies,id',
            'entries.*.debitSubGroupId' => 'required|integer',
            'entries.*.debitHeadId' => 'required|integer|exists:account_ledger_name,id',
            'entries.*.debit' => 'required|numeric|min:0',
            'entries.*.creditSubGroupId' => 'required|integer',
            'entries.*.creditHeadId' => 'required|integer|exists:account_ledger_name,id',
            'entries.*.credit' => 'required|numeric|min:0',
            'entries.*.checkNo' => 'nullable|string|max:255',
            'entries.*.checkDate' => 'nullable|date',
            'entries.*.trxId' => 'nullable|string|max:255',
            'entries.*.ref' => 'nullable|string|max:255',
            'entries.*.note' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'entries.*.voucherNo.required' => 'Voucher number is required for each entry.',
            'entries.*.voucherDate.required' => 'Voucher date is required for each entry.',
            'entries.*.companyId.exists' => 'The selected company does not exist.',
            'entries.*.debitSubGroupId.exists' => 'The selected debit sub-group does not exist.',
            'entries.*.debitHeadId.exists' => 'The selected debit head does not exist.',
            'entries.*.creditSubGroupId.exists' => 'The selected credit sub-group does not exist.',
            'entries.*.creditHeadId.exists' => 'The selected credit head does not exist.',
            'entries.*.debit.min' => 'Debit amount must be at least 0.',
            'entries.*.credit.min' => 'Credit amount must be at least 0.',
        ];
    }
}