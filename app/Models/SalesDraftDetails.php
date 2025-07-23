<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesDraftDetails extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function sale(){
        return $this->hasOne(SalesOrder::class,'id','saleId');
    }

    public function product(){
        return $this->hasOne(Product::class,'id','productId');
    }

    public function unit(){
        return $this->hasOne(Unit::class,'id','unitId');
    }

    //update joining
    public function salesDraft()
    {
        return $this->belongsTo(SalesDraft::class, 'saleId');
    }

}