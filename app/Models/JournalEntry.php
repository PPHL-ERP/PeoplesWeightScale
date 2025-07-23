<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalEntry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'voucherNo',
        'voucherDate',
        'approvedDate',
        'companyId',
        'debitSubGroupId',
        'debitHeadId',
        'debit',
        'creditSubGroupId',
        'creditHeadId',
        'credit',
        'checkNo',
        'checkDate',
        'trxId',
        'ref',
        'status',
        'createdBy',
        'modifiedBy',
        'deletedBy',
        'appBy',
        'note',
    ];

    protected $table = 'journal_entries';

    public function debitHead()
    {
        return $this->belongsTo(AccountLedgerName::class, 'debitHeadId', 'id');
    }

    public function creditHead()
    {
        return $this->belongsTo(AccountLedgerName::class, 'creditHeadId', 'id');
    }

    public function debitSubGroup()
    {
        return $this->belongsTo(AccountSubGroup::class, 'debitSubGroupId', 'id');
    }

    public function creditSubGroup()
    {
        return $this->belongsTo(AccountSubGroup::class, 'creditSubGroupId', 'id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'companyId', 'id');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'createdBy', 'id');
    }

    public function modifiedByUser()
    {
        return $this->belongsTo(User::class, 'modifiedBy', 'id');
    }

    public function deletedByUser()
    {
        return $this->belongsTo(User::class, 'deletedBy', 'id');
    }

    public function appByUser()
    {
        return $this->belongsTo(User::class, 'appBy', 'id');
    }


    // JournalEntryId unique auto create
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {

            $model->voucherNo = 'JEI' . date('y') . date('m') . str_pad(self::max('id') + 1, 4, '0', STR_PAD_LEFT);
        });
    }
}