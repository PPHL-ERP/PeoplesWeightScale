@csrf

<div class="row g-3">
  <div class="col-md-3">
    <label class="form-label">Transaction ID</label>
    <input type="text" name="transaction_id" class="form-control" value="{{ old('transaction_id', $transaction->transaction_id ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Weight Type</label>
    <input type="text" name="weight_type" class="form-control" value="{{ old('weight_type', $transaction->weight_type ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Transfer Type</label>
    <input type="text" name="transfer_type" class="form-control" value="{{ old('transfer_type', $transaction->transfer_type ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Select Mode</label>
    <input type="text" name="select_mode" class="form-control" value="{{ old('select_mode', $transaction->select_mode ?? '') }}">
  </div>

  <div class="col-md-3">
    <label class="form-label">Vehicle Type</label>
    <input type="text" name="vehicle_type" class="form-control" value="{{ old('vehicle_type', $transaction->vehicle_type ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Vehicle No</label>
    <input type="text" name="vehicle_no" class="form-control" value="{{ old('vehicle_no', $transaction->vehicle_no ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Material</label>
    <input type="text" name="material" class="form-control" value="{{ old('material', $transaction->material ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Product Type</label>
    <input type="text" name="productType" class="form-control" value="{{ old('productType', $transaction->productType ?? '') }}">
  </div>

  <div class="col-md-3">
    <label class="form-label">Gross Weight</label>
    <input type="number" step="0.01" name="gross_weight" class="form-control" value="{{ old('gross_weight', $transaction->gross_weight ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Gross Time</label>
    <input type="datetime-local" name="gross_time" class="form-control" value="{{ old('gross_time', isset($transaction)?optional($transaction->gross_time)->format('Y-m-d\TH:i'): '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Gross Operator</label>
    <input type="text" name="gross_operator" class="form-control" value="{{ old('gross_operator', $transaction->gross_operator ?? '') }}">
  </div>

  <div class="col-md-3">
    <label class="form-label">Tare Weight</label>
    <input type="number" step="0.01" name="tare_weight" class="form-control" value="{{ old('tare_weight', $transaction->tare_weight ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Tare Time</label>
    <input type="datetime-local" name="tare_time" class="form-control" value="{{ old('tare_time', isset($transaction)?optional($transaction->tare_time)->format('Y-m-d\TH:i'): '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Tare Operator</label>
    <input type="text" name="tare_operator" class="form-control" value="{{ old('tare_operator', $transaction->tare_operator ?? '') }}">
  </div>

  <div class="col-md-3">
    <label class="form-label">Volume</label>
    <input type="number" step="0.01" name="volume" class="form-control" value="{{ old('volume', $transaction->volume ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Price</label>
    <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price', $transaction->price ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Amount (auto)</label>
    <input type="number" step="0.01" class="form-control" value="{{ old('amount', $transaction->amount ?? '') }}" disabled>
  </div>
  <div class="col-md-3">
    <label class="form-label">Discount</label>
    <input type="number" step="0.01" name="discount" class="form-control" value="{{ old('discount', $transaction->discount ?? '') }}">
  </div>

  <div class="col-md-3">
    <label class="form-label">Real Net (auto)</label>
    <input type="number" step="0.01" class="form-control" value="{{ old('real_net', $transaction->real_net ?? '') }}" disabled>
  </div>

  <div class="col-md-3">
    <label class="form-label">Customer ID</label>
    <input type="number" name="customer_id" class="form-control" value="{{ old('customer_id', $transaction->customer_id ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Vendor ID</label>
    <input type="number" name="vendor_id" class="form-control" value="{{ old('vendor_id', $transaction->vendor_id ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Customer Name</label>
    <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name', $transaction->customer_name ?? '') }}">
  </div>

  <div class="col-md-3">
    <label class="form-label">Sale ID</label>
    <input type="text" name="sale_id" class="form-control" value="{{ old('sale_id', $transaction->sale_id ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Purchase ID</label>
    <input type="text" name="purchase_id" class="form-control" value="{{ old('purchase_id', $transaction->purchase_id ?? '') }}">
  </div>

  <div class="col-md-3">
    <label class="form-label">Sector ID</label>
    <input type="number" name="sector_id" class="form-control" value="{{ old('sector_id', $transaction->sector_id ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Sector Name</label>
    <input type="text" name="sector_name" class="form-control" value="{{ old('sector_name', $transaction->sector_name ?? '') }}">
  </div>

  <div class="col-md-3">
    <label class="form-label">Status</label>
    <select name="status" class="form-select">
      <option value="">-- Select --</option>
      @foreach (['Unfinished','Finished','Reject'] as $st)
        <option value="{{ $st }}" @selected(old('status', $transaction->status ?? '')===$st)>{{ $st }}</option>
      @endforeach
    </select>
  </div>

  <div class="col-md-3">
    <label class="form-label">Username</label>
    <input type="text" name="username" class="form-control" value="{{ old('username', $transaction->username ?? '') }}">
  </div>

  <div class="col-md-6">
    <label class="form-label">Note</label>
    <textarea name="note" rows="2" class="form-control">{{ old('note', $transaction->note ?? '') }}</textarea>
  </div>
  <div class="col-md-6">
    <label class="form-label">Others</label>
    <textarea name="others" rows="2" class="form-control">{{ old('others', $transaction->others ?? '') }}</textarea>
  </div>
</div>

{{-- validation errors --}}
@if ($errors->any())
  <div class="alert alert-danger mt-3">
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif
