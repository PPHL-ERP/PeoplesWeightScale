<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankTransferInfo extends Model
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

    public function bankTo(){
        return $this->hasOne(BankList::class,'id','bankIdTo');
    }

    public function bankFrom(){
        return $this->hasOne(BankList::class,'id','bankIdFrom');
    }

    public function accountLedgerName(){
        return $this->hasOne(AccountLedgerName::class,'id','headId');
    }

    public function entBy() {
        return $this->hasOne(User::class, 'id', 'entryBy');
    }
    public function approvedBy()
    {
        return $this->hasOne(User::class, 'id', 'appBy');
    }

    public static function boot()
      {
          parent::boot();

          static::creating(function ($model) {

              $model->btrId = 'BTR' . date('y') . date('m') . str_pad(self::max('id') + 1, 4, '0', STR_PAD_LEFT);
          });
      }
}