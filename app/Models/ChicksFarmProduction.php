<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChicksFarmProduction extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'chicks_productions';
    protected $guarded = ['id'];


    public function flock()
    {
        return $this->hasOne(Flock::class, 'id', 'flockId');
    }

    public function breed()
    {
        return $this->hasOne(Breed::class, 'id', 'breedId');
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

    public function hatchery()
    {
        return $this->hasOne(Sector::class, 'id', 'hatcheryId');
    }

    public function details()
{
    return $this->hasMany(ChicksFarmProductionDetails::class, 'pId');
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


    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {

            $model->productionId = 'CHP' . date('y') . date('m') . str_pad(self::max('id') + 1, 4, '0', STR_PAD_LEFT);
        });
    }

}