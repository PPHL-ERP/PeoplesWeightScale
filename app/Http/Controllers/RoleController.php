<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users','permissions')->orderBy('roleName')->paginate(15);
        return view('rbac.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('name')->get();
        return view('rbac.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'roleName'    => ['required','string','max:150','unique:roles,roleName'],
            'permissions' => ['array']
        ]);

        $role = Role::create(['roleName' => $data['roleName']]);
        $role->permissions()->sync($data['permissions'] ?? []);

        return redirect()->route('roles.index')->with('success','Role created.');
    }

    public function edit($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::orderBy('name')->get();
        return view('rbac.roles.edit', compact('role','permissions'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $data = $request->validate([
            'roleName'    => ['required','string','max:150','unique:roles,roleName,'.$role->id],
            'permissions' => ['array']
        ]);

        $role->update(['roleName' => $data['roleName']]);
        $role->permissions()->sync($data['permissions'] ?? []);

        return redirect()->route('roles.index')->with('success','Role updated.');
    }

    public function destroy($id)
    {
        Role::findOrFail($id)->delete();
        return back()->with('success','Role deleted.');
    }
}