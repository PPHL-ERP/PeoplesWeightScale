<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesReturnDetails extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function saleReturn(){
        return $this->hasOne(SalesReturn::class,'id','saleReturnId');
    }

    public function product(){
        return $this->hasOne(Product::class,'id','productId');
    }

    public function unit(){
        return $this->hasOne(Unit::class,'id','unitId');
    }


    //update joining
    public function salesReturn()
    {
        return $this->belongsTo(SalesReturn::class, 'saleReturnId');
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
}