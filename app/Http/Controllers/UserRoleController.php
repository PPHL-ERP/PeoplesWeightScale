<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

    //
    public function create()
{
    $roles = Role::orderBy('roleName')->get();
    return view('rbac.users.create', compact('roles'));
}

public function store(Request $request)
{
    $data = $request->validate([
        'name'     => ['required','string','max:150'],
        'email'    => ['required','email','max:200','unique:users,email'],
        'password' => ['required','string','min:8','confirmed'], // needs password_confirmation
        'isSuperAdmin' => ['sometimes','boolean'],
        'roles'    => ['nullable','array'],
        'roles.*'  => ['integer','exists:roles,id'],
    ]);

    $user = User::create([
        'name'         => $data['name'],
        'email'        => $data['email'],
        'password'     => Hash::make($data['password']),
        'isSuperAdmin' => (bool) ($data['isSuperAdmin'] ?? false),
        'isAdmin'      => false,
        'isBanned'     => false,
    ]);

    // রোল অ্যাসাইন (থাকলে)
    if (!empty($data['roles'])) {
        $user->roles()->sync($data['roles']);
    }

    return redirect()->route('users.index')->with('success','User created successfully.');
}

    //

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