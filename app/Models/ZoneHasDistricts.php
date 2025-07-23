<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ZoneHasDistricts extends Model
{
    protected $table='zone_has_districts';
    use HasFactory;
    protected $guarded = ['id'];

    public function zones()
    {
        return $this->belongsTo(Zone::class, 'zoneId');
    }

    public function districts()
    {
        return $this->belongsTo(District::class, 'districtId');
    }
}
