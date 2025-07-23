<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewChicksStock extends Model
{
    protected $table = 'view_chicks_stocks';

    public $timestamps = false;

    // If needed (optional)
    protected $guarded = [];
}