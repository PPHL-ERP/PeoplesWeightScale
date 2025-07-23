<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankLedger extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    public function company()
    {
        return $this->hasOne(Company::class, 'id', 'companyId');
    }

    public function sector(){
        return $this->hasOne(Sector::class,'id','sectorId');
    }

    public function bank(){
        return $this->hasOne(BankList::class,'id','bankId');
    }

    public function approvedBy()
    {
        return $this->hasOne(User::class, 'id', 'appBy');
    }
}
