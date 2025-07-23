<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends Model
{
    use HasFactory,SoftDeletes;
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

    public function salesDraft(){
        return $this->hasOne(SalesDraft::class,'id','salesDraftId');
    }

    public function company(){
        return $this->hasOne(Company::class,'id','companyId');
    }

    public function accountLedgerName()
    {
        return $this->hasOne(AccountLedgerName::class, 'id', 'chartOfHeadId');
    }

    public function createdBy() {
        return $this->hasOne(User::class, 'id', 'crBy');
      }

      public function approvedBy() {
        return $this->hasOne(User::class, 'id', 'appBy');
      }
//
      public function salesDetails()
    {
        return $this->hasMany(SalesOrderDetails::class, 'saleId');
    }


    //salesDraft status approve to order create join
    public function details()
    {
        return $this->hasMany(SalesOrderDetails::class, 'saleId');
    }

    public function returnDetails()
    {
        return $this->hasManyThrough(
            \App\Models\SalesReturnDetails::class,
            \App\Models\SalesReturn::class,
            'saleId', // Foreign key on SalesReturn
            'saleReturnId', // Foreign key on FeedSalesReturnDetails
            'id', // Local key on saleOrder
            'id'  // Local key on SalesReturn
        )->whereHas('saleReturn', function ($q) {
            $q->where('status', 'approved');
        })->with('product'); // eager load product info
    }


// saleId unique auto create
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $category = optional($model->saleCategory)->name;

            $prefix = '';
            if ($category === 'Egg') {
                $prefix = 'ESO';
            }elseif ($category === 'Life Bird') {
                $prefix = 'LFO';
            }elseif ($category === 'Curl Bird') {
                $prefix = 'CLO';
            }elseif ($category === 'Chicks') {
                $prefix = 'CSO';
            }elseif ($category === 'Feed') {
                $prefix = 'CFO';
            } elseif ($category === 'Fertilizer') {
                $prefix = 'FSO';
            }

            $model->saleId = $prefix . date('y') . date('m') . str_pad(self::max('id') + 1, 4, '0', STR_PAD_LEFT);
        });
    }

}