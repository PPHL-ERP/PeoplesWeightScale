@extends('layouts.dashboard')
@section('title','Create Material')

@section('content')
  <h2 class="mb-3">Create Material</h2>

  <form action="{{ route('w_materials.store') }}" method="POST" class="card card-body">
    @include('w_materials._form', ['material' => null])
    <div class="mt-3 d-flex gap-2">
      <button class="btn btn-primary">Save</button>
      <a href="{{ route('w_materials.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
@endsection
