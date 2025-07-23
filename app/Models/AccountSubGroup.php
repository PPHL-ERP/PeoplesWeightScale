<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountSubGroup extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = ['id'];

    public function accountGroup()
    {
        return $this->hasOne(AccountGroup::class, 'id', 'groupId');
    }

}
