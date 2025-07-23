<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Sector;
use App\Models\User;
use App\Models\UserHasRoles;
use App\Models\UserManageProduct;
use App\Models\UserManagesSectors;
use Illuminate\Http\Request;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use function PHPUnit\Framework\isEmpty;
use App\Traits\UploadAble;


class UserController extends Controller
{
    use  UploadAble;
    public function clear()
    {
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('config:cache');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        return response()->json([
            'message' => 'successful!'
        ], 200);
    }

    // public function allUser()
    // {
    //     try {
    //         $users = User::with(['roles.role', 'sectors.sector'])->latest()->get();
    //         return response()->json(['users' => UserResource::collection($users)]);
    //     } catch (DecryptException $e) {
    //         abort(404);
    //     }
    // }

    public function allUser(Request $request)
    {
        $username = $request->username ?? null;
        $email = $request->email ?? null;
        $employeeId = $request->employeeId ?? null;


        $query = User::with(['roles.role', 'sectors.sector','categories.category'])->latest();

        // Filter by username
        if ($username) {
            $query->where('username', 'LIKE', '%' . $username . '%');
        }

        // Filter by email
        if ($email) {
            $query->orWhere('email', $email);
        }

         // Filter by employee
        if ($employeeId) {
            $query->orWhere('employeeId', $employeeId);
        }

        $users = $query->get();

        // Check if any users found
        if ($users->isEmpty()) {
            return response()->json(['message' => 'No User found', 'data' => []], 200);
        }

        // Use the UserResource to transform the data
        $transformedUsers = UserResource::collection($users);

        return response()->json([
            'message' => 'Success!',
            'data' => $transformedUsers
        ], 200);
    }

    public function userFilter(Request $request)
    {
        $isAdmin = $request->isAdmin;
        $isSuperAdmin = $request->isSuperAdmin;
        $employeeId = $request->employeeId;
        $userData =  User::with(['roles.role', 'sectors.sector', 'categories.category' ])
            ->where('employeeId', $employeeId)
            ->orWhere('isSuperAdmin', $isSuperAdmin)
            ->orWhere('isAdmin', $isAdmin)->get();
        if ($userData->isEmpty()) {
            return response()->json([
                'message' => 'data not found!'
            ], 200);
        }
        return response()->json([
            'message' => 'data get successfully!',
            'users' => UserResource::collection($userData)
        ]);
    }


    public function getAdminUser($userId)
    {
        try {
            $users = User::with(['roles.role', 'sectors.sector', 'categories.category'])->find($userId);
            return response()->json(new UserResource($users));
        } catch (DecryptException $e) {
            abort(404);
        }
    }

    public function storeAdminUser(Request $request)
    {
        $roleIds = $request->roleIds ?? [];
        $sectorIds = $request->sectorIds ?? [];
        $productCategoryIds = $request->productCategoryIds ?? [];

        try {
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => trim(strtolower($request->input('email'))),
                'employeeId' => $request->employeeId,
                'ipAddress' => $request->ipAddress,
                'groupId' => $request->groupId,
                'groupRole' => $request->groupRole,
                'note' => $request->note,
                'status' => 1,
                'password' => Hash::make($request->input('password')),
            ]);

            // Insert into emp_manage_groups table after user created
            \App\Models\EmployeeManageGroup::create([
                'groupId' => $request->groupId,
                'empId' => $request->employeeId,
                'userId' => $user->id,
            ]);

            if ($request->hasFile('image')) {
                $filename = $this->uploadOne($request->image, 300, 300, config('imagepath.user'));
                $user->image = $filename;    // update new filename
                $user->save();
            }

            if ($request->hasFile('signature')) {
                $filename = $this->uploadOne($request->signature, 400, 200, config('imagepath.signature'));
                $user->signature = $filename;    // update new filename
                $user->save();
            }

            foreach ($roleIds as $role) {
                $data = new UserHasRoles();
                $data->userId = $user->id;
                $data->roleId = $role;
                $data->save();
            }

            foreach ($sectorIds as $sectors) {
                $sector = new UserManagesSectors();
                $sector->sectorId = $sectors;
                $sector->userId = $user->id;
                $sector->save();
            }

            foreach ($productCategoryIds as $categories) {
                $category = new UserManageProduct();
                $category->productCategoryId = $categories;
                $category->userId = $user->id;
                $category->save();
            }

            return response()->json(['message' => 'User created successfully'], 200);
        } catch (DecryptException $e) {
            abort(404);
        }
    }

    public function updateAdminUser(Request $request, $userId)
    {
        $roleIds = $request->input('roleIds', []);
        $sectorIds = $request->input('sectorIds', []);
        $productCategoryIds = $request->input('productCategoryIds', []);

        // TODO validate roleIds and sectorIds and productCategoryIds to have integers only
        try {
            $user = User::findOrFail($userId);
            $user->update([
                'name' => $request->name ?? $user->name,
                'username' => $request->username ?? $user->username,
                'isAdmin' => $request->isAdmin ?? $user->isAdmin,
                'email' => $request->email ? trim(strtolower($request->input('email'))) : $user->email,
                'employeeId' => $request->employeeId,
                'ipAddress' => $request->ipAddress,
                'groupId' => $request->groupId,
                'groupRole' => $request->groupRole,
                'note' => $request->note,
                'password' => $request->password ? Hash::make($request->input('password')) : $user->password,
            ]);

            // ✅ Add this block next
            $existing = \App\Models\EmployeeManageGroup::where('userId', $user->id)->first();

            if (!$existing) {
                \App\Models\EmployeeManageGroup::create([
                    'groupId' => $request->groupId,
                    'empId' => $request->employeeId,
                    'userId' => $user->id,
                ]);
            } else {
                if (
                    $existing->groupId != $request->groupId ||
                    $existing->empId != $request->employeeId
                ) {
                    $existing->update([
                        'groupId' => $request->groupId,
                        'empId' => $request->employeeId,
                    ]);
                }
            }

            if ($request->hasFile('image')) {
                $filename = $this->uploadOne($request->image, 300, 300, config('imagepath.user'));
                $this->deleteOne(config('imagepath.user'), $user->image);
                $user->update(['image' => $filename]);
            }

            if ($request->hasFile('signature')) {
                $filename = $this->uploadOne($request->signature, 400, 200, config('imagepath.signature'));
                $this->deleteOne(config('imagepath.signature'), $user->signature);
                $user->update(['signature' => $filename]);
            }

            $data = UserHasRoles::where('userId', $userId)->get();
            foreach ($data as $value) {
                $value->delete();
            }
            foreach ($roleIds as $role) {
                $data = new UserHasRoles();
                $data->userId = $user->id;
                $data->roleId = $role;
                $data->save();
            }
            $data = UserManagesSectors::where('userId', $userId)->get();
            foreach ($data as $value) {
                $value->delete();
            }
            foreach ($sectorIds as $sectors) {
                $sector = new UserManagesSectors();
                $sector->sectorId = $sectors;
                $sector->userId = $user->id;
                $sector->save();
            }
            $data = UserManageProduct::where('userId', $userId)->get();
            foreach ($data as $value) {
                $value->delete();
            }
            foreach ($productCategoryIds as $categories) {
                $category = new UserManageProduct();
                $category->productCategoryId = $categories;
                $category->userId = $user->id;
                $category->save();
            }
            return response()->json(['message' => 'User updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'User not found', 'error' => $e], 404);
        }
    }

    public function deleteUser($userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->isSuperAdmin === '1') {
            return response()->json(['message' => 'Cannot Delete Super Admin'], 403);
        }

        if ($user->image) {
            $this->deleteOne(config('imagepath.user'), $user->image);
          }

        $user->delete();
        $data = UserHasRoles::where('userId', $userId)->get();
        foreach ($data as $value) {
            $value->delete();
        }
        $data = UserManagesSectors::where('userId', $userId)->get();
        foreach ($data as $value) {
            $value->delete();
        }
        $data = UserManageProduct::where('userId', $userId)->get();
        foreach ($data as $value) {
            $value->delete();
        }
        return response()->json(['message' => 'User delete successfully'], 200);
    }

    public function statusUpdate(Request $request, $id)
    {
        $user = User::find($id);
        $user->status = $request->status;
        $user->update();
        return response()->json([
            'message' => 'User Status change successfully',
        ], 200);
    }


    public function getSelf()
    {
        $userId = auth()->user()->id;

        $user = User::with('roles2.permissions')->where('id', $userId)->first();

        // Extract permission names from roles
        $permissionNames = collect($user->roles2)->flatMap(function ($role) {
            return collect($role->permissions)->pluck('name');
        })->unique()->values()->all();

        $user['permissions'] = $permissionNames;

        return response()->json(['message' => 'User get successfully', 'user' => $user],  200);
    }
}
