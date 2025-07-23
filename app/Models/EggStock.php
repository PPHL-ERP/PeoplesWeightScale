<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EggStock extends Model
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