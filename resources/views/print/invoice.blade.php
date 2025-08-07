<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $transaction->transaction_id }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }
        .invoice-container {
            width: 90%;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ccc;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            font-size: 22px;
        }
        .contact-info {
            text-align: center;
            font-size: 12px;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .title {
            text-align: center;
            font-size: 18px;
            margin-bottom: 15px;
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding-bottom: 8px;
        }
        .details-table, .transaction-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .details-table td, .transaction-table td, .transaction-table th {
            padding: 10px;
            border: 1px solid #ddd;
        }
        .details-table td {
            width: 50%;
        }
        .transaction-table thead {
            background-color: #f8f8f8;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <h2>Peoples Feed</h2>
        </div>
        <div class="contact-info">
            Mobile: 01705463375 <br>
            Depot: SolinBazar, Sagardighi, Ghatail, Tangail <br>
            Head Office: 3 Shahid Tajuddin Ahamed Sharoni, 3rd floor (NCC Bank Building), Moghbazar, Dhaka-1217
        </div>

        <!-- Invoice Title -->
        <div class="title">Invoice #{{ $transaction->transaction_id }}</div>

        <!-- Info Table -->
        <table class="details-table">
            <tr>
                <td><strong>Date:</strong> {{ $transaction->created_at->format('d-m-Y H:i') }}</td>
                <td><strong>Vehicle:</strong> {{ $transaction->vehicle_no }}</td>
            </tr>
            <tr>
                <td><strong>Customer:</strong> {{ $transaction->customer_name ?? 'N/A' }}</td>
                <td><strong>Vendor:</strong> {{ $transaction->vendor_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td colspan="2"><strong>Sector:</strong> {{ $transaction->sector_name ?? 'N/A' }}</td>
            </tr>
        </table>

        <!-- Transaction Table -->
        <h4 style="margin-top: 30px;">Transaction Details</h4>
        <table class="transaction-table">
            <thead>
                <tr>
                    <th>Gross Weight</th>
                    <th>Tare Weight</th>
                    <th>Net Weight</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ number_format($transaction->gross_weight, 2) }} KG</td>
                    <td>{{ number_format($transaction->tare_weight, 2) }} KG</td>
                    <td>{{ number_format($transaction->gross_weight - $transaction->tare_weight, 2) }} KG</td>
                </tr>
            </tbody>
        </table>

        {{-- Optional: Price section --}}
        @if($transaction->price && $transaction->amount)
        <table class="transaction-table" style="margin-top: 20px;">
            <thead>
                <tr>
                    <th>Rate (Per KG)</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ number_format($transaction->price, 2) }} BDT</td>
                    <td>{{ number_format($transaction->amount, 2) }} BDT</td>
                </tr>
            </tbody>
        </table>
        @endif

        <!-- Footer -->
        <div class="footer">
            Thank you for your business!
        </div>
    </div>
</body>
</html>
