<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeedReceive extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    public function feedTransfers(){
        return $this->hasOne(FeedTransfer::class,'id','transferId');
    }
    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'productId');
    }

    public function createdBy()
    {
        return $this->hasOne(User::class, 'id', 'crBy');
    }

    public function approvedBy()
    {
        return $this->hasOne(User::class, 'id', 'appBy');
    }
    public function transferFromSector()
    {
        return $this->hasOne(Sector::class, 'id', 'transferFrom');
    }

    public function recStoreSector()
    {
        return $this->hasOne(Sector::class, 'id', 'recStore');
    }
    public function sector()
    {
        return $this->hasOne(Sector::class, 'id', 'transferFrom');
    }

    public function labInfo()
    {
        return $this->hasOne(LabourInfo::class, 'id', 'unLoadBy');
    }
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {

            $model->recId = 'FTR' . date('y') . date('m') . str_pad(self::max('id') + 1, 4, '0', STR_PAD_LEFT);
        });
    }
    // Define relationship to EggTransferDetail

    public function details()
    {
        return $this->hasMany(FeedReceiveDetail::class, 'receiveId');
    }}
