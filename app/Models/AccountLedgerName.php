<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountLedgerName extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = ['id'];
    protected $table = 'account_ledger_name';
    protected $casts = [
        'is_active' => 'boolean',
        'is_posting_allowed' => 'boolean',
    ];
    public function accountGroup()
    {
        return $this->hasOne(AccountGroup::class, 'id', 'groupId');
    }

    public function accountSubGroup()
    {
        return $this->hasOne(AccountSubGroup::class, 'id', 'subGroupId');
    }

    public function accountClass()
    {
        return $this->hasOne(AccountClass::class, 'id', 'classId');
    }

    public function company()
    {
        return $this->hasOne(Company::class, 'id', 'company_id');
    }

}