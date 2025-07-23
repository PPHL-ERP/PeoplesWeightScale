<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\ViewPaymentReceiveInfo;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
class PaymentReceiveReportController extends Controller
{
    public function index(Request $request)
    {
        $query = ViewPaymentReceiveInfo::query();

        if ($request->filled('companyId')) {
            $query->where('companyId', $request->companyId);
        }

        if ($request->filled('recType')) {
            $query->where('recType', $request->recType);
        }

        if ($request->filled('chartOfHeadId')) {
            $query->where('chartOfHeadId', $request->chartOfHeadId);
        }

        if ($request->filled('paymentFor')) {
            $query->where('paymentFor', $request->paymentFor);
        }
        if ($request->filled('paymentType')) {
            $query->where('paymentType', $request->paymentType);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('startDate') && $request->filled('endDate')) {
            $query->whereBetween('recDate', [$request->startDate, $request->endDate]);
        }

        return response()->json([
            // 'data' => $query->orderBy('recDate', 'desc')->paginate(100)
            'data' => $query->orderBy('recDate', 'desc')->get()
        ]);


    }


// public function getFilteredCollectionSummary(Request $request)
// {
//     $query = DB::table('view_payment_receive_infos')
//         ->select(
//             'companyname',
//             'paymentforname as category',
//             'bankname',
//             DB::raw('SUM(amount) as totalamount')
//         )
//         ->where('status', 1);

//     // ✅ filter by paymentforname if provided
//     if ($request->filled('paymentforname')) {
//         $query->where('paymentforname', 'like', '%' . $request->paymentforname . '%');
//     }

//     // optional filters
//     if ($request->filled('companyId')) {
//         $query->where('companyid', $request->companyId);
//     }

//     if ($request->filled('startDate') && $request->filled('endDate')) {
//         $query->whereBetween('recdate', [$request->startDate, $request->endDate]);
//     }

//     $query->groupBy('companyname', 'paymentforname', 'bankname')
//           ->orderBy('companyname')
//           ->orderBy('paymentforname')
//           ->orderBy('bankname');

//     $summary = $query->get();

//     return response()->json([
//         'data' => $summary
//     ]);
// }

// with pagination
public function getFilteredCollectionSummary(Request $request)
{
    $query = DB::table('view_payment_receive_infos')
        ->select(
            'companyname',
            'paymentforname as category',
            'bankname',
            DB::raw('SUM(amount) as totalamount')
        )
        ->where('status', 1);

    if ($request->filled('paymentforname')) {
        $query->where('paymentforname', 'like', '%' . $request->paymentforname . '%');
    }

    if ($request->filled('companyId')) {
        $query->where('companyid', $request->companyId);
    }

    if ($request->filled('startDate') && $request->filled('endDate')) {
        $query->whereBetween('recdate', [$request->startDate, $request->endDate]);
    }

    $query->groupBy('companyname', 'paymentforname', 'bankname')
          ->orderBy('companyname')
          ->orderBy('paymentforname')
          ->orderBy('bankname');

    // ✅ Use pagination
    $summary = $query->paginate(25);

    //return response()->json($summary);

    return response()->json([
        'data' => $summary
    ]);
}

public function getCompanyWiseBankSummary(Request $request)
{
    $query = DB::table('view_payment_receive_infos')
        ->select(
            'companyname',
            'bankname',
            DB::raw('SUM(amount) as totalamount')
        )
        ->where('status', 1);

    // Filter by companyId if provided
    if ($request->filled('companyId')) {
        $query->where('companyid', $request->companyId);
    }

    // ✅ New: Filter by company name
    if ($request->filled('companyname')) {
        $query->where('companyname', $request->companyname);
    }

    // Filter by date
    if ($request->filled('startDate') && $request->filled('endDate')) {
        $query->whereBetween('recdate', [$request->startDate, $request->endDate]);
    }

    $query->groupBy('companyname', 'bankname')
        ->orderBy('companyname')
        ->orderBy('bankname');

    $summary = $query->get();

    return response()->json([
        'data' => $summary
    ]);
}

public function getEggBankTotal(Request $request)
{
    $mode = $request->input('mode', 'daily'); // default 'daily'
    $date = Carbon::parse($request->input('date', now()));

    // Handle date range
    if ($mode === 'weekly') {
        $startDate = $date->copy()->subDays(6)->startOfDay();
        $endDate = $date->endOfDay();
    } else {
        $startDate = $date->startOfDay();
        $endDate = $date->endOfDay();
    }

    $totalEggAmount = DB::table('view_payment_receive_infos')
        ->where('status', 1)
        ->where('paymentforname', 'Egg') // use lowercase field name
        ->whereBetween('recDate', [$startDate, $endDate])
        ->sum('amount');

    return response()->json([
        'data' => [
            'totalEgg' => $totalEggAmount,
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString()
        ]
    ]);
}

public function getFeedBankTotal(Request $request)
{
    $mode = $request->input('mode', 'daily'); // default 'daily'
    $date = Carbon::parse($request->input('date', now()));

    // Handle date range
    if ($mode === 'weekly') {
        $startDate = $date->copy()->subDays(6)->startOfDay();
        $endDate = $date->endOfDay();
    } else {
        $startDate = $date->startOfDay();
        $endDate = $date->endOfDay();
    }

    $totalFeedAmount = DB::table('view_payment_receive_infos')
        ->where('status', 1)
        ->where('paymentforname', 'Feed') // use lowercase field name
        ->whereBetween('recDate', [$startDate, $endDate])
        ->sum('amount');

    return response()->json([
        'data' => [
            'totalFeed' => $totalFeedAmount,
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString()
        ]
    ]);
}

}