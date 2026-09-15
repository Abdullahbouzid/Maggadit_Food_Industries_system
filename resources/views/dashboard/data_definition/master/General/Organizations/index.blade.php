@extends('dashboard.partials.layout')

@section('title', 'إدارة المنظمات')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-building" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            المنظمات
        </h2>
        <p class="nova-page-subtitle">إدارة الهرمكية التنظيمية، الشيفرات، ومعلومات GLN</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('organizations.export', request()->query()) }}" class="nova-btn nova-btn-secondary nova-btn-sm">
            <i class="fas fa-download"></i> تصدير
        </a>
        <a href="{{ route('organizations.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
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
            <i class="fas fa-th"></i>
        </div>
        <p class="nova-stat-label">إجمالي المنظمات</p>
        <p class="nova-stat-value">{{ $totalOrganizations ?? $organizations->total() }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon secondary">
            <i class="fas fa-home"></i>
        </div>
        <p class="nova-stat-label">منظمات رئيسية</p>
        <p class="nova-stat-value">{{ $rootOrganizations ?? 0 }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon accent">
            <i class="fas fa-sitemap"></i>
        </div>
        <p class="nova-stat-label">منظمات فرعية</p>
        <p class="nova-stat-value">{{ $subOrganizations ?? 0 }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon danger">
            <i class="fas fa-hashtag"></i>
        </div>
        <p class="nova-stat-label">مع GLN</p>
        <p class="nova-stat-value">{{ $withGlnCount ?? 0 }}</p>
    </div>
</div>

{{-- Import Section --}}
<div class="nova-card mb-4">
    <div class="nova-card-header">
        <h2 class="nova-card-title" style="margin: 0;">
            <i class="fas fa-upload"></i>
            استيراد المنظمات
        </h2>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('organizations.import') }}" method="POST" enctype="multipart/form-data" class="nova-row cols-3" style="align-items: end;">
            @csrf
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">ملف إكسل (.xlsx, .xls, .csv)</label>
                <input type="file" name="import_file" class="nova-input" required>
                <div class="nova-help-text">العمود المطلوب: <strong>name, code, parent_id, organization_type, gln</strong>. Parent ID يجب أن يكون موجوداً.</div>
            </div>
            <div style="height: fit-content;">
                <button type="submit" class="nova-btn nova-btn-primary nova-btn-block">
                    <i class="fas fa-upload"></i> استيراد البيانات
                </button>
            </div>
            <div style="height: fit-content;">
                <a href="{{ route('organizations.template') }}" class="nova-btn nova-btn-outline nova-btn-block">
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
        <form action="{{ route('organizations.export') }}" method="GET" class="nova-row cols-3" style="align-items: end;">
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">بحث (الاسم/الكود/GLN)</label>
                <input type="text" name="search" class="nova-input" placeholder="{{ __('messages.filter.search') }}">
            </div>
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">نوع المنظمة</label>
                <select name="organization_type" class="nova-select">
                    <option value="">{{ __('messages.filter.all') }}</option>
                    <option value="headquarters">المقر الرئيسي</option>
                    <option value="branch">فرع</option>
                    <option value="warehouse">مستودع</option>
                </select>
            </div>
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">حالة GLN</label>
                <select name="gln_status" class="nova-select">
                    <option value="">{{ __('messages.filter.all') }}</option>
                    <option value="with">بـ GLN</option>
                    <option value="without">بدون GLN</option>
                </select>
            </div>
            <div style="height: fit-content;">
                <button type="submit" class="nova-btn nova-btn-primary nova-btn-block">
                    <i class="fas fa-file-excel"></i> تصدير مفلتر
                </button>
            </div>
        </form>
        <div class="nova-help-text" style="margin-top: 0.5rem;">استخدم هذه الفلاتر لتنزيل ملف إكسل يحتوي فقط على السجلات المطابقة.</div>
    </div>
</div>

{{-- Main فلتر (for table) --}}
<div class="nova-card mb-4">
    <div class="nova-card-body" style="padding: 1rem 1.5rem;">
        <form id="filterForm" method="GET" action="{{ route('organizations.index') }}" class="nova-row cols-3" style="align-items: end; gap: 0.75rem;">
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">بحث (الجدول)</label>
                <div class="nova-input-group" style="display: flex; gap: 0;">
                    <span class="nova-input-group-text" style="display: flex; align-items: center; padding: 0 0.875rem; background: var(--nova-surface-2); border: 1.5px solid var(--nova-border); border-end: none; border-radius: 10px 0 0 10px; color: var(--nova-text-4);">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" class="nova-input" placeholder="الاسم، الكود، أو GLN..." value="{{ request('search') }}" style="border-radius: 0 10px 10px 0; border-start: none;">
                </div>
            </div>
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">{{ __('messages.filter.per_page') }}</label>
                <select name="per_page" class="nova-select">
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
                    <a href="{{ route('organizations.index') }}" class="nova-btn nova-btn-outline">{{ __('messages.filter.reset') }}</a>
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
                قائمة المنظمات
            </h3>
        </div>
        <div class="nova-toolbar-actions">
            <a href="{{ route('organizations.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
                <i class="fas fa-plus"></i> إضافة منظمة
            </a>
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>اسم المنظمة</th>
                    <th>الشيفرة</th>
                    <th>المنظمة الأصلية</th>
                    <th>النوع</th>
                    <th>GLN</th>
                    <th style="text-align: right;">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($organizations as $org)
                <tr>
                    <td class="nova-fw-semibold">{{ $loop->iteration + ($organizations->currentصفحة() - 1) * $organizations->perصفحة() }}</td>
                    <td>
                        <div class="nova-flex nova-items-center">
                            <div class="nova-stat-icon primary" style="width: 32px; height: 32px; margin-inline-end: 0.5rem;">
                                <i class="fas fa-building"></i>
                            </div>
                            <span>{{ $org->name }}</span>
                        </div>
                    </td>
                    <td><span class="nova-badge secondary">{{ $org->code }}</span></td>
                    <td>
                        @if($org->parent)
                            <i class="fas fa-sitemap nova-text-muted me-1"></i>
                            {{ $org->parent->name }}
                        @else
                            <span class="nova-text-muted fst-italic">— جذر —</span>
                        @endif
                    </td>
                    <td>{{ $org->organization_type ?: '—' }}</td>
                    <td>
                        @if($org->gln)
                            <code class="nova-text-primary" style="background: var(--nova-surface-2); padding: 0.125rem 0.375rem; border-radius: 4px;">{{ $org->gln }}</code>
                        @else
                            <span class="nova-text-muted">—</span>
                        @endif
                    </td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                            <a href="{{ route('organizations.show', $org) }}" class="nova-icon-btn nova-btn-sm" title="عرض">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('organizations.edit', $org) }}" class="nova-icon-btn nova-btn-sm" title="تعديل">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger" title="حذف" onclick="confirmحذف({{ $org->id }}, '{{ addslashes($org->name) }}')">
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
                                <i class="fas fa-database"></i>
                            </div>
                            <h3 class="nova-empty-title">لا توجد منظمات</h3>
                            <p class="nova-empty-text">جرّب تعديل البحث أو أنشئ منظمة جديدة.</p>
                            <a href="{{ route('organizations.create') }}" class="nova-btn nova-btn-primary">
                                <i class="fas fa-plus"></i> إنشاء منظمة
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
                عرض {{ $organizations->firstItem() ?? 0 }} إلى {{ $organizations->lastItem() ?? 0 }} من أصل {{ $organizations->total() }} سجلاً
            </div>
            <div class="nova-pagination">
                {{ $organizations->appends(request()->query())->links() }}
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
                    <button type="submit" class="nova-btn nova-btn-danger">حفظ</button>
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
        document.getElementById('deleteForm').action = '/organizations/' + id;
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
