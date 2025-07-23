<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class CompanyLedger extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    public function company()
    {
        return $this->hasOne(Company::class, 'id', 'companyId');
    }

    public function approvedBy()
    {
        return $this->hasOne(User::class, 'id', 'appBy');
    }
}
