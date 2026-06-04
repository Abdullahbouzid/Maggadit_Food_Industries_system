@extends('dashboard.partials.layout')

@section('title', 'City Details: ' . $city->name)

@section('content')
<div class="container-fluid p-0">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1><i data-feather="map-pin"></i> {{ $city->name }}</h1>
            <p class="text-muted">Complete information about {{ $city->name }}.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="{{ route('cities.edit', $city) }}" class="btn btn-warning btn-lg shadow-sm">
                <i data-feather="edit"></i> Edit
            </a>
            <a href="{{ route('cities.index') }}" class="btn btn-secondary btn-lg shadow-sm ms-2">
                <i data-feather="arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <!-- City Information Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <h5 class="fw-bold mb-0"><i data-feather="info"></i> Basic Information</h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="bg-light rounded-3 p-3">
                        <div class="text-muted small text-uppercase mb-2">City Name</div>
                        <div class="fs-4 fw-semibold">{{ $city->name }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="bg-light rounded-3 p-3">
                        <div class="text-muted small text-uppercase mb-2">Country</div>
                        <div class="fs-5">
                            <i data-feather="flag"></i>
                            <a href="{{ route('countries.show', $city->country) }}">{{ $city->country->name ?? '—' }}</a>
                            <span class="text-muted">({{ $city->country->code ?? '' }})</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="bg-light rounded-3 p-3">
                        <div class="text-muted small text-uppercase mb-2">State</div>
                        <div class="fs-5">{{ $city->state->name_en ?? '—' }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="bg-light rounded-3 p-3">
                        <div class="text-muted small text-uppercase mb-2">City Code</div>
                        <div class="fs-5">{{ $city->code ?: '—' }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="bg-light rounded-3 p-3">
                        <div class="text-muted small text-uppercase mb-2">Coordinates</div>
                        <div class="fs-5">
                            @if($city->latitude && $city->longitude)
                                <i data-feather="map-pin" class="me-1"></i>
                                {{ $city->latitude }}, {{ $city->longitude }}
                            @else
                                <span class="text-muted fst-italic">— Not set —</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Map Display (if coordinates exist) -->
    @if($city->latitude && $city->longitude)
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <h5 class="fw-bold mb-0"><i data-feather="map"></i> Location on Map</h5>
        </div>
        <div class="card-body p-1">
            <div id="cityMap" style="height: 400px; width: 100%; border-radius: 0.75rem;"></div>
        </div>
    </div>
    @endif

    <!-- Danger Zone -->
    <div class="card border-danger shadow-sm rounded-4">
        <div class="card-header bg-white text-danger border-0 pt-4 px-4">
            <h5 class="fw-bold"><i data-feather="alert-triangle"></i> Danger Zone</h5>
        </div>
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <p class="mb-0 text-muted">Delete this city permanently. This action cannot be undone.</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <button type="button" class="btn btn-outline-danger" onclick="confirmDeleteCity({{ $city->id }}, '{{ addslashes($city->name) }}')">
                        <i data-feather="trash-2"></i> Delete City
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteCityModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center pt-0">
                <div class="bg-danger bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3">
                    <i data-feather="alert-triangle" class="text-danger" style="width: 40px;"></i>
                </div>
                <h4 class="fw-bold">Confirm Deletion</h4>
                <p>Delete city <strong id="deleteCityName" class="text-danger"></strong>?<br>This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteCityForm" method="POST" action="{{ route('cities.destroy', $city) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger px-4">Delete Permanently</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .rounded-4 { border-radius: 1rem !important; }
    .bg-opacity-10 { opacity: 0.1; }
</style>
@endpush

@push('scripts')
@if($city->latitude && $city->longitude)
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var map = L.map('cityMap').setView([{{ $city->latitude }}, {{ $city->longitude }}], 10);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        L.marker([{{ $city->latitude }}, {{ $city->longitude }}]).addTo(map)
            .bindPopup('<strong>{{ $city->name }}</strong><br>{{ $city->country->name ?? '' }}')
            .openPopup();
    });
</script>
@endif
<script>
    function confirmDeleteCity(id, name) {
        document.getElementById('deleteCityName').innerText = name;
        // action is already set in the form, but we keep for dynamic if needed
        var modal = new bootstrap.Modal(document.getElementById('deleteCityModal'));
        modal.show();
    }
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof feather !== 'undefined') feather.replace();
    });
</script>
@endpush