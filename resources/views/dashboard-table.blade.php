<!doctype html>
<html lang="en" class="light-theme">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="{{ asset('assets/images/favicon-32x32.png') }}" type="image/png" />
  <link href="{{ asset('assets/plugins/simplebar/css/simplebar.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/plugins/vectormap/jquery-jvectormap-2.0.2.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/bootstrap-extended.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/icons.css') }}" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
  <link href="{{ asset('assets/css/pace.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/dark-theme.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/light-theme.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/semi-dark.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/header-colors.css') }}" rel="stylesheet" />
  <title>Weight Transactions Table</title>
</head>
<body>
  <div class="wrapper">
    @include('components.dashboard-header')
    @include('components.dashboard-sidebar')
    <main class="page-content">
      <div class="container mt-4">
        <h2 class="mb-4">Weight Transactions</h2>
        <form method="GET" action="{{ route('dashboard-table') }}" class="mb-3">
          <div class="input-group">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search by Transaction ID, Vehicle No, Customer..." class="form-control">
            <button class="btn btn-primary">Search</button>
          </div>
        </form>
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
        <div class="d-flex justify-content-center">
          {!! $transactions->links() !!}
        </div>
      </div>
    </main>
    @include('components.dashboard-footer')
  </div>
  <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/simplebar/js/simplebar.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js') }}"></script>
  <script src="{{ asset('assets/js/pace.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/vectormap/jquery-jvectormap-2.0.2.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/vectormap/jquery-jvectormap-world-mill-en.js') }}"></script>
  <script src="{{ asset('assets/plugins/apexcharts-bundle/js/apexcharts.min.js') }}"></script>
  <script src="{{ asset('assets/js/app.js') }}"></script>
  <script src="{{ asset('assets/js/index.js') }}"></script>
</body>
</html>
