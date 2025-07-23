<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReturnLog;
use Illuminate\Http\Request;

class ReturnLogController extends Controller
{
    public function index()
    {
        try {
            $returnLogs = ReturnLog::all();
            return response()->json(['data' => $returnLogs], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve return logs'], 500);
        }
    }

    public function show($id)
    {
        try {
            $returnLog = ReturnLog::find($id);
            if (!$returnLog) {
                return response()->json(['error' => 'Return Log not found'], 404);
            }
            return response()->json(['data' => $returnLog], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve return log'], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'sales_log_id' => 'required|exists:sales_logs,id',
            'productId' => 'required|exists:products,id',
            'companyId' => 'nullable|exists:companies,id',
            'sales_endpoint_id' => 'nullable|exists:sales_endpoints,id',
            'quantity_returned' => 'required|integer|min:1',
            'reason' => 'nullable|string',
            'return_date' => 'required|date',
            'remarks' => 'nullable|string',
            'crBy' => 'nullable|exists:users,id',
            'appBy' => 'nullable|exists:users,id',
        ]);

        try {
            $returnLog = ReturnLog::create($request->all());

            // Update Inventory
            $inventory = Inventory::where('sales_endpoint_id', $returnLog->sales_endpoint_id)
                ->where('productId', $returnLog->productId)
                ->first();

            if ($inventory) {
                $inventory->quantity += $returnLog->quantity_returned;
                $inventory->save();
            } else {
                Inventory::create([
                    'unitId' => null, // Adjust if necessary
                    'productId' => $returnLog->productId,
                    'companyId' => $returnLog->companyId,
                    'sales_endpoint_id' => $returnLog->sales_endpoint_id,
                    'quantity' => $returnLog->quantity_returned,
                    'date' => $returnLog->return_date,
                    'crBy' => $returnLog->crBy,
                    'appBy' => $returnLog->appBy,
                ]);
            }

            return response()->json(['data' => $returnLog], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create return log'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'sales_log_id' => 'required|exists:sales_logs,id',
            'productId' => 'required|exists:products,id',
            'companyId' => 'nullable|exists:companies,id',
            'sales_endpoint_id' => 'nullable|exists:sales_endpoints,id',
            'quantity_returned' => 'required|integer|min:1',
            'reason' => 'nullable|string',
            'return_date' => 'required|date',
            'remarks' => 'nullable|string',
            'crBy' => 'nullable|exists:users,id',
            'appBy' => 'nullable|exists:users,id',
        ]);

        try {
            $returnLog = ReturnLog::find($id);
            if (!$returnLog) {
                return response()->json(['error' => 'Return Log not found'], 404);
            }

            $returnLog->update($request->all());
            return response()->json(['data' => $returnLog], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update return log'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $returnLog = ReturnLog::find($id);
            if (!$returnLog) {
                return response()->json(['error' => 'Return Log not found'], 404);
            }
            $returnLog->delete();
            return response()->json(['message' => 'Return Log deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete return log'], 500);
        }
    }
}
