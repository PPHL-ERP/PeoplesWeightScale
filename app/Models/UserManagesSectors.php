<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserManagesSectors extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function sector()
    {
        return $this->belongsTo(Sector::class, 'sectorId');
    }
}
