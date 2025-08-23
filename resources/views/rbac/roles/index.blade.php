@extends('layouts.dashboard')
@section('title','Roles')

@section('content')
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Roles</h2>
    <a href="{{ route('roles.create') }}" class="btn btn-success">+ Create Role</a>
  </div>

  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr><th>#</th><th>Name</th><th>Users</th><th>Permissions</th><th width="180">Actions</th></tr>
    </thead>
    <tbody>
      @foreach($roles as $i => $r)
        <tr>
          <td>{{ $roles->firstItem() + $i }}</td>
          <td>{{ $r->roleName }}</td>
          <td>{{ $r->users_count }}</td>
          <td>{{ $r->permissions_count }}</td>
          <td>
            <a class="btn btn-sm btn-outline-primary" href="{{ route('roles.edit',$r->id) }}">Edit</a>
            <form class="d-inline" method="POST" action="{{ route('roles.destroy',$r->id) }}" onsubmit="return confirm('Delete?')">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-outline-danger">Delete</button>
            </form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>

  {{ $roles->links('pagination::bootstrap-5') }}
@endsection
