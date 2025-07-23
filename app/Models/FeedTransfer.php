<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeedTransfer extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    public function tStore(){
        return $this->hasOne(Sector::class,'id','toStore');
    }
    public function fStore()
{
    return $this->hasOne(Sector::class, 'id', 'fromStore');
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

    public function sector()
    {
        return $this->hasOne(Sector::class, 'id', 'sectorId');
    }

    public function labInfo()
    {
        return $this->hasOne(LabourInfo::class, 'id', 'loadBy');
    }
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {

            $model->trId = 'CFT' . date('y') . date('m') . str_pad(self::max('id') + 1, 4, '0', STR_PAD_LEFT);
        });
    }
    // Define relationship to FeedTransferDetail
    public function details()
    {
        return $this->hasMany(FeedTransferDetail::class, 'transferId');
    }}