<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>POS Receipt</title>
    <style>
        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }
            body {
                font-family: 'Courier New', monospace;
                font-size: 14px;
                font-weight: 700;
                color: #000;
                padding: 10px;
                margin: 0;
            }
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            font-weight: 700;
            color: #000;
            padding: 10px;
            margin: 0;
        }

        .receipt {
            width: 80mm;
            margin: auto;
        }

        .center {
            text-align: center;
            font-weight: 700;
        }

        .line {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        table {
            width: 100%;
            font-size: 13px;
            margin-bottom: 10px;
        }

        td, th {
            padding: 2px 0;
        }

        h4 {
            margin: 5px 0;
            font-size: 14px;
            text-align: center;
        }
    </style>
</head>
<body onload="window.print()">
    <div class="receipt">

        <div class="center">
            <h3>Peoples feed</h3>
            <div>Mobile: 01705463375</div>
            <div>Depot: SolinBazar, Sagardighi, Ghatail, Tangail</div>
            <div>Head Office: 3 Shahid Tajuddin Ahamed Sharoni,</div>
            <div>3rd floor (NCC Bank Building), Moghbazar, Dhaka-1217</div>
        </div>

        <div class="line"></div>

        <table>
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

        <div class="line"></div>
        <h4>Transaction Details</h4>
        <table border="1" cellspacing="0" cellpadding="4">
            <thead>
                <tr>
                    <th>Gross</th>
                    <th>Tare</th>
                    <th>Net</th>
                    <th>Rate</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ number_format($transaction->gross_weight, 2) }}</td>
                    <td>{{ number_format($transaction->tare_weight, 2) }}</td>
                    {{ number_format($transaction->gross_weight - $transaction->tare_weight, 2) }}

                    <td>{{ number_format($transaction->price, 2) }}</td>
                    <td>{{ number_format($transaction->amount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="line"></div>
        <div class="center">Thank you for your business!</div>
    </div>
</body>
</html>
