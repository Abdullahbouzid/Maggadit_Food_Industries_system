@extends('dashboard.partials.layout')

@section('title', 'Create Organization')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <!-- Header -->
                <div class="row mb-4">
                    <div class="col-12 col-lg-8">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="plus-circle" class="align-middle me-2" style="width: 32px; height: 32px;"></i>
                            Create Organization
                        </h1>
                        <p class="text-muted">Add a new organization to the hierarchy. All fields marked with <span class="text-danger">*</span> are required.</p>
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('organizations.index') }}" class="btn btn-outline-secondary shadow-sm">
                            <i data-feather="arrow-left" class="align-middle me-1"></i> Back to List
                        </a>
                    </div>
                </div>

                <!-- Form Card -->
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                            <div class="card-header bg-white border-0 py-3 px-4">
                                <h5 class="fw-bold mb-0">
                                    <i data-feather="file-text" class="align-middle me-1"></i>
                                    Organization Information
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <form action="{{ route('organizations.store') }}" method="POST">
                                    @csrf

                                    <!-- Organization Name -->
                                    <div class="mb-4">
                                        <label for="name" class="form-label fw-semibold">
                                            Organization Name <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group shadow-sm">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i data-feather="building" class="text-muted" style="width: 18px;"></i>
                                            </span>
                                            <input type="text" 
                                                   class="form-control border-start-0 @error('name') is-invalid @enderror" 
                                                   id="name" 
                                                   name="name" 
                                                   value="{{ old('name') }}" 
                                                   placeholder="e.g., Acme Corporation"
                                                   required>
                                        </div>
                                        @error('name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>

                                    <!-- Organization Code -->
                                    <div class="mb-4">
                                        <label for="code" class="form-label fw-semibold">
                                            Organization Code <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group shadow-sm">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i data-feather="hash" class="text-muted" style="width: 18px;"></i>
                                            </span>
                                            <input type="text" 
                                                   class="form-control border-start-0 @error('code') is-invalid @enderror" 
                                                   id="code" 
                                                   name="code" 
                                                   value="{{ old('code') }}" 
                                                   placeholder="Unique identifier, e.g., ORG-001"
                                                   required>
                                        </div>
                                        @error('code') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>

                                    <!-- Parent Organization -->
                                    <div class="mb-4">
                                        <label for="parent_id" class="form-label fw-semibold">
                                            Parent Organization
                                        </label>
                                        <div class="input-group shadow-sm">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i data-feather="layers" class="text-muted" style="width: 18px;"></i>
                                            </span>
                                            <select class="form-select border-start-0 @error('parent_id') is-invalid @enderror" 
                                                    id="parent_id" 
                                                    name="parent_id">
                                                <option value="">— None (Root Organization) —</option>
                                                @foreach($organizations as $org)
                                                    <option value="{{ $org->id }}" {{ old('parent_id') == $org->id ? 'selected' : '' }}>
                                                        {{ $org->name }} ({{ $org->code }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-text text-muted">
                                            <i data-feather="info" class="align-middle me-1" style="width: 12px;"></i>
                                            Leave empty if this is a top-level organization.
                                        </div>
                                        @error('parent_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>

                                    <!-- Organization Type -->
                                    <div class="mb-4">
                                        <label for="organization_type" class="form-label fw-semibold">
                                            Organization Type
                                        </label>
                                        <div class="input-group shadow-sm">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i data-feather="tag" class="text-muted" style="width: 18px;"></i>
                                            </span>
                                            <input type="text" 
                                                   class="form-control border-start-0 @error('organization_type') is-invalid @enderror" 
                                                   id="organization_type" 
                                                   name="organization_type" 
                                                   value="{{ old('organization_type') }}" 
                                                   placeholder="e.g., Corporate, Branch, Department">
                                        </div>
                                        @error('organization_type') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>

                                    <!-- GLN -->
                                    <div class="mb-4">
                                        <label for="gln" class="form-label fw-semibold">
                                            GLN (Global Location Number)
                                        </label>
                                        <div class="input-group shadow-sm">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i data-feather="globe" class="text-muted" style="width: 18px;"></i>
                                            </span>
                                            <input type="text" 
                                                   class="form-control border-start-0 @error('gln') is-invalid @enderror" 
                                                   id="gln" 
                                                   name="gln" 
                                                   value="{{ old('gln') }}" 
                                                   placeholder="13-digit GS1 identifier">
                                        </div>
                                        @error('gln') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>

                                    <!-- Contact Section Title -->
                                    <h5 class="fw-bold mt-3 mb-3 text-primary">
                                        <i data-feather="phone" class="align-middle me-1"></i>
                                        بيانات التواصل
                                    </h5>

                                    <!-- Phone -->
                                    <div class="mb-4">
                                        <label for="phone" class="form-label fw-semibold">رقم الهاتف</label>
                                        <div class="input-group shadow-sm">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i data-feather="phone" class="text-muted" style="width: 18px;"></i>
                                            </span>
                                            <input type="text" class="form-control border-start-0" id="phone" name="phone" value="{{ old('phone') }}" placeholder="+966 12 345 6789">
                                        </div>
                                    </div>

                                    <!-- WhatsApp -->
                                    <div class="mb-4">
                                        <label for="whatsapp" class="form-label fw-semibold">رقم الواتساب</label>
                                        <div class="input-group shadow-sm">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i data-feather="message-circle" class="text-muted" style="width: 18px;"></i>
                                            </span>
                                            <input type="text" class="form-control border-start-0" id="whatsapp" name="whatsapp" value="{{ old('whatsapp') }}" placeholder="+966 55 555 5555">
                                        </div>
                                    </div>

                                    <!-- Email -->
                                    <div class="mb-4">
                                        <label for="email" class="form-label fw-semibold">البريد الإلكتروني</label>
                                        <div class="input-group shadow-sm">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i data-feather="mail" class="text-muted" style="width: 18px;"></i>
                                            </span>
                                            <input type="email" class="form-control border-start-0" id="email" name="email" value="{{ old('email') }}" placeholder="info@maggadit.com">
                                        </div>
                                    </div>

                                    <!-- Website -->
                                    <div class="mb-4">
                                        <label for="website" class="form-label fw-semibold">موقع الشركة</label>
                                        <div class="input-group shadow-sm">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i data-feather="globe" class="text-muted" style="width: 18px;"></i>
                                            </span>
                                            <input type="url" class="form-control border-start-0" id="website" name="website" value="{{ old('website') }}" placeholder="https://www.maggadit.com">
                                        </div>
                                    </div>

                                    <!-- Address -->
                                    <div class="mb-4">
                                        <label for="address" class="form-label fw-semibold">العنوان</label>
                                        <div class="input-group shadow-sm">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i data-feather="map-pin" class="text-muted" style="width: 18px;"></i>
                                            </span>
                                            <input type="text" class="form-control border-start-0" id="address" name="address" value="{{ old('address') }}" placeholder="المدينة - الشارع">
                                        </div>
                                    </div>

                                    <!-- Map Picker -->
                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">الموقع على الخريطة</label>
                                        <div id="map-picker" style="height: 350px; border-radius: 12px; z-index: 1;"></div>
                                        <div class="row mt-2">
                                            <div class="col-md-6">
                                                <label class="form-label small">خط العرض (Latitude)</label>
                                                <input type="text" name="latitude" id="latitude" class="form-control" value="{{ old('latitude') }}" placeholder="مثال: 32.8872">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small">خط الطول (Longitude)</label>
                                                <input type="text" name="longitude" id="longitude" class="form-control" value="{{ old('longitude') }}" placeholder="مثال: 13.1913">
                                            </div>
                                        </div>
                                        <div class="form-text">انقر على الخريطة لتحديد الموقع، أو أدخل الإحداثيات يدوياً</div>
                                    </div>

                                    <!-- Email Forwarding Toggle -->
                                    <div class="card border mb-4">
                                        <div class="card-body">
                                            <div class="form-check form-switch mb-3">
                                                <input type="checkbox" name="forward_contact_messages_to_email" class="form-check-input" id="forwardEmail" value="1" {{ old('forward_contact_messages_to_email') ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold" for="forwardEmail">تحويل رسائل التواصل إلى البريد الإلكتروني</label>
                                                <div class="form-text">عند التفعيل، ستصل جميع رسائل العملاء إلى البريد المحدد تلقائياً</div>
                                            </div>
                                            <div class="ms-4">
                                                <label class="form-label fw-semibold">البريد المستلم</label>
                                                <input type="email" class="form-control @error('contact_email_recipient') is-invalid @enderror" name="contact_email_recipient" value="{{ old('contact_email_recipient') }}" placeholder="support@maggadit.com">
                                                <div class="form-text">اتركه فارغاً لاستخدام بريد المنظمة الافتراضي</div>
                                                @error('contact_email_recipient') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="d-flex gap-2 justify-content-end mt-4 pt-2 border-top">
                                        <a href="{{ route('organizations.index') }}" class="btn btn-light px-4 shadow-sm">
                                            Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary px-5 shadow-sm">
                                            <i data-feather="save" class="align-middle me-1"></i> Save Organization
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .rounded-4 {
        border-radius: 1rem !important;
    }
    .input-group-text {
        background-color: #fff;
    }
    .form-control:focus, .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13,110,253,.15);
    }
    #map-picker {
        border: 2px solid #e5e7eb;
        border-radius: 12px;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        feather.replace();
        
        const defaultLat = parseFloat(document.getElementById('latitude').value) || 32.8872;
        const defaultLng = parseFloat(document.getElementById('longitude').value) || 13.1913;
        
        const map = L.map('map-picker').setView([defaultLat, defaultLng], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19,
        }).addTo(map);
        
        let marker = null;
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        
        if (latInput.value && lngInput.value) {
            marker = L.marker([parseFloat(latInput.value), parseFloat(lngInput.value)]).addTo(map);
        }
        
        map.on('click', function(e) {
            const { lat, lng } = e.latlng;
            latInput.value = lat.toFixed(7);
            lngInput.value = lng.toFixed(7);
            
            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng]).addTo(map);
            }
        });
        
        [latInput, lngInput].forEach(input => {
            input.addEventListener('change', function() {
                const lat = parseFloat(latInput.value);
                const lng = parseFloat(lngInput.value);
                if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                    map.setView([lat, lng], 13);
                    if (marker) {
                        marker.setLatLng([lat, lng]);
                    } else {
                        marker = L.marker([lat, lng]).addTo(map);
                    }
                }
            });
        });
    });
</script>
@endpush