<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;

class StockAdjustmentController extends Controller
{
    public function index()
    {
        try {
            $stockAdjustments = StockAdjustment::with(['unit', 'product', 'salesEndpoint'])->get();
            return response()->json(['data' => $stockAdjustments], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve stock adjustments'], 500);
        }
    }

    public function show($id)
    {
        try {
            $stockAdjustment = StockAdjustment::with(['unit', 'product', 'salesEndpoint'])->find($id);
            if (!$stockAdjustment) {
                return response()->json(['error' => 'Stock Adjustment not found'], 404);
            }
            return response()->json(['data' => $stockAdjustment], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve stock adjustment'], 500);
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'product_id' => 'required|exists:products,id',
            'adjustment_quantity' => 'required|integer',
            'reason' => 'nullable|string',
        ]);

        try {
            $stockAdjustment = StockAdjustment::create($validatedData);
            return response()->json(['data' => $stockAdjustment], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create stock adjustment'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'product_id' => 'required|exists:products,id',
            'adjustment_quantity' => 'required|integer',
            'reason' => 'nullable|string',
        ]);

        try {
            $stockAdjustment = StockAdjustment::find($id);
            if (!$stockAdjustment) {
                return response()->json(['error' => 'Stock Adjustment not found'], 404);
            }
            $stockAdjustment->update($validatedData);
            return response()->json(['data' => $stockAdjustment], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update stock adjustment'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $stockAdjustment = StockAdjustment::find($id);
            if (!$stockAdjustment) {
                return response()->json(['error' => 'Stock Adjustment not found'], 404);
            }
            $stockAdjustment->delete();
            return response()->json(['message' => 'Stock Adjustment deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete stock adjustment'], 500);
        }
    }

    public function getStockAdjustmentsByInventory($inventory_id)
    {
        try {
            $stockAdjustments = StockAdjustment::with(['unit', 'product', 'salesEndpoint'])
                ->where('inventory_id', $inventory_id)
                ->get();
            return response()->json(['data' => $stockAdjustments], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve stock adjustments by inventory'], 500);
        }
    }
}
