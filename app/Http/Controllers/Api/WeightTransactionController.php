<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\WeightTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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


    public function storeold(Request $request)
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

    public function store(Request $request)
        {
            // ---- Correlation ID for tracing this request in logs ----
            $rid = (string) Str::uuid();
            Log::withContext(['rid' => $rid]);

            // ---- Build files meta (name/size/mime) safely ----
            $filesMeta = [];
            try {
                foreach ($request->allFiles() as $key => $fileOrArray) {
                    if (is_array($fileOrArray)) {
                        foreach ($fileOrArray as $file) {
                            $filesMeta[] = [
                                'field' => $key,
                                'name'  => $file->getClientOriginalName(),
                                'size'  => $file->getSize(),
                                'mime'  => $file->getMimeType(),
                            ];
                        }
                    } else {
                        $file = $fileOrArray;
                        $filesMeta[] = [
                            'field' => $key,
                            'name'  => $file->getClientOriginalName(),
                            'size'  => $file->getSize(),
                            'mime'  => $file->getMimeType(),
                        ];
                    }
                }
            } catch (\Throwable $e) {
                $filesMeta = ['error' => $e->getMessage()];
            }

            // ---- Raw body (cap to avoid huge logs) ----
            $rawBody = $request->getContent();
            if (mb_strlen($rawBody) > 20000) {
                $rawBody = mb_substr($rawBody, 0, 20000) . '...[truncated]';
            }

            // ---- Choose a log channel: 'requests' (if defined) else default ----
            $logChannel = config('logging.channels.requests') ? 'requests' : config('logging.default', 'stack');

            // ---- Log everything about the request ----
            Log::channel($logChannel)->info('WeightTransaction.store: incoming request', [
                'url'     => $request->fullUrl(),
                'method'  => $request->method(),
                'ip'      => $request->ip(),
                'user_id' => optional($request->user())->id,
                'headers' => $request->headers->all(),
                'query'   => $request->query(),
                'input'   => $request->except(['password','password_confirmation','token','authorization']),
                'json'    => $request->isJson() ? $request->json()->all() : null,
                'files'   => $filesMeta,
                'raw'     => $rawBody,
            ]);

            // ---- Fast store (no validation, Query Builder insert) ----
            $data = $request->only([
                'transaction_id',
                'weight_type',
                'transfer_type',
                'select_mode',
                'vehicle_type',
                'vehicle_no',
                'material',
                'productType',
                'gross_weight',
                'gross_time',
                'gross_operator',
                'tare_weight',
                'tare_time',
                'tare_operator',
                'volume',
                'price',
                'discount',
                'customer_id',
                'vendor_id',
                'sale_id',
                'purchase_id',
                'sector_id',
                'note',
                'others',
                'username',
                'status',
                'detection', // <-- new column
            ]);

            // Computed fields
            $gross    = (float)($data['gross_weight'] ?? 0);
            $tare     = (float)($data['tare_weight'] ?? 0);
            $net      = $gross - $tare;
            $price    = (float)($data['price'] ?? 0);
            $amount   = $net * $price;
            $discount = (float)($data['discount'] ?? 0);
            $realNet  = $amount - $discount;

            $data['amount']   = $amount;
            $data['real_net'] = $realNet;

            // Lightweight lookups (names)
            if (!empty($data['customer_id'])) {
                $c = DB::table('w_customer')->select('cName')->where('id', $data['customer_id'])->first();
                $data['customer_name'] = $c->cName ?? null;
            }
            if (!empty($data['sector_id'])) {
                $s = DB::table('sectors')->select('name')->where('id', $data['sector_id'])->first();
                $data['sector_name'] = $s->name ?? null;
            }
            // NOTE: vendor_name column does not exist in your migration, so we skip it.

            $now = now();
            $data['created_at'] = $now;
            $data['updated_at'] = $now;

            try {
                $id = DB::table('weight_transactions')->insertGetId($data);
                $transaction = WeightTransaction::find($id);

                Log::channel($logChannel)->info('WeightTransaction.store: created', [
                    'id'              => $id,
                    'transaction_id'  => $transaction->transaction_id ?? null,
                ]);

                return response()->json([
                    'message' => 'Transaction created successfully.',
                    'data'    => $transaction,
                    'rid'     => $rid,
                ], 201);
            } catch (\Throwable $e) {
                Log::channel($logChannel)->error('WeightTransaction.store: insert failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return response()->json([
                    'message' => 'Failed to create transaction.',
                    'error'   => $e->getMessage(),
                    'rid'     => $rid,
                ], 500);
            }
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

        // refresh to load any DB-level changes and ensure transaction_id is present
        $transaction->refresh();

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
