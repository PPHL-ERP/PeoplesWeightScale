<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WCustomer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'w_customer';
    protected $guarded = ['id'];

    protected $fillable = [
        'cId', 'oldcId', 'cName', 'cNameBangla', 'phone', 'address', 'note',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];
}
