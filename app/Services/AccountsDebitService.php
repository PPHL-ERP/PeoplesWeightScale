<?php

namespace App\Services;

use App\Models\Transaction;

class AccountsDebitService
{
    public function setDebitData(int $chartOfHeadId, int $companyId, string $voucherNo, string $voucherType, string $voucherDate, string $note, float $debit)
    {
        Transaction::create([
            'chartOfHeadId' => $chartOfHeadId,
            'companyId' => $companyId,
            'voucherNo' => $voucherNo,
            'voucherType' => $voucherType,
            'voucherDate' => $voucherDate,
            'note' => $note,
            'debit' => $debit,
            'credit' => 0,
            'status' => 1,
            'createdBy' => auth()->id(),
        ]);
    }
}