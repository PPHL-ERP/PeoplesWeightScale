<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FlockRequest;
use App\Http\Resources\FlockResource;
use App\Models\Flock;
use Illuminate\Http\Request;

class FlockController extends Controller
{

    public function index()
    {
        $flocks = Flock::latest()->get();

        if($flocks->isEmpty()){
            return response()->json(['message' => 'No Flock found'], 200);
        }
        return FlockResource::collection($flocks);
    }


    public function store(FlockRequest $request)
    {
        $flock = Flock::create([
            'flockName' => $request->flockName,
            'flockType' => $request->flockType,
            'sectorId' => $request->sectorId,
            'flockStartDate' => $request->flockStartDate,
            'note' => $request->note,
            'status' => 'pending',
        ]);
        return response()->json([
            'message' => 'Flock created successfully',
            'data' => new FlockResource($flock),
        ],200);
    }


    public function show($id)
    {
        $flock = Flock::find($id);
        if (!$flock) {
            return response()->json(['message' => 'Flock not found'], 404);
        }
        return new FlockResource($flock);
    }


    public function update(FlockRequest $request, $id)
    {
        $flock = Flock::find($id);

        $flock->update([
            'flockName' => $request->flockName,
            'flockType' => $request->flockType,
            'sectorId' => $request->sectorId,
            'flockStartDate' => $request->flockStartDate,
            'note' => $request->note,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Flock updated successfully',
            'data' => new FlockResource($flock),
        ],200);
    }

    public function statusUpdate(Request $request, $id)
    {
      $flock = Flock::find($id);
      $flock->status = $request->status;

      $flock->update();
      return response()->json([
        'message' => 'Flock Status change successfully',
      ], 200);
    }


    public function destroy($id)
    {
        $flock = Flock::find($id);
        if (!$flock) {
            return response()->json(['message' => 'Flock not found'], 404);
        }

        $flock->delete();
        return response()->json([
            'message' => 'Flock deleted successfully',
        ],200);
    }
}