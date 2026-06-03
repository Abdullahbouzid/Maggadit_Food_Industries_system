@extends('dashboard.partials.layout')

@section('title', 'Warehouse Details')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold"><i data-feather="home"></i> Warehouse: {{ $warehouse->code }}</h1>
                        <p class="text-muted">Detailed view of warehouse information</p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 pt-4">
                        <h5 class="fw-bold mb-0">Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr><th style="width: 180px;">Code</th><td><code>{{ $warehouse->code }}</code></td></tr>
                                    <tr><th>Name (English)</th><td>{{ $warehouse->name_en }}</td></tr>
                                    <tr><th>Name (Arabic)</th><td>{{ $warehouse->name_ar }}</td></tr>
                                    <tr><th>Warehouse Type</th><td>{{ $warehouse->warehouseType?->name_en ?? '-' }}</td></tr>
                                    <tr><th>Organization</th><td>{{ $warehouse->organization?->name ?? '-' }}</td></tr>
                                    <tr><th>Phone</th><td>{{ $warehouse->phone ?? '-' }}</td></tr>
                                    <tr><th>Fax</th><td>{{ $warehouse->fax ?? '-' }}</td></tr>
                                    <tr><th>Barcode</th><td>{{ $warehouse->barcode ?? '-' }}</td></tr>
                                    <tr><th>Address (EN)</th><td>{{ $warehouse->address_en ?? '-' }}</td></tr>
                                    <tr><th>Address (AR)</th><td>{{ $warehouse->address_ar ?? '-' }}</td></tr>
                                    <tr><th>Street</th><td>{{ $warehouse->street ?? '-' }}</td></tr>
                                    <tr><th>Zip Code</th><td>{{ $warehouse->zip_code ?? '-' }}</td></tr>
                                    <tr><th>GPS Location</th><td>
                                        @if($warehouse->gps_latitude && $warehouse->gps_longitude)
                                            <a href="https://www.google.com/maps?q={{ $warehouse->gps_latitude }},{{ $warehouse->gps_longitude }}" target="_blank">
                                                {{ $warehouse->gps_latitude }}, {{ $warehouse->gps_longitude }}
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </td></tr>
                                    <tr><th>Country</th><td>{{ $warehouse->country?->name ?? '-' }}</td></tr>
                                    <tr><th>State</th><td>{{ $warehouse->state?->name_en ?? '-' }}</td></tr>
                                    <tr><th>City</th><td>{{ $warehouse->city?->name ?? '-' }}</td></tr>
                                    <tr><th>Area</th><td>{{ $warehouse->area?->name_en ?? '-' }}</td></tr>
                                    <tr><th>Destinations</th><td>
                                        @if($warehouse->destinations)
                                            {{ is_array($warehouse->destinations) ? implode(', ', $warehouse->destinations) : $warehouse->destinations }}
                                        @else -
                                        @endif
                                    </td></tr>
                                    <tr><th>Sources</th><td>
                                        @if($warehouse->sources)
                                            {{ is_array($warehouse->sources) ? implode(', ', $warehouse->sources) : $warehouse->sources }}
                                        @else -
                                        @endif
                                    </td></tr>
                                    <tr><th>Employees</th><td>
                                        @if($warehouse->employees)
                                            {{ is_array($warehouse->employees) ? implode(', ', $warehouse->employees) : $warehouse->employees }}
                                        @else -
                                        @endif
                                    </td></tr>
                                    <tr><th>Status</th><td>{!! $warehouse->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td></tr>
                                    <tr><th>Sort Order</th><td>{{ $warehouse->sort_order }}</td></tr>
                                    <tr><th>Created At</th><td>{{ $warehouse->created_at->format('d M Y, h:i A') }}</td></tr>
                                    <tr><th>Last Updated</th><td>{{ $warehouse->updated_at->format('d M Y, h:i A') }}</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light border-0">
                                    <div class="card-body">
                                        <div id="map" style="height: 350px; border-radius: 0.75rem;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0">
                        <a href="{{ route('warehouses.edit', $warehouse) }}" class="btn btn-warning">Edit</a>
                        <a href="{{ route('warehouses.index') }}" class="btn btn-secondary">Back to List</a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const lat = {{ $warehouse->gps_latitude ?: '24.7136' }};
        const lng = {{ $warehouse->gps_longitude ?: '46.6753' }};
        const map = L.map('map').setView([lat, lng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
        L.marker([lat, lng]).addTo(map).bindPopup('<strong>{{ addslashes($warehouse->name_en) }}</strong>').openPopup();
        if (typeof feather !== 'undefined') feather.replace();
    });
</script>
@endpush