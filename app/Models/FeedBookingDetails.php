<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedBookingDetails extends Model
{
    use HasFactory;
    public function booking(){
        return $this->hasOne(FeedBooking::class,'id','bookingId');
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
    }}