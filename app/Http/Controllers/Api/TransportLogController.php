<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransportLogRequest;
use App\Models\TransportLog;
use Illuminate\Http\Request;

class TransportLogController extends Controller
{
    public function index()
    {
        try {
            $transportLogs = TransportLog::all();
            return response()->json(['data' => $transportLogs], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve transport logs'], 500);
        }
    }

    public function show($id)
    {
        try {
            $transportLog = TransportLog::find($id);
            if (!$transportLog) {
                return response()->json(['error' => 'Transport Log not found'], 404);
            }
            return response()->json(['data' => $transportLog], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve transport log'], 500);
        }
    }

    public function store(TransportLogRequest $request)
    {
        try {
            $transportLog = TransportLog::create($request->validated());
            return response()->json(['data' => $transportLog], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create transport log'], 500);
        }
    }

    public function update(TransportLogRequest $request, $id)
    {
        try {
            $transportLog = TransportLog::find($id);
            if (!$transportLog) {
                return response()->json(['error' => 'Transport Log not found'], 404);
            }
            $transportLog->update($request->validated());
            return response()->json(['data' => $transportLog], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update transport log'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $transportLog = TransportLog::find($id);
            if (!$transportLog) {
                return response()->json(['error' => 'Transport Log not found'], 404);
            }
            $transportLog->delete();
            return response()->json(['message' => 'Transport Log deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete transport log'], 500);
        }
    }

    public function trackTransport($transport_id)
    {
        try {
            $transportDetails = TransportLog::with(['unit', 'product', 'salesEndpoint'])
                                            ->where('id', $transport_id)
                                            ->first();

            if (!$transportDetails) {
                return response()->json(['error' => 'Transport Log not found'], 404);
            }

            return response()->json(['data' => $transportDetails], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to track transport'], 500);
        }
    }
}
