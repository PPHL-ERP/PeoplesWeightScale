<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dealer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dealers';

    protected $fillable = [
        'dealerCode','dealerType', 'tradeName', 'tradeNameBn', 'contactPerson', 'address',
        'addressBn', 'shippingAddress', 'zoneId', 'divisionId', 'districtId',
        'upazilaId', 'phone', 'email', 'tradeLicenseNo', 'isDueable',
        'dueLimit', 'referenceBy', 'guarantor', 'guarantorPerson',
        'guarantorByCheck', 'dealerGroup', 'crBy', 'appBy', 'status'
    ];

    protected $casts = [
        'guarantorByCheck' => 'array',
        'isDueable' => 'boolean',
    ];

    protected $dates = ['deleted_at'];

    public function createdBy() {
        return $this->hasOne(User::class, 'id', 'crBy');
      }

      public function approvedBy() {
        return $this->hasOne(User::class, 'id', 'appBy');
      }

      public function zone(){
        return $this->hasOne(Zone::class,'id','zoneId');
    }
    public function division(){
        return $this->hasOne(Division::class,'id','divisionId');
    }
    public function district(){
        return $this->hasOne(District::class,'id','districtId');
    }
    public function upazila(){
        return $this->hasOne(Upazila::class,'id','upazilaId');
    }

    //dealer unique code
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {

            $model->dealerCode = 'DLR' . date('y') . str_pad(self::max('id') + 1, 4, '0', STR_PAD_LEFT);
        });
    }

}
