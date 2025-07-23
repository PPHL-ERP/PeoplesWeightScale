<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class EpLedger extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = ['id'];

    public function sector()
    {
        return $this->belongsTo(Sector::class,'sectorId');
    }

    public function product()
    {
        return $this->belongsTo(Product::class,'productId');
    }

    public function approvedBy() {
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
}