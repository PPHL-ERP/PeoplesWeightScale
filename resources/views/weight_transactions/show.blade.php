@extends('layouts.dashboard')
@section('title','Weight Transaction Details')
@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Transaction Details #{{ $transaction->id }}</h2>
    <div class="d-flex gap-2">
      {{-- <a href="{{ route('weight_transactions.edit',$transaction->id) }}" class="btn btn-primary">Edit</a> --}}
      <a href="{{ route('weight_transactions.index') }}" class="btn btn-secondary">Back</a>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="card">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-3"><strong>Txn ID:</strong> {{ $transaction->transaction_id }}</div>
        <div class="col-md-3"><strong>Status:</strong> {{ $transaction->status }}</div>
        <div class="col-md-3"><strong>Vehicle No:</strong> {{ $transaction->vehicle_no }}</div>
        <div class="col-md-3"><strong>Customer:</strong> {{ $transaction->customer_name }}</div>

        <div class="col-md-3"><strong>Gross:</strong> {{ $transaction->gross_weight }}</div>
        <div class="col-md-3"><strong>Gross Time:</strong> {{ $transaction->gross_time }}</div>
        <div class="col-md-3"><strong>Tare:</strong> {{ $transaction->tare_weight }}</div>
        <div class="col-md-3"><strong>Tare Time:</strong> {{ $transaction->tare_time }}</div>

        <div class="col-md-3"><strong>Volume:</strong> {{ $transaction->volume }}</div>
        <div class="col-md-3"><strong>Price:</strong> {{ $transaction->price }}</div>
        <div class="col-md-3"><strong>Amount:</strong> {{ $transaction->amount }}</div>
        <div class="col-md-3"><strong>Discount:</strong> {{ $transaction->discount }}</div>

        <div class="col-md-3"><strong>Real Net:</strong> <span class="fw-bold">{{ $transaction->real_net }}</span></div>
        <div class="col-md-3"><strong>Sector:</strong> {{ $transaction->sector_name }} (ID: {{ $transaction->sector_id }})</div>
        <div class="col-md-3"><strong>Sale ID:</strong> {{ $transaction->sale_id }}</div>
        <div class="col-md-3"><strong>Purchase ID:</strong> {{ $transaction->purchase_id }}</div>

        <div class="col-md-12"><strong>Note:</strong><br>{{ $transaction->note }}</div>
        <div class="col-md-12"><strong>Others:</strong><br>{{ $transaction->others }}</div>
      </div>
    </div>
  </div>
</div>
@endsection
