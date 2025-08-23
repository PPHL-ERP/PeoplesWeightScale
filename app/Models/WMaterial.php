<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WMaterial extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'w_material';
    protected $guarded = ['id'];

    protected $fillable = [
        'mId', 'oldmId', 'mName', 'mNameBangla', 'categoryType', 'note',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];
}
