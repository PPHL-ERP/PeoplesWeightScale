<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UnitRequest;
use App\Http\Resources\UnitResource;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{

    public function index()
    {
        $units = Unit::latest()->get();

        if($units->isEmpty()){
            return response()->json(['message' => 'No Unit found'], 200);
        }
        return UnitResource::collection($units);
    }


    public function store(UnitRequest $request)
    {
        $unit = Unit::create([
            'name' => $request->name,
            'qty' => $request->qty,
            'note' => $request->note,
            'status' => 'active',
        ]);

        return response()->json([
            'message' => 'Unit created successfully',
            'data' => new UnitResource($unit),
        ],200);
    }


    public function show($id)
    {
        $unit = Unit::find($id);
        if (!$unit) {
            return response()->json(['message' => 'Unit not found'], 404);
        }
        return new UnitResource($unit);
     }


    public function update(UnitRequest $request, $id)
    {
        $unit = Unit::find($id);
        if (!$unit) {
            return response()->json(['message' => 'Unit not found'], 404);
        }
        $unit->update([
            'name' => $request->name,
            'qty' => $request->qty,
            'note' => $request->note,
            'status' => $request->status,
        ]);
        return response()->json([
            'message' => 'Unit updated successfully',
            'data' => new UnitResource($unit),
        ],200);
    }

    public function statusUpdate(Request $request, $id)
    {
      $unit = Unit::find($id);
      $unit->status = $request->status;
      $unit->update();
      return response()->json([
        'message' => 'Unit Status change successfully',
      ], 200);
    }

    public function destroy($id)
    {
        $unit = Unit::find($id);
        if (!$unit) {
            return response()->json(['message' => 'Unit not found'], 404);
        }
        $unit->delete();
        return response()->json([
            'message' => 'Unit deleted successfully',
        ],200);
    }
}
