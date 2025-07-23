<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentPayableInfo extends Model
{
    protected $table = 'payment_payable_infos';
    protected $fillable = [
        'voucherNo',
        'companyId',
        'paidType',
        'chartOfHeadId',
        'amount',
        'paidDate',
        'invoiceType',
        'checkNo',
        'checkDate',
        'trxId',
        'ref',
        'status',
        'createdBy',
        'modifiedBy',
        'deletedBy',
        'paymentType',
        'paymentMode',
        'paymentFor',
        'note',
        'appBy'
    ];

    //
    public function company()
    {
        return $this->hasOne(Company::class, 'id', 'companyId');
    }

    public function dealer()
    {
        return $this->hasOne(Dealer::class, 'id', 'chartOfHeadId');
    }
    public function employee()
    {
        return $this->hasOne(Employee::class, 'id', 'chartOfHeadId');
    }

    public function bank(){
        return $this->hasOne(BankList::class,'id','paymentType');
    }

    public function pFor(){
        return $this->hasOne(PaymentType::class,'id','paymentFor');
    }

    public function approvedBy()
    {
        return $this->hasOne(User::class, 'id', 'appBy');
    }


    // PaymentPayableId unique auto create
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->voucherNo = 'PPI' . date('y') . date('m') . str_pad(self::max('id') + 1, 4, '0', STR_PAD_LEFT);
        });
    }

    public function invoiceWisePaymentPayable()
    {
        return $this->hasMany(InvoiceWisePaymentPayable::class, 'paymentPayableId', 'id');
    }
}