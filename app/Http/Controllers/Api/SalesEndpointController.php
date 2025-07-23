<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalesEndpointRequest;
use App\Models\SalesEndpoint;
use Illuminate\Http\Request;

class SalesEndpointController extends Controller
{

    public function index()
    {
        try {
            $salesEndpoints = SalesEndpoint::all();
            return response()->json(['data' => $salesEndpoints], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve sales endpoints'], 500);
        }
    }


    public function store(SalesEndpointRequest $request)
    {
        try {
            $salesEndpoint = SalesEndpoint::create($request->validated());
            return response()->json(['data' => $salesEndpoint], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create sales endpoint'], 500);
        }
    }

    public function show($id)
    {
        try {
            $salesEndpoint = SalesEndpoint::find($id);
            if (!$salesEndpoint) {
                return response()->json(['error' => 'Sales Endpoint not found'], 404);
            }
            return response()->json(['data' => $salesEndpoint], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve sales endpoint'], 500);
        }
    }


    public function update(SalesEndpointRequest $request, $id)
    {
        try {
            $salesEndpoint = SalesEndpoint::find($id);
            if (!$salesEndpoint) {
                return response()->json(['error' => 'Sales Endpoint not found'], 404);
            }
            $salesEndpoint->update($request->validated());
            return response()->json(['data' => $salesEndpoint], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update sales endpoint'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $salesEndpoint = SalesEndpoint::find($id);
            if (!$salesEndpoint) {
                return response()->json(['error' => 'Sales Endpoint not found'], 404);
            }
            $salesEndpoint->delete();
            return response()->json(['message' => 'Sales Endpoint deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete sales endpoint'], 500);
        }
    }
}