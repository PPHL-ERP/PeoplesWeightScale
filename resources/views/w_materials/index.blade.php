@extends('layouts.dashboard')
@section('title','Materials')

@section('content')
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
<main class="page-content">
  <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Tables</div>
    <div class="ps-3">
      <ol class="breadcrumb mb-0 p-0">
        <li class="breadcrumb-item"><a href="#"><i class="bx bx-home-alt"></i></a></li>
        <li class="breadcrumb-item active">Material</li>
      </ol>
    </div>
  </div>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Materials</h2>
    <a href="{{ route('w_materials.create') }}" class="btn btn-success">+ Create</a>
  </div>

  <form method="GET" action="{{ route('w_materials.index') }}" class="row g-2 mb-3">
    <div class="col-md-5">
      <input type="text" name="s" value="{{ request('s') }}" class="form-control"
             placeholder="Search by Name / Material ID / Category">
    </div>
    <div class="col-md-2">
      <button class="btn btn-primary w-100">Search</button>
    </div>
    @if(request()->has('s'))
      <div class="col-md-2">
        <a href="{{ route('w_materials.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
      </div>
    @endif
  </form>

  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>Material ID</th>
          <th>Old ID</th>
          <th>Name</th>
          <th>Name (Bangla)</th>
          <th>Category</th>
          <th>Note</th>
          <th width="220">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($materials as $i => $m)
          <tr>
            <td>{{ $materials->firstItem() + $i }}</td>
            <td>{{ $m->mId ?? '—' }}</td>
            <td>{{ $m->oldmId ?? '—' }}</td>
            <td>{{ $m->mName }}</td>
            <td>{{ $m->mNameBangla ?? '—' }}</td>
            <td>{{ $m->categoryType ?? '—' }}</td>
            <td>{{ Str::limit($m->note, 40) }}</td>
            <td class="d-flex gap-1">
              <a href="{{ route('w_materials.show',$m->id) }}" class="btn btn-sm btn-outline-info">View</a>
              <a href="{{ route('w_materials.edit',$m->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
              <form action="{{ route('w_materials.destroy',$m->id) }}" method="POST"
                    onsubmit="return confirm('Delete this material?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="8" class="text-center">No materials found</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{ $materials->links('pagination::bootstrap-5') }}
  </main>
@endsection
