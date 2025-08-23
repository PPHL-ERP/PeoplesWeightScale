@extends('layouts.dashboard')
@section('title','Create Role')

@section('content')
  <h2 class="mb-3">Create Role</h2>
  <form method="POST" action="{{ route('roles.store') }}" class="card card-body">
    @csrf
    <div class="mb-3">
      <label class="form-label">Role Name *</label>
      <input type="text" name="roleName" class="form-control" value="{{ old('roleName') }}" required>
      @error('roleName')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>

    <div class="mb-2"><strong>Permissions</strong></div>
    <div class="row">
      @foreach($permissions as $p)
        <div class="col-md-3">
          <label class="form-check">
            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $p->id }}">
            <span class="form-check-label">{{ $p->name }}</span>
          </label>
        </div>
      @endforeach
    </div>

    <div class="mt-3 d-flex gap-2">
      <button class="btn btn-primary">Save</button>
      <a href="{{ route('roles.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
@endsection
