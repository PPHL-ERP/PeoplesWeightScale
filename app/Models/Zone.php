<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Zone extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = ['id'];
    public function districts()
    {
        return $this->belongsToMany(District::class, 'zone_has_districts', 'zoneId', 'districtId');
    }
}
