@extends('dashboard.partials.layout')

@section('title', 'إدارة الموردون')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-truck" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            الموردين
        </h2>
        <p class="nova-page-subtitle">إدارة معلومات الموردين وعلاقات التوريق</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('vendors.export', request()->query()) }}" class="nova-btn nova-btn-secondary nova-btn-sm">
            <i class="fas fa-download"></i> تصدير
        </a>
        <a href="{{ route('vendors.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus"></i> إضافة مورد
        </a>
    </div>
</div>

{{-- Alert Messages --}}
@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="nova-alert danger">
        <i class="fas fa-exclamation-circle"></i>
        {{ session('error') }}
    </div>
@endif

@if(session('warning'))
    <div class="nova-alert warning">
        <i class="fas fa-exclamation-triangle"></i>
        {{ session('warning') }}
    </div>
@endif

@if(session('import_errors'))
    <div class="nova-alert danger">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>أخطاء الاستيراد:</strong>
        <ul style="margin-top: 0.5rem; padding-inline-start: 1.25rem;">
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
            <i class="fas fa-truck"></i>
        </div>
        <p class="nova-stat-label">إجمالي الموردين</p>
        <p class="nova-stat-value">{{ $totalVendors ?? $vendors->total() }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon success">
            <i class="fas fa-toggle-on"></i>
        </div>
        <p class="nova-stat-label">موردين نشطين</p>
        <p class="nova-stat-value">{{ $activeVendors ?? 0 }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon danger">
            <i class="fas fa-toggle-منf"></i>
        </div>
        <p class="nova-stat-label">غير نشطين</p>
        <p class="nova-stat-value">{{ $inactiveVendors ?? 0 }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon accent">
            <i class="fas fa-percentage"></i>
        </div>
        <p class="nova-stat-label">متوسط العمولة</p>
        <p class="nova-stat-value">{{ isset($avgCommission) ? number_format($avgCommission, 2) . '%' : ($vendors->avg('commission_percentage') ? number_format($vendors->avg('commission_percentage'), 2) . '%' : '—') }}</p>
    </div>
</div>

{{-- Import Section --}}
<div class="nova-card mb-4">
    <div class="nova-card-header">
        <h2 class="nova-card-title" style="margin: 0;">
            <i class="fas fa-upload"></i>
            استيراد الموردين
        </h2>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('vendors.import') }}" method="POST" enctype="multipart/form-data" class="nova-row cols-3" style="align-items: end;">
            @csrf
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">ملف إكسل (.xlsx, .xls, .csv)</label>
                <input type="file" name="import_file" class="nova-input" required>
                <div class="nova-help-text">العمود المطلوب: <strong>vendor_name, code, phone, fax, contact_person, contact_number, street, commission_percentage, inactive</strong>.</div>
            </div>
            <div style="height: fit-content;">
                <button type="submit" class="nova-btn nova-btn-primary nova-btn-block">
                    <i class="fas fa-upload"></i> استيراد البيانات
                </button>
            </div>
            <div style="height: fit-content;">
                <a href="{{ route('vendors.template') }}" class="nova-btn nova-btn-outline nova-btn-block">
                    <i class="fas fa-file-alt"></i> تنزيل القالب
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Data Table --}}
{{-- ===== Filter Section ===== --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-body">
        <form method="GET" action="{{ route('vendors.index') }}" id="filterForm">
            <div class="nova-row cols-3" style="align-items: end; gap: 0.75rem;">
                <div class="nova-form-group" style="margin-bottom: 0;">
                    <label class="nova-label">{{ __('messages.filter.search') }}</label>
                    <div class="nova-input-group" style="display: flex; gap: 0;">
                        <span class="nova-input-group-text" style="display: flex; align-items: center; padding: 0 0.875rem; background: var(--nova-surface-2); border: 1.5px solid var(--nova-border); border-end: none; border-radius: 10px 0 0 10px; color: var(--nova-text-4);">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="search" class="nova-input" placeholder="{{ __('messages.filter.placeholder_vendors') }}"" value="{{ request('search') }}" style="border-radius: 0 10px 10px 0; border-start: none;">
                    </div>
                </div>
                <div class="nova-form-group" style="margin-bottom: 0;">
                    <label class="nova-label">{{ __('messages.filter.per_page') }}</label>
                    <select name="per_page" class="nova-select" onchange="document.getElementById('filterForm').submit();">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>{{ __('messages.filter.all') }}</option>
                    </select>
                </div>
                <div class="nova-flex nova-gap-2" style="height: fit-content;">
                    <button type="submit" class="nova-btn nova-btn-primary">{{ __('messages.filter.apply') }}</button>
                    @if(request('search') || request('per_page'))
                        <a href="{{ route('vendors.index') }}" class="nova-btn nova-btn-outline">{{ __('messages.filter.reset') }}</a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<div class="nova-table-wrapper">
    <div class="nova-toolbar">
        <div class="nova-toolbar-filters">
            <h3 class="nova-card-title" style="margin: 0;">
                <i class="fas fa-list"></i>
                قائمة الموردين
            </h3>
        </div>
        <div class="nova-toolbar-search">
            <i class="fas fa-search"></i>
            <input type="search" class="nova-input" placeholder="ابحث في الموردين...">
        </div>
        <div class="nova-toolbar-actions">
            <a href="{{ route('vendors.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
                <i class="fas fa-plus"></i> إضافة مورد
            </a>
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>اسم المورد</th>
                    <th>الشيفرة</th>
                    <th>الهاتف</th>
                    <th>الشخص التواصل</th>
                    <th>نسبة العمولة</th>
                    <th>الحالة</th>
                    <th style="text-align: right;">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vendors as $vendor)
                <tr class="{{ $vendor->inactive ? 'nova-row-inactive' : '' }}">
                    <td class="nova-fw-semibold">{{ $loop->iteration + ($vendors->currentصفحة() - 1) * $vendors->perصفحة() }}</td>
                    <td>
                        <div class="nova-flex nova-items-center">
                            <div class="nova-stat-icon primary me-2" style="width: 32px; height: 32px;">
                                <i class="fas fa-truck"></i>
                            </div>
                            <span>{{ $vendor->vendor_name }}</span>
                        </div>
                    </td>
                    <td><span class="nova-badge secondary">{{ $vendor->code }}</span></td>
                    <td>{{ $vendor->phone ?: '—' }}</td>
                    <td>{{ $vendor->contact_person ?: '—' }}</td>
                    <td>{{ $vendor->commission_percentage ? number_format($vendor->commission_percentage, 2) . '%' : '0%' }}</td>
                    <td>
                        @if($vendor->inactive)
                            <span class="nova-badge secondary">غير نشط</span>
                        @else
                            <span class="nova-badge success">نشط</span>
                        @endif
                    </td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                            <a href="{{ route('vendors.show', $vendor) }}" class="nova-icon-btn nova-btn-sm" title="عرض">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('vendors.edit', $vendor) }}" class="nova-icon-btn nova-btn-sm" title="تعديل">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger" title="حذف" onclick="confirmحذف({{ $vendor->id }}, '{{ addslashes($vendor->vendor_name) }}')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="nova-empty">
                            <div class="nova-empty-icon">
                                <i class="fas fa-database"></i>
                            </div>
                            <h3 class="nova-empty-title">لا توجد موردين</h3>
                            <p class="nova-empty-text">أنشئ أول مورد الآن.</p>
                            <a href="{{ route('vendors.create') }}" class="nova-btn nova-btn-primary">
                                <i class="fas fa-plus"></i> إنشاء مورد
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
                عرض {{ $vendors->firstItem() ?? 0 }} إلى {{ $vendors->lastItem() ?? 0 }} من أصل {{ $vendors->total() }} سجلاً
            </div>
            <div class="nova-pagination">
                {{ $vendors->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

{{-- حذف تأكيدation Modal (لاva) --}}
<div class="nova-modal-backdrop" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">تأكيد الحذف</h4>
            <p class="nova-delete-modal-text">هل أنت متأكد أنك تريد حذف <strong id="deleteName" class="nova-text-danger"></strong>؟<br>لا يمكن التراجع عن هذا الإجراء.</p>
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.confirmحذف = function(id, name) {
        document.getElementById('deleteName').innerText = name;
        document.getElementById('deleteForm').action = '/vendors/' + id;
        document.getElementById('deleteModal').classList.add('show');
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

<style>
.nova-row-inactive {
    background: rgba(148, 163, 184, 0.05);
}
.nova-me-2 {
    margin-right: 0.5rem;
}
</style>
