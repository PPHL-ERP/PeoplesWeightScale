<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleHasPermissions extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function role()
    {
        return $this->belongsTo(Role::class, 'roleId');
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permissionId');
    }
}
