<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\WeightTransaction;
use Illuminate\Http\Request;

class WeightTransactionController extends Controller
{

    public function showTable(Request $request)
    {
        $search = $request->input('search');

        $transactions = WeightTransaction::query()
            ->leftJoin('w_customer', 'weight_transactions.customer_id', '=', 'w_customer.id')
            ->leftJoin('w_vendor', 'weight_transactions.vendor_id', '=', 'w_vendor.id')
            ->leftJoin('sectors', 'weight_transactions.sector_id', '=', 'sectors.id')
            ->select(
                'weight_transactions.*',
                'w_customer.cName as customer_name',
                'w_vendor.vName as vendor_name',
                'sectors.name as sector_name'
            )
            ->when($search, function ($query, $search) {
                $query->where('weight_transactions.transaction_id', 'like', "%{$search}%")
                      ->orWhere('weight_transactions.vehicle_no', 'like', "%{$search}%")
                      ->orWhere('w_customer.cName', 'like', "%{$search}%")
                      ->orWhere('w_vendor.vName', 'like', "%{$search}%");
            })
            ->orderByDesc('weight_transactions.id')
            ->paginate(10); // Pagination

        return view('dashboard', compact('transactions', 'search'));
    }

    public function printA4($id)
    {
        $transaction = WeightTransaction::with(['customer', 'vendor', 'sector'])->findOrFail($id);
        return view('print.transaction_a4', compact('transaction'));
    }

    public function printPOS($id)
    {
        $transaction = WeightTransaction::with(['customer', 'vendor', 'sector'])->findOrFail($id);
        return view('print.transaction_pos', compact('transaction'));
    }


    // ✅ List with optional filters
    public function indexold(Request $request)
    {
        $query = WeightTransaction::query();

        if ($request->filled('transaction_id')) {
            $query->where('transaction_id', $request->transaction_id);
        }

        if ($request->filled('vehicle_no')) {
            $query->where('vehicle_no', 'ilike', "%{$request->vehicle_no}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [$request->from_date, $request->to_date]);
        }

        return response()->json($query->latest()->paginate(25));
    }
    public function index(Request $request)
    {
        $query = WeightTransaction::query();

        // Join customer, vendor, and sector tables
        $query->leftJoin('w_customer', 'weight_transactions.customer_id', '=', 'w_customer.id')
              ->leftJoin('w_vendor', 'weight_transactions.vendor_id', '=', 'w_vendor.id')
              ->leftJoin('sectors', 'weight_transactions.sector_id', '=', 'sectors.id')
              ->select(
                  'weight_transactions.*',
                  'w_customer.cName as customer_name',
                  'w_vendor.vName as vendor_name',
                  'sectors.name as sector_name'
              );

        // Filters
        if ($request->filled('transaction_id')) {
            $query->where('weight_transactions.transaction_id', $request->transaction_id);
        }
        if ($request->filled('vehicle_no')) {
            $query->where('weight_transactions.vehicle_no', 'ilike', "%{$request->vehicle_no}%");
        }
        if ($request->filled('status')) {
            $query->where('weight_transactions.status', $request->status);
        }
        if ($request->filled('sector_id')) {
            $query->where('weight_transactions.sector_id', $request->sector_id);
        }
        if ($request->filled('vendor_id')) {
            $query->where('weight_transactions.vendor_id', $request->vendor_id);
        }
        if ($request->filled('customer_id')) {
            $query->where('weight_transactions.customer_id', $request->customer_id);
        }
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('weight_transactions.created_at', [$request->from_date, $request->to_date]);
        }

        return response()->json($query->orderByDesc('weight_transactions.id')->get());
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_id'     => 'nullable|string',
            'weight_type'        => 'nullable|string',
            'transfer_type'      => 'nullable|string',
            'select_mode'        => 'nullable|string',
            'vehicle_type'       => 'nullable|string',
            'vehicle_no'         => 'nullable|string',
            'material'           => 'nullable|string',
            'productType'        => 'nullable|string',
            'gross_weight'       => 'nullable|numeric',
            'gross_time'         => 'nullable|date',
            'gross_operator'     => 'nullable|string',
            'tare_weight'        => 'nullable|numeric',
            'tare_time'          => 'nullable|date',
            'tare_operator'      => 'nullable|string',
            'volume'             => 'nullable|numeric',
            'price'              => 'nullable|numeric',
            'discount'           => 'nullable|numeric',
            'customer_id'        => 'nullable|integer',
            'vendor_id'          => 'nullable|integer',
            'sale_id'            => 'nullable|string',
            'purchase_id'        => 'nullable|string',
            'sector_id'          => 'nullable|integer',
            'note'               => 'nullable|string',
            'others'             => 'nullable|string',
            'username'           => 'nullable|string',
            'status'             => 'nullable|string',
        ]);

        // Calculate amount & real_net
        $gross = $validated['gross_weight'] ?? 0;
        $tare = $validated['tare_weight'] ?? 0;
        $net = $gross - $tare;

        $price = $validated['price'] ?? 0;
        $amount = $net * $price;
        $discount = $validated['discount'] ?? 0;
        $realNet = $amount - $discount;

        $validated['amount'] = $amount;
        $validated['real_net'] = $realNet;

        // Auto fetch names
        if (!empty($validated['customer_id'])) {
            $customer = \DB::table('w_customer')->find($validated['customer_id']);
            $validated['customer_name'] = $customer->cName ?? null;
        }

        if (!empty($validated['vendor_id'])) {
            $vendor = \DB::table('w_vendor')->find($validated['vendor_id']);
            $validated['vendor_name'] = $vendor->vName ?? null;
        }

        if (!empty($validated['sector_id'])) {
            $sector = \DB::table('sectors')->find($validated['sector_id']);
            $validated['sector_name'] = $sector->name ?? null;
        }

        $transaction = WeightTransaction::create($validated);

        return response()->json([
            'message' => 'Transaction created successfully.',
            'data'    => $transaction
        ], 201);
    }


    // ✅ Show one
    public function show($id)
    {
        $transaction = WeightTransaction::findOrFail($id);
        return response()->json($transaction);
    }

    // ✅ Update
    public function update(Request $request, $id)
    {
        $transaction = WeightTransaction::findOrFail($id);

        $validated = $request->validate([
            'transaction_id'     => 'nullable|string',
            'weight_type'        => 'nullable|string',
            'transfer_type'      => 'nullable|string',
            'select_mode'        => 'nullable|string',
            'vehicle_type'       => 'nullable|string',
            'vehicle_no'         => 'nullable|string',
            'material'           => 'nullable|string',
            'productType'        => 'nullable|string',
            'gross_weight'       => 'nullable|numeric',
            'gross_time'         => 'nullable|date',
            'gross_operator'     => 'nullable|string',
            'tare_weight'        => 'nullable|numeric',
            'tare_time'          => 'nullable|date',
            'tare_operator'      => 'nullable|string',
            'volume'             => 'nullable|numeric',
            'price'              => 'nullable|numeric',
            'discount'           => 'nullable|numeric',
            'customer_id'        => 'nullable|integer',
            'vendor_id'          => 'nullable|integer',
            'sale_id'            => 'nullable|string',
            'purchase_id'        => 'nullable|string',
            'sector_id'          => 'nullable|integer',
            'note'               => 'nullable|string',
            'others'             => 'nullable|string',
            'username'           => 'nullable|string',
            'status'             => 'nullable|string',
        ]);

        // Recalculate amount & real_net
        $gross = $validated['gross_weight'] ?? $transaction->gross_weight;
        $tare = $validated['tare_weight'] ?? $transaction->tare_weight;
        $net = $gross - $tare;

        $price = $validated['price'] ?? $transaction->price;
        $amount = $net * $price;
        $discount = $validated['discount'] ?? $transaction->discount;
        $realNet = $amount - $discount;

        $validated['amount'] = $amount;
        $validated['real_net'] = $realNet;

        // Auto fetch names
        if (!empty($validated['customer_id'])) {
            $customer = \DB::table('w_customer')->find($validated['customer_id']);
            $validated['customer_name'] = $customer->cName ?? null;
        }

        if (!empty($validated['vendor_id'])) {
            $vendor = \DB::table('w_vendor')->find($validated['vendor_id']);
            $validated['vendor_name'] = $vendor->vName ?? null;
        }

        if (!empty($validated['sector_id'])) {
            $sector = \DB::table('sectors')->find($validated['sector_id']);
            $validated['sector_name'] = $sector->name ?? null;
        }

        $transaction->update($validated);

        return response()->json([
            'message' => 'Transaction updated successfully.',
            'data'    => $transaction,
        ]);
    }


    // ✅ Delete
    public function destroy($id)
    {
        $transaction = WeightTransaction::findOrFail($id);
        $transaction->delete();

        return response()->json([
            'message' => 'Transaction deleted successfully'
        ]);
    }
}
