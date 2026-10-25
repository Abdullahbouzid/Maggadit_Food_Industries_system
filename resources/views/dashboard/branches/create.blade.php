@extends('dashboard.partials.layout')

@section('title', 'إضافة فرع')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-plus" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            إضافة فرع
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">أدخل بيانات الفرع الجديد</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('branches.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> العودة
        </a>
    </div>
</div>

@if($errors->any())
    <div class="nova-alert danger">
        <i class="fas fa-exclamation-triangle"></i>
        <h6 class="nova-fw-bold" style="margin: 0 0 0.25rem;">يرجى تصحيح الأخطاء التالية:</h6>
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('branches.store') }}" method="POST">
    @csrf
    <div class="nova-card">
        <div class="nova-card-header">
            <h3 class="nova-card-title">
                <i class="fas fa-file-alt me-1"></i> بيانات الفرع
            </h3>
        </div>
        <div class="nova-card-body">

            {{-- 2-Column Grid Form --}}
            <div class="branch-form-grid">

                {{-- Row 1 --}}
                <div class="nova-form-group">
                    <label class="nova-label">
                        <i class="fas fa-user me-1"></i> العميل <span class="nova-text-danger">*</span>
                    </label>
                    <select name="customer_id" class="nova-select @error('customer_id') nova-invalid @enderror" required>
                        <option value="">— اختر العميل —</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ (isset($customer) && $customer->id == $c->id) || old('customer_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('customer_id') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                <div class="nova-form-group">
                    <label class="nova-label">
                        <i class="fas fa-store me-1"></i> اسم الفرع <span class="nova-text-danger">*</span>
                    </label>
                    <input type="text"
                           class="nova-input @error('name') nova-invalid @enderror"
                           name="name"
                           value="{{ old('name') }}"
                           placeholder="أدخل اسم الفرع..."
                           required>
                    @error('name') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                {{-- Row 2 --}}
                <div class="nova-form-group">
                    <label class="nova-label">
                        <i class="fas fa-phone me-1"></i> رقم الهاتف
                    </label>
                    <input type="tel"
                           class="nova-input @error('phone') nova-invalid @enderror"
                           name="phone"
                           value="{{ old('phone') }}"
                           placeholder="+218 9X XXX XXXX"
                           dir="ltr">
                    @error('phone') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                <div class="nova-form-group">
                    <label class="nova-label">
                        <i class="fas fa-toggle-on me-1"></i> الحالة
                    </label>
                    <div class="nova-form-check status-check">
                        <label class="nova-form-check-label">
                            <input type="checkbox"
                                   name="is_active"
                                   value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}
                                   class="nova-form-check-input">
                            <span class="nova-form-check-text"> الفرع نشط</span>
                        </label>
                    </div>
                </div>

                {{-- Row 3 --}}
                <div class="nova-form-group">
                    <label class="nova-label">
                        <i class="fas fa-clock me-1"></i> توقيت العمل من
                    </label>
                    <input type="time"
                           class="nova-input @error('working_hours_from') nova-invalid @enderror"
                           name="working_hours_from"
                           value="{{ old('working_hours_from') }}">
                    @error('working_hours_from') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                <div class="nova-form-group">
                    <label class="nova-label">
                        <i class="fas fa-clock me-1"></i> توقيت العمل إلى
                    </label>
                    <input type="time"
                           class="nova-input @error('working_hours_to') nova-invalid @enderror"
                           name="working_hours_to"
                           value="{{ old('working_hours_to') }}">
                    @error('working_hours_to') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                {{-- Row 4: Address (Full Width) --}}
                <div class="nova-form-group form-full">
                    <label class="nova-label">
                        <i class="fas fa-map-marker-alt me-1"></i> العنوان
                    </label>
                    <textarea class="nova-input @error('address') nova-invalid @enderror"
                              name="address"
                              rows="2"
                              placeholder="أدخل العنوان الكامل...">{{ old('address') }}</textarea>
                    @error('address') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                {{-- Row 5: GPS Section (Full Width) --}}
                <div class="nova-form-group form-full">
                    <div class="gps-section">
                        <div class="gps-section-header">
                            <label class="nova-label" style="margin: 0;">
                                <i class="fas fa-map me-1"></i> الموقع الجغرافي (GPS)
                            </label>
                            <button type="button" id="getLocationBtn" class="nova-btn nova-btn-outline nova-btn-sm">
                                <i class="fas fa-location-crosshairs"></i> تحديد موقعي
                            </button>
                        </div>

                        <div id="map" class="gps-map"></div>

                        <div class="gps-coords-grid">
                            <div class="nova-form-group">
                                <label class="nova-label">
                                    <i class="fas fa-crosshairs me-1"></i> خط العرض (Latitude)
                                </label>
                                <input type="text" name="latitude" id="latitude"
                                       class="nova-input @error('latitude') nova-invalid @enderror"
                                       value="{{ old('latitude') }}"
                                       dir="ltr"
                                       placeholder="24.713600">
                                @error('latitude') <div class="nova-form-error">{{ $message }}</div> @enderror
                            </div>

                            <div class="nova-form-group">
                                <label class="nova-label">
                                    <i class="fas fa-crosshairs me-1"></i> خط الطول (Longitude)
                                </label>
                                <input type="text" name="longitude" id="longitude"
                                       class="nova-input @error('longitude') nova-invalid @enderror"
                                       value="{{ old('longitude') }}"
                                       dir="ltr"
                                       placeholder="46.675300">
                                @error('longitude') <div class="nova-form-error">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Row 6: Form Actions (Full Width) --}}
                <div class="form-full">
                    <div class="nova-form-actions">
                        <a href="{{ route('branches.index') }}" class="nova-btn nova-btn-outline">
                            <i class="fas fa-x me-1"></i> إلغاء
                        </a>
                        <button type="submit" class="nova-btn nova-btn-primary">
                            <i class="fas fa-save me-1"></i> حفظ الفرع
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    /* ============================================
       Two-Column Form Grid
       ============================================ */
    .branch-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem 1.5rem;
        align-items: start;
    }

    .branch-form-grid .nova-form-group {
        margin-bottom: 0;
    }

    /* العناصر التي تأخذ عرض الصف كامل */
    .branch-form-grid .form-full {
        grid-column: 1 / -1;
    }

    /* Checkbox status alignment */
    .status-check {
        display: flex;
        align-items: center;
        min-height: 42px;
        padding: 0 0.25rem;
    }

    /* ============================================
       GPS Section
       ============================================ */
    .gps-section {
        border: 1px solid var(--nova-border);
        border-radius: 0.75rem;
        padding: 1rem;
        background: var(--nova-bg-soft, #fafbfc);
    }

    .gps-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 0.75rem;
        flex-wrap: wrap;
    }

    .gps-map {
        height: 300px;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
        overflow: hidden;
        border: 1px solid var(--nova-border);
        z-index: 1;
    }

    /* Coordinates: 2 columns inside GPS */
    .gps-coords-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .gps-coords-grid .nova-form-group {
        margin-bottom: 0;
    }

    /* ============================================
       Responsive
       ============================================ */
    @media (max-width: 768px) {
        .branch-form-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .gps-coords-grid {
            grid-template-columns: 1fr;
        }

        .gps-map {
            height: 250px;
        }

        .gps-section-header {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    /* Map Elements */
    let map, marker;
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');

    /* Map Initialization */
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

    /* Get Current Location Button */
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
                alert('تعذر الحصول على موقعك. يرجى التحقق من الأذونات.');
            });
        } else {
            alert('الموقع الجغرافي غير مدعوم في هذا المتصفح.');
        }
    });

    /* DOM Content Loaded */
    document.addEventListener('DOMContentLoaded', function() {
        initMap();
    });
</script>
@endpush