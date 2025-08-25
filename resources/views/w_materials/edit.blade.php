@extends('layouts.dashboard')
@section('title','Edit Material')

@section('content')
  <h2 class="mb-3">Edit Material #{{ $material->id }}</h2>

  <form action="{{ route('w_materials.update', $material->id) }}" method="POST" class="card card-body">
    @method('PUT')
    @include('w_materials._form', ['material' => $material])
    <div class="mt-3 d-flex gap-2">
      <button class="btn btn-primary">Update</button>
      <a href="{{ route('w_materials.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
@endsection
