@extends('dashboard.partials.layout')

@section('title', 'قائمة المدن')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-map-marker-alt" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            جميع المدن
        </h2>
        <p class="nova-page-subtitle">إدارة جميع المدن في النظام</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('cities.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus"></i> إضافة مدينة
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

@if(session('import_errors'))
    <div class="nova-alert warning">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>أخطاء الاستيراد:</strong>
        <ul style="margin-top: 0.5rem; padding-left: 1.25rem;">
            @foreach(session('import_errors') as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Statistics Cards --}}
<div class="nova-row cols-2 cols-lg-4 nova-mb-4">
    <div class="nova-stat">
        <div class="nova-stat-icon primary">
            <i class="fas fa-map-marker-alt"></i>
        </div>
        <p class="nova-stat-label">إجمالي المدن</p>
        <p class="nova-stat-value">{{ $totalCities ?? $cities->count() }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon secondary">
            <i class="fas fa-flag"></i>
        </div>
        <p class="nova-stat-label">البلدان</p>
        <p class="nova-stat-value">{{ $totalCountries ?? 0 }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon accent">
            <i class="fas fa-map-marked-alt"></i>
        </div>
        <p class="nova-stat-label">مع إحداثيات</p>
        <p class="nova-stat-value">{{ $cities->whereNotNull('latitude')->count() }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon danger">
            <i class="fas fa-layer-group"></i>
        </div>
        <p class="nova-stat-label">الولايات</p>
        <p class="nova-stat-value">{{ $totalStates ?? 0 }}</p>
    </div>
</div>

{{-- Data Table --}}
<div class="nova-table-wrapper">
    <div class="nova-toolbar">
        <div class="nova-toolbar-filters">
            <h3 class="nova-card-title" style="margin: 0;">
                <i class="fas fa-list"></i>
                قائمة المدن
            </h3>
        </div>
        <div class="nova-toolbar-search">
            <i class="fas fa-search"></i>
            <input type="search" class="nova-input" placeholder="ابحث في المدن...">
        </div>
        <div class="nova-toolbar-actions">
            <a href="{{ route('cities.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
                <i class="fas fa-plus"></i> إضافة مدينة
            </a>
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>اسم المدينة</th>
                    <th>الشيفرة</th>
                    <th>البلد</th>
                    <th>الولاية</th>
                    <th>الإحداثيات</th>
                    <th class="nova-text-end">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cities as $city)
                <tr>
                    <td class="nova-fw-semibold">{{ $loop->iteration }}</td>
                    <td>{{ $city->name }} @if($city->code)<small class="nova-text-muted">({{ $city->code }})</small>@endif</td>
                    <td>{{ $city->code ?: '—' }}</td>
                    <td>{{ $city->country->name ?? '—' }}</td>
                    <td>{{ $city->state->name_en ?? '—' }}</td>
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
                    <td colspan="7">
                        <div class="nova-empty">
                            <div class="nova-empty-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <h3 class="nova-empty-title">لا توجد مدن</h3>
                            <p class="nova-empty-text">أنشئ أول مدينة الآن.</p>
                            <a href="{{ route('cities.create') }}" class="nova-btn nova-btn-primary">
                                <i class="fas fa-plus"></i> إنشاء مدينة
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="nova-card-footer">
        <div class="nova-flex nova-justify-between nova-items-center nova-flex-wrap nova-gap-2">
                 <div class="nova-text-muted nova-fs-sm">
                عرض {{ $cities->count() }} سجلاً
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal (Nova) --}}
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

@push('scripts')
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
