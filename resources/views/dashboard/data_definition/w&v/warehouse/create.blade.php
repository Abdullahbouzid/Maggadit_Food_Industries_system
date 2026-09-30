@extends('dashboard.partials.layout')

@section('title', 'Create Warehouse')

@section('content')
{{-- ===== Action Bar ===== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-plus-circle" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            Create Warehouse
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Add a new physical storage location</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('warehouses.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> Back
        </a>
    </div>
</div>

{{-- ===== Validation Errors ===== --}}
@if($errors->any())
    <div class="nova-alert danger">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <strong>Please fix the following errors:</strong>
            <ul class="nova-mb-0 nova-mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<form action="{{ route('warehouses.store') }}" method="POST" id="warehouseForm">
    @csrf

    {{-- ============================================================
         Section 1: Basic Information
         ============================================================ --}}
    <div class="nova-card nova-mb-4">
        <div class="nova-card-header">
            <h5 class="nova-fw-bold mb-0">
                <i class="fas fa-file-alt me-1"></i> Basic Information
            </h5>
        </div>
        <div class="nova-card-body">
            <div class="form-grid form-grid-3">
                <div class="nova-form-group">
                    <label class="nova-label">Code <span class="nova-text-danger">*</span></label>
                    <input type="text" name="code"
                           class="nova-input @error('code') nova-invalid @enderror"
                           value="{{ old('code') }}" required>
                    @error('code') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                <div class="nova-form-group">
                    <label class="nova-label">Name (English) <span class="nova-text-danger">*</span></label>
                    <input type="text" name="name_en"
                           class="nova-input @error('name_en') nova-invalid @enderror"
                           value="{{ old('name_en') }}" required>
                    @error('name_en') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                <div class="nova-form-group">
                    <label class="nova-label">Name (Arabic) <span class="nova-text-danger">*</span></label>
                    <input type="text" name="name_ar"
                           class="nova-input @error('name_ar') nova-invalid @enderror"
                           value="{{ old('name_ar') }}" required>
                    @error('name_ar') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                <div class="nova-form-group form-grid-span-1">
                    <label class="nova-label">Warehouse Type <span class="nova-text-danger">*</span></label>
                    <select name="warehouse_type_id"
                            class="nova-select @error('warehouse_type_id') nova-invalid @enderror" required>
                        <option value="">Select Type</option>
                        @foreach($warehouseTypes as $type)
                            <option value="{{ $type->id }}"
                                {{ old('warehouse_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name_en }}
                            </option>
                        @endforeach
                    </select>
                    @error('warehouse_type_id') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                <div class="nova-form-group form-grid-span-1">
                    <label class="nova-label">Organization <span class="nova-text-danger">*</span></label>
                    <select name="organization_id"
                            class="nova-select @error('organization_id') nova-invalid @enderror" required>
                        <option value="">Select Organization</option>
                        @foreach($organizations as $org)
                            <option value="{{ $org->id }}"
                                {{ old('organization_id') == $org->id ? 'selected' : '' }}>
                                {{ $org->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('organization_id') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                <div class="nova-form-group">
                    <label class="nova-label">Phone</label>
                    <input type="text" name="phone" class="nova-input" value="{{ old('phone') }}">
                </div>

                <div class="nova-form-group">
                    <label class="nova-label">Fax</label>
                    <input type="text" name="fax" class="nova-input" value="{{ old('fax') }}">
                </div>

                <div class="nova-form-group">
                    <label class="nova-label">Barcode</label>
                    <input type="text" name="barcode" class="nova-input" value="{{ old('barcode') }}">
                </div>

                <div class="nova-form-group form-grid-span-3">
                    <label class="nova-label">Address (English)</label>
                    <textarea name="address_en" class="nova-input" rows="2">{{ old('address_en') }}</textarea>
                </div>

                <div class="nova-form-group form-grid-span-3">
                    <label class="nova-label">Address (Arabic)</label>
                    <textarea name="address_ar" class="nova-input" rows="2">{{ old('address_ar') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         Section 2: Geographical Location
         ============================================================ --}}
    <div class="nova-card nova-mb-4">
        <div class="nova-card-header">
            <h5 class="nova-fw-bold mb-0">
                <i class="fas fa-map-marker-alt me-1"></i> Geographical Location
            </h5>
        </div>
        <div class="nova-card-body">
            <div class="form-grid form-grid-2">
                <div class="nova-form-group">
                    <label class="nova-label">Country</label>
                    <select name="country_id" id="country_id" class="nova-select">
                        <option value="">Select Country</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}"
                                {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                {{ $country->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="nova-form-group">
                    <label class="nova-label">State</label>
                    <select name="state_id" id="state_id" class="nova-select">
                        <option value="">Select State</option>
                    </select>
                </div>

                <div class="nova-form-group">
                    <label class="nova-label">City</label>
                    <select name="city_id" id="city_id" class="nova-select">
                        <option value="">Select City</option>
                    </select>
                </div>

                <div class="nova-form-group">
                    <label class="nova-label">Area</label>
                    <select name="area_id" id="area_id" class="nova-select">
                        <option value="">Select Area</option>
                    </select>
                </div>

                <div class="nova-form-group">
                    <label class="nova-label">Street</label>
                    <input type="text" name="street" class="nova-input" value="{{ old('street') }}">
                </div>

                <div class="nova-form-group">
                    <label class="nova-label">Zip Code</label>
                    <input type="text" name="zip_code" class="nova-input" value="{{ old('zip_code') }}">
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         Section 3: Destinations & Sources
         ============================================================ --}}
    <div class="nova-card nova-mb-4">
        <div class="nova-card-header">
            <h5 class="nova-fw-bold mb-0">
                <i class="fas fa-route me-1"></i> Destinations & Sources
            </h5>
        </div>
        <div class="nova-card-body">
            <div class="form-grid form-grid-1">
                <div class="nova-form-group">
                    <label class="nova-label">Destinations (comma separated)</label>
                    <input type="text" name="destinations" class="nova-input"
                           placeholder="e.g., STORE1, STORE2"
                           value="{{ old('destinations') }}">
                </div>

                <div class="nova-form-group">
                    <label class="nova-label">Sources (comma separated)</label>
                    <input type="text" name="sources" class="nova-input"
                           placeholder="e.g., SUPPLIER A, SUPPLIER B"
                           value="{{ old('sources') }}">
                </div>

                <div class="nova-form-group">
                    <label class="nova-label">Employees (comma separated)</label>
                    <input type="text" name="employees" class="nova-input"
                           placeholder="e.g., John, Ahmed, Sara"
                           value="{{ old('employees') }}">
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         Section 4: GPS Location
         ============================================================ --}}
    <div class="nova-card nova-mb-4">
        <div class="nova-card-header">
            <h5 class="nova-fw-bold mb-0">
                <i class="fas fa-map me-1"></i> GPS Location
            </h5>
        </div>
        <div class="nova-card-body">
            <div id="map" class="gps-map"></div>

            <div class="form-grid form-grid-2">
                <div class="nova-form-group">
                    <label class="nova-label">Latitude</label>
                    <input type="text" name="gps_latitude" id="latitude" class="nova-input"
                           value="{{ old('gps_latitude') }}">
                </div>

                <div class="nova-form-group">
                    <label class="nova-label">Longitude</label>
                    <input type="text" name="gps_longitude" id="longitude" class="nova-input"
                           value="{{ old('gps_longitude') }}">
                </div>
            </div>

            <button type="button" id="getLocationBtn" class="nova-btn nova-btn-outline nova-btn-sm">
                <i class="fas fa-location-crosshairs"></i> Get Current Location
            </button>
        </div>
    </div>

    {{-- ============================================================
         Section 5: Status
         ============================================================ --}}
    <div class="nova-card nova-mb-4">
        <div class="nova-card-header">
            <h5 class="nova-fw-bold mb-0">
                <i class="fas fa-toggle-on me-1"></i> Status
            </h5>
        </div>
        <div class="nova-card-body">
            <div class="form-grid form-grid-2">
                <div class="nova-form-group">
                    <label class="nova-label">Active Status</label>
                    <div class="nova-form-check status-checkbox-wrapper">
                        <input type="checkbox" name="is_active" value="1"
                               class="nova-form-check-input" id="is_active"
                               {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="nova-form-check-label" for="is_active">Active</label>
                    </div>
                </div>

                <div class="nova-form-group">
                    <label class="nova-label">Sort Order</label>
                    <input type="number" name="sort_order" class="nova-input"
                           value="{{ old('sort_order', 0) }}">
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         Form Actions
         ============================================================ --}}
    <div class="form-actions">
        <button type="submit" class="nova-btn nova-btn-primary">
            <i class="fas fa-save"></i> Create Warehouse
        </button>
        <a href="{{ route('warehouses.index') }}" class="nova-btn nova-btn-outline">
            <i class="fas fa-times"></i> Cancel
        </a>
    </div>
</form>

@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    /* ============================================================
       Form Grid System
       ============================================================ */
    .form-grid {
        display: grid;
        gap: 1.25rem;
    }

    .form-grid-1 {
        grid-template-columns: 1fr;
    }

    .form-grid-2 {
        grid-template-columns: repeat(2, 1fr);
    }

    .form-grid-3 {
        grid-template-columns: repeat(3, 1fr);
    }

    .form-grid-span-1 {
        grid-column: span 1;
    }

    .form-grid-span-2 {
        grid-column: span 2;
    }

    .form-grid-span-3 {
        grid-column: span 3;
    }

    /* Ensure form groups don't have extra margin inside grid */
    .form-grid .nova-form-group {
        margin-bottom: 0;
    }

    /* ============================================================
       GPS Map
       ============================================================ */
    .gps-map {
        height: 300px;
        border-radius: 0.75rem;
        margin-bottom: 1rem;
        overflow: hidden;
        border: 1px solid var(--nova-border);
        z-index: 1;
    }

    /* ============================================================
       Status Checkbox
       ============================================================ */
    .status-checkbox-wrapper {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0;
    }

    /* ============================================================
       Form Actions
       ============================================================ */
    .form-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        padding: 1rem 0;
        border-top: 1px solid var(--nova-border);
    }

    /* ============================================================
       Responsive
       ============================================================ */
    @media (max-width: 992px) {
        .form-grid-3 {
            grid-template-columns: repeat(2, 1fr);
        }

        .form-grid-span-3 {
            grid-column: span 2;
        }
    }

    @media (max-width: 768px) {
        .form-grid-2,
        .form-grid-3 {
            grid-template-columns: 1fr;
        }

        .form-grid-span-2,
        .form-grid-span-3 {
            grid-column: span 1;
        }

        .gps-map {
            height: 250px;
        }

        .form-actions {
            flex-direction: column;
        }

        .form-actions .nova-btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    /* ============================================================
       Map Elements
       ============================================================ */
    let map, marker;
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');

    /* ============================================================
       Map Initialization
       ============================================================ */
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

    /* ============================================================
       Get Current Location Button
       ============================================================ */
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

    /* ============================================================
       DOM Content Loaded
       ============================================================ */
    document.addEventListener('DOMContentLoaded', function() {
        initMap();

        const countrySelect = document.getElementById('country_id');
        const stateSelect = document.getElementById('state_id');
        const citySelect = document.getElementById('city_id');
        const areaSelect = document.getElementById('area_id');

        /* ============================================================
           Load States
           ============================================================ */
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

                    if (selectedStateId) {
                        const oldCityId = "{{ old('city_id') }}";
                        const oldAreaId = "{{ old('area_id') }}";
                        loadCities(selectedStateId, oldCityId);
                        loadAreas(selectedStateId, oldAreaId);
                    }
                })
                .catch(err => console.error('Error loading states:', err));
        }

        /* ============================================================
           Load Cities
           ============================================================ */
        function loadCities(stateId, selectedCityId = null) {
            citySelect.innerHTML = '<option value="">Select City</option>';
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
                })
                .catch(err => console.error('Error loading cities:', err));
        }

        /* ============================================================
           Load Areas
           ============================================================ */
        function loadAreas(stateId, selectedAreaId = null) {
            areaSelect.innerHTML = '<option value="">Select Area</option>';
            if (!stateId) return;

            fetch(`/warehouses/areas-by-state/${stateId}`)
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

        /* ============================================================
           Event Listeners
           ============================================================ */
        countrySelect.addEventListener('change', function() {
            loadStates(this.value);
        });

        stateSelect.addEventListener('change', function() {
            loadCities(this.value);
            loadAreas(this.value);
        });

        /* ============================================================
           Initial Load (Old Input)
           ============================================================ */
        const oldCountryId = "{{ old('country_id') }}";
        if (oldCountryId) {
            const oldStateId = "{{ old('state_id') }}";
            loadStates(oldCountryId, oldStateId);
        }
    });
</script>
@endpush