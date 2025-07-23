<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedOrderDetails extends Model
{
    use HasFactory;
    protected $guarded = ['id'];


    public function feed(){
        return $this->hasOne(FeedOrder::class,'id','feedId');
    }

    public function product(){
        return $this->hasOne(Product::class,'id','productId');
    }

    public function unit(){
        return $this->hasOne(Unit::class,'id','unitId');
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
//update joining
    public function feedOrder()
    {
        return $this->belongsTo(FeedOrder::class, 'feedId');
    }}