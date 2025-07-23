<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeedFarmProduction extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    // Relationships
    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'productId');
    }

    public function flock()
    {
        return $this->hasOne(Flock::class, 'id', 'flockId');
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

            $model->productionId = 'FDP' . date('y') . date('m') . str_pad(self::max('id') + 1, 4, '0', STR_PAD_LEFT);
        });
    }}
