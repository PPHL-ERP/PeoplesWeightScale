<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{

    public function index()
    {
        $departments = Department::latest()->get();

        if($departments->isEmpty()){
            return response()->json(['message' => 'No Department found'], 200);
        }
        return DepartmentResource::collection($departments);
    }


    public function store(DepartmentRequest $request)
    {

        $department = Department::create([
            'name' => $request->name,
            'description' => $request->description,
            'depLocation' => $request->depLocation,
            'status' => 'approved',
        ]);

        return response()->json([
            'message' => 'Department created successfully',
            'data' => new DepartmentResource($department),
        ],200);
    }


    public function show($id)
    {
        $department = Department::find($id);
        if (!$department) {
            return response()->json(['message' => 'Department not found'], 404);
        }
        return new DepartmentResource($department);
     }


    public function update(DepartmentRequest $request, $id)
    {
        $department = Department::find($id);

        $department->update([
            'name' => $request->name,
            'description' => $request->description,
            'depLocation' => $request->depLocation,
            'status' => $request->status,
        ]);
        return response()->json([
            'message' => 'Department updated successfully',
            'data' => new DepartmentResource($department),
        ],200);
    }

    public function statusUpdate(Request $request,$id){
        $department = Department::find($id);
        $department->status = $request->status;
        $department->update();
        return response()->json([
            'message' => 'Department Status change successfully',
        ],200);
    }

    public function destroy($id)
    {
        $department = Department::find($id);
        if (!$department) {
            return response()->json(['message' => 'Department not found'], 404);
        }
        $department->delete();
        return response()->json([
            'message' => 'Department deleted successfully',
        ],200);
    }
}
