<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChicksProductionLedger extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = ['id'];

    public function hatchery()
    {
        return $this->belongsTo(Sector::class,'hatcheryId');
    }

    public function breed()
    {
        return $this->hasOne(Breed::class, 'id', 'breedId');
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