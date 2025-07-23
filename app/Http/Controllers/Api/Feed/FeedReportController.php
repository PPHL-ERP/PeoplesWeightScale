<?php

namespace App\Http\Controllers\Api\Feed;

use Illuminate\Http\Request;
use App\Models\Commission;
use App\Models\DealerTargetAchievementView;
use App\Http\Controllers\Controller;
use App\Models\FeedSalesReturn;
use App\Models\FeedSalesReturnDetails;
use Illuminate\Support\Facades\DB;

class FeedReportController extends Controller
{


public function allDealersTargetVsAchievementOld(Request $request)
{
    $year = $request->input('year', now()->year);
    $month = $request->input('month');
    $dealerIdFilter = $request->input('dealerId');

    $commissions = Commission::with(['commissionProducts.product', 'dealer'])
        ->where('status', 'approved')
        ->when($dealerIdFilter, fn($q) => $q->where('dealerId', $dealerIdFilter))
        ->get()
        ->groupBy('dealerId');

    $dealerSummaries = [];
    $grandTarget = 0;
    $grandAchieved = 0;
    $grandReturned = 0;

    foreach ($commissions as $dealerId => $dealerCommissions) {
        $dealer = $dealerCommissions->first()->dealer;

        $products = collect();
        foreach ($dealerCommissions as $commission) {
            foreach ($commission->commissionProducts as $cp) {
                $products->push([
                    'productId' => $cp->productId,
                    'productName' => $cp->product->shortName ?? 'N/A',
                    'targetQty' => $month ? $cp->monthlyTargetQuantity : $cp->yearlyTargetQuantity
                ]);
            }
        }

        $grouped = $products->groupBy('productId');
        $dealerData = [];
        $dealerTotalTarget = 0;
        $dealerTotalAchieved = 0;
        $dealerTotalReturn = 0;

        foreach ($grouped as $productId => $entries) {
            $targetQty = $entries->sum('targetQty');

            $returnQty = FeedSalesReturnDetails::where('productId', $productId)
                ->whereHas('salesReturn', function ($q) use ($dealerId, $year, $month) {
                    $q->where('dealerId', $dealerId)
                      ->where('status', 'approved')
                      ->whereYear('returnDate', $year)
                      ->when($month, fn($query) => $query->whereMonth('returnDate', $month));
                })
                ->sum(DB::raw('CAST("rQty" AS NUMERIC)'));

            $achievement = DealerTargetAchievementView::where('dealerId', $dealerId)
                ->where('productId', $productId)
                ->where('year', $year)
                ->when($month, fn($q) => $q->where('month', $month))
                ->first();

            $salesQty = $achievement->achieved_qty ?? 0;
            $netAchieved = $salesQty - $returnQty;

            $dealerData[] = [
                'productName' => $entries->first()['productName'],
                'targetQty' => $targetQty,
                'achievedQty' => $netAchieved,
                'returnQty' => $returnQty,
                'percentage' => $targetQty > 0 ? round(($netAchieved / $targetQty) * 100, 2) : 0
            ];

            $dealerTotalTarget += $targetQty;
            $dealerTotalAchieved += $netAchieved;
            $dealerTotalReturn += $returnQty;
        }

        $dealerData[] = [
            'productName' => 'TOTAL',
            'targetQty' => $dealerTotalTarget,
            'achievedQty' => $dealerTotalAchieved,
            'returnQty' => $dealerTotalReturn,
            'percentage' => $dealerTotalTarget > 0 ? round(($dealerTotalAchieved / $dealerTotalTarget) * 100, 2) : 0
        ];

        $dealerSummaries[] = [
            'dealerId' => $dealerId,
            'dealerName' => $dealer->tradeName ?? '',
            'dealerCode' => $dealer->dealerCode ?? '',
            'targetQty' => $dealerTotalTarget,
            'achievedQty' => $dealerTotalAchieved,
            'returnQty' => $dealerTotalReturn,
            'percentage' => $dealerTotalTarget > 0 ? round(($dealerTotalAchieved / $dealerTotalTarget) * 100, 2) : 0,
            'data' => $dealerData
        ];

        $grandTarget += $dealerTotalTarget;
        $grandAchieved += $dealerTotalAchieved;
        $grandReturned += $dealerTotalReturn;
    }

    $grandTotal = [
        'targetQty' => $grandTarget,
        'achievedQty' => $grandAchieved,
        'returnQty' => $grandReturned,
        'percentage' => $grandTarget > 0 ? round(($grandAchieved / $grandTarget) * 100, 2) : 0
    ];

    return response()->json([
        'year' => $year,
        'month' => $month,
        'message' => $dealerIdFilter ? 'Single dealer target vs achievement report' : 'All dealers target vs achievement report',
        'dealerSummaries' => $dealerSummaries,
        'grandTotal' => $grandTotal
    ]);
}

public function allDealersTargetVsAchievement(Request $request)
{
    //$year = $request->input('year', now()->year);
    $year = $request->input('year');
    $month = $request->input('month');
    $dealerIdFilter = $request->input('dealerId');

    //
    if (!$year || !$month) {
        return response()->json([
            'year' => $year,
            'month' => $month,
            'message' => 'Month and year are required',
            'dealerSummaries' => [],
            'grandTotal' => [
                'targetQty' => 0,
                'achievedQty' => 0,
                'returnQty' => 0,
                'percentage' => 0
            ]
        ]);
    }


    $commissions = Commission::with(['commissionProducts.product', 'dealer'])
        ->where('status', 'approved')
        ->when($dealerIdFilter, fn($q) => $q->where('dealerId', $dealerIdFilter))
       //
        ->whereYear('commissionDate', $year)
        ->whereMonth('commissionDate', $month)

        ->get()
        ->groupBy('dealerId');

    $dealerSummaries = [];
    $grandTarget = 0;
    $grandAchieved = 0;
    $grandReturned = 0;

    foreach ($commissions as $dealerId => $dealerCommissions) {
        $dealer = $dealerCommissions->first()->dealer;

        $products = collect();
        foreach ($dealerCommissions as $commission) {
            foreach ($commission->commissionProducts as $cp) {
                $products->push([
                    'productId' => $cp->productId,
                    'productName' => $cp->product->shortName ?? 'N/A',
                    'targetQty' => $month ? $cp->monthlyTargetQuantity : $cp->yearlyTargetQuantity
                ]);
            }
        }

        $grouped = $products->groupBy('productId');
        $dealerData = [];
        $dealerTotalTarget = 0;
        $dealerTotalAchieved = 0;
        $dealerTotalReturn = 0;

        foreach ($grouped as $productId => $entries) {
            $targetQty = $entries->sum('targetQty');

            $returnQty = FeedSalesReturnDetails::where('productId', $productId)
                ->whereHas('salesReturn', function ($q) use ($dealerId, $year, $month) {
                    $q->where('dealerId', $dealerId)
                      ->where('status', 'approved')
                      ->whereYear('returnDate', $year)
                      ->when($month, fn($query) => $query->whereMonth('returnDate', $month));
                })
                ->sum(DB::raw('CAST("rQty" AS NUMERIC)'));

            $achievement = DealerTargetAchievementView::where('dealerId', $dealerId)
                ->where('productId', $productId)
                ->where('year', $year)
                ->when($month, fn($q) => $q->where('month', $month))
                ->first();

            $salesQty = $achievement->achieved_qty ?? 0;
            $netAchieved = $salesQty - $returnQty;

            $dealerData[] = [
                'productName' => $entries->first()['productName'],
                'targetQty' => $targetQty,
                'achievedQty' => $netAchieved,
                'returnQty' => $returnQty,
                'percentage' => $targetQty > 0 ? round(($netAchieved / $targetQty) * 100, 2) : 0
            ];

            $dealerTotalTarget += $targetQty;
            $dealerTotalAchieved += $netAchieved;
            $dealerTotalReturn += $returnQty;
        }

        $dealerData[] = [
            'productName' => 'TOTAL',
            'targetQty' => $dealerTotalTarget,
            'achievedQty' => $dealerTotalAchieved,
            'returnQty' => $dealerTotalReturn,
            'percentage' => $dealerTotalTarget > 0 ? round(($dealerTotalAchieved / $dealerTotalTarget) * 100, 2) : 0
        ];

        $dealerSummaries[] = [
            'dealerId' => $dealerId,
            'dealerName' => $dealer->tradeName ?? '',
            'dealerCode' => $dealer->dealerCode ?? '',
            'targetQty' => $dealerTotalTarget,
            'achievedQty' => $dealerTotalAchieved,
            'returnQty' => $dealerTotalReturn,
            'percentage' => $dealerTotalTarget > 0 ? round(($dealerTotalAchieved / $dealerTotalTarget) * 100, 2) : 0,
            'data' => $dealerData
        ];

        $grandTarget += $dealerTotalTarget;
        $grandAchieved += $dealerTotalAchieved;
        $grandReturned += $dealerTotalReturn;
    }

    $grandTotal = [
        'targetQty' => $grandTarget,
        'achievedQty' => $grandAchieved,
        'returnQty' => $grandReturned,
        'percentage' => $grandTarget > 0 ? round(($grandAchieved / $grandTarget) * 100, 2) : 0
    ];

    return response()->json([
        'year' => $year,
        'month' => $month,
        'message' => $dealerIdFilter ? 'Single dealer target vs achievement report' : 'All dealers target vs achievement report',
        'dealerSummaries' => $dealerSummaries,
        'grandTotal' => $grandTotal
    ]);
}

public function allDealersReturnQty(Request $request)
{
    $year = $request->input('year');
    $month = $request->input('month');
    $dealerId = $request->input('dealerId'); // Optional filter
    $returns = FeedSalesReturnDetails::selectRaw('
            feed_sales_returns."dealerId",
            feed_sales_return_details."productId",
            SUM(feed_sales_return_details."rQty"::numeric) AS return_qty
        ')
        ->join('feed_sales_returns', 'feed_sales_return_details.saleReturnId', '=', 'feed_sales_returns.id')
        ->where('feed_sales_returns.status', 'approved')
        ->when($dealerId, fn($q) => $q->where('feed_sales_returns.dealerId', $dealerId))
        ->when($year, fn($q) => $q->whereYear('feed_sales_returns.returnDate', $year))
        ->when($month, fn($q) => $q->whereMonth('feed_sales_returns.returnDate', $month))
        ->groupBy('feed_sales_returns.dealerId', 'feed_sales_return_details.productId')
        ->orderBy('feed_sales_returns.dealerId')
        ->get();

    return response()->json([
        'year' => $year,
        'month' => $month,
        'dealerId' => $dealerId,
        'message' => $dealerId ? 'Single dealer feed return quantity' : 'All dealers feed return quantity',
        'data' => $returns
    ]);
}

public function dealerTargetVsAchievement(Request $request)
{
    $dealerId = $request->input('dealerId');
    $year = $request->input('year', now()->year);
    $month = $request->input('month');

    $commission = Commission::with(['commissionProducts.product', 'dealer'])
        ->where('dealerId', $dealerId)
        ->where('status', 'approved')
        ->latest()
        ->first();

    if (!$commission) {
        return response()->json(['message' => 'No commission data found for this dealer.'], 404);
    }

    $data = [];
    $totalTarget = 0;
    $totalAchieved = 0;

    foreach ($commission->commissionProducts as $cp) {
        $targetQty = $month ? $cp->monthlyTargetQuantity : $cp->yearlyTargetQuantity;

        $achievement = DealerTargetAchievementView::where('dealerId', $dealerId)
            ->where('productId', $cp->productId)
            ->where('year', $year)
            ->when($month, fn($q) => $q->where('month', $month))
            ->first();

        $netAchieved = $achievement->net_achieved_qty ?? $achievement->achieved_qty ?? 0;

        $data[] = [
            'productName' => $cp->product->shortName ?? 'N/A',
            'targetQty' => $targetQty,
            'achievedQty' => $netAchieved,
            'percentage' => $targetQty > 0 ? round(($netAchieved / $targetQty) * 100, 2) : 0
        ];

        $totalTarget += $targetQty;
        $totalAchieved += $netAchieved;
    }

    $data[] = [
        'productName' => 'TOTAL',
        'targetQty' => $totalTarget,
        'achievedQty' => $totalAchieved,
        'percentage' => $totalTarget > 0 ? round(($totalAchieved / $totalTarget) * 100, 2) : 0
    ];

    return response()->json([
        'dealerId' => $dealerId,
        'dealerName' => $commission->dealer->tradeName ?? '',
        'dealerCode' => $commission->dealer->dealerCode ?? '',
        'year' => $year,
        'month' => $month,
        'data' => $data,
        'totalTarget' => $totalTarget,
        'totalAchieved' => $totalAchieved,
        'percentage' => $totalTarget > 0 ? round(($totalAchieved / $totalTarget) * 100, 2) : 0
    ]);
}

//
// with feedid
// public function getFeedReturnReport(Request $request)
// {
//     $startDate = $request->query('startDate');
//     $endDate   = $request->query('endDate');

//     $rows = DB::table('view_feed_sale_return')
//         ->select([
//             'return_id',
//             'saleId',
//             'feed_order_code',
//             'saleReturnId',
//             'invoiceDate',
//             'returnDate',
//             'dealername',
//             'productId',
//             'productname',
//             'rQty',
//             'salePrice',
//         ])
//         ->whereBetween('returnDate', [$startDate, $endDate])
//         ->orderBy('returnDate', 'asc')
//         ->get()
//         ->groupBy('return_id');

//     return response()->json([
//         'status' => 'success',
//         'data'   => $rows,
//     ]);
// }

// FeedOrder print
public function getFeedReturnReport(Request $request)
{
    $startDate = $request->query('startDate');
    $endDate   = $request->query('endDate');

    $rows = DB::table('view_feed_sale_return')
        ->select([
            'return_id',
            'saleReturnId',
            'saleId',
            'feed_order_code',
            'totalAmount',
            'invoiceDate',
            'returnDate',
            'dealername',
            'productId',
            'productname',
            'sizeOrWeight',
            'qty',
            'rQty',
            'salePrice',
            'totalReturnAmount',

        ])
        ->whereBetween('returnDate', [$startDate, $endDate])
        ->orderBy('returnDate', 'asc')
        ->get();

    $withBags = $rows->map(function ($item) {
        $size = floatval($item->sizeOrWeight) ?: 0;
        $qty = floatval($item->qty) ?: 0;
        $rQty = floatval($item->rQty) ?: 0;
        $salePrice = floatval($item->salePrice);

        // Apply same logic from resource
        $item->bagQty = $size > 0 ? round($qty / $size, 2) : 0;
        $item->rBagQty = $size > 0 ? round($rQty / $size, 2) : 0;

        // Return amount = return bag qty * sale price
        $item->totalReturnAmount = round($item->rBagQty * $salePrice, 2);

        // Net amount = total - return
        $item->netAmount = floatval($item->totalAmount) - $item->totalReturnAmount;

        return $item;
    });


    // ৩) return_id দিয়ে groupBy করে response তৈরি
    $grouped = $withBags->groupBy('return_id');

    return response()->json([
        'status' => 'success',
        'data'   => $grouped,
    ]);
}

//Acc dashboard add this feed api
public function getFeedReturnSum(Request $request)
{
    //  current month range set করা হচ্ছে
    $startDate = now()->startOfMonth()->toDateString();
    $endDate   = now()->endOfMonth()->toDateString();

    $rows = DB::table('view_feed_sale_return')
        ->select([
            'return_id',
            'saleReturnId',
            'saleId',
            'feed_order_code',
            'totalAmount',
            'invoiceDate',
            'returnDate',
            'dealername',
            'productId',
            'productname',
            'sizeOrWeight',
            'qty',
            'rQty',
            'salePrice',
            'totalReturnAmount',
        ])
        ->whereBetween('returnDate', [$startDate, $endDate])
        ->orderBy('returnDate', 'asc')
        ->get();

        $withBags = $rows->map(function ($item) {
            $size = floatval($item->sizeOrWeight) ?: 0;
            $qty = floatval($item->qty) ?: 0;
            $rQty = floatval($item->rQty) ?: 0;
            $salePrice = floatval($item->salePrice);

            // Apply same logic from resource
            $item->bagQty = $size > 0 ? round($qty / $size, 2) : 0;
            $item->rBagQty = $size > 0 ? round($rQty / $size, 2) : 0;

            // Return amount = return bag qty * sale price
            $item->totalReturnAmount = round($item->rBagQty * $salePrice, 2);

            // Net amount = total - return
            $item->netAmount = floatval($item->totalAmount) - $item->totalReturnAmount;

            return $item;
        });

    $grouped = $withBags->groupBy('return_id');

    return response()->json([
        'status' => 'success',
        'data'   => $grouped,
    ]);
}


// EggOrder print
public function getEggReturnReport(Request $request)
{
    $startDate = $request->query('startDate');
    $endDate   = $request->query('endDate');

    $rows = DB::table('view_egg_sale_return')
        ->select([
            'return_id',
            'saleId',
            'egg_order_code',
            'totalAmount',
            'saleReturnId',
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
        ->whereBetween('returnDate', [$startDate, $endDate])
        ->orderBy('returnDate', 'asc')
        ->get()
        ->map(function ($item) {
            $item->totalReturnAmount = (float) $item->rQty * (float) $item->salePrice;
            $item->netAmount = (float) $item->totalAmount - $item->totalReturnAmount;
            return $item;
        })
        ->groupBy('return_id');

    return response()->json([
        'status' => 'success',
        'returns' => $rows,
    ]);
}


// AccountDashboard add this egg api
public function getEggReturnSum(Request $request)
{
    //  current month range set করা হচ্ছে
    $startDate = now()->startOfMonth()->toDateString();
    $endDate   = now()->endOfMonth()->toDateString();

    $rows = DB::table('view_egg_sale_return')
        ->select([
            'return_id',
            'saleId',
            'egg_order_code',
            'totalAmount',
            'saleReturnId',
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
        ->whereBetween('returnDate', [$startDate, $endDate])
        ->orderBy('returnDate', 'asc')
        ->get()
        ->map(function ($item) {
            $item->totalReturnAmount = (float) $item->rQty * (float) $item->salePrice;
            $item->netAmount = (float) $item->totalAmount - $item->totalReturnAmount;
            return $item;
        })
        ->groupBy('return_id');

    return response()->json([
        'status' => 'success',
        'returns' => $rows,
    ]);
}


}
