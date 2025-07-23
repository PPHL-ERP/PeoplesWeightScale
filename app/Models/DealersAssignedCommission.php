<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DealersAssignedCommission extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function dealer()
    {
        return $this->belongsTo(Dealer::class, 'dealerId');
    }

    public function commission()
    {
        return $this->belongsTo(Commission::class, 'commissionId');
    }
}
