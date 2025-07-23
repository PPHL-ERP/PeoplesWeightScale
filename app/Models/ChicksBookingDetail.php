<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChicksBookingDetail extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function chicksBooking(){
        return $this->hasOne(ChicksBooking::class,'id','cbId');
    }

    public function product(){
        return $this->hasOne(Product::class,'id','pId');
    }

    public function cdPrice(){
        return $this->hasOne(ChicksDailyPrice::class,'id','cdPriceId');
    }
    public function unit(){
        return $this->hasOne(Unit::class,'id','unitId');
    }

    public function childCategory()
    {
        return $this->hasOneThrough(
            ChildCategory::class,
            Product::class,
            'id',
            'id',
            'pId',
            'childCategoryId'
        );
    }

}
