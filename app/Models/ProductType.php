<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductType extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function products()
    {
        return $this->hasMany(Product::class, 'productTypeId');
    }
}
