<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\WMaterialRequest;
use App\Http\Resources\WMaterialResource;
use App\Models\WMaterial;
use Illuminate\Http\Request;

class WMaterialController extends Controller
{

    public function index()
    {
        $w_m = WMaterial::latest()->get();

        if($w_m->isEmpty()){
            return response()->json(['message' => 'No W M found'], 200);
        }
        return WMaterialResource::collection($w_m);
    }


    public function store(WMaterialRequest $request)
    {
        $w_m = WMaterial::create([
            'mId' => $request->mId,
            'mName' => $request->mName,
            'categoryType' => $request->categoryType,
            'note' => $request->note,
        ]);
        return response()->json([
            'message' => 'W M created successfully',
            'data' => new WMaterialResource($w_m),
        ],200);
    }


    public function show( $id)
    {
        $w_m = WMaterial::find($id);
        if (!$w_m) {
            return response()->json(['message' => 'W M not found'], 404);
        }
        return new WMaterialResource($w_m);
    }


    public function update(WMaterialRequest $request,  $id)
    {
        $w_m = WMaterial::find($id);

        $w_m->update([
            //'mId' => $request->mId,
            'mName' => $request->mName,
            'categoryType' => $request->categoryType,
            'note' => $request->note,
        ]);

        return response()->json([
            'message' => 'W V updated successfully',
            'data' => new WMaterialResource($w_m),
        ],200);
    }

    public function destroy($id)
    {
        $w_m = WMaterial::find($id);
        if (!$w_m) {
            return response()->json(['message' => 'W M not found'], 404);
        }

        $w_m->delete();
        return response()->json([
            'message' => 'W M deleted successfully',
        ],200);
    }
}