<!DOCTYPE html>
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
</html>
