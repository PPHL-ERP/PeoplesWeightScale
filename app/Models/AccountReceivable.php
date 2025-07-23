<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountReceivable extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'invoice_number',
        'customer_name',
        'transaction_date',
        'payment_term',
        'amount',
        'balance',
        'particular',
        'approved_by',
        'status',
    ];

    public function details()
    {
        return $this->hasMany(AccountReceivableDetail::class);
    }
}
