@extends('dashboard.partials.layout')

@section('title', 'Create Organization')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-plus-circle" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            إنشاء منظمة جديدة
        </h2>
        <p class="nova-page-subtitle">أضف منظمة جديدة إلى الهرمكية. الحقول المرمزة بـ <span class="nova-text-danger">*</span> مطلوبة.</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('organizations.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
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
            <i class="fas fa-file-alt"></i> بيانات المنظمة
        </h5>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('organizations.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="nova-row g-4">
                {{-- Organization Name --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-building me-1"></i> اسم المنظمة <span class="nova-text-danger">*</span>
                        </label>
                        <input type="text"
                               class="nova-input @error('name') nova-invalid @enderror"
                               name="name"
                               value="{{ old('name') }}"
                               placeholder="مثال: شركة ماجاديت للصناعات الغذائية"
                               required>
                    </div>
                    @error('name') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                {{-- Organization Code --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-hashtag me-1"></i> شيفرة المنظمة <span class="nova-text-danger">*</span>
                        </label>
                        <input type="text"
                               class="nova-input @error('code') nova-invalid @enderror"
                               name="code"
                               value="{{ old('code') }}"
                               placeholder="مثال: ORG-001">
                    </div>
                    @error('code') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                {{-- Parent Organization --}}
                <div class="nova-col nova-col-12">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-sitemap me-1"></i> المنظمة الأصلية
                        </label>
                        <select class="nova-select @error('parent_id') nova-invalid @enderror" name="parent_id">
                            <option value="">— لا شيء (منظمة رئيسية) —</option>
                            @foreach($organizations as $org)
                                <option value="{{ $org->id }}" {{ old('parent_id') == $org->id ? 'selected' : '' }}>
                                    {{ $org->name }} ({{ $org->code }})
                                </option>
                            @endforeach
                        </select>
                        <div class="nova-help-text">
                            <i class="fas fa-info-circle me-1"></i>
                            اتركه فارغاً إذا كانت هذه منظمة مستوى أعلى.
                        </div>
                    </div>
                    @error('parent_id') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                {{-- Organization Type --}}
                <div class="nova-col nova-col-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-tag me-1"></i> نوع المنظمة
                        </label>
                        <input type="text"
                               class="nova-input @error('organization_type') nova-invalid @enderror"
                               name="organization_type"
                               value="{{ old('organization_type') }}"
                               placeholder="مثال: شركة، فرع، قسم">
                    </div>
                    @error('organization_type') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                {{-- GLN --}}
                <div class="nova-col nova-col-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-globe me-1"></i> رقم الموقع العالمي (GLN)
                        </label>
                        <input type="text"
                               class="nova-input @error('gln') nova-invalid @enderror"
                               name="gln"
                               value="{{ old('gln') }}"
                               placeholder="مثال: 1234567890123">
                    </div>
                    @error('gln') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                {{-- Contact Section --}}
                <div class="nova-col nova-col-12">
                    <h4 class="nova-fw-semibold" style="margin: 1rem 0 1rem;">
                        <i class="fas fa-phone me-1"></i> بيانات التواصل
                    </h4>
                </div>

                {{-- Phone --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label"><i class="fas fa-phone me-1"></i> رقم الهاتف</label>
                        <input type="text" class="nova-input" name="phone" value="{{ old('phone') }}" placeholder="+966 12 345 6789">
                    </div>
                </div>

                {{-- WhatsApp --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label"><i class="fas fa-whatsapp me-1"></i> رقم الواتساب</label>
                        <input type="text" class="nova-input" name="whatsapp" value="{{ old('whatsapp') }}" placeholder="+966 55 555 5555">
                    </div>
                </div>

                {{-- Email --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label"><i class="fas fa-envelope me-1"></i> البريد الإلكتروني</label>
                        <input type="email" class="nova-input" name="email" value="{{ old('email') }}" placeholder="info@maggadit.com">
                    </div>
                </div>

                {{-- Website --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label"><i class="fas fa-globe me-1"></i> الموقع الإلكتروني</label>
                        <input type="url" class="nova-input" name="website" value="{{ old('website') }}" placeholder="https://www.maggadit.com">
                    </div>
                </div>

                {{-- Address --}}
                <div class="nova-col nova-col-12">
                    <div class="nova-form-group">
                        <label class="nova-label"><i class="fas fa-map-marker-alt me-1"></i> العنوان</label>
                        <input type="text" class="nova-input" name="address" value="{{ old('address') }}" placeholder="المدينة - الشارع">
                    </div>
                </div>

                {{-- Map Picker --}}
                <div class="nova-col nova-col-12">
                    <div class="nova-form-group">
                        <label class="nova-label"><i class="fas fa-map me-1"></i> الموقع على الخريطة</label>
                        <div id="map-picker" style="height: 350px; border-radius: 12px; z-index: 1;"></div>
                        <div class="nova-row g-3 mt-2">
                            <div class="nova-col nova-col-md-6">
                                <label class="nova-label small">خط العرض (Latitude)</label>
                                <input type="text" name="latitude" id="latitude" class="nova-input" value="{{ old('latitude') }}" placeholder="مثال: 32.8872">
                            </div>
                            <div class="nova-col nova-col-md-6">
                                <label class="nova-label small">خط الطول (Longitude)</label>
                                <input type="text" name="longitude" id="longitude" class="nova-input" value="{{ old('longitude') }}" placeholder="مثال: 13.1913">
                            </div>
                        </div>
                        <div class="nova-help-text">انقر على الخريطة لتحديد الموقع، أو أدخل الإحداثيات يدوياً</div>
                    </div>
                </div>

                {{-- Email Forwarding --}}
                <div class="nova-col nova-col-12">
                    <div class="nova-form-check">
                        <input type="checkbox" name="forward_contact_messages_to_email" class="nova-form-check-input" id="forwardEmail" value="1" {{ old('forward_contact_messages_to_email') ? 'checked' : '' }}>
                        <label class="nova-form-check-label fw-bold" for="forwardEmail">تحويل رسائل التواصل إلى البريد الإلكتروني</label>
                        <div class="nova-help-text">عند التفعيل، ستصل جميع رسائل العملاء إلى البريد المحدد تلقائياً</div>
                    </div>
                    <div class="ms-4 mt-2">
                        <label class="nova-label">البريد المستلم</label>
                        <input type="email" class="nova-input @error('contact_email_recipient') nova-invalid @enderror" name="contact_email_recipient" value="{{ old('contact_email_recipient') }}" placeholder="support@maggadit.com">
                        <div class="nova-help-text">اتركه فارغاً لاستخدام بريد المنظمة الافتراضي</div>
                        @error('contact_email_recipient') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Form إجراءات --}}
                <div class="nova-col nova-col-12">
                    <div class="nova-form-actions">
                        <a href="{{ route('organizations.index') }}" class="nova-btn nova-btn-outline">
                            <i class="fas fa-x me-1"></i> إلغاء
                        </a>
                        <button type="submit" class="nova-btn nova-btn-primary">
                            <i class="fas fa-save me-1"></i> حفظ المنظمة
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
    const defaultLat = parseFloat(document.getElementById('latitude').value) || 32.8872;
    const defaultLng = parseFloat(document.getElementById('longitude').value) || 13.1913;

    const map = L.map('map-picker').setعرض([defaultLat, defaultLng], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; مفتوحStreetMap contributors',
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
        if (marker) marker.setLatLng([lat, lng]);
        else marker = L.marker([lat, lng]).addTo(map);
    });

    [latInput, lngInput].forEach(input => {
        input.addEventListener('change', function() {
            const lat = parseFloat(latInput.value);
            const lng = parseFloat(lngInput.value);
            if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                map.setعرض([lat, lng], 13);
                if (marker) marker.setLatLng([lat, lng]);
                else marker = L.marker([lat, lng]).addTo(map);
            }
        });
    });
});
</script>
@endpush
