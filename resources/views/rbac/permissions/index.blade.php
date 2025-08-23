@extends('layouts.dashboard')
@section('title','Permissions')

@section('content')
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Permissions</h2>
    <a href="{{ route('permissions.create') }}" class="btn btn-success">+ Create Permission</a>
  </div>

  <table class="table table-bordered table-striped">
    <thead class="table-dark"><tr><th>#</th><th>Name</th><th width="180">Actions</th></tr></thead>
    <tbody>
      @foreach($permissions as $i => $p)
        <tr>
          <td>{{ $permissions->firstItem() + $i }}</td>
          <td>{{ $p->name }}</td>
          <td>
            <a class="btn btn-sm btn-outline-primary" href="{{ route('permissions.edit',$p->id) }}">Edit</a>
            <form class="d-inline" method="POST" action="{{ route('permissions.destroy',$p->id) }}" onsubmit="return confirm('Delete?')">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-outline-danger">Delete</button>
            </form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>

  {{ $permissions->links('pagination::bootstrap-5') }}
@endsection
