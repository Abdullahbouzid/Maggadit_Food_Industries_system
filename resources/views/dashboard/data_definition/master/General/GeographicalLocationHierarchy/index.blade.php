@extends('dashboard.partials.layout')

@section('title', 'Geographical Locations')

@section('content')
<div class="container-fluid p-0">
    <div class="row mb-4">
        <div class="col-12 col-lg-8">
            <h1><i data-feather="map-pin"></i> Geographical Locations</h1>
            <p class="text-muted">Countries and cities on map – manage hierarchy</p>
        </div>
        <div class="col-12 col-lg-4 text-lg-end">
            <a href="{{ route('countries.create') }}" class="btn btn-primary"><i data-feather="flag"></i> Add Country</a>
            <a href="{{ route('cities.create') }}" class="btn btn-success"><i data-feather="map"></i> Add City</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <!-- خريطة Leaflet -->
    <div class="card shadow-sm rounded-4 mb-4">
        <div class="card-header bg-light"><i data-feather="map"></i> Locations Map (Countries & Cities)</div>
        <div class="card-body p-1">
            <div id="map" style="height: 500px; width: 100%; border-radius: 0.75rem;"></div>
        </div>
    </div>

    <!-- جدولان جنباً إلى جنب -->
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow-sm rounded-4">
                <div class="card-header"><i data-feather="flag"></i> Countries</div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light"><tr><th>#</th><th>Name</th><th>Code</th><th>Coordinates</th><th class="text-end">Actions</th></tr></thead>
                        <tbody>
                            @forelse($countries as $c)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $c->name }}</td>
                                <td><span class="badge bg-secondary">{{ $c->code }}</span></td>
                                <td>@if($c->latitude) {{ $c->latitude }}, {{ $c->longitude }} @else — @endif</td>
                                <td class="text-end">
                                    <a href="{{ route('countries.show', $c) }}" class="btn btn-sm btn-outline-info"><i data-feather="eye"></i></a>
                                    <a href="{{ route('countries.edit', $c) }}" class="btn btn-sm btn-outline-warning"><i data-feather="edit"></i></a>
                                    <button class="btn btn-sm btn-outline-danger" onclick="confirmDeleteCountry({{ $c->id }}, '{{ addslashes($c->name) }}')"><i data-feather="trash-2"></i></button>
                                </td>
                            </tr>
                            @empty <tr><td colspan="5" class="text-center">No countries yet. <a href="{{ route('countries.create') }}">Create one</a></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm rounded-4">
                <div class="card-header"><i data-feather="map"></i> Cities</div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light"><tr><th>#</th><th>Name</th><th>Country</th><th>Coordinates</th><th>State</th><th class="text-end">Actions</th></tr></thead>
                        <tbody>
                            @forelse($cities as $city)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $city->name }} @if($city->code)<small>({{ $city->code }})</small>@endif</td>
                                <td>{{ $city->country->name ?? 'N/A' }}</td>
                                <td>@if($city->latitude) {{ $city->latitude }}, {{ $city->longitude }} @else — @endif</td>
                                <td>{{ $city->state->name_en ?? 'N/A' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('cities.show', $city) }}" class="btn btn-sm btn-outline-info"><i data-feather="eye"></i></a>
                                    <a href="{{ route('cities.edit', $city) }}" class="btn btn-sm btn-outline-warning"><i data-feather="edit"></i></a>
                                    <button class="btn btn-sm btn-outline-danger" onclick="confirmDeleteCity({{ $city->id }}, '{{ addslashes($city->name) }}')"><i data-feather="trash-2"></i></button>
                                </td>
                            </tr>
                            @empty <td><td colspan="5" class="text-center">No cities yet. <a href="{{ route('cities.create') }}">Create one</a></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- مودالات الحذف -->
<div class="modal fade" id="deleteCountryModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body text-center"><i data-feather="alert-triangle"></i> <h4>Confirm Deletion</h4><p>Delete <strong id="deleteCountryName"></strong> and all its cities? This cannot be undone.</p><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><form id="deleteCountryForm" method="POST" style="display:inline-block">@csrf @method('DELETE')<button type="submit" class="btn btn-danger">Delete</button></form></div></div></div></div>
<div class="modal fade" id="deleteCityModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body text-center"><i data-feather="alert-triangle"></i> <h4>Confirm Deletion</h4><p>Delete city <strong id="deleteCityName"></strong>? This cannot be undone.</p><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><form id="deleteCityForm" method="POST" style="display:inline-block">@csrf @method('DELETE')<button type="submit" class="btn btn-danger">Delete</button></form></div></div></div></div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map { z-index: 1; }
    .rounded-4 { border-radius: 1rem !important; }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    let map;
    let markers = @json($markers);

    function initMap() {
        // إحداثيات المركز الافتراضية (منتصف العالم)
        const center = { lat: 20, lng: 0 };
        map = L.map('map').setView([center.lat, center.lng], 2);
        
        // إضافة طبقة الخريطة الأساسية (OpenStreetMap)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // إضافة العلامات (markers) بناءً على البيانات المستلمة من الـ Controller
        markers.forEach(m => {
            const marker = L.marker([m.lat, m.lng]).addTo(map);
            marker.bindPopup(`<strong>${m.name}</strong><br>${m.type}`);
        });
    }

    // تأكد من تحميل الخريطة بعد اكتمال تحميل الصفحة
    document.addEventListener('DOMContentLoaded', function() {
        initMap();
        if (typeof feather !== 'undefined') feather.replace();
    });

    // دوال تأكيد الحذف (كما هي)
    window.confirmDeleteCountry = (id, name) => {
        document.getElementById('deleteCountryName').innerText = name;
        document.getElementById('deleteCountryForm').action = '/countries/' + id;
        new bootstrap.Modal(document.getElementById('deleteCountryModal')).show();
    };
    window.confirmDeleteCity = (id, name) => {
        document.getElementById('deleteCityName').innerText = name;
        document.getElementById('deleteCityForm').action = '/cities/' + id;
        new bootstrap.Modal(document.getElementById('deleteCityModal')).show();
    };
</script>
@endpush