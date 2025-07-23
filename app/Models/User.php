<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;



    protected $guarded = [];


    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
//    public function employee(){
//        return $this->hasOne(Employee::class,'id','employee_id');
//    }
    public function roles()
    {
        return $this->hasMany(UserHasRoles::class, 'userId');
    }


    public function roles2() {
        return $this->belongsToMany(Role::class, 'user_has_roles', 'userId', 'roleId');
    }

    public function sectors()
    {
        return $this->hasMany(UserManagesSectors::class, 'userId');
    }

    public function categories()
    {
        return $this->hasMany(UserManageProduct::class, 'userId');
    }

    public function empSalesGroup()
    {
        return $this->hasOne(EmployeeSalesGroup::class, 'id', 'groupId');
    }


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }

}
