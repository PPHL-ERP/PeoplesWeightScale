@extends('layouts.dashboard')
@section('title','Weight Transaction Create')

@section('content')
<div class="container">
  <h2 class="mb-3">Create Weight Transaction</h2>

  <form action="{{ route('weight_transactions.store') }}" method="POST" class="card card-body">
    @include('weight_transactions._form', ['transaction' => null])
    <div class="mt-3 d-flex gap-2">
      <button class="btn btn-primary">Save</button>
      <a href="{{ route('weight_transactions.index') }}" class="btn btn-secondary">Back</a>
    </div>
  </form>
</div>
@endsection
