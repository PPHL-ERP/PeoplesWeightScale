<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ledger;
use Illuminate\Http\Request;

class LedgerController extends Controller
{
    public function index()
    {
        try {
            $ledgers = Ledger::with(['unit', 'product', 'salesEndpoint'])->get();
            return response()->json(['data' => $ledgers], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve ledgers'], 500);
        }
    }

    public function show($id)
    {
        try {
            $ledger = Ledger::with(['unit', 'product', 'salesEndpoint'])->find($id);
            if (!$ledger) {
                return response()->json(['error' => 'Ledger not found'], 404);
            }
            return response()->json(['data' => $ledger], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve ledger'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $ledger = Ledger::create($request->all());
            return response()->json(['data' => $ledger], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create ledger'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $ledger = Ledger::find($id);
            if (!$ledger) {
                return response()->json(['error' => 'Ledger not found'], 404);
            }
            $ledger->update($request->all());
            return response()->json(['data' => $ledger], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update ledger'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $ledger = Ledger::find($id);
            if (!$ledger) {
                return response()->json(['error' => 'Ledger not found'], 404);
            }
            $ledger->delete();
            return response()->json(['message' => 'Ledger deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete ledger'], 500);
        }
    }

    public function getJournal(Request $request)
    {
        try {
            $journal = Ledger::with(['unit', 'product', 'salesEndpoint'])
                ->whereBetween('date', [$request->start_date, $request->end_date])
                ->get();
            return response()->json(['data' => $journal], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate journal'], 500);
        }
    }

    public function getUnitJournal($unit_id)
    {
        try {
            $unitJournal = Ledger::with(['unit', 'product', 'salesEndpoint'])
                ->where('unit_id', $unit_id)
                ->get();
            return response()->json(['data' => $unitJournal], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve unit journal'], 500);
        }
    }

    public function getTransportJournal($transport_id)
    {
        try {
            $transportJournal = Ledger::with(['unit', 'product', 'salesEndpoint'])
                ->where('transport_id', $transport_id)
                ->get();
            return response()->json(['data' => $transportJournal], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve transport journal'], 500);
        }
    }
}
