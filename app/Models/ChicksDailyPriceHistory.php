<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChicksDailyPriceHistory extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = ['id'];


    public function chicksDailyPrices(){
        return $this->hasOne(ChicksDailyPrice::class,'id','chicksDPriceId');
    }


    public function product(){
        return $this->hasOne(Product::class,'id','pId');
    }

    // public function cZone(){
    //     return $this->hasOne(CZone::class,'id','cZoneId');
    // }

    public function createdBy() {
        return $this->hasOne(User::class, 'id', 'crBy');
      }

      public function approvedBy() {
        return $this->hasOne(User::class, 'id', 'appBy');
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
