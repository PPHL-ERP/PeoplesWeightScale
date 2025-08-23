<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $q = \App\Models\User::with('roles');

        if ($s = $request->get('s')) {
            $q->where(function($w) use ($s){
                $w->where('name','like',"%$s%")
                  ->orWhere('email','like',"%$s%");
            });
        }

        $users = $q->orderBy('name')->paginate(12)->withQueryString();
        return view('rbac.users.index', compact('users'));
    }

    // ইউজারের রোল এডিট ফর্ম
    public function edit($id)
    {
        $user  = User::with('roles')->findOrFail($id);
        $roles = Role::orderBy('roleName')->get();

        return view('rbac.users.roles', compact('user','roles'));
    }

    // ইউজারের রোল আপডেট
    public function update(Request $request, $id)
    {
        $user = User::with('roles')->findOrFail($id);

        // SuperAdmin ইউজারের রোল পরিবর্তন ব্লক (ইচ্ছেমত শিথিল করতে পারো)
        if ($user->isSuperAdmin) {
            return back()->withErrors('Cannot modify Super Admin roles.');
        }

        $data = $request->validate([
            'roles'   => ['array'],
            'roles.*' => ['integer','exists:roles,id'],
        ]);

        $user->roles()->sync($data['roles'] ?? []);
        return redirect()->route('users.roles.edit', $user->id)->with('success','User roles updated.');
    }
}