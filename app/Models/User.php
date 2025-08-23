<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
// Sanctum ব্যবহার করলে রাখো, না হলে এই লাইন আর trait দুটোই তুলে দাও
use Laravel\Sanctum\HasApiTokens;

use App\Models\Role;

class User extends Authenticatable
{
    // Sanctum না থাকলে HasApiTokens বাদ দাও
    //use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';

    /**
     * Guarded/Fillable: সহজে রাখতে guarded ব্যবহার করছি
     * চাইলে fillable-এ নির্দিষ্ট ফিল্ড তালিকাও দিতে পারো।
     */
    protected $guarded = ['id'];

    /**
     * Sensitive ফিল্ড হাইড করো
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'deleted_at'        => 'datetime',
        'isSuperAdmin'      => 'boolean',
        'isAdmin'           => 'boolean',
        'isBanned'          => 'boolean',
        'status'            => 'integer',
    ];

    /**
     * পাসওয়ার্ড সেট করলে অটো-হ্যাশ (আগে থেকে হ্যাশ হলে আবার করবে না)
     */
    public function setPasswordAttribute($value): void
    {
        if (!$value) return;

        // ইতিমধ্যে bcrypt হ্যাশ কিনা চেক (যদি $2y$ দিয়ে শুরু হয়)
        if (is_string($value) && str_starts_with($value, '$2y$')) {
            $this->attributes['password'] = $value;
        } else {
            $this->attributes['password'] = bcrypt($value);
        }
    }

    /* =======================
     *      Relationships
     * ======================= */

    // User ↔ Roles (pivot: user_has_roles, keys: userId, roleId)
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_has_roles', 'userId', 'roleId')
                    ->withTimestamps();
    }

    // (ঐচ্ছিক) অন্য রিলেশন থাকলে এখানে রাখো
    // public function employee() { return $this->belongsTo(Employee::class, 'employeeId'); }
    // public function managedProduct() { return $this->belongsTo(UserManageProduct::class, 'userManageProductId'); }

    /* =======================
     *   Role/Permission Helper
     * ======================= */

    // রোল আছে?
    public function hasRole(string|array $roles): bool
    {
        if ($this->isSuperAdmin) return true;

        $roles = (array) $roles;
        return $this->roles()
            ->whereIn('roleName', $roles)
            ->exists();
    }

    // পারমিশন আছে? (role_has_permissions এর মাধ্যমে)
    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin) return true;

        return $this->roles()
            ->whereHas('permissions', function ($q) use ($permission) {
                $q->where('name', $permission);
            })
            ->exists();
    }

    // ইউজারকে রোল অ্যাসাইন করো (id/নাম/Role object—সবই সাপোর্টেড)
    public function giveRole(string|int|Role $role): void
    {
        $roleId = $role instanceof Role
            ? $role->id
            : (is_numeric($role) ? (int) $role : Role::where('roleName', $role)->value('id'));

        if ($roleId) {
            // pivot: user_has_roles (userId, roleId)
            $this->roles()->syncWithoutDetaching([$roleId]);
        }
    }
}
