<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentReceiveInfo extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];
    protected $fillable = [
        'voucherNo',
        'companyId',
        'recType',
        'chartOfHeadId',
        'amount',
        'recDate',
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

    //protected $table = 'payment_receive_infos';

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

    public function createBy()
    {
        return $this->hasOne(User::class, 'id', 'createdBy');
    }

    public function approvedBy()
    {
        return $this->hasOne(User::class, 'id', 'appBy');
    }

    public function invoicePaymentReceivesList(): HasMany
    {
        return $this->hasMany(InvoiceWisePaymentReceive::class, 'paymentReceiveId', 'id');
    }

    // PaymentReceiveInfoId unique auto create
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {

            $model->voucherNo = 'PRI' . date('y') . date('m') . str_pad(self::max('id') + 1, 4, '0', STR_PAD_LEFT);
        });
    }
}
