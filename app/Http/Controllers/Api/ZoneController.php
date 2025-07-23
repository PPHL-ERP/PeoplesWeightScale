<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ZoneRequest;
use App\Http\Resources\ZoneResource;
use App\Models\Zone;
use App\Models\ZoneHasDistricts;
use Illuminate\Http\Request;

class ZoneController extends Controller
{

//   public function index()
//   {
//     $zones = Zone::with(['districts'])->latest()->get();

//     if ($zones->isEmpty()) {
//       return response()->json(['message' => 'No Zone found'], 200);
//     }
//     return ZoneResource::collection($zones);
//   }

public function index(Request $request)
{

 $zoneName = $request->zoneName ?? null;
 $zonalInCharge = $request->zonalInCharge ?? null;

 $query = Zone::query();

 // Filter by zoneName
    if ($zoneName) {
   $query->where('zoneName', 'LIKE', '%' . $zoneName . '%');
 }

 //Filter by zonalInCharge
 if ($zonalInCharge) {
   $query->orWhere('zonalInCharge', $zonalInCharge);
 }

 // Fetch zones with eager loading of related data
 $zones = $query->latest()->get();

 // Check if any zones found
 if ($zones->isEmpty()) {
   return response()->json(['message' => 'No Zone found', 'data' => []], 200);
 }

 // Use the ZoneResource to transform the data
 $transformedZones = ZoneResource::collection($zones);

 // Return products transformed with the resource
 return response()->json([
   'message' => 'Success!',
   'data' => $transformedZones
 ], 200);
}


  public function store(ZoneRequest $request)
  {
    $districtIds = $request->districtIds;

    $zone = Zone::create([
      'zoneName' => $request->zoneName,
      'zonalInCharge' => $request->zonalInCharge,
      'note' => $request->note
    ]);

    $zoneId = $zone->id;

    if (is_array($districtIds)) {
      foreach ($districtIds as $districtId) {
        $record = new ZoneHasDistricts();
        $record->zoneId = $zoneId;
        $record->districtId = $districtId;
        $record->save();
      }
    }

    return response()->json([
      'message' => 'Zone created successfully',
      'data' => new ZoneResource($zone),
    ], 200);
  }


  public function show($id)
  {

    $zone = Zone::with(['districts'])->find($id);

    if (!$zone) {
      return response()->json(['message' => 'Zone not found'], 404);
    }

    return new ZoneResource($zone);
  }


  public function update(ZoneRequest $request, $id)
  {
    $zone = Zone::find($id);

    if (!$zone) {
      return response()->json(['message' => 'Zone not found'], 404);
    }

    $districtIds = $request->districtIds;

    $zone->update([
      'zoneName' => $request->zoneName,
      'zonalInCharge' => $request->zonalInCharge,
      'note' => $request->note
    ]);

    // Get existing records and remove them
    $existingRecords = ZoneHasDistricts::query()
      ->where('zoneId', $zone->id)
      ->get();

    foreach ($existingRecords as $record) {
      $record->delete();
    }

    $zoneId = $zone->id;

    // Add the new ones
    if (is_array($districtIds)) {
      foreach ($districtIds as $districtId) {
        $record = new ZoneHasDistricts();
        $record->zoneId = $zoneId;
        $record->districtId = $districtId;
        $record->save();
      }
    }

    return response()->json([
      'message' => 'Zone updated successfully',
      'data' => new ZoneResource($zone),
    ], 200);
  }


  public function destroy($id)
  {
    $zone = Zone::find($id);
    if (!$zone) {
      return response()->json(['message' => 'Zone not found'], 404);
    }
    $zone->delete();
    return response()->json([
      'message' => 'Zone deleted successfully',
    ], 200);
  }
}