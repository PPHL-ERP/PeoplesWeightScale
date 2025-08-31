@extends('layouts.dashboard')
@section('title','Vendor Details')

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
    <h2 class="mb-0">Vendor Details #{{ $vendor->id }}</h2>
    <div class="d-flex gap-2">
      <a href="{{ route('w_vendors.edit',$vendor->id) }}" class="btn btn-primary">Edit</a>
      <a href="{{ route('w_vendors.index') }}" class="btn btn-secondary">Back to list</a>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-3"><strong>Vendor ID:</strong> {{ $vendor->vId ?? '—' }}</div>
        <div class="col-md-3"><strong>Old ID:</strong> {{ $vendor->oldvId ?? '—' }}</div>
        <div class="col-md-3"><strong>Name:</strong> {{ $vendor->vName }}</div>
        <div class="col-md-3"><strong>Name (Bangla):</strong> {{ $vendor->vNamebangla ?? '—' }}</div>

        <div class="col-md-3"><strong>Phone:</strong> {{ $vendor->phone ?? '—' }}</div>
        <div class="col-md-9"><strong>Address:</strong> {{ $vendor->address ?? '—' }}</div>

        <div class="col-md-12"><strong>Note:</strong><br>{{ $vendor->note ?? '—' }}</div>
      </div>
    </div>
  </div>
</main>
@endsection
