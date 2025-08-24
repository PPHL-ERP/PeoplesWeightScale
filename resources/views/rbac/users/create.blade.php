@extends('layouts.dashboard')
@section('title','Create User')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="mb-0">Create User</h2>
  <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">← Back</a>
</div>

@if ($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach ($errors->all() as $e)
        <li>{{ $e }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ route('users.store') }}" class="row g-3">
  @csrf

  <div class="col-md-6">
    <label class="form-label">Name <span class="text-danger">*</span></label>
    <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
  </div>

  <div class="col-md-6">
    <label class="form-label">Email <span class="text-danger">*</span></label>
    <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
  </div>

  <div class="col-md-6">
    <label class="form-label">Password <span class="text-danger">*</span></label>
    <input type="password" name="password" class="form-control" required minlength="8">
  </div>

  <div class="col-md-6">
    <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
    <input type="password" name="password_confirmation" class="form-control" required minlength="8">
  </div>

  <div class="col-12">
    <label class="form-label d-block">Assign Roles</label>
    <div class="row">
      @forelse($roles as $r)
        <div class="col-md-3">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $r->id }}" id="role-{{ $r->id }}">
            <label class="form-check-label" for="role-{{ $r->id }}">{{ $r->roleName }}</label>
          </div>
        </div>
      @empty
        <p class="text-muted">No roles found. Create roles first.</p>
      @endforelse
    </div>
  </div>

  <div class="col-12">
    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="isSuperAdmin" id="isSuperAdmin" value="1">
      <label class="form-check-label" for="isSuperAdmin">Make Super Admin (full access)</label>
    </div>
  </div>

  <div class="col-12">
    <button class="btn btn-primary">Create</button>
  </div>
</form>
@endsection
