<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChicksBooking extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = ['id'];

    public function dealer(){
        return $this->hasOne(Dealer::class,'id','dealerId');
    }
    public function category(){
        return $this->hasOne(Category::class,'id','categoryId');
    }

    public function subCategory(){
        return $this->hasOne(SubCategory::class,'id','subCategoryId');
    }
    public function childCategory(){
        return $this->hasOne(ChildCategory::class,'id','childCategoryId');
    }
    public function commission(){
        return $this->hasOne(Commission::class,'id','commissionId');
    }

    public function bookingPoint(){
        return $this->hasOne(Sector::class,'id','bookingPointId');
    }

    public function cPrice(){
        return $this->hasOne(ChicksPrice::class,'id','chicksPriceId');
    }

    public function createdBy() {
        return $this->hasOne(User::class, 'id', 'crBy');
      }

      public function approvedBy() {
        return $this->hasOne(User::class, 'id', 'appBy');
      }

    public function chicksBookingDetails()
    {
        return $this->hasMany(ChicksBookingDetail::class, 'cbId');
    }

          // bookingId unique auto create
          public static function boot()
          {
              parent::boot();

              static::creating(function ($model) {
                  $category = optional($model->saleCategory)->name;

                  $prefix = '';
                  if ($category === 'Egg') {
                      $prefix = 'ESB';
                  }elseif ($category === 'Life Bird') {
                      $prefix = 'LFB';
                  }elseif ($category === 'Curl Bird') {
                      $prefix = 'CLB';
                  }elseif ($category === 'Chicks') {
                      $prefix = 'CSB';
                  }elseif ($category === 'Feed') {
                      $prefix = 'FSB';
                  } elseif ($category === 'Fertilizer') {
                      $prefix = 'FSB';
                  }

                  $model->cBookingId = $prefix . date('y') . date('m') . str_pad(self::max('id') + 1, 4, '0', STR_PAD_LEFT);
              });
          }

}