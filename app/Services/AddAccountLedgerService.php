<?php 
namespace App\Services;

use App\Models\AccountLedgerName;

class AddAccountLedgerService
{
    public function addAccountLedger($name, $code, $groupId, $subGroupId, $description, $company_id, $classId, $nature, $opening_balance, $current_balance, $is_active, $is_posting_allowed, $partyId, $partyType)
    {
        AccountLedgerName::create([
            'name' => $name,
            'code' => $code,
            'groupId' => $groupId,
            'subGroupId' => $subGroupId,
            'description' => $description,
            'company_id' => $company_id,
            'classId' => $classId,
            'nature' => $nature,
            'opening_balance' => $opening_balance,
            'current_balance' => $current_balance,
            'is_active' => $is_active,
            'is_posting_allowed' => $is_posting_allowed,
            'partyId' => $partyId,
            'partyType' => $partyType
        ]);
    }
}