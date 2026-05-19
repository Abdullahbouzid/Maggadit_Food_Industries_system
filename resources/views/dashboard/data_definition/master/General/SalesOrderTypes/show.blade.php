@extends('dashboard.partials.layout')

@section('title', 'Sales Order Type Details')

@section('content')
<div class="container-fluid p-0">
    <div class="row mb-4">
        <div class="col-md-8"><h1><i data-feather="info"></i> {{ $salesOrderType->name }}</h1><p class="text-muted">Detailed information</p></div>
        <div class="col-md-4 text-md-end"><a href="{{ route('sales-order-types.edit', $salesOrderType) }}" class="btn btn-warning">Edit</a> <a href="{{ route('sales-order-types.index') }}" class="btn btn-secondary">Back</a></div>
    </div>
    <div class="card shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-4"><strong>Name:</strong> {{ $salesOrderType->name }}</div>
                <div class="col-md-4"><strong>Code:</strong> {{ $salesOrderType->code }}</div>
                <div class="col-md-4"><strong>Active:</strong> {!! $salesOrderType->active ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>' !!}</div>
                <div class="col-md-4"><strong>Default:</strong> {!! $salesOrderType->default ? '<span class="badge bg-warning text-dark">Yes</span>' : '<span class="badge bg-secondary">No</span>' !!}</div>
                <div class="col-md-4"><strong>Created At:</strong> {{ $salesOrderType->created_at->format('Y-m-d H:i') }}</div>
                <div class="col-md-4"><strong>Last Updated:</strong> {{ $salesOrderType->updated_at->format('Y-m-d H:i') }}</div>
            </div>
        </div>
    </div>
</div>
@endsection