@csrf
<div class="row g-3">
  <div class="col-md-3">
    <label class="form-label">Vendor ID</label>
    <input type="text" name="vId" class="form-control" value="{{ old('vId', $vendor->vId ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Old Vendor ID</label>
    <input type="text" name="oldvId" class="form-control" value="{{ old('oldvId', $vendor->oldvId ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Name *</label>
    <input type="text" name="vName" class="form-control" required value="{{ old('vName', $vendor->vName ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Name (Bangla)</label>
    <input type="text" name="vNamebangla" class="form-control" value="{{ old('vNamebangla', $vendor->vNamebangla ?? '') }}">
  </div>

  <div class="col-md-3">
    <label class="form-label">Phone</label>
    <input type="text" name="phone" class="form-control" value="{{ old('phone', $vendor->phone ?? '') }}">
  </div>
  <div class="col-md-9">
    <label class="form-label">Address</label>
    <input type="text" name="address" class="form-control" value="{{ old('address', $vendor->address ?? '') }}">
  </div>

  <div class="col-md-12">
    <label class="form-label">Note</label>
    <textarea name="note" rows="3" class="form-control">{{ old('note', $vendor->note ?? '') }}</textarea>
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
