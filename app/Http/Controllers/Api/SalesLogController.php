<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalesLogRequest;
use App\Models\SalesLog;
use Illuminate\Http\Request;

class SalesLogController extends Controller
{
    public function index()
    {
        try {
            $salesLogs = SalesLog::all();
            return response()->json(['data' => $salesLogs], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve sales logs'], 500);
        }
    }

    public function show($id)
    {
        try {
            $salesLog = SalesLog::find($id);
            if (!$salesLog) {
                return response()->json(['error' => 'Sales Log not found'], 404);
            }
            return response()->json(['data' => $salesLog], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve sales log'], 500);
        }
    }

    public function store(SalesLogRequest $request)
    {
        try {
            $salesLog = SalesLog::create($request->validated());
            return response()->json(['data' => $salesLog], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create sales log'], 500);
        }
    }

    public function update(SalesLogRequest $request, $id)
    {
        try {
            $salesLog = SalesLog::find($id);
            if (!$salesLog) {
                return response()->json(['error' => 'Sales Log not found'], 404);
            }
            $salesLog->update($request->validated());
            return response()->json(['data' => $salesLog], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update sales log'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $salesLog = SalesLog::find($id);
            if (!$salesLog) {
                return response()->json(['error' => 'Sales Log not found'], 404);
            }
            $salesLog->delete();
            return response()->json(['message' => 'Sales Log deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete sales log'], 500);
        }
    }

    public function getSalesLogsByMarket($market_id)
    {
        try {
            $salesLogs = SalesLog::where('sales_endpoint_id', $market_id)->get();
            if ($salesLogs->isEmpty()) {
                return response()->json(['error' => 'No sales logs found for this market'], 404);
            }
            return response()->json(['data' => $salesLogs], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve sales logs'], 500);
        }
    }

    public function getUnsoldItems()
    {
        try {
            $unsoldItems = SalesLog::where('status', 'unsold')->get();
            return response()->json(['data' => $unsoldItems], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve unsold items'], 500);
        }
    }
}
