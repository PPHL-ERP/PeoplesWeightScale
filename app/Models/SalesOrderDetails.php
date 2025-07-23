<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrderDetails extends Model
{
    use HasFactory;
    protected $guarded = ['id'];


    public function sale(){
        return $this->hasOne(SalesOrder::class,'id','saleId');
    }

    public function product(){
        return $this->hasOne(Product::class,'id','productId');
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
            'productId',
            'childCategoryId'
        );
    }
//update joining
    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'saleId');
    }


    public function dailyPrice()
    {
        return $this->hasOne(DailyPrice::class, 'productId', 'productId')
                    ->latestOfMany(); // or ->latestOfMany('date') if you have a 'date' column
    }

}
