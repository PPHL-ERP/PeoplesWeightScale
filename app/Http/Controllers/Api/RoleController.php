<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\RolePermissionResource;
use App\Http\Resources\RoleResource;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RoleHasPermissions;
use App\Models\UserHasRoles;
use Illuminate\Http\Request;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Validation\Rule;
class RoleController extends Controller
{

    public function index()
    {
        $rolePermissions = RoleHasPermissions::with('role', 'permission')->get();
        $groupedRolePermissions = $rolePermissions->groupBy('roleId');
        $formattedData = [];
        foreach ($groupedRolePermissions as $roleId => $permissions) {
            $role = $permissions->first()->role;
            $rolePermissionsData = $permissions->map(function ($permission) {
                return [
                    'permissionId' => $permission->permissionId,
                    'permissionName' => $permission->permission->name,
                ];
            });
            $formattedData[] = [
                'roleId' => $roleId,
                'roleName' => $role->roleName,
                'permissions' => $rolePermissionsData,
            ];
        }

        return response()->json([
            'message' => 'Role and Permission data retrieved successfully!',
            'data' => $formattedData
        ], 200);
    }


    public function storeRole(Request $request){
        $permissions = $request->permission;
        $data = new Role();
        $data->roleName = $request->roleName;
        $data->save();
        foreach ($permissions as $permissionId) {
            $roleHasPermission = new RoleHasPermissions();
            $roleHasPermission->roleId = $data->id;
            $roleHasPermission->permissionId = $permissionId;
            $roleHasPermission->save();
        }
        return response()->json([
            'message' => 'Role Created Successfully!',
            'data' =>new RoleResource($data)
        ], 200);
    }

    public function search_by_role($id){
        $rolePermissions = RoleHasPermissions::where('roleId',$id)->with('role', 'permission')->get();
        $groupedRolePermissions = $rolePermissions->groupBy('roleId');
        $formattedData = [];
        foreach ($groupedRolePermissions as $roleId => $permissions) {
            $role = $permissions->first()->role;
            $rolePermissionsData = $permissions->map(function ($permission) {
                return [
                    'permissionId' => $permission->permissionId,
                    'permissionName' => $permission->permission->name,
                ];
            });
            $formattedData[] = [
                'roleId' => $roleId,
                'roleName' => $role->roleName,
                'permissions' => $rolePermissionsData,
            ];
        }

        return response()->json([
            'message' => 'Role and Permission data retrieved successfully!',
            'data' => $formattedData
        ], 200);
    }

    public function update_role_By_RoleId(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        if (!$role){
            return response()->json([
                'message' => 'Role not found!',
            ], 200);
        }
        if ($request->has('roleName')) {
            $role->roleName = $request->roleName;
            $role->save();
        }
        if ($request->has('permission')) {
            $permissions = $request->permission;

            $role->permissions()->sync($permissions);
        }
        return response()->json([
            'message' => 'Role Updated Successfully!',
            'data' => new RoleResource($role)
        ], 200);
    }

    public function delete_role_By_RoleId($id){
        $data = Role::findOrFail($id);
        $data->delete();
        $rolePermission = RoleHasPermissions::where('roleId',$id)->get();
        foreach($rolePermission as $value){
            $value->delete();
        }
        return response()->json([
            'message' => 'Role With permission Delete Successfully!',
        ], 200);

    }
    //end
//permission section start

    public function permission()
    {
        $permissions = Permission::all();
        if($permissions->isEmpty()){
            return response()->json(['message' => 'No Permission found'], 200);
        }
        return PermissionResource::collection($permissions);
    }

    public function store(Request $request)
    {
        $permission = Permission::create(['name' => $request->name]);
        $permission->name = $request->name;
        $permission->save();
        return response()->json([
            'message' => 'Permission Create Successfully!',
            'data' => new PermissionResource($permission)
        ], 200);
    }

    public function updatePermission(Request $request, $id){
        $permission = Permission::find($id);
        if(!$permission){
            return response()->json(['message' => 'No Permission found'], 200);
        }
        $permission->name =$request->name;
        $permission->update();
        return response()->json([
            'message' => 'Permission Updated Successfully!',
            'data' => new PermissionResource($permission)
        ], 200);
    }

    public function deletePermission($id){
        $permission = Permission::find($id);
        if(!$permission){
            return response()->json(['message' => 'No Permission found'], 200);
        }
        $permission->delete();
        return response()->json([
            'message' => 'Permission Delete Successfully!',
            'data' => new PermissionResource($permission)
        ], 200);
    }

//    permission section end


//Admin User Add

    public function edit($id)
    {
        try {
            $role = Role::findById($id);
            $permissions = Permission::where('guard_name', 'web')->get();
            return response()->json([
                'role' => $role,
                'permissions' => $permissions
            ]);
        } catch (DecryptException $e) {
            abort(404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:24', 'min:2',
                    Rule::unique('roles')->ignore($id)->where('guard_name', 'web')
                ],
                'permissions' => 'required|array',
            ]);

            $role = Role::findById($id);
            $permissions = $request->input('permissions');
            if (!empty($permissions)) {
                $role->syncPermissions($permissions);
                $role->name = $request->name;
                $role->update();
                return response()->json(['message' => 'Role Update successfully'], 200);
            }
        } catch (DecryptException $e) {
            abort(404);
        }
    }

    public function getRolePermission(){

        $rolePermission = RoleHasPermissions::all();
        if($rolePermission->isEmpty()){
            return response()->json(['message' => 'No Data found'], 200);
        }
        return RolePermissionResource::collection($rolePermission);
    }

    public function storeRolePermission(Request $request){
        $roleId = $request->roleId;
        $permissionIds = $request->permissionId;

        foreach ($permissionIds as $permissionId) {
            $roleHasPermission = new RoleHasPermissions();
            $roleHasPermission->roleId = $roleId;
            $roleHasPermission->permissionId = $permissionId;
            $roleHasPermission->save();
        }
        return response()->json(['message' => 'Roles with permissions inserted successfully']);
    }

    public function updateRolePermission(Request $request){
        $roleId = $request->roleId;
        $permissionIds = $request->permissionId;
        $data = RoleHasPermissions::where('roleId', $roleId)->get();
        if($data->isEmpty()){
            return response()->json(['message' => 'No Data found'], 200);
        }
        foreach($data as $value){
            $value->delete();
        }
        foreach ($permissionIds as $permissionId) {
            $roleHasPermission = new RoleHasPermissions();
            $roleHasPermission->roleId = $roleId;
            $roleHasPermission->permissionId = $permissionId;
            $roleHasPermission->save();
        }
        return response()->json([
            'message' => 'Roles with permissions updated successfully',
        ], 200);
    }

    public function getPermissionByRole(Request $request){
        $roleId = $request->roleId;
        $getData = RoleHasPermissions::where('roleId', $roleId)->get();
        if($getData->isEmpty()){
            return response()->json(['message' => 'No Data found'], 200);
        }
        return response()->json([
            'message' => 'Data Get Successfully',
            'data' => RolePermissionResource::collection($getData),
        ], 200);
    }

    public function userHasRole(Request $request){
        $userId = $request->userId;
        $roleIds = $request->roleId;
        foreach ($roleIds as $roleId){
            $data = new UserHasRoles();
            $data->userId = $userId;
            $data->roleId = $roleId;
            $data->save();
        }
        return response()->json([
            'message' => 'Assign Role successfully!',
        ], 200);

    }

    public function updateUserHasRole(Request $request){
        $userId = $request->userId;
        $roleIds = $request->roleId;
        $data = UserHasRoles::where('userId', $userId)->get();
        if($data->isEmpty()){
            return response()->json(['message' => 'No Data found'], 200);
        }
        foreach($data as $value){
            $value->delete();
        }
        foreach ($roleIds as $roleId){
            $data = new UserHasRoles();
            $data->userId = $userId;
            $data->roleId = $roleId;
            $data->save();
        }
        return response()->json([
            'message' => 'Assign Role successfully!',
        ], 200);


    }


}
