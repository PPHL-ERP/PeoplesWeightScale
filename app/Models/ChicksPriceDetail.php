<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChicksPriceDetail extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function cPrice(){
        return $this->hasOne(ChicksPrice::class,'id','cpId');
    }

    public function product(){
        return $this->hasOne(Product::class,'id','productId');
    }

    // public function cDailyPrice(){
    //     return $this->hasOne(ChicksDailyPrice::class,'id','dailyPriceId');
    // }

    public function childCategory()
    {
        return $this->hasOneThrough(
            ChildCategory::class,
            Product::class,
            'id',
            'id',
            'productId',
            'childCategoryId'
        );
    }
}
