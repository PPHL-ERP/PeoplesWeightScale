<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LabourPayment extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    public function labInfo()
    {
        return $this->hasOne(LabourInfo::class, 'id', 'labourId');
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