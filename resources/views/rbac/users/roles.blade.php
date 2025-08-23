@extends('layouts.dashboard')
@section('title','Assign Roles')

@section('content')
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

  <h2 class="mb-3">Assign Roles to: {{ $user->name }} ({{ $user->email }})</h2>

  <form method="POST" action="{{ route('users.roles.update', $user->id) }}" class="card card-body">
    @csrf @method('PUT')

    <div class="row">
      @foreach($roles as $r)
        <div class="col-md-3">
          <label class="form-check">
            <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $r->id }}"
              @checked($user->roles->pluck('id')->contains($r->id))>
            <span class="form-check-label">{{ $r->roleName }}</span>
          </label>
        </div>
      @endforeach
    </div>

    <div class="mt-3 d-flex gap-2">
      <button class="btn btn-primary">Save</button>
      <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>
    </div>
  </form>
@endsection
