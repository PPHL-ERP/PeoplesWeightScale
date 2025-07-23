<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuantityTypeRequest;
use App\Http\Resources\QuantityTypeResource;
use App\Models\QuantityType;
use Illuminate\Http\Request;
use App\Models\UserActivity;


class QuantityTypeController extends Controller
{

    public function index()
    {
        $quantity_types = QuantityType::latest()->get();

        if($quantity_types->isEmpty()){
            return response()->json(['message' => 'No Quantity Type found'], 200);
        }
        return QuantityTypeResource::collection($quantity_types);
   }

    public function store(QuantityTypeRequest $request)
    {
        $quantity_type = QuantityType::create([
            'name' => $request->name,
            'codeName' => $request->codeName,
            'note' => $request->note,
        ]);
        $userActivity = UserActivity::create([
            'user_id' => auth()->user()->id,
            'module_name' => 'Quantity Type',
            'message' => 'New Quantity Type created successfully',
            'module_details' => json_encode([
              'name' => $quantity_type->name,
              'codeName' => $quantity_type->codeName,
              'note' => $quantity_type->note,
            ]),
          ]);
        return response()->json([
            'message' => 'Quantity Type created successfully',
            'data' => new QuantityTypeResource($quantity_type),
        ],200);
    }


    public function show($id)
    {
        $quantity_type = QuantityType::find($id);
        if (!$quantity_type) {
            return response()->json(['message' => 'Quantity Type not found'], 404);
        }
        return new QuantityTypeResource($quantity_type);
    }


    public function update(QuantityTypeRequest $request, $id)
    {
        $quantity_type = QuantityType::find($id);

        $quantity_type->update([
            'name' => $request->name,
            'codeName' => $request->codeName,
            'note' => $request->note,
        ]);
        $userActivity = UserActivity::create([
            'user_id' => auth()->user()->id,
            'module_name' => 'Quantity Type',
            'message' => 'Quantity Type Updated Successfully!',
          ]);
        return response()->json([
            'message' => 'Quantity Type updated successfully',
            'data' => new QuantityTypeResource($quantity_type),
        ],200);
    }

    public function destroy($id)
    {
        $quantity_type = QuantityType::find($id);
        if (!$quantity_type) {
            return response()->json(['message' => 'Quantity Type not found'], 404);
        }
        $userActivity = UserActivity::create([
            'user_id' => auth()->user()->id,
            'module_name' => 'Quantity Type',
            'message' => 'Quantity Type Deleted Successfully!',
          ]);
        $quantity_type->delete();
        return response()->json([
            'message' => 'Quantity Type deleted successfully',
        ],200);
    }
}