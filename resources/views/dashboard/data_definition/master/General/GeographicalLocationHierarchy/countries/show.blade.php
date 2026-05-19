{{-- resources/views/dashboard/data_definition/master/General/GeographicalLocationHierarchy/countries/show.blade.php --}}
@extends('dashboard.partials.layout')

@section('title', 'Country Details: ' . $country->name)

@section('content')
<div class="container-fluid p-0">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1><i data-feather="flag"></i> {{ $country->name }}</h1>
            <p class="text-muted">Complete information about {{ $country->name }} and its cities.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="{{ route('countries.edit', $country) }}" class="btn btn-warning btn-lg shadow-sm">
                <i data-feather="edit"></i> Edit
            </a>
            <a href="{{ route('geographical.index') }}" class="btn btn-secondary btn-lg shadow-sm ms-2">
                <i data-feather="arrow-left"></i> Back to Map
            </a>
        </div>
    </div>

    <!-- Country Information Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <h5 class="fw-bold mb-0"><i data-feather="info"></i> Basic Information</h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="bg-light rounded-3 p-3">
                        <div class="text-muted small text-uppercase mb-2">Country Name</div>
                        <div class="fs-4 fw-semibold">{{ $country->name }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="bg-light rounded-3 p-3">
                        <div class="text-muted small text-uppercase mb-2">Country Code</div>
                        <div class="fs-4 fw-semibold">
                            <span class="badge bg-secondary bg-opacity-25 text-dark px-3 py-2">{{ $country->code }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="bg-light rounded-3 p-3">
                        <div class="text-muted small text-uppercase mb-2">Coordinates</div>
                        <div class="fs-5">
                            @if($country->latitude && $country->longitude)
                                <i data-feather="map-pin" class="me-1"></i>
                                {{ $country->latitude }}, {{ $country->longitude }}
                            @else
                                <span class="text-muted fst-italic">— Not set —</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Map Card (shows country location) -->
    @if($country->latitude && $country->longitude)
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <h5 class="fw-bold mb-0"><i data-feather="map"></i> Location on Map</h5>
        </div>
        <div class="card-body p-1">
            <div id="countryMap" style="height: 400px; width: 100%; border-radius: 0.75rem;"></div>
        </div>
    </div>
    @endif

    <!-- Cities List -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <h5 class="fw-bold mb-0"><i data-feather="map-pin"></i> Cities in {{ $country->name }}</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>City Name</th>
                            <th>Code</th>
                            <th>Coordinates</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($country->cities as $city)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $city->name }}</td>
                            <td>{{ $city->code ?: '—' }}</td>
                            <td>
                                @if($city->latitude && $city->longitude)
                                    {{ $city->latitude }}, {{ $city->longitude }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('cities.show', $city) }}" class="btn btn-outline-info" title="View"><i data-feather="eye"></i></a>
                                    <a href="{{ route('cities.edit', $city) }}" class="btn btn-outline-warning" title="Edit"><i data-feather="edit"></i></a>
                                    <button type="button" class="btn btn-outline-danger" title="Delete" onclick="confirmDeleteCity({{ $city->id }}, '{{ addslashes($city->name) }}')">
                                        <i data-feather="trash-2"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="py-4">
                                    <i data-feather="map" style="width: 48px; height: 48px;" class="text-muted mb-3"></i>
                                    <h5 class="text-muted">No cities found</h5>
                                    <p class="text-muted">Add cities to {{ $country->name }}.</p>
                                    <a href="{{ route('cities.create', ['country_id' => $country->id]) }}" class="btn btn-primary">
                                        <i data-feather="plus-circle"></i> Add City
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete City Modal -->
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
                <form id="deleteCityForm" method="POST" action="">
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
    .table thead th { border-bottom-width: 1px; font-weight: 600; }
    .btn-group .btn { transition: all 0.2s ease; }
    .btn-group .btn:hover { transform: translateY(-2px); }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@if($country->latitude && $country->longitude)
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize map for country location
        var map = L.map('countryMap').setView([{{ $country->latitude }}, {{ $country->longitude }}], 6);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        L.marker([{{ $country->latitude }}, {{ $country->longitude }}]).addTo(map)
            .bindPopup('<strong>{{ $country->name }}</strong><br>Capital / Location')
            .openPopup();
    });
</script>
@endif
<script>
    // Delete city confirmation
    window.confirmDeleteCity = function(id, name) {
        document.getElementById('deleteCityName').innerText = name;
        document.getElementById('deleteCityForm').action = '/cities/' + id;
        var modalEl = document.getElementById('deleteCityModal');
        var modal = new bootstrap.Modal(modalEl);
        modal.show();
    };

    // Feather icons replacement
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof feather !== 'undefined') feather.replace();
    });
</script>
@endpush