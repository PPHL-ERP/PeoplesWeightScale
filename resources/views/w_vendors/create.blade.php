@extends('layouts.dashboard')
@section('title','Create Vendor')

@section('content')
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
  <h2 class="mb-3">Create Vendor</h2>

  <form action="{{ route('w_vendors.store') }}" method="POST" class="card card-body">
    @include('w_vendors._form', ['vendor' => null])
    <div class="mt-3 d-flex gap-2">
      <button class="btn btn-primary">Save</button>
      <a href="{{ route('w_vendors.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
  </main>
@endsection
