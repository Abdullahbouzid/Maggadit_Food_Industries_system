@extends('dashboard.partials.layout')

@section('title', 'Vehicle Details')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold">
                            <i data-feather="info"></i> Vehicle Details
                        </h1>
                        <p class="text-muted">View complete information of the vehicle.</p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6"><label class="text-muted">Vehicle Name</label><p class="fw-semibold">{{ $vehicle->vehicle_name }}</p></div>
                            <div class="col-md-6"><label class="text-muted">Vehicle Code</label><p class="fw-semibold"><code>{{ $vehicle->vehicle_code }}</code></p></div>
                            <div class="col-md-6"><label class="text-muted">Plate No.</label><p class="fw-semibold">{{ $vehicle->plate_no }}</p></div>
                            <div class="col-md-6"><label class="text-muted">Barcode</label><p class="fw-semibold">{{ $vehicle->barcode ?? '-' }}</p></div>
                            <div class="col-md-6"><label class="text-muted">Organization</label><p class="fw-semibold">{{ $vehicle->organization->name ?? '-' }}</p></div>
                            <div class="col-md-6"><label class="text-muted">Vehicle Type</label><p class="fw-semibold">{{ $vehicle->vehicleType->name_en ?? '-' }}</p></div>
                            <div class="col-md-3"><label class="text-muted">Odometer</label><p class="fw-semibold">{{ $vehicle->odometer ?? '-' }}</p></div>
                            <div class="col-md-3"><label class="text-muted">Capacity</label><p class="fw-semibold">{{ $vehicle->capacity ?? '-' }}</p></div>
                            <div class="col-md-3"><label class="text-muted">Amount</label><p class="fw-semibold">{{ $vehicle->amount ?? '-' }}</p></div>
                            <div class="col-md-3"><label class="text-muted">Volume</label><p class="fw-semibold">{{ $vehicle->volume ?? '-' }}</p></div>
                            <div class="col-md-4"><label class="text-muted">Min Load QTY</label><p class="fw-semibold">{{ $vehicle->min_load_qty ?? '-' }}</p></div>
                            <div class="col-md-4"><label class="text-muted">Status</label><p>{!! $vehicle->inactive ? '<span class="badge bg-danger">Inactive</span>' : '<span class="badge bg-success">Active</span>' !!}</p></div>
                        </div>
                        <div class="mt-4 pt-3 border-top">
                            <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-warning"><i data-feather="edit"></i> Edit</a>
                            <a href="{{ route('vehicles.index') }}" class="btn btn-secondary">Back to List</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script>feather.replace();</script>
@endpush