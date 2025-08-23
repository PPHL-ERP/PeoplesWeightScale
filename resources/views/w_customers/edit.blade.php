@extends('layouts.dashboard')
@section('title','Edit Customer')

@section('content')
  <h2 class="mb-3">Edit Customer #{{ $customer->id }}</h2>

  <form action="{{ route('w_customers.update', $customer->id) }}" method="POST" class="card card-body">
    @method('PUT')
    @include('w_customers._form', ['customer' => $customer])
    <div class="mt-3 d-flex gap-2">
      <button class="btn btn-primary">Update</button>
      <a href="{{ route('w_customers.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
@endsection
