<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedTransferDetail extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $fillable = [
        'transferId', 'productId', 'qty', 'transferFor','note'
    ];

    // Define inverse relationship to EggTransfer
    public function transfer()
    {
        return $this->belongsTo(FeedTransfer::class, 'transferId');
    }
    public function product(){
        return $this->hasOne(Product::class,'id','productId');
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
