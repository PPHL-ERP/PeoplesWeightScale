<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountSubGroupRequest;
use App\Http\Resources\AccountSubGroupResource;
use App\Models\AccountSubGroup;
use Illuminate\Http\Request;

class AccountSubGroupController extends Controller
{

    public function index()
    {
        $acc_sub_groups = AccountSubGroup::latest()->get();

        if($acc_sub_groups->isEmpty()){
            return response()->json(['message' => 'No Account SubGroup found'], 200);
        }
        return AccountSubGroupResource::collection($acc_sub_groups);
    }



    public function store(AccountSubGroupRequest $request)
    {
        try {
            $acc_sub_group = new AccountSubGroup();
            $acc_sub_group->name = $request->name;
            $acc_sub_group->groupId = $request->groupId;
            $acc_sub_group->description = $request->description;

            $acc_sub_group->save();
            return response()->json([
              'message' => 'Account SubGroup created successfully',
              'data' => new AccountSubGroupResource($acc_sub_group),
            ], 200);
          } catch (\Exception $e) {
            // Handle the exception here
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
          }
      }


    public function show($id)
    {
        $acc_sub_group = AccountSubGroup::find($id);

        if (!$acc_sub_group) {
          return response()->json(['message' => 'Account SubGroup not found'], 404);
        }
        return new AccountSubGroupResource($acc_sub_group);
    }


    public function update(AccountSubGroupRequest $request, $id)
    {
        $acc_sub_group = AccountSubGroup::find($id);

        $acc_sub_group->update([
            'name' => $request->name,
            'groupId' => $request->groupId,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Account SubGroup updated successfully',
            'data' => new AccountSubGroupResource($acc_sub_group),
        ],200);
    }


    public function destroy($id)
    {
        $acc_sub_group = AccountSubGroup::find($id);
        if (!$acc_sub_group) {
            return response()->json(['message' => 'Account SubGroup not found'], 404);
        }

        $acc_sub_group->delete();
        return response()->json([
            'message' => 'Account SubGroup deleted successfully',
        ],200);
    }
}
