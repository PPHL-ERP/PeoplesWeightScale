<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class DiscountLedger extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = ['id'];

    public function sale(){
        return $this->hasOne(SalesOrder::class,'id','saleId');
    }

    public function saleCategory(){
        return $this->hasOne(Category::class,'id','saleCategoryId');
    }
    public function dealer(){
        return $this->hasOne(Dealer::class,'id','dealerId');
    }

    public function approvedBy() {
    return $this->hasOne(User::class, 'id', 'appBy');
    }}