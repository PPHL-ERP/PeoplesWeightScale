<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::orderBy('name')->paginate(20);
        return view('rbac.permissions.index', compact('permissions'));
    }

    public function create()
    {
        return view('rbac.permissions.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:150','unique:permissions,name']
        ]);

        Permission::create($data);
        return redirect()->route('permissions.index')->with('success','Permission created.');
    }

    public function edit($id)
    {
        $permission = Permission::findOrFail($id);
        return view('rbac.permissions.edit', compact('permission'));
    }

    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);
        $data = $request->validate([
            'name' => ['required','string','max:150','unique:permissions,name,'.$permission->id]
        ]);

        $permission->update($data);
        return redirect()->route('permissions.index')->with('success','Permission updated.');
    }

    public function destroy($id)
    {
        Permission::findOrFail($id)->delete();
        return back()->with('success','Permission deleted.');
    }
}