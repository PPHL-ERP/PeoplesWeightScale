<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InventoryRequest;
use App\Models\Inventory;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index()
    {
        try {
            $inventories = Inventory::all();
            return response()->json(['data' => $inventories], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve inventories'], 500);
        }
    }

    public function show($id)
    {
        try {
            $inventory = Inventory::find($id);
            if (!$inventory) {
                return response()->json(['error' => 'Inventory not found'], 404);
            }
            return response()->json(['data' => $inventory], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve inventory'], 500);
        }
    }

    public function store(InventoryRequest $request)
    {
        try {
            $inventory = Inventory::create($request->validated());
            return response()->json(['data' => $inventory], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create inventory'], 500);
        }
    }

    public function update(InventoryRequest $request, $id)
    {
        try {
            $inventory = Inventory::find($id);
            if (!$inventory) {
                return response()->json(['error' => 'Inventory not found'], 404);
            }
            $inventory->update($request->validated());
            return response()->json(['data' => $inventory], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update inventory'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $inventory = Inventory::find($id);
            if (!$inventory) {
                return response()->json(['error' => 'Inventory not found'], 404);
            }
            $inventory->delete();
            return response()->json(['message' => 'Inventory deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete inventory'], 500);
        }
    }

    public function getCurrentStock()
    {
        try {
            $currentStock = Inventory::selectRaw('productId, SUM(quantity) as total_quantity')
                                    ->groupBy('productId')
                                    ->get();
            return response()->json(['data' => $currentStock], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve current stock'], 500);
        }
    }

    public function getCategoryWiseStock()
    {
        try {
            $categoryWiseStock = Inventory::selectRaw('categories.name as category_name, SUM(inventories.quantity) as total_quantity')
                                        ->join('products', 'inventories.productId', '=', 'products.id')
                                        ->join('categories', 'products.categoryId', '=', 'categories.id')
                                        ->groupBy('categories.name')
                                        ->get();
            return response()->json(['data' => $categoryWiseStock], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve category-wise stock'], 500);
        }
    }

    public function trackSupplyByUnit($unit_id)
    {
        try {
            $supplyTrack = Inventory::where('unitId', $unit_id)->get();
            return response()->json(['data' => $supplyTrack], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to track supply'], 500);
        }
    }
}
