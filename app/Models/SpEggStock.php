<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpEggStock extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = ['id'];

    public function stock(){
        return $this->hasOne(Stock::class,'id','stockId');
    }
    public function farmStock(){
        return $this->hasOne(FarmEggStock::class,'id','farmStockId');
    }
}
