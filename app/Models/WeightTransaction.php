<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Optional: if you want soft deletes
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WeightTransaction extends Model
{
    use HasFactory;
    // use SoftDeletes; // Uncomment if soft deletes are needed

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        // 'transaction_id',
        // 'weight_type',
        // 'transfer_type',
        // 'select_mode',
        // 'vehicle_type',
        // 'vehicle_no',
        // 'material',
        'productType',
        'gross_weight',
        'gross_time',
        'gross_operator',
        'tare_weight',
        'tare_time',
        'tare_operator',
        'volume',
        'price',
        'amount',
        'discount',
        'real_net',
        'customer_id',
        'vendor_id',
        // 'customer_name',
        // 'vendor_name',
        // 'sale_id',
        // 'purchase_id',
        'sector_id',
        // 'sector_name',
        'note',
        'others',
        'username',
        'status',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    // protected $casts = [
    //     'gross_weight' => 'float',
    //     'tare_weight'  => 'float',
    //     'volume'       => 'float',
    //     'price'        => 'float',
    //     'amount'       => 'float',
    //     'discount'     => 'float',
    //     'real_net'     => 'float',
    //     'gross_time'   => 'datetime',
    //     'tare_time'    => 'datetime',
    // ];

    /**
     * Relationships
     */
    public function customer()
    {
        return $this->belongsTo(WCustomer::class, 'customer_id');
    }

    public function vendor()
    {
        return $this->belongsTo(WVendor::class, 'vendor_id');
    }

    public function sector()
    {
        return $this->belongsTo(Sector::class, 'sector_id');
    }
}
