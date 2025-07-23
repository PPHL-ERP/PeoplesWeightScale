<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Commission extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zoneId');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'categoryId');
    }

    public function dealer()
    {
        return $this->belongsTo(Dealer::class, 'dealerId');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'companyId');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'crBy');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'appBy');
    }

    public function commissionProducts()
    {
        return $this->hasMany(CommissionProduct::class, 'commissionId');
    }




    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {

            $model->commissionNo = 'COM' . date('y') . date('m') . str_pad(self::max('id') + 1, 4, '0', STR_PAD_LEFT);
        });
    }
}
