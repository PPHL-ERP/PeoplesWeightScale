<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class ChicksDailyPrice extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = ['id'];
    protected $fillable = [
        'date',
        'pId',
        'cZoneId',
        'pCost',
        'mrp',
        'salePrice',
        'categoryId',
        'subCategoryId',
        'childCategoryId',
        'crBy',
        'appBy',
        'status',
    ];

    public function category(){
        return $this->hasOne(Category::class,'id','categoryId');
    }

    public function subCategory(){
        return $this->hasOne(SubCategory::class,'id','subCategoryId');
    }
    public function childCategory(){
        return $this->hasOne(ChildCategory::class,'id','childCategoryId');
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

      //
      public function chicksDpHistory()
    {
        return $this->hasMany(ChicksDailyPriceHistory::class, 'chicksDPriceId')->limit(4);
    }


}