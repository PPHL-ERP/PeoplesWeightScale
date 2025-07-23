<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyAndDealerBasedTransactionView extends Model
{
    use HasFactory;
    protected $table = 'view_dealer_wise_transactions';
    public $timestamps = false;
}
