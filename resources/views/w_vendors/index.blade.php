@extends('layouts.dashboard')
@section('title','Vendors')

@section('content')
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Vendors</h2>
    <a href="{{ route('w_vendors.create') }}" class="btn btn-success">+ Create</a>
  </div>

  <form method="GET" action="{{ route('w_vendors.index') }}" class="row g-2 mb-3">
    <div class="col-md-5">
      <input type="text" name="s" value="{{ request('s') }}" class="form-control"
             placeholder="Search by Name / Vendor ID / Phone">
    </div>
    <div class="col-md-2">
      <button class="btn btn-primary w-100">Search</button>
    </div>
    @if(request()->has('s'))
      <div class="col-md-2">
        <a href="{{ route('w_vendors.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
      </div>
    @endif
  </form>

  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>Vendor ID</th>
          <th>Old Vendor ID</th>
          <th>Name</th>
          <th>Name (Bangla)</th>
          <th>Phone</th>
          <th>Address</th>
          <th>Note</th>
          <th width="220">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($vendors as $i => $v)
          <tr>
            <td>{{ $vendors->firstItem() + $i }}</td>
            <td>{{ $v->vId ?? '—' }}</td>
            <td>{{ $v->oldvId ?? '—' }}</td>
            <td>{{ $v->vName }}</td>
            <td>{{ $v->vNamebangla ?? '—' }}</td>
            <td>{{ $v->phone ?? '—' }}</td>
            <td>{{ Str::limit($v->address, 40) }}</td>
            <td>{{ Str::limit($v->note, 40) }}</td>
            <td class="d-flex gap-1">
              <a href="{{ route('w_vendors.show',$v->id) }}" class="btn btn-sm btn-outline-info">View</a>
              <a href="{{ route('w_vendors.edit',$v->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
              <form action="{{ route('w_vendors.destroy',$v->id) }}" method="POST"
                    onsubmit="return confirm('Delete this vendor?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="text-center">No vendors found</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{ $vendors->links('pagination::bootstrap-5') }}
@endsection
