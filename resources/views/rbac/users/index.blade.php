@extends('layouts.dashboard')
@section('title','Users')

@section('content')
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Users</h2>
    <form method="GET" class="d-flex gap-2">
      <input type="text" name="s" value="{{ request('s') }}" class="form-control" placeholder="Search name/email">
      <button class="btn btn-primary">Search</button>
    </form>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-dark">
        <tr>
          <th>#</th><th>Name</th><th>Email</th><th>Roles</th><th width="160">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($users as $i => $u)
          <tr>
            <td>{{ $users->firstItem() + $i }}</td>
            <td>{{ $u->name }}</td>
            <td>{{ $u->email }}</td>
            <td>{{ $u->roles->pluck('roleName')->join(', ') ?: '—' }}</td>
            <td>
              <a href="{{ route('users.roles.edit',$u->id) }}" class="btn btn-sm btn-outline-primary">
                Assign Roles
              </a>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="text-center">No users found</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{ $users->links('pagination::bootstrap-5') }}
@endsection
