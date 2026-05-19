@extends('dashboard.partials.layout')

@section('title', 'Add Country')

@section('content')
<div class="container-fluid p-0">
    <div class="row mb-4">
        <div class="col">
            <h1><i data-feather="flag"></i> Add Country</h1>
        </div>
    </div>
    <div class="card shadow-sm rounded-4">
        <div class="card-body">
            <form action="{{ route('countries.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Country Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Country Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" required placeholder="e.g. SA, EG, US">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Latitude</label>
                        <input type="number" step="any" name="latitude" id="lat" class="form-control" placeholder="24.774265">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Longitude</label>
                        <input type="number" step="any" name="longitude" id="lng" class="form-control" placeholder="46.738586">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Pick Location on Map</label>
                        <div id="mapPicker" style="height: 400px; width: 100%; border-radius: 0.75rem; background: #e9ecef;"></div>
                        <div class="form-text mt-2">Click on the map or drag the marker to set coordinates.</div>
                    </div>
                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary px-5">Save Country</button>
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
        // انتظر حتى يتم تحميل الصفحة بالكامل
        window.addEventListener('load', function() {
            const mapDiv = document.getElementById('mapPicker');
            if (!mapDiv) {
                console.error('Element #mapPicker not found!');
                return;
            }

            // الإحداثيات الافتراضية (الرياض)
            let defaultLat = 24.774265;
            let defaultLng = 46.738586;

            // محاولة أخذ القيم من الحقول إذا كانت موجودة (مفيد عند التعديل)
            const latInput = document.getElementById('lat');
            const lngInput = document.getElementById('lng');
            if (latInput && latInput.value && !isNaN(parseFloat(latInput.value))) {
                defaultLat = parseFloat(latInput.value);
            }
            if (lngInput && lngInput.value && !isNaN(parseFloat(lngInput.value))) {
                defaultLng = parseFloat(lngInput.value);
            }

            // إنشاء الخريطة
            const map = L.map('mapPicker').setView([defaultLat, defaultLng], 5);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // إضافة علامة قابلة للسحب
            const marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

            // دالة تحديث الحقول بالإحداثيات
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

            // تحديث الحقول في البداية إذا كانت القيم الافتراضية موجودة
            updateCoordinates(defaultLat, defaultLng);
            console.log('Map initialized successfully');
        });
    })();
</script>
@endpush