<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeedStockAdjustment extends Model
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


}
