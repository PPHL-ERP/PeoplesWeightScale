@extends('layouts.dashboard')
@section('title','Edit Vendor')

@section('content')
  <h2 class="mb-3">Edit Vendor #{{ $vendor->id }}</h2>

  <form action="{{ route('w_vendors.update', $vendor->id) }}" method="POST" class="card card-body">
    @method('PUT')
    @include('w_vendors._form', ['vendor' => $vendor])
    <div class="mt-3 d-flex gap-2">
      <button class="btn btn-primary">Update</button>
      <a href="{{ route('w_vendors.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
@endsection
