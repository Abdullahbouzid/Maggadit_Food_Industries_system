@extends('dashboard.partials.layout')

@section('title', 'Device Details')

@section('content')
<div class="wrapper">
    <div class="main"><main class="content"><div class="container-fluid p-0">
        <div class="row mb-4"><div class="col-12"><h1 class="display-5 fw-bold"><i data-feather="info"></i> Device: {{ $device->serial }}</h1></div></div>
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><th style="width:180px">Name (EN)</th><td>{{ $device->name_en }}</td></tr>
                    <tr><th>Name (AR)</th><td>{{ $device->name_ar }}</td></tr>
                    <tr><th>Serial Number</th><td><code>{{ $device->serial }}</code></td></tr>
                    <tr><th>Organization</th><td>{{ $device->organization?->name ?? '-' }}</td></tr>
                    <tr><th>Model No</th><td>{{ $device->model_no ?? '-' }}</td></tr>
                    <tr><th>Brand</th><td>{{ $device->brand ?? '-' }}</td></tr>
                    <tr><th>Purchase Date</th><td>{{ $device->purchase_date?->format('d M Y') ?? '-' }}</td></tr>
                    <tr><th>Warranty Expiry</th><td>{{ $device->warranty_expiry?->format('d M Y') ?? '-' }}</td></tr>
                    <tr><th>Status</th><td>{!! $device->is_inactive ? '<span class="badge bg-secondary">Inactive</span>' : '<span class="badge bg-success">Active</span>' !!}</td></tr>
                    <tr><th>Remark</th><td>{{ $device->remark ?? '-' }}</td></tr>
                    <tr><th>Created At</th><td>{{ $device->created_at->format('d M Y, h:i A') }}</td></tr>
                    <tr><th>Last Updated</th><td>{{ $device->updated_at->format('d M Y, h:i A') }}</td></tr>
                </table>
            </div>
            <div class="card-footer bg-white border-0"><a href="{{ route('devices.edit', $device) }}" class="btn btn-warning">Edit</a> <a href="{{ route('devices.index') }}" class="btn btn-secondary">Back</a></div>
        </div>
    </div></main></div>
</div>
@endsection