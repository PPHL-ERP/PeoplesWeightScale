<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{User, Role, Permission};

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $perms = [
            'materials.view','materials.create','materials.edit','materials.delete',
            'vendors.view','vendors.create','vendors.edit','vendors.delete',
            'customers.view','customers.create','customers.edit','customers.delete',
            'transactions.view','transactions.create','transactions.edit','transactions.delete',
        ];
        foreach ($perms as $p) { Permission::firstOrCreate(['name' => $p]); }

        $admin   = Role::firstOrCreate(['roleName' => 'Admin']);
        $manager = Role::firstOrCreate(['roleName' => 'Manager']);
        $viewer  = Role::firstOrCreate(['roleName' => 'Viewer']);

        $admin->permissions()->sync(Permission::pluck('id'));

        // এখানে তোমার অ্যাডমিন ইউজারের ইমেইল দাও
        $email = 'superadmin@gmail.com';
        $u = User::where('email', $email)->first();

        if ($u) {
            $u->giveRole('Admin');
            $u->isSuperAdmin = 1; // চাইলে বাদ দাও
            $u->save();
        }
    }
}
