@extends('layouts.dashboard')
@section('title','Material Details')

@section('content')
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Material Details #{{ $material->id }}</h2>
    <div class="d-flex gap-2">
      <a href="{{ route('w_materials.edit',$material->id) }}" class="btn btn-primary">Edit</a>
      <a href="{{ route('w_materials.index') }}" class="btn btn-secondary">Back to list</a>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-3"><strong>Material ID:</strong> {{ $material->mId ?? '—' }}</div>
        <div class="col-md-3"><strong>Old ID:</strong> {{ $material->oldmId ?? '—' }}</div>
        <div class="col-md-3"><strong>Name:</strong> {{ $material->mName }}</div>
        <div class="col-md-3"><strong>Name (Bangla):</strong> {{ $material->mNameBangla ?? '—' }}</div>

        <div class="col-md-6"><strong>Category:</strong> {{ $material->categoryType ?? '—' }}</div>
        <div class="col-md-6"><strong>Note:</strong> {{ $material->note ?? '—' }}</div>
      </div>
    </div>
  </div>
@endsection
