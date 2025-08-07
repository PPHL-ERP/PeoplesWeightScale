<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DashboardTableController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = DB::table('weight_transactions');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%$search%")
                  ->orWhere('vehicle_no', 'like', "%$search%")
                  ->orWhere('customer_name', 'like', "%$search%")
                  ->orWhere('vendor_name', 'like', "%$search%")
                ;
            });
        }

        $transactions = $query->orderByDesc('id')->paginate(10);

        return view('dashboard-table', [
            'transactions' => $transactions,
            'search' => $search,
        ]);
    }
}
