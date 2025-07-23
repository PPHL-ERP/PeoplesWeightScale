<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmpManageGroupRequest;
use App\Http\Resources\EmpManageGroupResource;
use App\Models\EmployeeManageGroup;
use Illuminate\Http\Request;

class EmpManageGroupController extends Controller
{

    public function index()
    {
        $manage_groups = EmployeeManageGroup::latest()->get();

        if($manage_groups->isEmpty()){
            return response()->json(['message' => 'No EmpManageGroup found'], 200);
        }
        return EmpManageGroupResource::collection($manage_groups);
    }


    public function store(EmpManageGroupRequest $request)
    {
        $manage_group = EmployeeManageGroup::create([
            'groupId' => $request->groupId,
            'empId' => $request->empId,
            'userId' => $request->userId,
        ]);
        return response()->json([
            'message' => 'EmpManageGroup created successfully',
            'data' => new EmpManageGroupResource($manage_group),
        ],200);
    }



    public function show(string $id)
    {
        $manage_group = EmployeeManageGroup::find($id);
        if (!$manage_group) {
            return response()->json(['message' => 'EmpManageGroup not found'], 404);
        }
        return new EmpManageGroupResource($manage_group);
    }


    public function update(EmpManageGroupRequest $request, $id)
    {
        $manage_group = EmployeeManageGroup::find($id);

        $manage_group->update([
            'groupId' => $request->groupId,
            'empId' => $request->empId,
            'userId' => $request->userId,
        ]);

        return response()->json([
            'message' => 'EmpManageGroup updated successfully',
            'data' => new EmpManageGroupResource($manage_group),
        ],200);
    }


    public function destroy(string $id)
    {
        $manage_group = EmployeeManageGroup::find($id);
        if (!$manage_group) {
            return response()->json(['message' => 'EmpManageGroup not found'], 404);
        }

        $manage_group->delete();
        return response()->json([
            'message' => 'EmpManageGroup deleted successfully',
        ],200);
    }
}
