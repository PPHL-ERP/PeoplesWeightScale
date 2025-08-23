@extends('layouts.dashboard')
@section('title','Weight Transaction Update')

@section('content')
<div class="container">
  <h2 class="mb-3">Edit Weight Transaction #{{ $transaction->id }}</h2>

  <form action="{{ route('weight_transactions.update', $transaction->id) }}" method="POST" class="card card-body">
    @method('PUT')
    @include('weight_transactions._form', ['transaction' => $transaction])
    <div class="mt-3 d-flex gap-2">
      <button class="btn btn-primary">Update</button>
      {{-- <a href="{{ route('weight_transactions.show', $transaction->id) }}" class="btn btn-secondary">Cancel</a> --}}
      <a href="{{ route('weight_transactions.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>
@endsection
