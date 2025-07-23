<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountClassRequest;
use App\Http\Resources\AccountClassResource;
use App\Models\AccountClass;
use Illuminate\Http\Request;

class AccountClassController extends Controller
{

    public function index()
    {
        $acc_class = AccountClass::latest()->get();

        if($acc_class->isEmpty()){
            return response()->json(['message' => 'No Account Class found'], 200);
        }
        return AccountClassResource::collection($acc_class);
    }


    public function store(AccountClassRequest $request)
    {
        try {
            $acc_class = new AccountClass();
            $acc_class->name = $request->name;
            $acc_class->description = $request->description;

            $acc_class->save();
            return response()->json([
              'message' => 'Account Class created successfully',
              'data' => new AccountClassResource($acc_class),
            ], 200);
          } catch (\Exception $e) {
            // Handle the exception here
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
          }
      }

    public function show($id)
    {
        $acc_class = AccountClass::find($id);

        if (!$acc_class) {
          return response()->json(['message' => 'Account Class not found'], 404);
        }
        return new AccountClassResource($acc_class);
    }


    public function update(AccountClassRequest $request, $id)
    {
        $acc_class = AccountClass::find($id);

        $acc_class->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Account Class updated successfully',
            'data' => new AccountClassResource($acc_class),
        ],200);
    }


    public function destroy($id)
    {
        $acc_class = AccountClass::find($id);
        if (!$acc_class) {
            return response()->json(['message' => 'Account Class not found'], 404);
        }

        $acc_class->delete();
        return response()->json([
            'message' => 'Account Class deleted successfully',
        ],200);
    }



    public function getGroupsAndSubGroupsBy(Request $request)
{
    try {
        $id = $request->id;


        $query = AccountClass::with('groups.subGroups');


        if ($id) {
            $request->validate([
                'id' => 'required|integer|exists:account_class,id',
            ]);
            $query->where('id', $id);
        }

        $accountClasses = $query->get();

        if ($accountClasses->isEmpty()) {
            return response()->json(['message' => 'No Account Classes found'], 404);
        }

        $groupsAndSubGroups = $accountClasses->map(function ($accountClass) {
            return [
                'id' => $accountClass->id,
                'name' => $accountClass->name,
                'groups' => $accountClass->groups->map(function ($group) {
                    return [
                        'groupId' => $group->id,
                        'groupName' => $group->name,
                        'subGroups' => $group->subGroups->map(function ($subGroup) {
                            return [
                                'subGroupId' => $subGroup->id,
                                'subGroupName' => $subGroup->name,
                            ];
                        }),
                    ];
                }),
            ];
        });

        return response()->json([
            'data' => $groupsAndSubGroups,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to fetch groups and sub-groups',
            'message' => $e->getMessage(),
        ], 500);
    }
}



}
