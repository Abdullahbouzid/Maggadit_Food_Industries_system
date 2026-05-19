@extends('dashboard.partials.layout')

@section('title', 'Edit Country')

@section('content')
<div class="container-fluid p-0">
    <div class="row mb-4">
        <div class="col">
            <h1><i data-feather="edit-2"></i> Edit Country: {{ $country->name }}</h1>
        </div>
    </div>
    <div class="card shadow-sm rounded-4">
        <div class="card-body">
            <form action="{{ route('countries.update', $country) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Country Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $country->name) }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Country Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" value="{{ old('code', $country->code) }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Latitude</label>
                        <input type="number" step="any" name="latitude" id="lat" value="{{ old('latitude', $country->latitude) }}" class="form-control" placeholder="24.774265">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Longitude</label>
                        <input type="number" step="any" name="longitude" id="lng" value="{{ old('longitude', $country->longitude) }}" class="form-control" placeholder="46.738586">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Pick Location on Map</label>
                        <div id="mapPicker" style="height: 400px; width: 100%; border-radius: 0.75rem; background: #e9ecef;"></div>
                        <div class="form-text mt-2">Click on the map or drag the marker to update coordinates.</div>
                    </div>
                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary px-5">Update Country</button>
                        <a href="{{ route('geographical.index') }}" class="btn btn-secondary px-5 ms-2">Cancel</a>
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
    (function() {
        // انتظر تحميل الصفحة بالكامل
        window.addEventListener('load', function() {
            const mapDiv = document.getElementById('mapPicker');
            if (!mapDiv) {
                console.error('Element #mapPicker not found!');
                return;
            }

            // جلب الإحداثيات الحالية من الحقول (إذا كانت موجودة)
            let latInput = document.getElementById('lat');
            let lngInput = document.getElementById('lng');
            let currentLat = (latInput && latInput.value && !isNaN(parseFloat(latInput.value))) 
                                ? parseFloat(latInput.value) 
                                : 24.774265;
            let currentLng = (lngInput && lngInput.value && !isNaN(parseFloat(lngInput.value))) 
                                ? parseFloat(lngInput.value) 
                                : 46.738586;

            // إنشاء الخريطة
            const map = L.map('mapPicker').setView([currentLat, currentLng], 6);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // إضافة علامة قابلة للسحب
            const marker = L.marker([currentLat, currentLng], { draggable: true }).addTo(map);

            // دالة تحديث الحقول
            function updateCoordinates(lat, lng) {
                if (latInput) latInput.value = lat.toFixed(6);
                if (lngInput) lngInput.value = lng.toFixed(6);
            }

            // عند سحب العلامة
            marker.on('dragend', function(e) {
                const pos = marker.getLatLng();
                updateCoordinates(pos.lat, pos.lng);
            });

            // عند النقر على الخريطة
            map.on('click', function(e) {
                marker.setLatLng(e.latlng);
                updateCoordinates(e.latlng.lat, e.latlng.lng);
            });

            // تعيين الحقول أول مرة (للتأكد)
            updateCoordinates(currentLat, currentLng);
            console.log('Edit map initialized');
        });
    })();
</script>
@endpush