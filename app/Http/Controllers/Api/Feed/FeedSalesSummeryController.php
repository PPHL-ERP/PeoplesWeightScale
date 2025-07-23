<?php

namespace App\Http\Controllers\Api\Feed;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ViewSalesSummery;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class FeedSalesSummeryController extends Controller
{

    //with date range feedSaleTotal
    public function getFeedSalesSummary(Request $request)
{
    $mode = $request->input('mode', 'today');
    $date = $request->input('date');

    $parsedDate = $date ? Carbon::parse($date) : Carbon::today();

    $query = DB::table('view_feed_order_summary')
        ->whereNull('order_deleted_at')
        ->where('order_status', 'approved');

    $startDate = $parsedDate->toDateString();
    $endDate = $parsedDate->toDateString();

    if ($mode === 'today') {
        $query->whereRaw('DATE("invoiceDate") = ?', [$parsedDate->toDateString()]);
    } elseif ($mode === 'weekly') {
        $startDate = $parsedDate->copy()->startOfWeek()->toDateString();
        $endDate = $parsedDate->copy()->endOfWeek()->toDateString();
        $query->whereRaw('DATE("invoiceDate") BETWEEN ? AND ?', [$startDate, $endDate]);
    } elseif ($mode === 'monthly') {
        $startDate = $parsedDate->copy()->startOfMonth()->toDateString();
        $endDate = $parsedDate->copy()->endOfMonth()->toDateString();
        $query->whereRaw('DATE("invoiceDate") BETWEEN ? AND ?', [$startDate, $endDate]);
    }

    $total = $query
        ->select('order_feedid', DB::raw('MAX("totalAmount") as "totalAmount"'))
        ->groupBy('order_feedid')
        ->get()
        ->sum('totalAmount');

    return response()->json([
        'data' => [
            'total' => (string) $total,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]
    ]);
}

    //with date range eggSaleTotal
    public function getEggSalesSummary(Request $request)
{
    $mode = $request->input('mode', 'today');
    $date = $request->input('date');

    $parsedDate = $date ? Carbon::parse($date) : Carbon::today();

    $query = DB::table('view_egg_order_summary')
        ->whereNull('order_deleted_at')
        ->where('order_status', 'approved');

    $startDate = $parsedDate->toDateString();
    $endDate = $parsedDate->toDateString();

    if ($mode === 'today') {
        $query->whereRaw('DATE("invoiceDate") = ?', [$parsedDate->toDateString()]);
    } elseif ($mode === 'weekly') {
        $startDate = $parsedDate->copy()->startOfWeek()->toDateString();
        $endDate = $parsedDate->copy()->endOfWeek()->toDateString();
        $query->whereRaw('DATE("invoiceDate") BETWEEN ? AND ?', [$startDate, $endDate]);
    } elseif ($mode === 'monthly') {
        $startDate = $parsedDate->copy()->startOfMonth()->toDateString();
        $endDate = $parsedDate->copy()->endOfMonth()->toDateString();
        $query->whereRaw('DATE("invoiceDate") BETWEEN ? AND ?', [$startDate, $endDate]);
    }

    $total = $query
        ->select('order_saleid', DB::raw('MAX("totalAmount") as "totalAmount"'))
        ->groupBy('order_saleid')
        ->get()
        ->sum('totalAmount');

    return response()->json([
        'data' => [
            'total' => (string) $total,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]
    ]);
}


// sector wise
// public function getSectorWiseSalesTotal(Request $request)
// {
//     $startDate = $request->input('startDate');
//     $endDate = $request->input('endDate');

//     $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::today()->startOfDay();
//     $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::today()->endOfDay();

//     $result = DB::table('view_feed_order_summary')
//     ->select(
//         DB::raw('"salesPointId" as sectorId'),
//         DB::raw('"salespointname" as sectorName'),
//         DB::raw('SUM("totalAmount") as total')
//     )
//     ->whereNull('order_deleted_at')
//     ->where('order_status', 'approved')
//     ->whereBetween('invoiceDate', [$start, $end])
//     ->groupBy(DB::raw('"salesPointId"'), DB::raw('"salespointname"'))
//     ->get();

//     return response()->json([
//         'data' => $result,
//         'startDate' => $start->toDateString(),
//         'endDate' => $end->toDateString(),
//     ]);
// }

//with date filter
public function getSectorWiseSalesTotal(Request $request)
{
    $startDate = $request->input('startDate');
    $endDate = $request->input('endDate');

    $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::today()->startOfDay();
    $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::today()->endOfDay();

    $result = DB::table('view_feed_order_summary')
    ->select(
        DB::raw('"salesPointId" as sectorId'),
        DB::raw('"salespointname" as sectorName'),
        DB::raw('SUM("totalAmount") as total')
    )
    ->whereNull('order_deleted_at')
    ->where('order_status', 'approved')
    ->whereBetween('invoiceDate', [$startDate, $endDate])
    ->groupBy(DB::raw('"salesPointId"'), DB::raw('"salespointname"'))
    ->get();


    return response()->json([
        'data' => $result,
        'startDate' => $start->toDateString(),
        'endDate' => $end->toDateString(),
    ]);
}

//total,paid,due
public function getFeedSaleSummary(Request $request)
{
    $startDate = $request->input('startDate', now()->toDateString());
    $endDate = $request->input('endDate', now()->toDateString());

    $summary = DB::table('view_feed_order_summary')
        ->selectRaw('
            SUM(COALESCE("totalAmount", 0)) AS total_sale,
            SUM(COALESCE("totalAmount", 0)) - SUM(COALESCE("dueAmount", 0)) AS paid_amount,
            SUM(COALESCE("dueAmount", 0)) AS due_amount
        ')
        ->whereNull('order_deleted_at')
        ->where('order_status', 'approved')
        ->whereBetween('invoiceDate', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
        ->first();

    return response()->json([
        'data' => [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalSale' => $summary?->total_sale ?? 0,
            'paidAmount' => $summary?->paid_amount ?? 0,
            'dueAmount' => $summary?->due_amount ?? 0,
        ]
    ]);
}


// FeedSalesReport sector wise
public function getFeedOrderSummary0000(Request $request)
{
    $query = DB::table('view_feed_order_summary')
        ->selectRaw(
            '"salesPointId", "salespointname", DATE("invoiceDate") as invoice_date, ' .
            '"productId", "productname", "dealerId", "dealername", "salesPerson", "sizeOrWeight","salePrice", ' .
            'SUM(qty * "salePrice") as total_sale_amount, SUM(qty) as total_qty'
        )
        ->whereNotNull(DB::raw('"salesPointId"'));

    // Filters
    // ✅ Only products under Feed category
    $query->whereIn('productId', function ($subquery) {
        $subquery->select('id')
            ->from('products')
            ->where('salecategoryname', 'Feed'); // Or ->where('categoryId', 1);
    });
    if ($request->filled('sectorId')) {
        $query->where(DB::raw('"salesPointId"'), $request->sectorId);
    }

    if ($request->filled('productId')) {
        $query->where(DB::raw('"productId"'), $request->productId);
    }

    if ($request->filled('salesPerson')) {
        $query->where(DB::raw('"salesPerson"'), $request->salesPerson);
    }

    if ($request->filled('dealerId')) {
        $query->where(DB::raw('"dealerId"'), $request->dealerId);
    }

    if ($request->filled('startDate') && $request->filled('endDate')) {
        $query->whereBetween(DB::raw('"invoiceDate"'), [$request->startDate, $request->endDate]);
    }

    $data = $query
        ->groupBy(DB::raw(
            '"salesPointId", "salespointname", DATE("invoiceDate"), ' .
            '"productId", "productname", "dealerId", "dealername", "salesPerson","sizeOrWeight","salePrice"'
        ))
        ->orderBy(DB::raw('DATE("invoiceDate")'), 'asc')
        ->paginate(500);

    return response()->json([
        'success' => true,
        'data' => $data
    ]);
}
public function getFeedOrderSummary(Request $request)
{
    $query = DB::table('view_feed_order_summary')
        ->selectRaw(
            '"salesPointId", "salespointname", DATE("invoiceDate") as invoice_date, ' .
            '"productId", "productname", "dealerId", "dealername", "salesPerson", "sizeOrWeight","salePrice", ' .
            'SUM(qty * "salePrice") as total_sale_amount, SUM(qty) as total_qty'
        )
        ->whereNotNull(DB::raw('"salesPointId"'));

    // ✅ Only products under Feed category
    $query->whereIn('productId', function ($subquery) {
        $subquery->select('id')
            ->from('products')
            ->where('salecategoryname', 'Feed'); // or ->where('categoryId', 1);
    });

    // Filters
    if ($request->filled('sectorId')) {
        $query->where(DB::raw('"salesPointId"'), $request->sectorId);
    }
    if ($request->filled('productId')) {
        $query->where(DB::raw('"productId"'), $request->productId);
    }
    if ($request->filled('salesPerson')) {
        $query->where(DB::raw('"salesPerson"'), $request->salesPerson);
    }
    if ($request->filled('dealerId')) {
        $query->where(DB::raw('"dealerId"'), $request->dealerId);
    }
    if ($request->filled('startDate') && $request->filled('endDate')) {
        $query->whereBetween(DB::raw('"invoiceDate"'), [$request->startDate, $request->endDate]);
    }

    $saleData = $query
        ->groupBy(DB::raw(
            '"salesPointId", "salespointname", DATE("invoiceDate"), ' .
            '"productId", "productname", "dealerId", "dealername", "salesPerson","sizeOrWeight","salePrice"'
        ))
        ->orderBy(DB::raw('DATE("invoiceDate")'), 'asc')
        ->paginate(500);

    /**
     * 🟨 Fetch matching feed return data from `view_feed_sale_return`
     */
    $returnQuery = DB::table('view_feed_sale_return')
        ->select([
            'return_id',
            'saleReturnId',
            'saleId',
            'feed_order_code',
            'invoiceDate',
            'returnDate',
            'dealername',
            'productId',
            'productname',
            'sizeOrWeight',
            'qty',
            'rQty',
            'salePrice',
        ])
        ->orderBy('returnDate', 'asc');

    // Same filters for return query
    if ($request->filled('sectorId')) {
        $returnQuery->where(DB::raw('"salesPointId"'), $request->sectorId);
    }
    if ($request->filled('productId')) {
        $returnQuery->where(DB::raw('"productId"'), $request->productId);
    }
    if ($request->filled('salesPerson')) {
        $returnQuery->where(DB::raw('"salesPerson"'), $request->salesPerson);
    }
    if ($request->filled('dealerId')) {
        $returnQuery->where(DB::raw('"dealerId"'), $request->dealerId);
    }
    if ($request->filled('startDate') && $request->filled('endDate')) {
        $returnQuery->whereBetween(DB::raw('"returnDate"'), [$request->startDate, $request->endDate]);
    }

    $returnRows = $returnQuery->get();

    // Add bagQty and rBagQty
    $returnRowsWithBags = $returnRows->map(function ($item) {
        $sw  = floatval($item->sizeOrWeight) ?: 0;
        $qty = floatval($item->qty) ?: 0;
        $rq  = floatval($item->rQty) ?: 0;

        $item->bagQty  = $sw > 0 ? round($qty / $sw, 2) : null;
        $item->rBagQty = $sw > 0 ? round($rq  / $sw, 2) : null;

        return $item;
    });

    $groupedReturns = $returnRowsWithBags->groupBy('return_id');

    return response()->json([
        'success' => true,
        'data' => $saleData,
        'returns' => $groupedReturns
    ]);
}




















}
