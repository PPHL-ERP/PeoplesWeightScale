<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountGroup extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = ['id'];

    public function accountClass()
    {
        return $this->hasOne(AccountClass::class, 'id', 'classId');
    }

    public function subGroups()
{
    return $this->hasMany(AccountSubGroup::class, 'groupId', 'id');
}


}