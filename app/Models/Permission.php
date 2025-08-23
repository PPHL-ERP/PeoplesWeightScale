<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $fillable = ['name'];

    // public function roles()
    // {
    //     return $this->belongsToMany(Role::class);
    // }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_has_permissions', 'permissionId', 'roleId')
                    ->withTimestamps();
    }
}