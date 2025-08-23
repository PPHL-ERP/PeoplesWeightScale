@extends('layouts.dashboard')
@section('title','Create Customer')

@section('content')
  <h2 class="mb-3">Create Customer</h2>

  <form action="{{ route('w_customers.store') }}" method="POST" class="card card-body">
    @include('w_customers._form', ['customer' => null])
    <div class="mt-3 d-flex gap-2">
      <button class="btn btn-primary">Save</button>
      <a href="{{ route('w_customers.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
@endsection
