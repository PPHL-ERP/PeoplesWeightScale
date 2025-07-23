<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllChartOfHeadTransactionView extends Model
{
    use HasFactory;
    protected $table = 'view_all_chart_of_head_transactions';
    public $timestamps = false;
}