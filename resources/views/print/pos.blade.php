<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>POS Receipt</title>
  <style>
    :root { --w-receipt: 80mm; }

    * { box-sizing: border-box; }

    /* Global (screen + print) */
    body {
      margin: 0;
      padding: 10px;
      font-family: Arial, Helvetica, sans-serif; /* thermal-friendly bold */
      font-size: 14px;
      font-weight: 700;     /* force bold everywhere */
      color: #000;
    }
    .receipt { width: var(--w-receipt); margin: 0 auto; }

    h3, h4, h5 {
      margin: 4px 0;
      text-align: center;
      font-weight: 900;
      letter-spacing: .2px;
    }
    .center { text-align: center; font-weight: 600; }
    .r { text-align: right; }
    .c { text-align: center; }
    .line { border-top: 1px dashed #000; margin: 6px 0; }

    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    th, td { padding: 2px 0; font-weight: 700; vertical-align: top; }
    th { font-weight: 900; text-align: left; }

    .meta td { padding: 2px 0; }
    .w50 { width: 50%; }
    .em { font-weight: 900; }

    /* Key/Value list (no box) */
    .kv { width:100%; }
    .kv td { padding: 2px 0; }
    .kv .key { width: 42%; }
    .kv .val { width: 58%; text-align: right; }

    .sign-row {
      display: flex;
      gap: 6px;
      margin-top: 18px;
    }
    .sign-box {
      flex: 1;
      border-top: 1px solid #000;
      padding-top: 4px;
      text-align: center;
      font-weight: 900;
    }
    .footer-note { font-size: 12px; text-align: center; margin-top: 6px; }

    @media print {
      @page { size: 80mm auto; margin: 0; }
      html, body { margin: 0; padding: 10px; }
      body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
  </style>
</head>
<body onload="window.print()">
  <div class="receipt">
    <!-- Header -->
    <h3>Peoples Weighting</h3>
    <div class="center">Mobile: 01705463375</div>
    <div class="center">Depot: SolinBazar, Sagardighi, Ghatail, Tangail</div>
    <div class="center">Head Office: 3 Shahid Tajuddin Ahamed Sharoni, 3rd floor (NCC Bank Building), Moghbazar, Dhaka-1217</div>

    <div class="line"></div>

    <!-- Meta -->
    <table class="meta">
      <tr>
        <td class="w50"><strong>Date:</strong> {{ $transaction->created_at->format('d-m-Y H:i') }}</td>
        <td class="w50 r"><strong>Challan:</strong> {{ $transaction->transaction_id ?? 'N/A' }}</td>
      </tr>
      <tr>
        <td><strong>Weight Type:</strong> {{ $transaction->weight_type ?? 'N/A' }}</td>
        <td class="r"><strong>Status:</strong> {{ $transaction->status ?? 'N/A' }}</td>
      </tr>
      <tr>
        <td><strong>Vehicle:</strong> {{ $transaction->vehicle_type ? $transaction->vehicle_type . ' - ' : '' }}{{ $transaction->vehicle_no ?? 'N/A' }}</td>
        <td class="r"><strong>Sector:</strong> {{ $transaction->sector_name ?? 'N/A' }}</td>
      </tr>
      <tr>
        <td><strong>Customer:</strong> {{ $transaction->customer_name ?? 'N/A' }}</td>
        <td class="r"><strong>Vendor:</strong> {{ $transaction->vendor_name ?? 'N/A' }}</td>
      </tr>
      <tr>
        <td><strong>G Time:</strong>
          @if(!empty($transaction->gross_time))
            {{ \Carbon\Carbon::parse($transaction->gross_time)->format('d-m-Y H:i') }}
          @else N/A @endif
        </td>
        <td class="r"><strong>T Time:</strong>
          @if(!empty($transaction->tare_time))
            {{ \Carbon\Carbon::parse($transaction->tare_time)->format('d-m-Y H:i') }}
          @else N/A @endif
        </td>
      </tr>
      @if(!empty($transaction->sale_id) || !empty($transaction->purchase_id))
      <tr>
        <td><strong>Sale ID:</strong> {{ $transaction->sale_id ?? '—' }}</td>
        <td class="r"><strong>Purchase ID:</strong> {{ $transaction->purchase_id ?? '—' }}</td>
      </tr>
      @endif
    </table>

    <div class="line"></div>

    <!-- TRANSACTION DETAILS (no box, simple key:value list) -->
    <h4>TRANSACTION DETAILS</h4>
    @php
      $gross  = (float)($transaction->gross_weight ?? 0);
      $tare   = (float)($transaction->tare_weight ?? 0);
      $deduct = (float)($transaction->deduction ?? $transaction->detractionQty ?? 0);

      // ✅ FIX: Use real_net only if it is set AND > 0; otherwise compute (gross - tare - deduct)
      $hasReal = isset($transaction->real_net) && $transaction->real_net !== '' && (float)$transaction->real_net > 0;
      $netCalc = $hasReal ? (float)$transaction->real_net : ($gross - $tare - $deduct);
    @endphp

    <table class="kv">
      <tr>
        <td class="key"><strong>Material</strong></td>
        <td class="val">{{ $transaction->material ?? 'N/A' }}</td>
      </tr>
      <tr>
        <td class="key"><strong>Gross</strong></td>
        <td class="val">{{ number_format($gross, 2) }}</td>
      </tr>
      <tr>
        <td class="key"><strong>Tare</strong></td>
        <td class="val">{{ number_format($tare, 2) }}</td>
      </tr>
      <tr>
        <td class="key"><strong>Deduction</strong></td>
        <td class="val">{{ number_format($deduct, 2) }}</td>
      </tr>
      <tr>
        <td class="key"><strong>Net</strong></td>
        <td class="val em">{{ number_format($netCalc, 2) }}</td>
      </tr>
    </table>

    @if(!empty($transaction->note))
      <div class="line"></div>
      <div><strong>Note:</strong> {{ $transaction->note }}</div>
    @endif

    <div class="line"></div>

    <!-- Signatures -->
    <div class="sign-row" style="padding-top: 28px">
      <div class="sign-box">C Signature</div>
      <div class="sign-box">Authorized By</div>
    </div>

    <div class="footer-note">THANK YOU FOR YOUR BUSINESS!</div>
  </div>
</body>
</html>
