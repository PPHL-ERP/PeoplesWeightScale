<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class EggStockAdjustment extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function sector()
    {
        return $this->hasOne(Sector::class, 'id', 'sectorId');
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

    // public static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($model) {

    //         $model->adjId = 'EAJ' . date('y') . date('m') . str_pad(self::max('id') + 1, 4, '0', STR_PAD_LEFT);
    //     });
    // }
}