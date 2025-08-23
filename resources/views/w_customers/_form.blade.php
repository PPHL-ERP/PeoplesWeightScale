@csrf
<div class="row g-3">
  <div class="col-md-3">
    <label class="form-label">Customer ID</label>
    <input type="text" name="cId" class="form-control" value="{{ old('cId', $customer->cId ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Old Customer ID</label>
    <input type="text" name="oldcId" class="form-control" value="{{ old('oldcId', $customer->oldcId ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Name *</label>
    <input type="text" name="cName" class="form-control" required value="{{ old('cName', $customer->cName ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Name (Bangla)</label>
    <input type="text" name="cNameBangla" class="form-control" value="{{ old('cNameBangla', $customer->cNameBangla ?? '') }}">
  </div>

  <div class="col-md-3">
    <label class="form-label">Phone</label>
    <input type="text" name="phone" class="form-control" value="{{ old('phone', $customer->phone ?? '') }}">
  </div>
  <div class="col-md-9">
    <label class="form-label">Address</label>
    <input type="text" name="address" class="form-control" value="{{ old('address', $customer->address ?? '') }}">
  </div>

  <div class="col-md-12">
    <label class="form-label">Note</label>
    <textarea name="note" rows="3" class="form-control">{{ old('note', $customer->note ?? '') }}</textarea>
  </div>
</div>

@if ($errors->any())
  <div class="alert alert-danger mt-3">
    <ul class="mb-0">
      @foreach ($errors->all() as $e)
        <li>{{ $e }}</li>
      @endforeach
    </ul>
  </div>
@endif
