<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewPaymentReceiveInfo extends Model
{
    protected $table = 'view_payment_receive_infos';
    public $timestamps = false; // Because views typically don't have created_at/updated_at

    // Optional: If you want to prevent updates/inserts
    public $incrementing = false;
    protected $guarded = [];
}
