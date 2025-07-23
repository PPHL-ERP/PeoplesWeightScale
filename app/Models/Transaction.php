<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'chartOfHeadId',
        'companyId',
        'voucherNo',
        'voucherType',
        'voucherDate',
        'note',
        'debit',
        'credit',
        'status',
        'createdBy',
        'modifiedBy'
    ];

    protected $table = 'transactions';

    public function company()
    {
        return $this->belongsTo(Company::class, 'companyId', 'id');
    }
}
