<?php

namespace App\Services;

use App\Models\Transaction;

class AccountsCreditService
{
    public function setCreditData(int $chartOfHeadId, int $companyId, string $voucherNo, string $voucherType, string $voucherDate, string $note, float $credit)
    {
        Transaction::create([
            'chartOfHeadId' => $chartOfHeadId,
            'companyId' => $companyId,
            'voucherNo' => $voucherNo,
            'voucherType' => $voucherType,
            'voucherDate' => $voucherDate,
            'note' => $note,
            'debit' => 0,
            'credit' => $credit,
            'status' => 1,
            'createdBy' => auth()->id(),
        ]);
    }
}