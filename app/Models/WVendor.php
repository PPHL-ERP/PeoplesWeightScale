<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WVendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'w_vendor';
    protected $guarded = ['id'];

    protected $fillable = [
        'vId', 'vName', 'phone',
    ];
    protected $casts = [
        'deleted_at' => 'datetime',
    ];
}
