@extends('dashboard.partials.layout')

@section('title', 'Add City')

@section('content')
<div class="container-fluid p-0">
    <div class="row mb-4">
        <div class="col">
            <h1><i data-feather="map"></i> Add City</h1>
            <p class="text-muted">Add a new city to an existing country and set its location on map.</p>
        </div>
    </div>

    <div class="card shadow-sm rounded-4">
        <div class="card-body">
            <form action="{{ route('cities.store') }}" method="POST">
                @csrf
                <div class="row g-4">
                    <!-- Country Selection -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Country <span class="text-danger">*</span></label>
                        <select name="country_id" class="form-select @error('country_id') is-invalid @enderror" required>
                            <option value="">-- Select Country --</option>
                            @foreach($countries as $c)
                                <option value="{{ $c->id }}" {{ request('country_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} ({{ $c->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('country_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- City Name -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">City Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- City Code -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">City Code</label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="Optional, e.g., RUH, CAI">
                        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Latitude & Longitude (hidden fields but visible) -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Latitude</label>
                        <input type="number" step="any" name="latitude" id="lat" class="form-control" value="{{ old('latitude') }}" readonly style="background-color: #f8f9fa;">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Longitude</label>
                        <input type="number" step="any" name="longitude" id="lng" class="form-control" value="{{ old('longitude') }}" readonly style="background-color: #f8f9fa;">
                    </div>

                    <!-- Map Picker -->
                    <div class="col-12">
                        <label class="form-label fw-semibold">Pick Location on Map</label>
                        <div id="mapPicker" style="height: 400px; width: 100%; border-radius: 0.75rem; background: #e9ecef;"></div>
                        <div class="form-text mt-2">Click on the map or drag the marker to set coordinates.</div>
                    </div>

                    <!-- Submit -->
                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary px-5">
                            <i data-feather="save"></i> Save City
                        </button>
                        <a href="{{ route('cities.index') }}" class="btn btn-secondary px-5 ms-2">
                            <i data-feather="x-circle"></i> Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #mapPicker { height: 400px; width: 100%; border-radius: 0.75rem; z-index: 1; }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mapDiv = document.getElementById('mapPicker');
        if (!mapDiv) return;

        // Default center (Riyadh)
        let defaultLat = 24.774265;
        let defaultLng = 46.738586;

        const map = L.map('mapPicker').setView([defaultLat, defaultLng], 5);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        const marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

        function updateFields(lat, lng) {
            document.getElementById('lat').value = lat.toFixed(6);
            document.getElementById('lng').value = lng.toFixed(6);
        }

        marker.on('dragend', function(e) {
            const pos = marker.getLatLng();
            updateFields(pos.lat, pos.lng);
        });

        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            updateFields(e.latlng.lat, e.latlng.lng);
        });

        // Initialize fields
        updateFields(defaultLat, defaultLng);
    });
</script>
@endpush