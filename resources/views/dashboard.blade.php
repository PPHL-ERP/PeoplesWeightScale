@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1>🎉 Welcome, {{ session('user')['name'] ?? 'User' }}!</h1>
    <p>Email: {{ session('user')['email'] ?? 'N/A' }}</p>


    {{-- <a href="{{ route('logout') }}" class="btn btn-danger mt-3">Logout</a> --}}
</div>
<div class="container mt-4">
    <h2 class="mb-4">Weight Transactions</h2>

    <!-- Search Form -->
    <form method="GET" action="{{ route('dashboard') }}" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by Transaction ID, Vehicle No, Customer..." class="form-control">
            <button class="btn btn-primary">Search</button>
        </div>
    </form>

    <!-- Table -->
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Transaction ID</th>
                <th>Vehicle No</th>
                <th>Customer</th>
                <th>Vendor</th>
                <th>Net Weight (KG)</th>
                <th>Price</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Date</th>
                <th>Print</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $key => $transaction)
                <tr>
                    <td>{{ $transactions->firstItem() + $key }}</td>
                    <td>{{ $transaction->transaction_id }}</td>
                    <td>{{ $transaction->vehicle_no }}</td>
                    <td>{{ $transaction->customer_name ?? 'N/A' }}</td>
                    <td>{{ $transaction->vendor_name ?? 'N/A' }}</td>
                                       <td>
    Net: {{ number_format($transaction->gross_weight - $transaction->tare_weight, 2) }} kg<br>
    (G: {{ number_format($transaction->gross_weight, 2) }} / T: {{ number_format($transaction->tare_weight, 2) }})
</td>


                    <td>{{ number_format($transaction->price, 2) }}</td>
                    <td>{{ number_format($transaction->amount, 2) }}</td>
                    <td>{{ ucfirst($transaction->status) }}</td>
                    <td>{{ \Carbon\Carbon::parse($transaction->created_at)->format('d-m-Y') }}</td>
                    <td>
                        <a href="{{ route('print.invoice', $transaction->id) }}" target="_blank" class="btn btn-success">📥 A4 PDF</a>
                        <a href="{{ route('print.pos', $transaction->id) }}" target="_blank" class="btn btn-primary">🖨 POS Print</a>


                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">No Transactions Found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="d-flex justify-content-center">
        {!! $transactions->links() !!}
    </div>
</div>
@endsection
