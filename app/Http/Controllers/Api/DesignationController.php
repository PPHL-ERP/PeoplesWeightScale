<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DesignationRequest;
use App\Http\Resources\DesignationResource;
use App\Models\Designation;
use Illuminate\Http\Request;

class DesignationController extends Controller
{
    // public function index()
    // {
    //     $designations = Designation::latest()->get();

    //     if($designations->isEmpty()){
    //         return response()->json(['message' => 'No Designation found'], 200);
    //     }
    //     return DesignationResource::collection($designations);

    // }
    public function index(Request $request)
    {
      $name = $request->name ?? null;
      $departmentId = $request->departmentId ?? null;

      $query = Designation::query();

      // Filter by designation
      if ($name) {
        $query->where('name', 'LIKE', '%' . $name . '%');
      }

      // Filter by departmentId
      if ($departmentId) {
        $query->where('departmentId', $departmentId);
      }

      // Fetch designations with eager loading of related data
      $designations = $query->latest()->get();

      // Check if any designations found
      if ($designations->isEmpty()) {
        return response()->json(['message' => 'No Designation found', 'data' => []], 200);
      }

      // Use the DesignationResource to transform the data
      $transformedDesignations = DesignationResource::collection($designations);

      // Return designations transformed with the resource
      return response()->json([
        'message' => 'Success!',
        'data' => $transformedDesignations
      ], 200);
    }

    public function store(DesignationRequest $request)
    {
      // Validate by name and departmentId
      $exists = Designation::where('name', 'ilike', '%'. strtolower($request->name). '%')
        ->where('departmentId', $request->departmentId)
        ->exists();

      if ($exists) {
        return response()->json([
          'message' => 'Designation already exists!'
        ], 400);
      }

      $designation = Designation::create([
        'name' => $request->name,
        'leaveCount' => $request->leaveCount,
        'description' => $request->description,
        'departmentId' => $request->departmentId,
        'status' => 'approved'
      ]);

      return response()->json([
        'message' => 'Designation created successfully',
        'data' => new DesignationResource($designation),
      ], 200);
    }

    public function show($id)
    {
        $designation = Designation::find($id);
        if (!$designation) {
          return response()->json(['message' => 'Designation not found'], 404);
        }
        return new DesignationResource($designation);
    }

    public function update(DesignationRequest $request, $id)
    {
      $designation = Designation::find($id);
      if (!$designation) {
        return response()->json(['message' => 'Designation not found'], 404);
      }
      $designation->update([
        'name' => $request->name,
        'leaveCount' => $request->leaveCount,
        'description' => $request->description,
        'departmentId' => $request->departmentId,
        'status' => $request->status,
      ]);
      return response()->json([
        'message' => 'Designation updated successfully',
        'data' => new DesignationResource($designation),
      ], 200);
    }

    public function statusUpdate(Request $request, $id)
    {
      $designation = Designation::find($id);
      $designation->status = $request->status;
      $designation->update();
      return response()->json([
        'message' => 'Designation Status change successfully',
      ], 200);
    }

    public function destroy($id)
    {
        $designation = Designation::find($id);
        if (!$designation) {
          return response()->json(['message' => 'Designation not found'], 404);
        }
        $designation->delete();
        return response()->json([
          'message' => 'Designation deleted successfully',
        ], 200);
    }
}
