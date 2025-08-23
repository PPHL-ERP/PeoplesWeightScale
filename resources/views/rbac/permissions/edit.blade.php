@extends('layouts.dashboard')
@section('title','Edit Permission')

@section('content')
  <h2 class="mb-3">Edit Permission</h2>
  <form method="POST" action="{{ route('permissions.update',$permission->id) }}" class="card card-body">
    @csrf @method('PUT')
    <div class="mb-3">
      <label class="form-label">Permission Name *</label>
      <input type="text" name="name" class="form-control" required value="{{ old('name',$permission->name) }}">
      @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="mt-3 d-flex gap-2">
      <button class="btn btn-primary">Update</button>
      <a href="{{ route('permissions.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
@endsection
