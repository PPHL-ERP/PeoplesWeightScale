<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebitVoucheritem extends Model
{
    use HasFactory;
    protected $fillable = [
        'debitVoucherId',
        'itemHeadId',
        'amount',
    ];
    protected $table = 'debit_voucheritems';

    public function head()
    {
        return $this->belongsTo(AccountLedgerName::class, 'itemHeadId', 'id');
    }
}
