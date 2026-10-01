@extends('dashboard.partials.layout')

@section('title', 'تفاصيل البلد: ' . $country->name)

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-info-circle" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            تفاصيل البلد
        </h2>
        <p class="nova-page-subtitle">{{ $country->name }}</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('countries.edit', $country) }}" class="nova-btn nova-btn-warning nova-btn-sm">
            <i class="fas fa-edit"></i> تعديل
        </a>
        <a href="{{ route('geographical.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> العودة للخريطة
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
                <td>{{ $country->id }}</td>
            </tr>
            <tr>
                <th>اسم البلد</th>
                <td>
                    <span class="nova-fw-semibold">{{ $country->name }}</span>
                </td>
            </tr>
            <tr>
                <th>شيفرة البلد</th>
                <td><span class="nova-badge secondary">{{ $country->code }}</span></td>
            </tr>
            <tr>
                <th>الإحداثيات</th>
                <td>
                    @if($country->latitude && $country->longitude)
                        <i class="fas fa-map-marker-alt nova-text-muted me-1"></i>
                        {{ $country->latitude }}, {{ $country->longitude }}
                    @else
                        <span class="nova-text-muted fst-italic">— غير محدد —</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>تاريخ الإنشاء</th>
                <td>
                    <i class="fas fa-calendar nova-text-muted me-1"></i>
                    {{ $country->created_at ? $country?->created_at?->format('F d, Y h:i A') ?? '—' : '—' }}
                </td>
            </tr>
            <tr>
                <th>آخر تحديث</th>
                <td>
                    <i class="fas fa-history nova-text-muted me-1"></i>
                    {{ $country->updated_at ? $country?->updated_at?->format('F d, Y h:i A') ?? '—' : '—' }}
                </td>
            </tr>
        </table>
    </div>
</div>

{{-- Map Card --}}
@if($country->latitude && $country->longitude)
<div class="nova-card nova-mt-4">
    <div class="nova-card-header">
        <h5 class="mb-0 fw-bold">
            <i class="fas fa-map"></i> الموقع على الخريطة
        </h5>
    </div>
    <div class="nova-card-body" style="padding: 0;">
        <div id="countryMap" style="height: 400px; width: 100%;"></div>
    </div>
</div>
@endif

{{-- Cities List --}}
<div class="nova-card nova-mt-4">
    <div class="nova-card-header">
        <h5 class="mb-0 fw-bold">
            <i class="fas fa-map-marker-alt"></i> مدن في {{ $country->name }}
        </h5>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>اسم المدينة</th>
                    <th>الشيفرة</th>
                    <th>الإحداثيات</th>
                    <th class="nova-text-end">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($country->cities as $city)
                <tr>
                    <td class="nova-fw-semibold">{{ $loop->iteration }}</td>
                    <td>{{ $city->name }}</td>
                    <td>{{ $city->code ?: '—' }}</td>
                    <td>
                        @if($city->latitude && $city->longitude)
                            {{ $city->latitude }}, {{ $city->longitude }}
                        @else
                            <span class="nova-text-muted">—</span>
                        @endif
                    </td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                            <a href="{{ route('cities.show', $city) }}" class="nova-icon-btn nova-btn-sm" title="عرض">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('cities.edit', $city) }}" class="nova-icon-btn nova-btn-sm" title="تعديل">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger" title="حذف" onclick="confirmDeleteCity({{ $city->id }}, '{{ addslashes($city->name) }}')">
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
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <h3 class="nova-empty-title">لا توجد مدن</h3>
                            <p class="nova-empty-text">أضف مدن إلى {{ $country->name }}.</p>
                            <a href="{{ route('cities.create', ['country_id' => $country->id]) }}" class="nova-btn nova-btn-primary">
                                <i class="fas fa-map-marker-alt"></i> إضافة مدينة
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@if($country->latitude && $country->longitude)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const map = L.map('countryMap').setView([{{ $country->latitude }}, {{ $country->longitude }}], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    L.marker([{{ $country->latitude }}, {{ $country->longitude }}]).addTo(map)
        .bindPopup('<strong>{{ $country->name }}</strong><br>{{ $country->code }}')
        .openPopup();
});
</script>
@endif
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.confirmDeleteCity = function(id, name) {
        document.getElementById('deleteCityName').innerText = name;
        document.getElementById('deleteCityForm').action = '/cities/' + id;
        document.getElementById('deleteCityModal').classList.add('show');
    };
});
</script>
@endpush

{{-- Delete City Modal --}}
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
