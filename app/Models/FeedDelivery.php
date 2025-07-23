<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeedDelivery extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = ['id'];

    protected $casts = [
        'deliveryPersonDetails' => 'array',
    ];

    public function feed(){
        return $this->hasOne(FeedOrder::class,'id','feedId');
    }

    public function dealer(){
        return $this->hasOne(Dealer::class,'id','dealerId');
    }

    public function approvedBy() {
    return $this->hasOne(User::class, 'id', 'appBy');
    }

    public function deliveryDetails()
    {
        return $this->hasMany(FeedDeliveryDetail::class, 'feedId');
    }
}
