<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class DailyPrice extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = ['id'];

    // public function product(){
    //     return $this->hasOne(Product::class,'id','productId');
    // }

    public function product()
    {
        return $this->belongsTo(Product::class, 'productId', 'id');
    }


    public function category(){
        return $this->hasOne(Category::class,'id','categoryId');
    }

    public function createdBy() {
        return $this->hasOne(User::class, 'id', 'crBy');
      }

      public function approvedBy() {
        return $this->hasOne(User::class, 'id', 'appBy');
      }


      //
      public function dailyPriceHistory()
    {
        return $this->hasMany(DailyPriceHistory::class, 'dailyPriceId')->limit(4);
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
