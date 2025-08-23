<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $fillable = ['roleName'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_has_roles', 'roleId', 'userId')
                    ->withTimestamps();
    }
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions', 'roleId', 'permissionId');
    }
}