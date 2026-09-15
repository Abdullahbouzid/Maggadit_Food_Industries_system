@extends('dashboard.partials.layout')

@section('title', 'تفاصيل المدينة: ' . $city->name)

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-info-circle" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            تفاصيل المدينة
        </h2>
        <p class="nova-page-subtitle">{{ $city->name }}</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('cities.edit', $city) }}" class="nova-btn nova-btn-warning nova-btn-sm">
            <i class="fas fa-edit"></i> تعديل
        </a>
        <a href="{{ route('cities.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> العودة للقائمة
        </a>
    </div>
</div>

@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

<div class="nova-card">
    <div class="nova-card-header">
        <h5 class="mb-0 fw-bold">
            <i class="fas fa-file-alt"></i> المعلومات الأساسية
        </h5>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <tr>
                <th style="width: 30%;">المعرف</th>
                <td>{{ $city->id }}</td>
            </tr>
            <tr>
                <th>اسم المدينة</th>
                <td>
                    <div class="nova-flex nova-items-center gap-2">
                        <div class="nova-stat-icon primary" style="width: 32px; height: 32px;">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <span>{{ $city->name }}</span>
                    </div>
                </td>
            </tr>
            <tr>
                <th>البلد</th>
                <td>
                    <i class="fas fa-flag nova-text-muted me-1"></i>
                    <a href="{{ route('countries.show', $city->country) }}" class="nova-text-primary fw-semibold">{{ $city->country->name ?? '—' }}</a>
                    @if($city->country->code ?? '')
                        <span class="nova-text-muted">({{ $city->country->code }})</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>الولاية</th>
                <td>{{ $city->state->name_en ?? '—' }}</td>
            </tr>
            <tr>
                <th>شيفرة المدينة</th>
                <td>{{ $city->code ?: '<span class="nova-text-muted fst-italic">—</span>' }}</td>
            </tr>
            <tr>
                <th>الإحداثيات</th>
                <td>
                    @if($city->latitude && $city->longitude)
                        <i class="fas fa-map-marker-alt nova-text-muted me-1"></i>
                        {{ $city->latitude }}, {{ $city->longitude }}
                    @else
                        <span class="nova-text-muted fst-italic">— غير محدد —</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>تاريخ الإنشاء</th>
                <td>
                    <i class="fas fa-calendar nova-text-muted me-1"></i>
                    {{ $city->created_at ? $city->created_at->format('F d, Y h:i A') : '—' }}
                </td>
            </tr>
            <tr>
                <th>آخر تحديث</th>
                <td>
                    <i class="fas fa-history nova-text-muted me-1"></i>
                    {{ $city->updated_at ? $city->updated_at->format('F d, Y h:i A') : '—' }}
                </td>
            </tr>
        </table>
    </div>
</div>

{{-- Map Display --}}
@if($city->latitude && $city->longitude)
<div class="nova-card nova-mt-4">
    <div class="nova-card-header">
        <h5 class="mb-0 fw-bold">
            <i class="fas fa-map"></i> الموقع على الخريطة
        </h5>
    </div>
    <div class="nova-card-body" style="padding: 0;">
        <div id="cityMap" style="height: 400px; width: 100%;"></div>
    </div>
</div>
@endif

{{-- Danger Zone --}}
<div class="nova-card nova-mt-4 nova-card-danger">
    <div class="nova-card-header" style="background: var(--nova-danger-bg); color: var(--nova-text-danger);">
        <h5 class="mb-0 fw-bold">
            <i class="fas fa-exclamation-triangle"></i> منطقة الخطر
        </h5>
    </div>
    <div class="nova-card-body">
        <div class="nova-flex nova-items-center nova-justify-between nova-gap-2">
            <div>
                <p class="nova-text-muted mb-0">حذف هذه المدينة نهائياً. لا يمكن التراجع عن هذا الإجراء.</p>
            </div>
            <button type="button" class="nova-btn nova-btn-outline nova-text-danger"
                    onclick="confirmحذف({{ $city->id }}, '{{ addslashes($city->name) }}')">
                <i class="fas fa-trash-alt"></i> حذف المدينة
            </button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@if($city->latitude && $city->longitude)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const map = L.map('cityMap').setعرض([{{ $city->latitude }}, {{ $city->longitude }}], 10);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">مفتوحStreetMap</a> contributors'
    }).addTo(map);
    L.marker([{{ $city->latitude }}, {{ $city->longitude }}]).addTo(map)
        .bindPopup('<strong>{{ $city->name }}</strong><br>{{ $city->country->name ?? '' }}')
        .openPopup();
});
</script>
@endif
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.confirmحذف = function(id, name) {
        document.getElementById('deleteName').innerText = name;
        document.getElementById('deleteForm').action = '/cities/' + id;
        document.getElementById('deleteModal').classList.add('show');
    };
});
</script>
@endpush

{{-- حذف تأكيدation Modal (لاva) --}}
<div class="nova-modal-backdrop" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">تأكيد الحذف</h4>
            <p class="nova-delete-modal-text">هل أنت متأكد أنك تريد حذف المدينة <strong id="deleteName" class="nova-text-danger"></strong>؟<br>لا يمكن التراجع عن هذا الإجراء.</p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('deleteModal').classList.remove('show')">إلغاء</button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="nova-btn nova-btn-danger">حذف نهائي</button>
                </form>
            </div>
        </div>
    </div>
</div>
