<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebitVoucher extends Model
{
    use HasFactory;
    protected $fillable = [
        'voucherNo',
        'companyId',
        'voucherDate',
        'creditHeadId',
        'amount',
        'checkNo',
        'checkDate',
        'trxId',
        'ref',
        'status',
        'createdBy',
        'modifiedBy',
        'deletedBy',
        'appBy',
        'note',
    ];
    protected $table = 'debit_vouchers';

    public function itemList()
    {
        return $this->hasMany(DebitVoucheritem::class, 'debitVoucherId', 'id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'companyId', 'id');
    }

    public function creditHead()
    {
        return $this->belongsTo(AccountLedgerName::class, 'creditHeadId', 'id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'appBy', 'id');
    }
}
