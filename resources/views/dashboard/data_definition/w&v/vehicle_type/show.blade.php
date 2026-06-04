@extends('dashboard.partials.layout')

@section('title', 'Vehicle Type Details')

@section('content')
<div class="wrapper">
    <div class="main"><main class="content"><div class="container-fluid p-0">
        <div class="row mb-4"><div class="col-12"><h1 class="display-5 fw-bold"><i data-feather="info"></i> Vehicle Type: {{ $vehicleType->code }}</h1></div></div>
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><th style="width:150px">Code</th><td>{{ $vehicleType->code }}</td></tr>
                    <tr><th>Name (EN)</th><td>{{ $vehicleType->name_en }}</td></tr>
                    <tr><th>Name (AR)</th><td>{{ $vehicleType->name_ar }}</td></tr>
                    <tr><th>Description</th><td>{{ $vehicleType->description ?? '—' }}</td></tr>
                    <tr><th>Status</th><td>{!! $vehicleType->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td></tr>
                    <tr><th>Sort Order</th><td>{{ $vehicleType->sort_order }}</td></tr>
                    <tr><th>Created At</th><td>{{ $vehicleType->created_at->format('d M Y, h:i A') }}</td></tr>
                    <tr><th>Last Updated</th><td>{{ $vehicleType->updated_at->format('d M Y, h:i A') }}</td></tr>
                </table>
            </div>
            <div class="card-footer bg-white border-0"><a href="{{ route('vehicle-types.edit', $vehicleType) }}" class="btn btn-warning">Edit</a> <a href="{{ route('vehicle-types.index') }}" class="btn btn-secondary">Back</a></div>
        </div>
    </div></main></div>
</div>
@endsection