<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class SalesAddressMap extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = ['id'];

    public function sale(){
        return $this->hasOne(SalesOrder::class,'id','saleId');
    }
}
