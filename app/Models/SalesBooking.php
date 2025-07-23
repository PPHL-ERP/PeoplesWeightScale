<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class SalesBooking extends Model
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

    // public function employee(){
    //     return $this->hasOne(Employee::class,'id','bookingPerson');
    // }

    public function createdBy() {
        return $this->hasOne(User::class, 'id', 'crBy');
      }

      public function approvedBy() {
        return $this->hasOne(User::class, 'id', 'appBy');
      }

      //update
      public function salesBookingDetails()
      {
          return $this->hasMany(SalesBookingDetails::class, 'bookingId');
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
                $prefix = 'CFB';
            } elseif ($category === 'Fertilizer') {
                $prefix = 'FSB';
            }

            $model->bookingId = $prefix . date('y') . date('m') . str_pad(self::max('id') + 1, 4, '0', STR_PAD_LEFT);
        });
    }

}
