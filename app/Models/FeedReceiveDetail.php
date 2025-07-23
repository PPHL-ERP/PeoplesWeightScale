<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedReceiveDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'receiveId', 'productId', 'trQty', 'rQty','deviationQty','batchNo','note'
    ];

    public function product(){
        return $this->hasOne(Product::class,'id','productId');
    }
    public function receive()
    {
        return $this->belongsTo(FeedReceive::class, 'receiveId');
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
