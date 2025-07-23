<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index()
    {
        try {
            $auditLogs = AuditLog::with(['company', 'createdBy'])->get();
            return response()->json(['data' => $auditLogs], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve audit logs'], 500);
        }
    }

    public function show($id)
    {
        try {
            $auditLog = AuditLog::with(['company', 'createdBy'])->find($id);
            if (!$auditLog) {
                return response()->json(['error' => 'Audit Log not found'], 404);
            }
            return response()->json(['data' => $auditLog], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve audit log'], 500);
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'crBy' => 'required|exists:users,id',
            'action' => 'required|string',
            'description' => 'nullable|string',
        ]);

        try {
            $auditLog = AuditLog::create($validatedData);
            return response()->json(['data' => $auditLog], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create audit log'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'crBy' => 'required|exists:users,id',
            'action' => 'required|string',
            'description' => 'nullable|string',
        ]);

        try {
            $auditLog = AuditLog::find($id);
            if (!$auditLog) {
                return response()->json(['error' => 'Audit Log not found'], 404);
            }
            $auditLog->update($validatedData);
            return response()->json(['data' => $auditLog], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update audit log'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $auditLog = AuditLog::find($id);
            if (!$auditLog) {
                return response()->json(['error' => 'Audit Log not found'], 404);
            }
            $auditLog->delete();
            return response()->json(['message' => 'Audit Log deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete audit log'], 500);
        }
    }

    public function getAuditLogsByModel($model_type, $model_id)
    {
        try {
            $auditLogs = AuditLog::with(['company', 'createdBy'])
                ->where('model_type', $model_type)
                ->where('model_id', $model_id)
                ->get();
            return response()->json(['data' => $auditLogs], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve audit logs by model'], 500);
        }
    }
}
