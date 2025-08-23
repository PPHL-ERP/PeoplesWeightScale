@extends('layouts.dashboard')
@section('title','Create Permission')

@section('content')
  <h2 class="mb-3">Create Permission</h2>
  <form method="POST" action="{{ route('permissions.store') }}" class="card card-body">
    @csrf
    <div class="mb-3">
      <label class="form-label">Permission Name *</label>
      <input type="text" name="name" class="form-control" required value="{{ old('name') }}" placeholder="e.g. materials.view">
      @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="mt-3 d-flex gap-2">
      <button class="btn btn-primary">Save</button>
      <a href="{{ route('permissions.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
@endsection
