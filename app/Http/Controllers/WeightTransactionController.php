<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\WeightTransaction;
use App\Traits\SectorFilter;
use App\Models\UserManagesSectors;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class WeightTransactionController extends Controller
{
    use SectorFilter;

private function currentUserId(): ?int
{
    if (auth()->check() && auth()->id()) {
        return (int) auth()->id();
    }

    foreach (['user_id', 'userId', 'auth_user_id', 'jwt_user_id', 'login_user_id'] as $k) {
        $v = session($k);
        if (is_numeric($v)) return (int) $v;
    }

    $id = data_get(session('user'), 'id'); // array/object safe
    return is_numeric($id) ? (int) $id : null;
}


public function filterOptions(Request $request)
{
    try {
        $userId = $this->currentUserId();
        if (!$userId) {
            return response()->json([
                'customers' => [],
                'materials' => [],
                'sectors'   => [],
                'users'     => [],
                'message'   => 'User session not found',
            ], 200);
        }

        $isAdmin = $this->isAdminUser($userId);
        $allowedSectorIds = $this->allowedSectorIds($userId);

        // customers
        $customers = DB::table('w_customer')
            ->whereNull('deleted_at')
            ->whereRaw('TRIM(COALESCE("cName", \'\')) <> \'\'')
            ->selectRaw('MIN(id) as id, TRIM("cName") as text')
            ->groupBy(DB::raw('TRIM("cName")'))
            ->orderBy('text', 'asc') // <-- FIX
            ->get();

        // materials
        $materials = DB::table('w_material')
            ->whereNull('deleted_at')
            ->whereRaw('TRIM(COALESCE("mName", \'\')) <> \'\'')
            ->selectRaw('MIN(id) as id, TRIM("mName") as text')
            ->groupBy(DB::raw('TRIM("mName")'))
            ->orderBy('text', 'asc') // <-- FIX
            ->get();

        // sectors
        $sectorsQ = DB::table('sectors')
            ->whereNull('deleted_at')
            ->whereRaw("TRIM(COALESCE(name, '')) <> ''")
            // `sectorCode` is the HRM-assigned code; it stays null for local-only rows.
            ->selectRaw('id, TRIM(name) as text, "sectorCode" as code');

        if (!$isAdmin) {
            if (empty($allowedSectorIds)) {
                $sectorsQ->whereRaw('1=0');
            } else {
                $sectorsQ->whereIn('id', $allowedSectorIds);
            }
        }

        $sectors = $sectorsQ
            ->orderBy('text', 'asc') // <-- FIX
            ->get();

        // users
        $users = DB::table('users')
            ->whereNull('deleted_at')
            ->whereRaw("TRIM(COALESCE(name, '')) <> ''")
            ->selectRaw("
                id,
                CASE
                    WHEN TRIM(COALESCE(username, '')) <> ''
                        THEN TRIM(name) || ' (' || TRIM(username) || ')'
                    ELSE TRIM(name)
                END as text
            ")
            ->orderBy('text', 'asc') // <-- FIX
            ->get();

        return response()->json([
            'customers' => $customers,
            'materials' => $materials,
            'sectors'   => $sectors,
            'users'     => $users,
        ]);
    } catch (\Throwable $e) {
        Log::error('weight_transactions.filterOptions failed', [
            'message' => $e->getMessage(),
        ]);

        return response()->json([
            'customers' => [],
            'materials' => [],
            'sectors'   => [],
            'users'     => [],
            'message'   => 'Filter options error',
            'error'     => $e->getMessage(),
        ], 500);
    }
}


public function datatable(Request $request)
{
    $q = WeightTransaction::query()
        // Read-only join for the HRM sector code. Soft-deleted sectors are kept in
        // the join so historical rows still render their code.
        ->leftJoin('sectors', 'sectors.id', '=', 'weight_transactions.sector_id')
        ->with([
            'imagesById:id,weighing_id,image_path,storage_backend,mode,captured_at,sector_id',
            'imagesByTxn:id,transaction_id,image_path,storage_backend,mode,captured_at,sector_id',
        ])
        ->select([
            'weight_transactions.id','weight_transactions.transaction_id',
            'weight_transactions.weight_type','weight_transactions.transfer_type','weight_transactions.select_mode',
            'weight_transactions.vehicle_type','weight_transactions.vehicle_no',
            'weight_transactions.material','weight_transactions.productType',
            'weight_transactions.gross_weight','weight_transactions.tare_weight','weight_transactions.real_net',
            'weight_transactions.deduction',
            'weight_transactions.volume','weight_transactions.price','weight_transactions.discount','weight_transactions.amount',
            'weight_transactions.customer_name',
            'weight_transactions.sale_id','weight_transactions.purchase_id',
            'weight_transactions.sector_id','weight_transactions.sector_name','weight_transactions.username',
            'weight_transactions.status','weight_transactions.created_at',
            DB::raw('sectors."sectorCode" as sector_code'),
        ]);

    // ---------- USER SECTOR SCOPE (auth/session safe) ----------
    $currentUserId = $this->currentUserId();
    $isAdmin = $this->isAdminUser($currentUserId);
    $allowedSectorIds = $this->allowedSectorIds($currentUserId);

    $requestedSectorId = (int) $request->get('sector_id');

    if ($isAdmin) {
        if ($requestedSectorId > 0) {
            $q->where('weight_transactions.sector_id', $requestedSectorId);
        }
    } else {
        if (empty($allowedSectorIds)) {
            $q->whereRaw('1=0');
        } else {
            if ($requestedSectorId > 0) {
                if (in_array($requestedSectorId, $allowedSectorIds, true)) {
                    $q->where('weight_transactions.sector_id', $requestedSectorId);
                } else {
                    $q->whereRaw('1=0');
                }
            } else {
                $q->whereIn('weight_transactions.sector_id', $allowedSectorIds);
            }
        }
    }

    // ---------- GLOBAL SEARCH ----------
    if ($s = trim((string)$request->get('search_text'))) {
        $q->where(function($x) use ($s){
            $x->where('transaction_id','ilike',"%{$s}%")
              ->orWhere('vehicle_no','ilike',"%{$s}%")
              ->orWhere('customer_name','ilike',"%{$s}%")
              ->orWhere('material','ilike',"%{$s}%")
              ->orWhere('sector_name','ilike',"%{$s}%")
              ->orWhere('username','ilike',"%{$s}%")
              // HRM sector code (matched as text so "12" finds 12)
              ->orWhereRaw('CAST(sectors."sectorCode" AS TEXT) ILIKE ?', ["%{$s}%"]);
        });
    }

    // ---------- CUSTOMER FILTER ----------
    if ($customerId = (int) $request->get('customer_id')) {
        $cName = DB::table('w_customer')->where('id', $customerId)->value('cName');
        $cName = trim((string)$cName);
        if ($cName !== '') {
            $q->whereRaw('LOWER(TRIM(customer_name)) = LOWER(?)', [$cName]);
        } else {
            $q->whereRaw('1=0');
        }
    }

    // ---------- MATERIAL FILTER ----------
    if ($materialId = (int) $request->get('material_id')) {
        $mName = DB::table('w_material')->where('id', $materialId)->value('mName');
        $mName = trim((string)$mName);
        if ($mName !== '') {
            $q->whereRaw('LOWER(TRIM(material)) = LOWER(?)', [$mName]);
        } else {
            $q->whereRaw('1=0');
        }
    }

    // ---------- USER FILTER ----------
    if ($userId = (int) $request->get('user_id')) {
        $u = DB::table('users')->select('name', 'username')->where('id', $userId)->first();

        if ($u) {
            $candidates = array_values(array_unique(array_filter([
                trim((string)($u->name ?? '')),
                trim((string)($u->username ?? '')),
            ])));

            if (!empty($candidates)) {
                $q->where(function ($qq) use ($candidates) {
                    foreach ($candidates as $cand) {
                        $qq->orWhereRaw('LOWER(TRIM(username)) = LOWER(?)', [$cand]);
                    }
                });
            } else {
                $q->whereRaw('1=0');
            }
        } else {
            $q->whereRaw('1=0');
        }
    }

    // ---------- Other filters ----------
    if ($from = $request->get('from_date')) {
        $to = $request->get('to_date') ?: $from;
        $q->whereBetween('weight_transactions.created_at', [$from.' 00:00:00', $to.' 23:59:59']);
    }

    if ($wt = $request->get('weight_type'))   $q->where('weight_type', $wt);
    if ($tt = $request->get('transfer_type')) $q->where('transfer_type', $tt);
    if ($st = $request->get('status'))        $q->where('weight_transactions.status', $st);

    if ($vno = trim((string)$request->get('vehicle_no'))) {
        $q->where('vehicle_no','ilike',"%{$vno}%");
    }

    $rows = $q->orderByDesc('weight_transactions.id')->limit(1000)->get();

    $data = $rows->map(function ($t) {
        $imgs = $t->imagesById->isNotEmpty() ? $t->imagesById : $t->imagesByTxn;

        $photos = $imgs->map(fn($img) => [
            'url'  => $img->url,
            'mode' => $img->mode,
            'at'   => optional($img->captured_at)->toIso8601String(),
        ])->filter(fn($p)=>!empty($p['url']))->values()->all();

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
            'deduction'      => $t->deduction,
            'volume'         => $t->volume,
            'price'          => $t->price,
            'discount'       => $t->discount,
            'amount'         => $t->amount,
            'customer_name'  => $t->customer_name,
            'sale_id'        => $t->sale_id,
            'purchase_id'    => $t->purchase_id,
            'sector_id'      => $t->sector_id,
            'sector_name'    => $t->sector_name,
            // HRM-assigned sector code (null until the sector has been synced).
            'sector_code'    => $t->sector_code !== null ? (int) $t->sector_code : null,
            'username'       => $t->username,
            'status'         => $t->status,
            'created_at'     => $t->created_at,
            'photos'         => $photos,
            'photo_urls'     => array_column($photos,'url'),
            'thumb'          => $photos[0]['url'] ?? null,
        ];
    });

    return response()->json(['data' => $data]);
}

    private function toBool($value): bool
    {
        if (is_bool($value)) return $value;
        $v = strtolower(trim((string) $value));
        return in_array($v, ['1','true','yes','y','on'], true);
    }

    private function isAdminUser(?int $userId): bool
    {
        if (!$userId) return false;
        $u = User::find($userId);
        if (!$u) return false;

        return $this->toBool($u->isAdmin ?? null) || $this->toBool($u->isSuperAdmin ?? null);
    }

    private function allowedSectorIds(?int $userId): array
    {
        if (!$userId) return [];

        return UserManagesSectors::query()
            ->where('userId', $userId)
            ->pluck('sectorId')
            ->map(fn($x) => (int)$x)
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Sector master rows the current user may pick from (same ACL as filterOptions).
     *
     * @return array<int, array{id:int,name:string,sectorCode:int|null}>
     */
    private function sectorOptions(): array
    {
        $userId = $this->currentUserId();
        $isAdmin = $this->isAdminUser($userId);
        $allowedSectorIds = $this->allowedSectorIds($userId);

        $query = DB::table('sectors')
            ->whereNull('deleted_at')
            ->whereRaw("TRIM(COALESCE(name, '')) <> ''")
            ->select('id', 'name', 'sectorCode');

        if (!$isAdmin) {
            if (empty($allowedSectorIds)) {
                $query->whereRaw('1=0');
            } else {
                $query->whereIn('id', $allowedSectorIds);
            }
        }

        return $query
            ->orderBy('name')
            ->get()
            ->map(fn($row) => [
                'id' => (int) $row->id,
                'name' => trim((string) $row->name),
                'sectorCode' => $row->sectorCode !== null ? (int) $row->sectorCode : null,
            ])
            ->all();
    }

    /**
     * Resolve the sector name from `sector_id` so the form never posts a free-text name.
     *
     * The `sector_id` + `sector_name` payload contract is unchanged; when an id is
     * supplied the server-side name wins, otherwise a posted name is kept as-is.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function withDerivedSectorName(array $data): array
    {
        $hasSectorId = array_key_exists('sector_id', $data);
        $sectorId = $hasSectorId && is_numeric($data['sector_id']) ? (int) $data['sector_id'] : 0;

        if ($sectorId <= 0) {
            if (array_key_exists('sector_name', $data)) {
                // An explicitly posted name keeps the old payload contract working.
                $name = trim((string) $data['sector_name']);
                $data['sector_name'] = $name !== '' ? $name : null;
            } elseif ($hasSectorId) {
                // Sector cleared in the form and no name supplied.
                $data['sector_name'] = null;
            }

            return $data;
        }

        $name = DB::table('sectors')
            ->where('id', $sectorId)
            ->whereNull('deleted_at')
            ->value('name');

        $name = trim((string) $name);

        if ($name !== '') {
            $data['sector_name'] = $name;
        }

        return $data;
    }


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

        $rows = $q->orderByDesc('created_at')->limit(200)->get(); // cap for speed
        return response()->json(['data' => $rows]);
    }



    public function create()
    {
        $statuses = ['Unfinished', 'Finished', 'Reject'];
        $sectors = $this->sectorOptions();
        return view('weight_transactions.create', compact('statuses', 'sectors'));
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
        $transaction = WeightTransaction::with('sector:id,name,sectorCode')->findOrFail($id);
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
    $transaction = WeightTransaction::with('sector:id,name,sectorCode')->findOrFail($id);
    $statuses = ['Unfinished','Finished','Reject'];
    $sectors = $this->sectorOptions();

    // previous URL সেশন-এ রাখা (edit পেজ না হলে)
    $prev = url()->previous();
    if (!str_contains($prev, '/weight_transactions/'.$id.'/edit')) {
        session(['wt_return' => $prev]);
    }

    return view('weight_transactions.edit', compact('transaction','statuses','sectors'));
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
        $data = $request->validate([
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

        // The sector form only posts `sector_id`; the stored name is derived here.
        return $this->withDerivedSectorName($data);
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
