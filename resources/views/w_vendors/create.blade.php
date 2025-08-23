@extends('layouts.dashboard')
@section('title','Create Vendor')

@section('content')
  <h2 class="mb-3">Create Vendor</h2>

  <form action="{{ route('w_vendors.store') }}" method="POST" class="card card-body">
    @include('w_vendors._form', ['vendor' => null])
    <div class="mt-3 d-flex gap-2">
      <button class="btn btn-primary">Save</button>
      <a href="{{ route('w_vendors.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
@endsection
