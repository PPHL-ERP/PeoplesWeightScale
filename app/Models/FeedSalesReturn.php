<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeedSalesReturn extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = ['id'];


    public function feed(){
        return $this->hasOne(FeedOrder::class,'id','saleId');
    }

    public function createdBy() {
        return $this->hasOne(User::class, 'id', 'crBy');
      }

      public function approvedBy() {
        return $this->hasOne(User::class, 'id', 'appBy');
      }

      public function dealer(){
        return $this->hasOne(Dealer::class,'id','dealerId');
    }

//
public function saleReturnDetails()
{
    return $this->hasMany(FeedSalesReturnDetails::class, 'saleReturnId');
}

 // Other model methods and properties

 public function details()
 {
     return $this->hasMany(FeedSalesReturnDetails::class, 'saleReturnId');
 }

 // saleReturnId unique auto create
 public static function boot()
      {
          parent::boot();

          static::creating(function ($model) {

              $model->saleReturnId = 'FSR' . date('y') . date('m') . str_pad(self::max('id') + 1, 4, '0', STR_PAD_LEFT);
          });
      }
}
