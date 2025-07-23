<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class DailyPriceHistory extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = ['id'];
    protected $fillable = [
        'dailyPriceId',
        'productId',
        'categoryId',
        'availableQty',
        'newPrice',
        'price',
        'date',
        'time',
        'crBy',
    ];


    public function dailyPrices(){
        return $this->hasOne(DailyPrice::class,'id','dailyPriceId');
    }

    public function product(){
        return $this->hasOne(Product::class,'id','productId');
    }

    public function category(){
        return $this->hasOne(Category::class,'id','categoryId');
    }

    public function createdBy() {
        return $this->hasOne(User::class, 'id', 'crBy');
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
