<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesHasPayments extends Model
{
    use HasFactory;
    protected $guarded = ['id'];


    public function sale(){
        return $this->hasOne(SalesOrder::class,'id','saleId');
    }

    public function payment(){
        return $this->hasOne(PaymentType::class,'id','paymentTypeId');
    }

    public function bank(){
        return $this->hasOne(BankList::class,'id','bankListId');
    }
}
