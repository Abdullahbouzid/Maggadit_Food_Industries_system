@extends('dashboard.partials.layout')

@section('title', 'Create Warehouse')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold"><i data-feather="plus-circle"></i> Create Warehouse</h1>
                        <p class="text-muted">Add a new physical storage location</p>
                    </div>
                </div>

                <form action="{{ route('warehouses.store') }}" method="POST" id="warehouseForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Basic Info Card -->
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-white border-0 pt-4">
                                    <h5 class="fw-bold mb-0">Basic Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Code <span class="text-danger">*</span></label>
                                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" required>
                                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Name (English) <span class="text-danger">*</span></label>
                                            <input type="text" name="name_en" class="form-control @error('name_en') is-invalid @enderror" value="{{ old('name_en') }}" required>
                                            @error('name_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Name (Arabic) <span class="text-danger">*</span></label>
                                            <input type="text" name="name_ar" class="form-control @error('name_ar') is-invalid @enderror" value="{{ old('name_ar') }}" required>
                                            @error('name_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Warehouse Type <span class="text-danger">*</span></label>
                                            <select name="warehouse_type_id" class="form-select @error('warehouse_type_id') is-invalid @enderror" required>
                                                <option value="">Select Type</option>
                                                @foreach($warehouseTypes as $type)
                                                    <option value="{{ $type->id }}" {{ old('warehouse_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name_en }}</option>
                                                @endforeach
                                            </select>
                                            @error('warehouse_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Organization <span class="text-danger">*</span></label>
                                            <select name="organization_id" class="form-select @error('organization_id') is-invalid @enderror" required>
                                                <option value="">Select Organization</option>
                                                @foreach($organizations as $org)
                                                    <option value="{{ $org->id }}" {{ old('organization_id') == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('organization_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Phone</label>
                                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Fax</label>
                                            <input type="text" name="fax" class="form-control" value="{{ old('fax') }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Barcode</label>
                                            <input type="text" name="barcode" class="form-control" value="{{ old('barcode') }}">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Address (English)</label>
                                            <textarea name="address_en" class="form-control" rows="2">{{ old('address_en') }}</textarea>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Address (Arabic)</label>
                                            <textarea name="address_ar" class="form-control" rows="2">{{ old('address_ar') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Geographical Hierarchy -->
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-white border-0 pt-4">
                                    <h5 class="fw-bold mb-0">Geographical Location</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Country</label>
                                            <select name="country_id" id="country_id" class="form-select">
                                                <option value="">Select Country</option>
                                                @foreach($countries as $country)
                                                    <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>{{ $country->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">State</label>
                                            <select name="state_id" id="state_id" class="form-select">
                                                <option value="">Select State</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">City</label>
                                            <select name="city_id" id="city_id" class="form-select">
                                                <option value="">Select City</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Area</label>
                                            <select name="area_id" id="area_id" class="form-select">
                                                <option value="">Select Area</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Street</label>
                                            <input type="text" name="street" class="form-control" value="{{ old('street') }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Zip Code</label>
                                            <input type="text" name="zip_code" class="form-control" value="{{ old('zip_code') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Destinations / Sources / Employees -->
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-white border-0 pt-4">
                                    <h5 class="fw-bold mb-0">Destinations & Sources</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label">Destinations (comma separated)</label>
                                            <input type="text" name="destinations" class="form-control" placeholder="e.g., STORE1, STORE2" value="{{ old('destinations') }}">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Sources (comma separated)</label>
                                            <input type="text" name="sources" class="form-control" placeholder="e.g., SUPPLIER A, SUPPLIER B" value="{{ old('sources') }}">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Employees (comma separated)</label>
                                            <input type="text" name="employees" class="form-control" placeholder="e.g., John, Ahmed, Sara" value="{{ old('employees') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <!-- Map Card -->
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-white border-0 pt-4">
                                    <h5 class="fw-bold mb-0">GPS Location</h5>
                                </div>
                                <div class="card-body">
                                    <div id="map" style="height: 300px; border-radius: 0.75rem; margin-bottom: 1rem;"></div>
                                    <label class="form-label">Latitude</label>
                                    <input type="text" name="gps_latitude" id="latitude" class="form-control mb-2" value="{{ old('gps_latitude') }}">
                                    <label class="form-label">Longitude</label>
                                    <input type="text" name="gps_longitude" id="longitude" class="form-control" value="{{ old('gps_longitude') }}">
                                    <button type="button" id="getLocationBtn" class="btn btn-sm btn-outline-secondary mt-2 w-100"><i data-feather="target"></i> Get Current Location</button>
                                </div>
                            </div>

                            <!-- Status & Flags -->
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-white border-0 pt-4">
                                    <h5 class="fw-bold mb-0">Status</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">Active</label>
                                    </div>
                                    <label class="form-label">Sort Order</label>
                                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3 mb-5">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-lg">Create Warehouse</button>
                            <a href="{{ route('warehouses.index') }}" class="btn btn-secondary btn-lg">Cancel</a>
                        </div>
                    </div>
                </form>
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
    let map, marker;
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');

    function initMap() {
        if (!latInput || !lngInput) return;
        let lat = parseFloat(latInput.value) || 24.7136;
        let lng = parseFloat(lngInput.value) || 46.6753;
        map = L.map('map').setView([lat, lng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
        marker = L.marker([lat, lng], { draggable: true }).addTo(map);
        marker.on('dragend', function() {
            const pos = marker.getLatLng();
            latInput.value = pos.lat.toFixed(6);
            lngInput.value = pos.lng.toFixed(6);
        });
        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            latInput.value = e.latlng.lat.toFixed(6);
            lngInput.value = e.latlng.lng.toFixed(6);
        });
    }

    document.getElementById('getLocationBtn')?.addEventListener('click', function() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                latInput.value = lat.toFixed(6);
                lngInput.value = lng.toFixed(6);
                if (map) {
                    map.setView([lat, lng], 15);
                    marker.setLatLng([lat, lng]);
                } else {
                    initMap();
                }
            }, function() {
                alert('Unable to retrieve your location. Please check permissions.');
            });
        } else {
            alert('Geolocation is not supported by this browser.');
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        initMap();
        feather.replace();

        // Cascading dropdowns (Country → State → City → Area)
        const countrySelect = document.getElementById('country_id');
        const stateSelect = document.getElementById('state_id');
        const citySelect = document.getElementById('city_id');
        const areaSelect = document.getElementById('area_id');

        function loadStates(countryId, selectedStateId = null) {
            stateSelect.innerHTML = '<option value="">Select State</option>';
            citySelect.innerHTML = '<option value="">Select City</option>';
            areaSelect.innerHTML = '<option value="">Select Area</option>';
            if (!countryId) return;

            fetch(`/warehouses/states/${countryId}`)
                .then(res => res.json())
                .then(data => {
                    data.forEach(state => {
                        const option = document.createElement('option');
                        option.value = state.id;
                        option.textContent = state.name_en;
                        if (selectedStateId == state.id) option.selected = true;
                        stateSelect.appendChild(option);
                    });
                    if (selectedStateId) loadCities(selectedStateId, null);
                })
                .catch(err => console.error('Error loading states:', err));
        }

        function loadCities(stateId, selectedCityId = null) {
            citySelect.innerHTML = '<option value="">Select City</option>';
            areaSelect.innerHTML = '<option value="">Select Area</option>';
            if (!stateId) return;

            fetch(`/warehouses/cities/${stateId}`)
                .then(res => res.json())
                .then(data => {
                    data.forEach(city => {
                        const option = document.createElement('option');
                        option.value = city.id;
                        option.textContent = city.name;
                        if (selectedCityId == city.id) option.selected = true;
                        citySelect.appendChild(option);
                    });
                    if (selectedCityId) loadAreas(selectedCityId, null);
                })
                .catch(err => console.error('Error loading cities:', err));
        }

        function loadAreas(cityId, selectedAreaId = null) {
            areaSelect.innerHTML = '<option value="">Select Area</option>';
            if (!cityId) return;

            fetch(`/warehouses/areas/${cityId}`)
                .then(res => res.json())
                .then(data => {
                    data.forEach(area => {
                        const option = document.createElement('option');
                        option.value = area.id;
                        option.textContent = area.name_en;
                        if (selectedAreaId == area.id) option.selected = true;
                        areaSelect.appendChild(option);
                    });
                })
                .catch(err => console.error('Error loading areas:', err));
        }

        countrySelect.addEventListener('change', function() {
            loadStates(this.value);
        });

        stateSelect.addEventListener('change', function() {
            loadCities(this.value);
        });

        citySelect.addEventListener('change', function() {
            loadAreas(this.value);
        });

        // Initial load if a country is pre-selected (from old input)
        if (countrySelect.value) {
            loadStates(countrySelect.value);
        }
    });
</script>
@endpush