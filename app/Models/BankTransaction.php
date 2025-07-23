<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankTransaction extends Model
{
    protected $table = 'view_bank_transactions';

    public $timestamps = false; // Disable timestamps if not present
}
