@extends('dashboard.partials.layout')

@section('title', 'إدارة حالات الطرود')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-layers" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            حالات الطرود
        </h2>
        <p class="nova-page-subtitle">إدارة تعريفات حالة الطرود لكل منظمة</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('package-statuses.export', request()->query()) }}" class="nova-btn nova-btn-secondary nova-btn-sm">
            <i class="fas fa-download"></i> تصدير
        </a>
        <a href="{{ route('package-statuses.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus"></i> إنشاء
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
    <div class="nova-alert warning">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>أكمل الاستيراد مع أخطاء في الصفوف التالية:</strong>
        <ul style="margin: 0.5rem 0 0; padding-inline-start: 1.25rem;">
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
            <i class="fas fa-layer-group"></i>
        </div>
        <p class="nova-stat-label">إجمالي الحالات</p>
        <p class="nova-stat-value">{{ $totalStatuses ?? $packageStatuses->total() }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon success">
            <i class="fas fa-shield-check"></i>
        </div>
        <p class="nova-stat-label">قابلة لإعادة البيع</p>
        <p class="nova-stat-value">{{ $totalStatuses ? $packageStatuses->where('resellable', true)->count() : 0 }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon danger">
            <i class="fas fa-clock"></i>
        </div>
        <p class="nova-stat-label">منتهية الصلاحية</p>
        <p class="nova-stat-value">{{ $totalStatuses ? $packageStatuses->where('expired', true)->count() : 0 }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon accent">
            <i class="fas fa-star"></i>
        </div>
        <p class="nova-stat-label">الحالة الافتراضية</p>
        <p class="nova-stat-value">{{ $totalStatuses ? $packageStatuses->where('default', true)->count() : 0 }}</p>
    </div>
</div>

{{-- Import Section --}}
<div class="nova-card mb-4">
    <div class="nova-card-header">
        <h2 class="nova-card-title" style="margin: 0;">
            <i class="fas fa-upload"></i>
            استيراد حالات الطرود
        </h2>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('package-statuses.import') }}" method="POST" enctype="multipart/form-data" class="nova-row cols-3" style="align-items: end;">
            @csrf
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">ملف إكسل (.xlsx, .xls, .csv)</label>
                <input type="file" name="import_file" class="nova-input" required>
                <div class="nova-help-text">العمود المطلوب: <strong>status_name, organization_id, resellable, expired, default, lpo_required, is_related_to_cn_request, is_price_editable</strong></div>
            </div>
            <div style="height: fit-content;">
                <button type="submit" class="nova-btn nova-btn-primary nova-btn-block">
                    <i class="fas fa-upload"></i> استيراد البيانات
                </button>
            </div>
            <div style="height: fit-content;">
                <a href="{{ route('package-statuses.template') }}" class="nova-btn nova-btn-outline nova-btn-block">
                    <i class="fas fa-file-alt"></i> تنزيل القالب
                </a>
            </div>
        </form>
    </div>
</div>

{{-- تصدير فلتر Section --}}
<div class="nova-card mb-4">
    <div class="nova-card-header">
        <h2 class="nova-card-title" style="margin: 0;">
            <i class="fas fa-download"></i>
            خيارات التصدير (فلاتر مخصصة)
        </h2>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('package-statuses.export') }}" method="GET" class="nova-row cols-3" style="align-items: end;">
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">اسم الحالة</label>
                <input type="text" name="search" class="nova-input" placeholder="كلمة مفتاح...">
            </div>
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">المنظمة</label>
                <select name="organization_id" class="nova-select">
                    <option value="">الكل</option>
                    @foreach($organizations as $org)
                        <option value="{{ $org->id }}">{{ $org->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">قابل لإعادة البيع</label>
                <select name="resellable" class="nova-select">
                    <option value="">الكل</option>
                    <option value="1">نعم</option>
                    <option value="0">لا</option>
                </select>
            </div>
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">افتراضي</label>
                <select name="default" class="nova-select">
                    <option value="">الكل</option>
                    <option value="1">نعم</option>
                    <option value="0">لا</option>
                </select>
            </div>
            <div style="height: fit-content;">
                <button type="submit" class="nova-btn nova-btn-primary nova-btn-block">
                    <i class="fas fa-file-excel"></i> تصدير
                </button>
            </div>
        </form>
        <div class="nova-help-text" style="margin-top: 0.5rem;">استخدم هذه الفلاتر لتنزيل ملف إكسل يحتوي فقط على السجلات المطابقة.</div>
    </div>
</div>

{{-- Main فلتر (for table) --}}
<div class="nova-card mb-4">
    <div class="nova-card-body" style="padding: 1rem 1.5rem;">
        <form id="filterForm" method="GET" action="{{ route('package-statuses.index') }}" class="nova-row cols-3" style="align-items: end; gap: 0.75rem;">
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">بحث</label>
                <input type="text" name="search" class="nova-input" placeholder="اسم الحالة أو المنظمة..." value="{{ request('search') }}">
            </div>
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">المنظمة</label>
                <select name="organization_id" class="nova-select">
                    <option value="">الكل</option>
                    @foreach($organizations as $org)
                        <option value="{{ $org->id }}" {{ request('organization_id') == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">{{ __('messages.filter.per_page') }}</label>
                <select name="per_page" class="nova-select">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>الكل</option>
                </select>
            </div>
            <div class="nova-flex nova-gap-2" style="height: fit-content;">
                <button type="submit" class="nova-btn nova-btn-primary">تطبيق</button>
                @if(request()->query())
                    <a href="{{ route('package-statuses.index') }}" class="nova-btn nova-btn-outline">{{ __('messages.filter.reset') }}</a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Data Table --}}
<div class="nova-table-wrapper">
    <div class="nova-toolbar">
        <div class="nova-toolbar-filters">
            <h3 class="nova-card-title" style="margin: 0;">
                <i class="fas fa-list"></i>
                قائمة حالات الطرود
            </h3>
        </div>
        <div class="nova-toolbar-actions">
            <a href="{{ route('package-statuses.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
                <i class="fas fa-plus"></i> إنشاء حالة
            </a>
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>اسم الحالة</th>
                    <th>المنظمة</th>
                    <th>قابل لإعادة البيع</th>
                    <th>منتهية الصلاحية</th>
                    <th>افتراضي</th>
                    <th>مطلوب LPO</th>
                    <th>مرتبط بـ CN</th>
                    <th>قابل لتحرير السعر</th>
                    <th style="text-align: right;">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($packageStatuses as $status)
                <tr>
                    <td class="nova-fw-semibold">{{ $loop->iteration + ($packageStatuses->currentPage() - 1) * $packageStatuses->perPage() }}</td>
                    <td>{{ $status->status_name }}</td>
                    <td>{{ $status->organization->name ?? '—' }}</td>
                    <td>@if($status->resellable) <span class="nova-badge success">نعم</span> @else <span class="nova-badge secondary">لا</span> @endif</td>
                    <td>@if($status->expired) <span class="nova-badge danger">نعم</span> @else <span class="nova-badge secondary">لا</span> @endif</td>
                    <td>@if($status->default) <span class="nova-badge secondary">نعم</span> @else <span class="nova-badge secondary">لا</span> @endif</td>
                    <td>@if($status->lpo_required) <span class="nova-badge accent">نعم</span> @else <span class="nova-badge secondary">لا</span> @endif</td>
                    <td>@if($status->is_related_to_cn_request) <span class="nova-badge primary">نعم</span> @else <span class="nova-badge secondary">لا</span> @endif</td>
                    <td>@if($status->is_price_editable) <span class="nova-badge secondary">نعم</span> @else <span class="nova-badge secondary">لا</span> @endif</td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                            <a href="{{ route('package-statuses.show', $status) }}" class="nova-icon-btn nova-btn-sm" title="عرض">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('package-statuses.edit', $status) }}" class="nova-icon-btn nova-btn-sm" title="تعديل">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger" title="حذف" onclick="confirmحذف({{ $status->id }}, '{{ addslashes($status->status_name) }}')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10">
                        <div class="nova-empty">
                            <div class="nova-empty-icon">
                                <i class="fas fa-database"></i>
                            </div>
                            <h3 class="nova-empty-title">لا توجد حالات طرود</h3>
                            <p class="nova-empty-text">أنشئ أول حالة الآن.</p>
                            <a href="{{ route('package-statuses.create') }}" class="nova-btn nova-btn-primary">
                                <i class="fas fa-plus"></i> إنشاء حالة
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
                عرض {{ $packageStatuses->firstItem() ?? 0 }} إلى {{ $packageStatuses->lastItem() ?? 0 }} من أصل {{ $packageStatuses->total() }} سجلاً
            </div>
            <div class="nova-pagination">
                {{ $packageStatuses->appends(request()->query())->links() }}
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
    const perصفحةSelect = document.querySelector('select[name="per_page"]');
    if (perصفحةSelect) {
        perصفحةSelect.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    }

    window.confirmحذف = function(id, name) {
        document.getElementById('deleteName').innerText = name;
        document.getElementById('deleteForm').action = '/package-statuses/' + id;
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
