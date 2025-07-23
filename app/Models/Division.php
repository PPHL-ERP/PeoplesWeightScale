<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;


class Division extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function districts()
    {
        return $this->hasMany(District::class, 'division_id');
    }

    public function districtsWithUpazilas()
    {
        return $this->hasMany(District::class, 'division_id')->with('upazilas');
    }
}
