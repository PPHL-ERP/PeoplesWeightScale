{{-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Weight Transactions</title>
</head>
<body>
    <div class="container mt-4">
        <h2 class="mb-4">Weight Transactions (All Data)</h2>

        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Transaction ID</th>
                    <th>Vehicle No</th>
                    <th>Customer</th>
                    <th>Vendor</th>
                    <th>Sector</th>
                    <th>Status</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $key => $transaction)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $transaction->transaction_id }}</td>
                        <td>{{ $transaction->vehicle_no }}</td>
                        <td>{{ $transaction->customer_name ?? 'N/A' }}</td>
                        <td>{{ $transaction->vendor_name ?? 'N/A' }}</td>
                        <td>{{ $transaction->sector_name ?? 'N/A' }}</td>
                        <td>{{ ucfirst($transaction->status) }}</td>
                        <td>{{ $transaction->created_at->format('d-m-Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No Transactions Found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html> --}}

{{-- @extends('layouts.app') --}}
@extends('layouts.dashboard')
@section('title','Weight Transactions')
@section('content')
<div class="container">
    <h2 class="mb-3">Weight Transactions</h2>

    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Search & Filters --}}
    <form method="GET" action="{{ route('weight_transactions.index') }}" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="text" name="s" value="{{ request('s') }}" class="form-control" placeholder="Search transaction/vehicle/customer">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">-- All Status --</option>
                @foreach (['Unfinished','Finished','Reject'] as $st)
                   <option value="{{ $st }}" @selected(request('status')===$st)>{{ $st }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100">Filter</button>
        </div>
        <div class="col-md-3 text-end">
            <a href="{{ route('weight_transactions.create') }}" class="btn btn-success">+ Create</a>
        </div>
    </form>

    <div class="table-responsive">
    <table class="table table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Txn ID</th>
                <th>Vehicle</th>
                <th>Customer</th>
                <th>Gross</th>
                <th>Tare</th>
                <th>Real Net</th>
                <th>Status</th>
                <th>Created</th>
                <th width="180">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $t)
            <tr>
                <td>{{ $t->id }}</td>
                <td>{{ $t->transaction_id }}</td>
                <td>{{ $t->vehicle_no }}</td>
                <td>{{ $t->customer_name }}</td>
                <td>{{ $t->gross_weight }}</td>
                <td>{{ $t->tare_weight }}</td>
                <td><strong>{{ $t->real_net }}</strong></td>
                <td>
                    @php $badge = $t->status==='Finished' ? 'success' : ($t->status==='Reject'?'danger':'secondary'); @endphp
                    <span class="badge bg-{{ $badge }}">{{ $t->status ?? 'N/A' }}</span>
                </td>
                <td>{{ $t->created_at?->format('Y-m-d H:i') }}</td>
                <td>
                    <a href="{{ route('weight_transactions.show',$t->id) }}" class="btn btn-sm btn-outline-info">View</a>
                    <a href="{{ route('weight_transactions.edit',$t->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                    <form action="{{ route('weight_transactions.destroy',$t->id) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Delete this transaction?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
              <tr><td colspan="10" class="text-center text-muted">No data found</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>

    {{ $transactions->links() }}
</div>
@endsection
