<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountReceivableDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_receivable_id',
        'invoice_id',
        'sale_id',
        'applied_amount',
    ];

    public function accountReceivable()
    {
        return $this->belongsTo(AccountReceivable::class);
    }
}
