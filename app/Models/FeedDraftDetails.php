<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedDraftDetails extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function feed(){
        return $this->hasOne(FeedOrder::class,'id','feedId');
    }

    public function product(){
        return $this->hasOne(Product::class,'id','productId');
    }

    public function unit(){
        return $this->hasOne(Unit::class,'id','unitId');
    }

    //update joining
    public function feedDraft()
    {
        return $this->belongsTo(FeedDraft::class, 'feedId');
    }
}