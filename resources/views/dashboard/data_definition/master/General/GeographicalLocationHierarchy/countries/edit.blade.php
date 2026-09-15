@extends('dashboard.partials.layout')

@section('title', 'تعديل البلد')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-edit" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            تعديل البلد
        </h2>
        <p class="nova-page-subtitle">تحديث بيانات {{ $country->name }}</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('geographical.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> العودة
        </a>
    </div>
</div>

@if($errors->any())
    <div class="nova-alert danger">
        <i class="fas fa-exclamation-triangle"></i>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="nova-card">
    <div class="nova-card-header">
        <h5 class="mb-0 fw-bold">
            <i class="fas fa-file-alt"></i> بيانات البلد
        </h5>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('countries.update', $country) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="nova-row g-4">
                {{-- Country Name --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-flag me-1"></i> اسم البلد <span class="nova-text-danger">*</span>
                        </label>
                        <input type="text" name="name" class="nova-input @error('name') nova-invalid @enderror" value="{{ old('name', $country->name) }}" required>
                    </div>
                    @error('name') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                {{-- Country Code --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-hashtag me-1"></i> شيفرة البلد <span class="nova-text-danger">*</span>
                        </label>
                        <input type="text" name="code" class="nova-input @error('code') nova-invalid @enderror" value="{{ old('code', $country->code) }}" required placeholder="مثال: SA, EG, US">
                    </div>
                    @error('code') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                {{-- Latitude --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label"><i class="fas fa-map-marker-alt me-1"></i> خط العرض</label>
                        <input type="number" step="any" name="latitude" id="lat" class="nova-input" value="{{ old('latitude', $country->latitude) }}" placeholder="24.774265">
                    </div>
                </div>

                {{-- Longitude --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label"><i class="fas fa-map-marker-alt me-1"></i> خط الطول</label>
                        <input type="number" step="any" name="longitude" id="lng" class="nova-input" value="{{ old('longitude', $country->longitude) }}" placeholder="46.738586">
                    </div>
                </div>

                {{-- Map Picker --}}
                <div class="nova-col nova-col-12">
                    <div class="nova-form-group">
                        <label class="nova-label"><i class="fas fa-map me-1"></i> الموقع على الخريطة</label>
                        <div id="mapPicker" style="height: 400px; width: 100%; border-radius: 0.75rem;"></div>
                        <div class="nova-help-text">انقر على الخريطة أو اسحب العلامة لتحديث الإحداثيات.</div>
                    </div>
                </div>

                {{-- Form إجراءات --}}
                <div class="nova-col nova-col-12">
                    <div class="nova-form-actions">
                        <a href="{{ route('geographical.index') }}" class="nova-btn nova-btn-outline">
                            <i class="fas fa-x me-1"></i> إلغاء
                        </a>
                        <button type="submit" class="nova-btn nova-btn-primary">
                            <i class="fas fa-save me-1"></i> تحديث البلد
                        </button>
                    </div>
                </div>
            </div>
        </form>
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
    const mapDiv = document.getElementById('mapPicker');
    if (!mapDiv) return;

    const latInput = document.getElementById('lat');
    const lngInput = document.getElementById('lng');
    let lat = (latInput && latInput.value && !isNaN(parseFloat(latInput.value))) ? parseFloat(latInput.value) : 24.774265;
    let lng = (lngInput && lngInput.value && !isNaN(parseFloat(lngInput.value))) ? parseFloat(lngInput.value) : 46.738586;

    const map = L.map('mapPicker').setعرض([lat, lng], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">مفتوحStreetMap</a> contributors'
    }).addTo(map);

    const marker = L.marker([lat, lng], { draggable: true }).addTo(map);

    function updateCoordinates(lat, lng) {
        if (latInput) latInput.value = lat.toFixed(6);
        if (lngInput) lngInput.value = lng.toFixed(6);
    }

    marker.on('dragend', function() {
        const pos = marker.getLatLng();
        updateCoordinates(pos.lat, pos.lng);
    });

    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        updateCoordinates(e.latlng.lat, e.latlng.lng);
    });

    updateCoordinates(lat, lng);
});
</script>
@endpush
