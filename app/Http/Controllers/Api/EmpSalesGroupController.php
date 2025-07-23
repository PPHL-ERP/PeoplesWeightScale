<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmpSalesGroupRequest;
use App\Http\Resources\EmpSalesGroupResource;
use App\Models\EmployeeSalesGroup;
use Illuminate\Http\Request;

class EmpSalesGroupController extends Controller
{

    public function index()
    {
        $sales_groups = EmployeeSalesGroup::latest()->get();

        if($sales_groups->isEmpty()){
            return response()->json(['message' => 'No EmpSalesGroup found'], 200);
        }
        return EmpSalesGroupResource::collection($sales_groups);
    }



    public function store(EmpSalesGroupRequest $request)
    {
        $sales_group = EmployeeSalesGroup::create([
            'groupName' => $request->groupName,
            'groupLocation' => $request->groupLocation,
            'groupLeader' => $request->groupLeader,
            'groupSup' => $request->groupSup,
            'note' => $request->note,
            'status' =>  'pending',
        ]);
        return response()->json([
            'message' => 'EmpSalesGroup created successfully',
            'data' => new EmpSalesGroupResource($sales_group),
        ],200);
    }


    public function show(string $id)
    {
        $sales_group = EmployeeSalesGroup::find($id);
        if (!$sales_group) {
            return response()->json(['message' => 'EmpSalesGroup not found'], 404);
        }
        return new EmpSalesGroupResource($sales_group);
    }


    public function update(EmpSalesGroupRequest $request, $id)
    {
        $sales_group = EmployeeSalesGroup::find($id);

        $sales_group->update([
            'groupName' => $request->groupName,
            'groupLocation' => $request->groupLocation,
            'groupLeader' => $request->groupLeader,
            'groupSup' => $request->groupSup,
            'note' => $request->note,
            'status' =>  $request->status,
        ]);

        return response()->json([
            'message' => 'EmpSalesGroup updated successfully',
            'data' => new EmpSalesGroupResource($sales_group),
        ],200);
    }

    public function statusUpdate(Request $request, $id)
    {
      $sales_group = EmployeeSalesGroup::find($id);
      $sales_group->status = $request->status;

      $sales_group->update();
      return response()->json([
        'message' => 'EmpSalesGroup Status change successfully',
      ], 200);
    }

    public function destroy(string $id)
    {
        $sales_group = EmployeeSalesGroup::find($id);
        if (!$sales_group) {
            return response()->json(['message' => 'EmpSalesGroup not found'], 404);
        }

        $sales_group->delete();
        return response()->json([
            'message' => 'EmpSalesGroup deleted successfully',
        ],200);
    }
}