<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesDraft extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'outTransportInfo' => 'array',
    ];

    public function booking(){
        return $this->hasOne(SalesBooking::class,'id','bookingId');
    }

    public function saleCategory(){
        return $this->hasOne(Category::class,'id','saleCategoryId');
    }
    public function dealer(){
        return $this->hasOne(Dealer::class,'id','dealerId');
    }

    public function sector(){
        return $this->hasOne(Sector::class,'id','salesPointId');
    }

    public function createdBy() {
        return $this->hasOne(User::class, 'id', 'crBy');
      }

      public function company(){
        return $this->hasOne(Company::class,'id','companyId');
    }

    public function accountLedgerName()
    {
        return $this->hasOne(AccountLedgerName::class, 'id', 'chartOfHeadId');
    }

      public function approvedBy() {
        return $this->hasOne(User::class, 'id', 'appBy');
      }

      //
      public function salesDraftDetails()
    {
        return $this->hasMany(SalesDraftDetails::class, 'saleId');
    }

    public function details()
    {
        return $this->hasMany(SalesDraftDetails::class, 'saleId');
    }

    // saleDraftId unique auto create
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $category = optional($model->saleCategory)->name;

            $prefix = '';
            if ($category === 'Egg') {
                $prefix = 'ESD';
            }elseif ($category === 'Life Bird') {
                $prefix = 'LFD';
            }elseif ($category === 'Curl Bird') {
                $prefix = 'CLD';
            }elseif ($category === 'Chicks') {
                $prefix = 'CSD';
            }elseif ($category === 'Feed') {
                $prefix = 'CFD';
            } elseif ($category === 'Fertilizer') {
                $prefix = 'FSD';
            }

            $model->saleId = $prefix . date('y') . date('m') . str_pad(self::max('id') + 1, 4, '0', STR_PAD_LEFT);
        });
    }

}