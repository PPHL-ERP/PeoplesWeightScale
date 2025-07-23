<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccountLedgerName;
use App\Models\BankTransaction;
use App\Models\CompanyAndDealerBasedTransactionView;
use App\Models\Dealer;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\EggSalesOrderService;
use App\Models\AllChartOfHeadTransactionView;
class LedgerReportController extends Controller
{
    protected $eggSalesOrderService;

    public function __construct(EggSalesOrderService $eggSalesOrderService)
    {
        $this->eggSalesOrderService = $eggSalesOrderService;
    }
    public function companyWiseDealerLedgerReportOLD(Request $request, $id)
    {
        $fromDate = $request->fromDate;
        $toDate = $request->toDate;
        $previousBalance = Transaction::join('account_ledger_name', 'account_ledger_name.id', '=', 'transactions.chartOfHeadId')
            ->where([
                ['transactions.voucherDate', '<', $fromDate],
                ['transactions.companyId', '=', $id],
                ['account_ledger_name.partyType', '=', 'D']
            ])
            ->selectRaw('SUM(transactions.debit) - SUM(transactions.credit) AS balance')
            ->value('balance');
        $previousBalance = is_null($previousBalance) ? 0 : $previousBalance;
        $reportData = Transaction::join('account_ledger_name', 'account_ledger_name.id', '=', 'transactions.chartOfHeadId')
            ->leftJoin('dealers', DB::raw('dealers.id::bigint'), '=', DB::raw('account_ledger_name."partyId"::bigint'))
            ->leftJoin('companies', 'companies.id', '=', 'transactions.companyId')
            ->where([
                ['transactions.voucherDate', '>=', $fromDate],
                ['transactions.voucherDate', '<=', $toDate],
                ['transactions.companyId', $id],
                ['account_ledger_name.partyType', 'D']
            ])->select(
                'transactions.*',
                'account_ledger_name.name AS ledgerName',
                'account_ledger_name.code AS ledgerCode',
                'account_ledger_name.partyId AS dealerId',
                DB::raw("CONCAT(dealers.\"tradeName\", ' - ', dealers.\"dealerCode\") AS dealerInfo"),
                'companies.nameEn AS companyName'
            )->get();
        $data = [
            'previousBalance' => $previousBalance,
            'reportData' => $reportData
        ];
        return response()->json([
            'data' => $data
        ], 200);
    }

    public function companyWiseDealerLedgerReport(Request $request, $id)
    {
        $fromDate = $request->fromDate;
        $toDate = $request->toDate;
        $previousBalance = CompanyAndDealerBasedTransactionView::where([
            ['voucherDate', '<', $fromDate],
            ['companyId', '=', $id]
        ])
            ->selectRaw('SUM(debit) - SUM(credit) AS balance')
            ->value('balance');

        $previousBalance = is_null($previousBalance) ? 0 : $previousBalance;
        $reportData = CompanyAndDealerBasedTransactionView::where([
            ['voucherDate', '>=', $fromDate],
            ['voucherDate', '<=', $toDate],
            ['companyId', $id]
        ])->get();
        $data = [
            'previousBalance' => $previousBalance,
            'reportData' => $reportData
        ];
        return response()->json([
            'data' => $data
        ], 200);
    }

    public function dealerLedgerReportOLD(Request $request, $id)
    {
        $fromDate = $request->fromDate;
        $toDate = $request->toDate;

        $previousBalance = CompanyAndDealerBasedTransactionView::where([
            ['voucherDate', '<', $fromDate],
            ['chartOfHeadId', $id]
        ])->selectRaw('SUM(debit) - SUM(credit) AS balance')
            ->value('balance');

        $previousBalance = is_null($previousBalance) ? 0 : $previousBalance;

        // Fetch report data with dealer and company info
        $reportData = CompanyAndDealerBasedTransactionView::where([
            ['voucherDate', '>=', $fromDate],
            ['voucherDate', '<=', $toDate],
            ['chartOfHeadId', $id]
        ])
            ->get();
        dd($reportData->voucherNo);
        dd($reportData->voucherType);
        // Prepare data for response
        $data = [
            'previousBalance' => $previousBalance,
            'reportData' => $reportData
        ];

        return response()->json([
            'data' => $data
        ], 200);
    }

    public function dealerLedgerReport0000(Request $request, $id, EggSalesOrderService $eggSalesOrderService)
    {
        $fromDate = $request->fromDate;
        $toDate = $request->toDate;

        // Calculate the previous balance
        $previousBalance = CompanyAndDealerBasedTransactionView::where([
            ['voucherDate', '<', $fromDate],
            ['chartOfHeadId', $id]
        ])->selectRaw('SUM(debit) - SUM(credit) AS balance')
            ->value('balance');

        $previousBalance = is_null($previousBalance) ? 0 : $previousBalance;

        // Fetch report data with dealer and company info
        $reportData = CompanyAndDealerBasedTransactionView::where([
            ['voucherDate', '>=', $fromDate],
            ['voucherDate', '<=', $toDate],
            ['chartOfHeadId', $id]
        ])->get();

        // Enrich report data with Egg Sales Order details
        $enrichedReportData = $reportData->map(function ($transaction) use ($eggSalesOrderService) {
            if ($transaction->voucherType === 'SalesOrder') {
                $salesOrderInfo = $eggSalesOrderService->getSalesOrderDetailsWithProducts($transaction->voucherNo, $transaction->voucherType);
                $transaction->salesOrderDetails = $salesOrderInfo; // Add sales order info to the transaction
            } elseif ($transaction->voucherType === 'PaymentRecieve') {
                $transaction->salesOrderDetails = null; // Set to null for PaymentRecieve
            }
            return $transaction;
        });
        // dd($enrichedReportData);
        // Prepare data for response
        $data = [
            'previousBalance' => $previousBalance,
            'reportData' => $enrichedReportData
        ];

        return response()->json([
            'data' => $data
        ], 200);
    }
    public function dealerLedgerReport(Request $request, $id, EggSalesOrderService $eggSalesOrderService)
    {
        $fromDate = $request->fromDate;
        $toDate = $request->toDate;

        // ✅ Step 1: Fetch opening balance from account_ledger_name
        $openingBalance = AccountLedgerName::where('id', $id)->value('opening_balance');
        $openingBalance = floatval($openingBalance ?? 0);

        // ✅ Step 2: Calculate the previous balance from transaction view
        $previousBalance = CompanyAndDealerBasedTransactionView::where([
            ['voucherDate', '<', $fromDate],
            ['chartOfHeadId', $id]
        ])
        ->selectRaw('SUM(debit) - SUM(credit) AS balance')
        ->value('balance');

        $previousBalance = floatval($previousBalance ?? 0);

        // ✅ Step 3: Add opening balance
        $previousBalance += $openingBalance;

        // ✅ Step 4: Get transaction data for the requested period
        $reportData = CompanyAndDealerBasedTransactionView::where([
            ['voucherDate', '>=', $fromDate],
            ['voucherDate', '<=', $toDate],
            ['chartOfHeadId', $id]
        ])
        ->orderBy('voucherDate', 'asc')
        ->get();

        // ✅ Step 5: Attach Egg Sales Order info where applicable
        $enrichedReportData = $reportData->map(function ($transaction) use ($eggSalesOrderService) {
            if ($transaction->voucherType === 'SalesOrder') {
                $transaction->salesOrderDetails = $eggSalesOrderService->getSalesOrderDetailsWithProducts(
                    $transaction->voucherNo,
                    $transaction->voucherType
                );
            } else {
                $transaction->salesOrderDetails = null;
            }
            return $transaction;
        });

        // ✅ Step 6: Return final response
        return response()->json([
            'data' => [
                'previousBalance' => $previousBalance,
                'reportData' => $enrichedReportData
            ]
        ], 200);
    }


    public function bankLedgerReportOldMain(Request $request, $id)
    {
        $fromDate = $request->fromDate;
        $toDate = $request->toDate;

        $previousBalance = BankTransaction::where([
            ['voucherDate', '<', $fromDate],
            ['chartOfHeadId', $id]
        ])
            ->selectRaw('SUM(debit) - SUM(credit) AS balance')
            ->value('balance');

        $previousBalance = is_null($previousBalance) ? 0 : $previousBalance;

        $reportData = BankTransaction::where([
            ['voucherDate', '>=', $fromDate],
            ['voucherDate', '<=', $toDate],
            ['chartOfHeadId', $id]
        ])
            ->select('voucherNo', 'voucherDate', 'debit', 'credit', 'note', 'voucherType', 'bankname', 'bankcode', 'companyname')
            ->get();

        $data = [
            'previousBalance' => $previousBalance,
            'reportData' => $reportData
        ];

        return response()->json([
            'data' => $data
        ], 200);
    }

    public function bankLedgerReport(Request $request, $id)
{
    $fromDate = $request->fromDate;
    $toDate = $request->toDate;
    $companyId = $request->companyId;

    $previousBalanceQuery = BankTransaction::where([
        ['voucherDate', '<', $fromDate],
        ['chartOfHeadId', $id]
    ]);

    if ($companyId) {
        $previousBalanceQuery->where('companyId', $companyId);
    }

    $previousBalance = $previousBalanceQuery
        ->selectRaw('SUM(debit) - SUM(credit) AS balance')
        ->value('balance');

    $previousBalance = is_null($previousBalance) ? 0 : $previousBalance;

    $reportQuery = BankTransaction::where([
        ['voucherDate', '>=', $fromDate],
        ['voucherDate', '<=', $toDate],
        ['chartOfHeadId', $id]
    ]);

    if ($companyId) {
        $reportQuery->where('companyId', $companyId);
    }

    $reportData = $reportQuery
        ->select('voucherNo', 'voucherDate', 'debit', 'credit', 'note', 'voucherType', 'bankname', 'bankcode', 'companyname')
        ->get();

    $data = [
        'previousBalance' => $previousBalance,
        'reportData' => $reportData
    ];

    return response()->json([
        'data' => $data
    ], 200);
}


    public function bankLedgerReportOld(Request $request, $id)
    {
        $fromDate = $request->fromDate;
        $toDate = $request->toDate;

        // Calculate previous balance
        $previousBalance = Transaction::join('account_ledger_name', 'account_ledger_name.id', '=', 'transactions.chartOfHeadId')
            ->where([
                ['transactions.voucherDate', '<', $fromDate],
                ['transactions.chartOfHeadId', $id],
                ['account_ledger_name.partyType', 'B']  // Filter for banks
            ])
            ->selectRaw('SUM(transactions.debit) - SUM(transactions.credit) AS balance')
            ->value('balance');
        $previousBalance = is_null($previousBalance) ? 0 : $previousBalance;

        // Fetch report data with bank and company info
        $reportData = Transaction::join('account_ledger_name', 'account_ledger_name.id', '=', 'transactions.chartOfHeadId')
            ->leftJoin('companies', 'companies.id', '=', 'transactions.companyId')
            ->where([
                ['transactions.voucherDate', '>=', $fromDate],
                ['transactions.voucherDate', '<=', $toDate],
                ['transactions.chartOfHeadId', $id],
                ['account_ledger_name.partyType', 'B']  // Filter for banks
            ])
            ->select(
                'transactions.*',
                'account_ledger_name.name AS bankName',
                'account_ledger_name.code AS bankCode',
                'account_ledger_name.current_balance AS currentBalance',
                'companies.nameEn AS companyName'
            )
            ->get();

        // Prepare data for response
        $data = [
            'previousBalance' => $previousBalance,
            'reportData' => $reportData
        ];

        return response()->json([
            'data' => $data
        ], 200);
    }

    public function getDealerClosingBalanceReportOld(Request $request)
    {
        $query = Dealer::join('account_ledger_name', 'account_ledger_name.partyId', 'dealers.id')
            ->where('account_ledger_name.partyType', 'D');

        // Apply dynamic filters
        $filters = ['dealerType', 'zoneId', 'dealerGroup'];
        foreach ($filters as $filter) {
            if ($request->filled($filter)) {
                $query->where("dealers.$filter", $request->$filter);
            }
        }

        $report = $query->select(
            'dealers.id as dealerId',
            'dealers.tradeName',
            'dealers.dealerCode',
            'dealers.dealerType',
            'dealers.zoneId',
            'dealers.dealerGroup',
            'account_ledger_name.current_balance',
            'account_ledger_name.id as chartOfHeadId'
        )->get();

        return response()->json([
            'data' => $report
        ], 200);
    }

    public function getDealerClosingBalanceReport(Request $request)
    {
        $query = DB::table('dealer_account_ledger_view');
        // Apply dynamic filters
        $filters = ['dealerType', 'zoneId', 'dealerGroup'];
        foreach ($filters as $filter) {
            if ($request->filled($filter)) {
                $query->where("$filter", $request->$filter);
            }
        }


        $report = $query->select(
            'dealerId',
            'tradeName',
            'dealerCode',
            'dealerType',
            'zoneId',
            'dealerGroup',
            'current_balance',
            'chartOfHeadId'
        )->get();

        return response()->json([
            'data' => $report
        ], 200);
    }


//// DealerGroup wise Egg, Feed Closing Balance
    public function getFeedDealerClosingBalanceReport(Request $request)
{
    //return $this->getDealerClosingBalanceByGroup($request, 'Feed');
    return $this->getDealerClosingBalanceByGroup($request, ['Feed', 'Feed And Chicks']);
}

public function getEggDealerClosingBalanceReport(Request $request)
{
    return $this->getDealerClosingBalanceByGroup($request, 'Egg');
}

// private function getDealerClosingBalanceByGroup(Request $request, $dealerGroup)
// {
//     $query = DB::table('dealer_account_ledger_view as dal')
//         ->leftJoin('zones as z', 'dal.zoneId', '=', 'z.id')
//         ->where('dal.dealerGroup', $dealerGroup);

//     // Apply dynamic filters
//     $filters = ['dealerType', 'zoneId'];
//     foreach ($filters as $filter) {
//         if ($request->filled($filter)) {
//             $query->where("dal.$filter", $request->$filter);
//         }
//     }

//     $report = $query->select(
//         'dal.dealerId',
//         'dal.tradeName',
//         'dal.dealerCode',
//         'dal.dealerType',
//         'dal.zoneId',
//         'z.zoneName',
//         'dal.dealerGroup',
//         'dal.current_balance',
//         'dal.chartOfHeadId'
//     )
//     ->orderBy('dal.dealerId', 'desc')
//     ->get();

//     return response()->json([
//         'data' => $report
//     ], 200);
// }

// with Feed And Chicks
private function getDealerClosingBalanceByGroup(Request $request, $dealerGroup)
{
    $dealerGroups = is_array($dealerGroup)
        ? $dealerGroup
        : [$dealerGroup];

    $query = DB::table('dealer_account_ledger_view as dal')
        ->leftJoin('zones as z', 'dal.zoneId', '=', 'z.id')
        ->whereIn('dal.dealerGroup', $dealerGroups);
    // Apply dynamic filters
    $filters = ['dealerType', 'zoneId'];
    foreach ($filters as $filter) {
        if ($request->filled($filter)) {
            $query->where("dal.$filter", $request->$filter);
        }
    }

    $report = $query->select(
        'dal.dealerId',
        'dal.tradeName',
        'dal.dealerCode',
        'dal.dealerType',
        'dal.zoneId',
        'z.zoneName',
        'dal.dealerGroup',
        'dal.current_balance',
        'dal.chartOfHeadId'
    )
    ->orderBy('dal.dealerId', 'desc')
    ->get();

    return response()->json([
        'data' => $report
    ], 200);
}


//

/// All ChartOfHeadId
// public function chartOfHeadLedgerReport(Request $request, $id)
// {
//     $fromDate = $request->fromDate;
//     $toDate = $request->toDate;

//     if (!$fromDate || !$toDate) {
//         return response()->json(['message' => 'fromDate and toDate are required.'], 422);
//     }

//     $transactions = AllChartOfHeadTransactionView::where('chartOfHeadId', $id)
//         ->whereBetween('voucherDate', [$fromDate, $toDate])
//         ->orderBy('voucherDate')
//         ->get();

//     $previousBalance = AllChartOfHeadTransactionView::where('chartOfHeadId', $id)
//         ->where('voucherDate', '<', $fromDate)
//         ->selectRaw('SUM(debit) - SUM(credit) AS balance')
//         ->value('balance');

//     $previousBalance = is_null($previousBalance) ? 0 : $previousBalance;

//     return response()->json([
//         'data' => [
//             'previousBalance' => $previousBalance,
//             'reportData' => $transactions
//         ]
//     ], 200);
// }

// All ChartOfHeadId with sales order details
public function chartOfHeadLedgerReport(Request $request, $id, EggSalesOrderService $eggSalesOrderService)
{
    $fromDate = $request->fromDate;
    $toDate = $request->toDate;

    if (!$fromDate || !$toDate) {
        return response()->json(['message' => 'fromDate and toDate are required.'], 422);
    }

    $transactions = AllChartOfHeadTransactionView::where('chartOfHeadId', $id)
        ->whereBetween('voucherDate', [$fromDate, $toDate])
        ->orderBy('voucherDate')
        ->get();

    $previousBalance = AllChartOfHeadTransactionView::where('chartOfHeadId', $id)
        ->where('voucherDate', '<', $fromDate)
        ->selectRaw('SUM(debit) - SUM(credit) AS balance')
        ->value('balance');

    $previousBalance = is_null($previousBalance) ? 0 : $previousBalance;

    $enrichedTransactions = $transactions->map(function ($transaction) use ($eggSalesOrderService) {
        if ($transaction->voucherType === 'SalesOrder') {
            $salesOrderInfo = $eggSalesOrderService->getSalesOrderDetailsWithProducts($transaction->voucherNo, $transaction->voucherType);
            $transaction->salesOrderDetails = $salesOrderInfo;
        } else {
            $transaction->salesOrderDetails = null;
        }
        return $transaction;
    });

    return response()->json([
        'data' => [
            'previousBalance' => $previousBalance,
            'reportData' => $enrichedTransactions
        ]
    ], 200);
}




}
