<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChicksFarmProductionDetails extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'chicks_production_details';
    protected $guarded = ['id'];

    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'productId');
    }


    public function production(){
        return $this->hasOne(ChicksFarmProduction::class,'id','pId');
    }

    public function breed()
    {
        return $this->hasOne(Breed::class, 'id', 'breedId');
    }



}