@csrf
<div class="row g-3">
  <div class="col-md-3">
    <label class="form-label">Material ID</label>
    <input type="text" name="mId" class="form-control" value="{{ old('mId', $material->mId ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Old Material ID</label>
    <input type="text" name="oldmId" class="form-control" value="{{ old('oldmId', $material->oldmId ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Name *</label>
    <input type="text" name="mName" class="form-control" required value="{{ old('mName', $material->mName ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Name (Bangla)</label>
    <input type="text" name="mNameBangla" class="form-control" value="{{ old('mNameBangla', $material->mNameBangla ?? '') }}">
  </div>

  <div class="col-md-6">
    <label class="form-label">Category</label>
    <input type="text" name="categoryType" class="form-control" value="{{ old('categoryType', $material->categoryType ?? '') }}">
  </div>
  <div class="col-md-6">
    <label class="form-label">Note</label>
    <input type="text" name="note" class="form-control" value="{{ old('note', $material->note ?? '') }}">
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
