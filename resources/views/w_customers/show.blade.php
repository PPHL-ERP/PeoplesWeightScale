@extends('layouts.dashboard')
@section('title','Customer Details')

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
        <li class="breadcrumb-item active">Customer Details</li>
      </ol>
    </div>
  </div>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Customer Details #{{ $customer->id }}</h2>
    <div class="d-flex gap-2">
      <a href="{{ route('w_customers.edit',$customer->id) }}" class="btn btn-primary">Edit</a>
      <a href="{{ route('w_customers.index') }}" class="btn btn-secondary">Back to list</a>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-3"><strong>Customer ID:</strong> {{ $customer->cId ?? '—' }}</div>
        <div class="col-md-3"><strong>Old ID:</strong> {{ $customer->oldcId ?? '—' }}</div>
        <div class="col-md-3"><strong>Name:</strong> {{ $customer->cName }}</div>
        <div class="col-md-3"><strong>Name (Bangla):</strong> {{ $customer->cNameBangla ?? '—' }}</div>

        <div class="col-md-3"><strong>Phone:</strong> {{ $customer->phone ?? '—' }}</div>
        <div class="col-md-9"><strong>Address:</strong> {{ $customer->address ?? '—' }}</div>

        <div class="col-md-12"><strong>Note:</strong><br>{{ $customer->note ?? '—' }}</div>
      </div>
    </div>
  </div>
</main>
@endsection
