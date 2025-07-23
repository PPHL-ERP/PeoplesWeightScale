<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sector extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function company()
    {
        return $this->belongsTo(Company::class, 'companyId');
    }

    // Scope for approved sectors
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // Scope for sales points
    public function scopeSalesPoints($query)
    {
        return $query->approved()->where('isSalesPoint', true);
    }

    // Scope for farms
    public function scopeFarms($query)
    {
        return $query->approved()->where('isFarm', true);
    }
}
