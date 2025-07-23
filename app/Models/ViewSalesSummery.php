<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewSalesSummery extends Model
{
    protected $table = 'view_feed_order_summary';
    public $timestamps = false;

    public $incrementing = false;
    protected $guarded = [];
}
