<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChicksPrice extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = ['id'];

    public function dealer(){
        return $this->hasOne(Dealer::class,'id','dealerId');
    }

    public function createdBy() {
        return $this->hasOne(User::class, 'id', 'crBy');
      }

      public function approvedBy() {
        return $this->hasOne(User::class, 'id', 'appBy');
      }

      public function priceDetails()
      {
          return $this->hasMany(ChicksPriceDetail::class, 'cpId');
      }

      public function employee()
{
    return $this->belongsTo(User::class, 'empId');
}

}