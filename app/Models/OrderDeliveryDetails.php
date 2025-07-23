<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDeliveryDetails extends Model
{
    use HasFactory;

    protected $table = 'order_delivery_details';

    protected $fillable = [
        'sale_id',
        'product_id',
        'qty',
        'dealer_code',
        'driver_name',
        'driver_phone',
        'vehicle_no',
        'vehicle_type',
        'status',
    ];
}
