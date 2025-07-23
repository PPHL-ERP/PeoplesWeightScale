<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransportOrder;
use Illuminate\Http\Request;

class TransportOrderController extends Controller
{
    public function index()
    {
        try {
            $transportOrders = TransportOrder::all();
            return response()->json(['data' => $transportOrders], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve transport orders'], 500);
        }
    }

    public function show($id)
    {
        try {
            $transportOrder = TransportOrder::find($id);
            if (!$transportOrder) {
                return response()->json(['error' => 'Transport Order not found'], 404);
            }
            return response()->json(['data' => $transportOrder], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve transport order'], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'origin_sales_endpoint_id' => 'required|exists:sales_endpoints,id',
            'destination_sales_endpoint_id' => 'required|exists:sales_endpoints,id',
            'productId' => 'required|exists:products,id',
            'companyId' => 'nullable|exists:companies,id',
            'quantity' => 'required|integer|min:1',
            'status' => 'required|string|in:Pending,In Transit,Delivered,Cancelled',
            'dispatch_date' => 'nullable|date',
            'expected_delivery_date' => 'nullable|date',
            'remarks' => 'nullable|string',
            'crBy' => 'nullable|exists:users,id',
            'appBy' => 'nullable|exists:users,id',
        ]);

        try {
            $transportOrder = TransportOrder::create($request->all());

            // Lock Inventory at Origin
            $inventory = Inventory::where('sales_endpoint_id', $transportOrder->origin_sales_endpoint_id)
                ->where('productId', $transportOrder->productId)
                ->first();

            if ($inventory && $inventory->quantity >= $transportOrder->quantity) {
                $inventory->quantity -= $transportOrder->quantity;
                $inventory->save();
            } else {
                return response()->json(['error' => 'Insufficient inventory at origin'], 400);
            }

            // You may need to record the locked quantity elsewhere if necessary

            return response()->json(['data' => $transportOrder], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create transport order'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:Pending,In Transit,Delivered,Cancelled',
            'dispatch_date' => 'nullable|date',
            'expected_delivery_date' => 'nullable|date',
            'actual_delivery_date' => 'nullable|date',
            'remarks' => 'nullable|string',
            'appBy' => 'nullable|exists:users,id',
        ]);

        try {
            $transportOrder = TransportOrder::find($id);
            if (!$transportOrder) {
                return response()->json(['error' => 'Transport Order not found'], 404);
            }

            $transportOrder->update($request->all());
            return response()->json(['data' => $transportOrder], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update transport order'], 500);
        }
    }

    public function confirmDelivery($id)
    {
        try {
            $transportOrder = TransportOrder::find($id);
            if (!$transportOrder) {
                return response()->json(['error' => 'Transport Order not found'], 404);
            }

            if ($transportOrder->status !== 'In Transit') {
                return response()->json(['error' => 'Transport Order is not in transit'], 400);
            }

            $transportOrder->status = 'Delivered';
            $transportOrder->actual_delivery_date = now();
            $transportOrder->save();

            // Update Inventory at Destination
            $inventory = Inventory::where('sales_endpoint_id', $transportOrder->destination_sales_endpoint_id)
                ->where('productId', $transportOrder->productId)
                ->first();

            if ($inventory) {
                $inventory->quantity += $transportOrder->quantity;
                $inventory->save();
            } else {
                Inventory::create([
                    'unitId' => null, // Adjust if necessary
                    'productId' => $transportOrder->productId,
                    'companyId' => $transportOrder->companyId,
                    'sales_endpoint_id' => $transportOrder->destination_sales_endpoint_id,
                    'quantity' => $transportOrder->quantity,
                    'date' => now(),
                    'crBy' => $transportOrder->crBy,
                    'appBy' => $transportOrder->appBy,
                ]);
            }

            return response()->json(['message' => 'Delivery confirmed successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to confirm delivery'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $transportOrder = TransportOrder::find($id);
            if (!$transportOrder) {
                return response()->json(['error' => 'Transport Order not found'], 404);
            }

            // If order was not delivered, unlock inventory
            if ($transportOrder->status !== 'Delivered') {
                $inventory = Inventory::where('sales_endpoint_id', $transportOrder->origin_sales_endpoint_id)
                    ->where('productId', $transportOrder->productId)
                    ->first();

                if ($inventory) {
                    $inventory->quantity += $transportOrder->quantity;
                    $inventory->save();
                }
            }

            $transportOrder->delete();
            return response()->json(['message' => 'Transport Order deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete transport order'], 500);
        }
    }
}
