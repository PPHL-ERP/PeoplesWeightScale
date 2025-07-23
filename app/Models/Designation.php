<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Designation extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = ['id'];

    public function department(){
        return $this->hasOne(Department::class,'id','departmentId');
    }
}
