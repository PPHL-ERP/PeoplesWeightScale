<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\WCustomerRequest;
use App\Http\Resources\WCustomerResource;
use App\Models\WCustomer;
use Illuminate\Http\Request;

class WCustomerController extends Controller
{

    public function index()
    {
        $w_c = WCustomer::latest()->get();

        if($w_c->isEmpty()){
            return response()->json(['message' => 'No W C found'], 200);
        }
        return WCustomerResource::collection($w_c);
    }


    public function store(WCustomerRequest $request)
    {
        $w_c = WCustomer::create([
            'cId' => $request->cId,
            'cName' => $request->cName,
            'phone' => $request->phone,
            'address' => $request->address,
            'note' => $request->note,
        ]);
        return response()->json([
            'message' => 'W C created successfully',
            'data' => new WCustomerResource($w_c),
        ],200);
    }


    public function show(string $id)
    {
        $w_c = WCustomer::find($id);
        if (!$w_c) {
            return response()->json(['message' => 'W C not found'], 404);
        }
        return new WCustomerResource($w_c);
    }


    public function update(WCustomerRequest $request, string $id)
    {
        $w_c = WCustomer::find($id);

        $w_c->update([
            // 'cId' => $request->cId,
            'cName' => $request->cName,
            'phone' => $request->phone,
            'address' => $request->address,
            'note' => $request->note,
        ]);

        return response()->json([
            'message' => 'W C updated successfully',
            'data' => new WCustomerResource($w_c),
        ],200);
    }


    public function destroy(string $id)
    {
        $w_c = WCustomer::find($id);
        if (!$w_c) {
            return response()->json(['message' => 'W C not found'], 404);
        }

        $w_c->delete();
        return response()->json([
            'message' => 'W C deleted successfully',
        ],200);
    }
}