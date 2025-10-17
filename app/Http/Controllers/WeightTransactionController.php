<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\WeightTransaction;

class WeightTransactionController extends Controller
{
    public function indexold(Request $request)
    {
        $q = WeightTransaction::query();

        // simple search
        if ($s = $request->get('s')) {
            $q->where(function ($sub) use ($s) {
                $sub->where('transaction_id', 'like', "%$s%")
                    ->orWhere('vehicle_no', 'like', "%$s%")
                    ->orWhere('customer_name', 'like', "%$s%");
            });
        }

        // status filter
        if ($st = $request->get('status')) {
            $q->where('status', $st);
        }

        $transactions = $q->latest()->paginate(10);
        $transactions->appends($request->query()); // preserve filters

        return view('weight_transactions.index', compact('transactions'));
    }

     public function index()
    {
        return view('weight_transactions.index'); // the Blade below
    }

    public function datatableold(Request $request)
    {
        $q = WeightTransaction::query()->select([
            'id',
            'transaction_id',
            'weight_type','transfer_type','select_mode',
            'vehicle_type','vehicle_no',
            'material','productType',
            'gross_weight','tare_weight','real_net',
            'volume','price','discount','amount',
            'customer_name',
            'sale_id','purchase_id',
            'sector_name','username',
            'status','created_at',
        ]);

        // ---- 6 filters ----
        // 1) global text
        if ($s = trim((string)$request->get('search_text'))) {
            $q->where(function($x) use ($s){
                $x->where('transaction_id','like',"%$s%")
                  ->orWhere('vehicle_no','like',"%$s%")
                  ->orWhere('customer_name','like',"%$s%")
                //   ->orWhere('vendor_name','like',"%$s%")
                  ->orWhere('material','like',"%$s%")
                  ->orWhere('sector_name','like',"%$s%")
                  ->orWhere('username','like',"%$s%");
            });
        }

        // 2) date range
        if ($from = $request->get('from_date')) {
            $to = $request->get('to_date') ?: $from;
            $q->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59']);
        }

        // 3) weight type
        if ($wt = $request->get('weight_type')) $q->where('weight_type', $wt);

        // 4) transfer type
        if ($tt = $request->get('transfer_type')) $q->where('transfer_type', $tt);

        // 5) status
        if ($st = $request->get('status')) $q->where('status', $st);

        // 6) vehicle no
        if ($vno = trim((string)$request->get('vehicle_no'))) {
            $q->where('vehicle_no','like',"%$vno%");
        }

        $rows = $q->orderByDesc('created_at')->limit(2000)->get(); // cap for speed
        return response()->json(['data' => $rows]);
    }
    public function datatable(Request $request)
    {
        $q = WeightTransaction::query()
            ->with([
                'imagesById:id,weighing_id,image_path,storage_backend,mode,captured_at,sector_id',
                'imagesByTxn:id,transaction_id,image_path,storage_backend,mode,captured_at,sector_id',
            ])
            ->select([
                'id','transaction_id',
                'weight_type','transfer_type','select_mode',
                'vehicle_type','vehicle_no',
                'material','productType',
                'gross_weight','tare_weight','real_net',
                'deduction',
                'customer_name',
                'sale_id','purchase_id',
                'sector_id','sector_name','username',
                'status','created_at',
            ]);

        // ---- your existing filters here (unchanged) ----
        if ($s = trim((string)$request->get('search_text'))) {
            $q->where(function($x) use ($s){
                $x->where('transaction_id','like',"%$s%")
                ->orWhere('vehicle_no','like',"%$s%")
                ->orWhere('customer_name','like',"%$s%")
                ->orWhere('material','like',"%$s%")
                ->orWhere('sector_name','like',"%$s%")
                ->orWhere('username','like',"%$s%");
            });
        }
        if ($from = $request->get('from_date')) {
            $to = $request->get('to_date') ?: $from;
            $q->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59']);
        }
        if ($wt = $request->get('weight_type'))   $q->where('weight_type', $wt);
        if ($tt = $request->get('transfer_type')) $q->where('transfer_type', $tt);
        if ($st = $request->get('status'))        $q->where('status', $st);
        if ($vno = trim((string)$request->get('vehicle_no'))) {
            $q->where('vehicle_no','like',"%$vno%");
        }

        $rows = $q->orderByDesc('id')->limit(200)->get();

        // Transform to plain arrays + photos & thumb
        $data = $rows->map(function ($t) {
            $imgs = $t->imagesById->isNotEmpty() ? $t->imagesById : $t->imagesByTxn;

            $photos = $imgs->map(fn($img) => [
                'url'  => $img->url,
                'mode' => $img->mode,
                'at'   => optional($img->captured_at)->toIso8601String(),
            ])->filter(fn($p)=>!empty($p['url']))->values()->all();

            $thumb = $photos[0]['url'] ?? null;

            return [
                'id'             => $t->id,
                'transaction_id' => $t->transaction_id,
                'weight_type'    => $t->weight_type,
                'transfer_type'  => $t->transfer_type,
                'select_mode'    => $t->select_mode,
                'vehicle_type'   => $t->vehicle_type,
                'vehicle_no'     => $t->vehicle_no,
                'material'       => $t->material,
                'productType'    => $t->productType,
                'gross_weight'   => $t->gross_weight,
                'tare_weight'    => $t->tare_weight,
                'real_net'       => $t->real_net,
                'volume'         => $t->volume,
                'price'          => $t->price,
                'discount'       => $t->discount,
                'amount'         => $t->amount,
                'customer_name'  => $t->customer_name,
                'sale_id'        => $t->sale_id,
                'purchase_id'    => $t->purchase_id,
                'sector_id'      => $t->sector_id,
                'sector_name'    => $t->sector_name,
                'username'       => $t->username,
                'status'         => $t->status,
                'created_at'     => $t->created_at,

                // images payload for table
                'photos'     => $photos,               // [{url, mode, at}, ...]
                'photo_urls' => array_column($photos,'url'), // [string,...] if needed
                'thumb'      => $thumb,                // first image (for inline thumb)
            ];
        });

        return response()->json(['data' => $data]);
    }


    public function create()
    {
        $statuses = ['Unfinished', 'Finished', 'Reject'];
        return view('weight_transactions.create', compact('statuses'));
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);

        // derives
        $data['amount']   = $this->calcAmount($data['volume'] ?? null, $data['price'] ?? null);
        $data['real_net'] = $this->calcRealNet($data['gross_weight'] ?? null, $data['tare_weight'] ?? null, $data['discount'] ?? null);

        $t = WeightTransaction::create($data);

        return redirect()->route('weight_transactions.show', $t->id)
            ->with('success', 'Transaction created.');
    }

    public function show($id)
    {
        $transaction = WeightTransaction::findOrFail($id);
        return view('weight_transactions.show', compact('transaction'));
    }

    // public function edit($id)
    // {
    //     $transaction = WeightTransaction::findOrFail($id);
    //     $statuses = ['Unfinished', 'Finished', 'Reject'];
    //     return view('weight_transactions.edit', compact('transaction', 'statuses'));
    // }
    public function edit($id)
{
    $transaction = WeightTransaction::findOrFail($id);
    $statuses = ['Unfinished','Finished','Reject'];

    // previous URL সেশন-এ রাখা (edit পেজ না হলে)
    $prev = url()->previous();
    if (!str_contains($prev, '/weight_transactions/'.$id.'/edit')) {
        session(['wt_return' => $prev]);
    }

    return view('weight_transactions.edit', compact('transaction','statuses'));
}

    public function update(Request $request, $id)
    {
        $t = WeightTransaction::findOrFail($id);
        $data = $this->validatePayload($request);

        // derive with fallback to existing
        $volume = $data['volume'] ?? $t->volume;
        $price  = $data['price']  ?? $t->price;
        $gross  = $data['gross_weight'] ?? $t->gross_weight;
        $tare   = $data['tare_weight']  ?? $t->tare_weight;
        $disc   = $data['discount']     ?? $t->discount;

        $data['amount']   = $this->calcAmount($volume, $price);
        $data['real_net'] = $this->calcRealNet($gross, $tare, $disc);

        $t->update($data);

        return redirect()->route('weight_transactions.show', $t->id)
            ->with('success', 'Transaction updated.');
    }

    public function destroy($id)
    {
        WeightTransaction::findOrFail($id)->delete();
        return redirect()->route('weight_transactions.index')
            ->with('success', 'Transaction deleted.');
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'transaction_id' => ['nullable','string','max:100'],
            'weight_type'    => ['nullable','string','max:50'],
            'transfer_type'  => ['nullable','string','max:50'],
            'select_mode'    => ['nullable','string','max:50'],

            'vehicle_type'   => ['nullable','string','max:50'],
            'vehicle_no'     => ['nullable','string','max:50'],
            'material'       => ['nullable','string','max:100'],
            'productType'    => ['nullable','string','max:100'],

            'gross_weight'   => ['nullable','numeric'],
            'gross_time'     => ['nullable','date'],
            'gross_operator' => ['nullable','string','max:100'],

            'tare_weight'    => ['nullable','numeric'],
            'tare_time'      => ['nullable','date'],
            'tare_operator'  => ['nullable','string','max:100'],

            'volume'         => ['nullable','numeric'],
            'price'          => ['nullable','numeric'],
            'amount'         => ['nullable','numeric'],
            'discount'       => ['nullable','numeric'],
            'real_net'       => ['nullable','numeric'],

            'customer_id'    => ['nullable','integer'],
            'vendor_id'      => ['nullable','integer'],
            'customer_name'  => ['nullable','string','max:150'],
            'sale_id'        => ['nullable','string','max:100'],
            'purchase_id'    => ['nullable','string','max:100'],

            'sector_id'      => ['nullable','integer'],
            'sector_name'    => ['nullable','string','max:150'],

            'note'           => ['nullable','string'],
            'others'         => ['nullable','string'],
            'username'       => ['nullable','string','max:100'],
            'status'         => ['nullable', Rule::in(['Unfinished','Finished','Reject'])],
        ]);
    }

    private function calcAmount($volume, $price): ?float
    {
        if ($volume === null || $price === null) return null;
        return round(((float)$volume) * ((float)$price), 2);
    }

    private function calcRealNet($gross, $tare, $disc): ?float
    {
        if ($gross === null || $tare === null) return null;
        $d = $disc ?? 0;
        return round(((float)$gross - (float)$tare) - (float)$d, 2);
    }

//     public function showTable()
// {
//     return redirect()->route('dashboard-table');
// }
}
