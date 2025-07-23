<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalesAddressMapRequest;
use App\Http\Resources\SalesAddressMapResource;
use App\Models\SalesAddressMap;
use Illuminate\Http\Request;

class SalesAddressMapController extends Controller
{
    public function index()
    {
        $sales_maps = SalesAddressMap::latest()->get();

        if ($sales_maps->isEmpty()) {
          return response()->json(['message' => 'No Sales Address Maps found'], 200);
        }
        return SalesAddressMapResource::collection($sales_maps);
    }


    public function store(SalesAddressMapRequest $request)
    {
        try {

            $sales_map = new SalesAddressMap();

            $sales_map->saleId = $request->saleId;
            $sales_map->transportId = $request->transportId;
            $sales_map->address = $request->address;
            $sales_map->note = $request->note;

            $sales_map->save();
            // dd($sales_map);

            return response()->json([
              'message' => 'Sales Address Maps created successfully',
              'data' => new SalesAddressMapResource($sales_map),
            ], 200);
          } catch (\Exception $e) {
            // Handle the exception here
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
          }
    }

    public function show($id)
    {
        $sales_map = SalesAddressMap::find($id);

        if (!$sales_map) {
          return response()->json(['message' => 'Sales Address Maps not found'], 404);
        }
        return new SalesAddressMapResource($sales_map);
    }


    public function update(SalesAddressMapRequest $request, $id)
    {
        try {

            $sales_map = SalesAddressMap::find($id);

            if (!$sales_map) {
              return $this->sendError('Sales Address Maps not found.');
            }

            $sales_map->saleId = $request->saleId;
            $sales_map->transportId = $request->transportId;
            $sales_map->address = $request->address;
            $sales_map->note = $request->note;

            $sales_map->update();


            return response()->json([
              'message' => 'Sales Address Maps Updated successfully',
              'data' => new SalesAddressMapResource($sales_map),
            ], 200);
          } catch (\Exception $e) {
            // Handle the exception here
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
          }
    }


    public function destroy($id)
    {
        $sales_map = SalesAddressMap::find($id);
        if (!$sales_map) {
          return response()->json(['message' => 'Sales Address Maps not found'], 404);
        }
        $sales_map->delete();
        return response()->json([
          'message' => 'Sales Address Maps deleted successfully',
        ], 200);
    }
}