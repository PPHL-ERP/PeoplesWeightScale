<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionProduct extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // public function product()
    // {
    //     return $this->belongsTo(Product::class);
    // }

    public function commission()
    {
        return $this->belongsTo(Commission::class, 'commissionId', 'id');
    }
    public function product()
{
    return $this->belongsTo(Product::class, 'productId', 'id');
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
}
