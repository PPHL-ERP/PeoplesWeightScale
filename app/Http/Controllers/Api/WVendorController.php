<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\WVendorRequest;
use App\Http\Resources\WVendorResource;
use App\Models\WVendor;
use Illuminate\Http\Request;

class WVendorController extends Controller
{

    public function index()
    {
        $w_V = WVendor::latest()->get();

        if($w_V->isEmpty()){
            return response()->json(['message' => 'No W V found'], 200);
        }
        return WVendorResource::collection($w_V);
    }

    public function store(WVendorRequest $request)
    {
        $w_V = WVendor::create([
            'vId' => $request->vId,
            'vName' => $request->vName,
            'phone' => $request->phone,
            'address' => $request->address,
            'note' => $request->note,
        ]);
        return response()->json([
            'message' => 'W V created successfully',
            'data' => new WVendorResource($w_V),
        ],200);
    }

    public function show( $id)
    {
        $w_v = WVendor::find($id);
        if (!$w_v) {
            return response()->json(['message' => 'W V not found'], 404);
        }
        return new WVendorResource($w_v);
    }


    public function update(WVendorRequest $request,  $id)
    {
        $w_v = WVendor::find($id);

        $w_v->update([
            //'vId' => $request->vId,
            'vName' => $request->vName,
            'phone' => $request->phone,
            'address' => $request->address,
            'note' => $request->note,
        ]);

        return response()->json([
            'message' => 'W V updated successfully',
            'data' => new WVendorResource($w_v),
        ],200);
    }


    public function destroy( $id)
    {
        $w_v = WVendor::find($id);
        if (!$w_v) {
            return response()->json(['message' => 'W V not found'], 404);
        }

        $w_v->delete();
        return response()->json([
            'message' => 'W V deleted successfully',
        ],200);
    }
}