@extends('dashboard.partials.layout')

@section('title', 'المواقع الجغرافية')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-map-pin" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            المواقع الجغرافية
        </h2>
        <p class="nova-page-subtitle">البلدان والمدن على الخريطة - إدارة الهرمكية</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('countries.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-flag"></i> إضافة بلد
        </a>
        <a href="{{ route('cities.create') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-map-marker-alt"></i> إضافة مدينة
        </a>
    </div>
</div>

@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

{{-- Map Card --}}
<div class="nova-card mb-4">
    <div class="nova-card-header">
        <h3 class="nova-card-title" style="margin: 0;">
            <i class="fas fa-map"></i> خريطة المواقع (البلدان والمدن)
        </h3>
    </div>
    <div class="nova-card-body" style="padding: 0;">
        <div id="map" style="height: 500px; width: 100%;"></div>
    </div>
</div>

{{-- Side-by-side tables --}}
<div class="nova-row cols-1 cols-lg-2">
    {{-- Countries --}}
    <div class="nova-card">
        <div class="nova-card-header">
            <h3 class="nova-card-title" style="margin: 0;">
                <i class="fas fa-flag"></i> البلدان
            </h3>
        </div>
        <div class="nova-table-scroll">
            <table class="nova-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الاسم</th>
                        <th>الشيفرة</th>
                        <th>الإحداثيات</th>
                        <th class="nova-text-end">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($countries as $c)
                    <tr>
                        <td class="nova-fw-semibold">{{ $loop->iteration }}</td>
                        <td>{{ $c->name }}</td>
                        <td><span class="nova-badge secondary">{{ $c->code }}</span></td>
                        <td>@if($c->latitude) {{ $c->latitude }}, {{ $c->longitude }} @else <span class="nova-text-muted">—</span> @endif</td>
                        <td class="nova-text-end">
                            <div class="nova-table-actions">
                                <a href="{{ route('countries.show', $c) }}" class="nova-icon-btn nova-btn-sm" title="عرض">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('countries.edit', $c) }}" class="nova-icon-btn nova-btn-sm" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger" title="حذف" onclick="confirmحذفCountry({{ $c->id }}, '{{ addslashes($c->name) }}')">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">
                            <div class="nova-empty">
                                <div class="nova-empty-icon">
                                    <i class="fas fa-flag"></i>
                                </div>
                                <h3 class="nova-empty-title">لا توجد بلدان بعد</h3>
                                <a href="{{ route('countries.create') }}" class="nova-btn nova-btn-primary">
                                    <i class="fas fa-flag"></i> إنشاء بلد
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

    {{-- Cities --}}
    <div class="nova-card">
        <div class="nova-card-header">
            <h3 class="nova-card-title" style="margin: 0;">
                <i class="fas fa-map-marker-alt"></i> المدن
            </h3>
        </div>
        <div class="nova-table-scroll">
            <table class="nova-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الاسم</th>
                        <th>البلد</th>
                        <th>الإحداثيات</th>
                        <th>الولاية</th>
                        <th class="nova-text-end">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cities as $city)
                    <tr>
                        <td class="nova-fw-semibold">{{ $loop->iteration }}</td>
                        <td>{{ $city->name }} @if($city->code)<small class="nova-text-muted">({{ $city->code }})</small>@endif</td>
                        <td>{{ $city->country->name ?? '—' }}</td>
                        <td>@if($city->latitude && $city->longitude) {{ $city->latitude }}, {{ $city->longitude }} @else <span class="nova-text-muted">—</span> @endif</td>
                        <td>{{ $city->state->name_en ?? '—' }}</td>
                        <td class="nova-text-end">
                            <div class="nova-table-actions">
                                <a href="{{ route('cities.show', $city) }}" class="nova-icon-btn nova-btn-sm" title="عرض">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('cities.edit', $city) }}" class="nova-icon-btn nova-btn-sm" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger" title="حذف" onclick="confirmحذفCity({{ $city->id }}, '{{ addslashes($city->name) }}')">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="nova-empty">
                                <div class="nova-empty-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <h3 class="nova-empty-title">لا توجد مدن بعد</h3>
                                <a href="{{ route('cities.create') }}" class="nova-btn nova-btn-primary">
                                    <i class="fas fa-map-marker-alt"></i> إنشاء مدينة
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

{{-- حذف Country Modal --}}
<div class="nova-modal-backdrop" id="deleteCountryModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">تأكيد الحذف</h4>
            <p class="nova-delete-modal-text">هل أنت متأكد أنك تريد حذف <strong id="deleteCountryName" class="nova-text-danger"></strong> وجميع مدنها؟<br>لا يمكن التراجع عن هذا الإجراء.</p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('deleteCountryModal').classList.remove('show')">إلغاء</button>
                <form id="deleteCountryForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="nova-btn nova-btn-danger">حذف نهائي</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- حذف City Modal --}}
<div class="nova-modal-backdrop" id="deleteCityModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">تأكيد الحذف</h4>
            <p class="nova-delete-modal-text">هل أنت متأكد أنك تريد حذف المدينة <strong id="deleteCityName" class="nova-text-danger"></strong>؟<br>لا يمكن التراجع عن هذا الإجراء.</p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('deleteCityModal').classList.remove('show')">إلغاء</button>
                <form id="deleteCityForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="nova-btn nova-btn-danger">حذف نهائي</button>
                </form>
            </div>
        </div>
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
    const markers = @json($markers);
    let map;

    if (typeمن L !== 'undefined') {
        const center = { lat: 20, lng: 0 };
        map = L.map('map').setعرض([center.lat, center.lng], 2);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">مفتوحStreetMap</a> contributors'
        }).addTo(map);
        markers.forEach(m => {
            L.marker([m.lat, m.lng]).addTo(map)
                .bindPopup(`<strong>${m.name}</strong><br>${m.type}`);
        });
    }

    window.confirmحذفCountry = function(id, name) {
        document.getElementById('deleteCountryName').innerText = name;
        document.getElementById('deleteCountryForm').action = '/countries/' + id;
        document.getElementById('deleteCountryModal').classList.add('show');
    };

    window.confirmحذفCity = function(id, name) {
        document.getElementById('deleteCityName').innerText = name;
        document.getElementById('deleteCityForm').action = '/cities/' + id;
        document.getElementById('deleteCityModal').classList.add('show');
    };

    setTimeout(function() {
        document.querySelectorالكل('.nova-alert').forEach(function(alert) {
            alert.style.transition = 'opacity 0.3s, transform 0.3s';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        });
    }, 5000);
});
</script>
@endpush
