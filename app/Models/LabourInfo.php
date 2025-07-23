<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LabourInfo extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    public function depot()
    {
        return $this->hasOne(Sector::class, 'id', 'depotId');
    }

    public function unit(){
        return $this->hasOne(Unit::class,'id','unitId');
    }
    public function createdBy()
    {
        return $this->hasOne(User::class, 'id', 'crBy');
    }

    public function approvedBy()
    {
        return $this->hasOne(User::class, 'id', 'appBy');
    }
}