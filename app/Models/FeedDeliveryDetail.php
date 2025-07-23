<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedDeliveryDetail extends Model
{
    use HasFactory;

    public function delivery(){
        return $this->hasOne(FeedDelivery::class,'id','deliveryInfo');
    }
    public function product(){
        return $this->hasOne(Product::class,'id','productId');
    }
}