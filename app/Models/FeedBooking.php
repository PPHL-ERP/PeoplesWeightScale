<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeedBooking extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = ['id'];

    public function dealer(){
        return $this->hasOne(Dealer::class,'id','dealerId');
    }

    public function saleCategory(){
        return $this->hasOne(Category::class,'id','saleCategoryId');
    }

    public function bookingPoint(){
        return $this->hasOne(Sector::class,'id','bookingPointId');
    }

    public function commission(){
        return $this->hasOne(Commission::class,'id','commissionId');
    }

    public function createdBy() {
        return $this->hasOne(User::class, 'id', 'crBy');
      }

      public function approvedBy() {
        return $this->hasOne(User::class, 'id', 'appBy');
      }

      public function subCategory(){
        return $this->hasOne(SubCategory::class,'id','subCategoryId');
    }
    public function childCategory(){
        return $this->hasOne(ChildCategory::class,'id','childCategoryId');
    }

      //update
      public function feedBookingDetails()
      {
          return $this->hasMany(FeedBookingDetails::class, 'bookingId');
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

            $model->bookingId = $prefix . date('y') . date('m') . str_pad(self::max('id') + 1, 4, '0', STR_PAD_LEFT);
        });
    }}
