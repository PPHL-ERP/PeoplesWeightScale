<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountGroupRequest;
use App\Http\Resources\AccountGroupResource;
use App\Models\AccountGroup;
use Illuminate\Http\Request;

class AccountGroupController extends Controller
{

    public function index()
    {
        $acc_groups = AccountGroup::latest()->get();

        if($acc_groups->isEmpty()){
            return response()->json(['message' => 'No Account Group found'], 200);
        }
        return AccountGroupResource::collection($acc_groups);
    }


    public function store(AccountGroupRequest $request)
    {
        try {
            $acc_group = new AccountGroup();
            $acc_group->name = $request->name;
            $acc_group->classId = $request->classId;
            $acc_group->description = $request->description;

            $acc_group->save();
            return response()->json([
              'message' => 'Account Group created successfully',
              'data' => new AccountGroupResource($acc_group),
            ], 200);
          } catch (\Exception $e) {
            // Handle the exception here
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
          }
      }

    public function show($id)
    {
        $acc_group = AccountGroup::find($id);

        if (!$acc_group) {
          return response()->json(['message' => 'Account Group not found'], 404);
        }
        return new AccountGroupResource($acc_group);
    }


    public function update(AccountGroupRequest $request, $id)
    {
        $acc_group = AccountGroup::find($id);

        $acc_group->update([
            'name' => $request->name,
            'classId' => $request->classId,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Account Group updated successfully',
            'data' => new AccountGroupResource($acc_group),
        ],200);
    }


    public function destroy($id)
    {
        $acc_group = AccountGroup::find($id);
        if (!$acc_group) {
            return response()->json(['message' => 'Account Group not found'], 404);
        }

        $acc_group->delete();
        return response()->json([
            'message' => 'Account Group deleted successfully',
        ],200);
    }
}
