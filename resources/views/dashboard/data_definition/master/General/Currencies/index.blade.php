@extends('dashboard.partials.layout')

@section('title', 'إدارة العملات')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-dollar-sign" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            العملات
        </h2>
        <p class="nova-page-subtitle">إدارة أسماء العملات والشيفرات ووصف العملات وعملة القاعدة الأساسية</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('currencies.export', request()->query()) }}" class="nova-btn nova-btn-secondary nova-btn-sm">
            <i class="fas fa-download"></i> تصدير
        </a>
        <a href="{{ route('currencies.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
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
        <h6 class="nova-fw-bold" style="margin: 0 0 0.25rem;"><i class="fas fa-exclamation-circle"></i> أخطاء الاستيراد:</h6>
        <ul style="margin: 0.25rem 0 0; padding-left: 1.25rem;">
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
        <p class="nova-stat-label">إجمالي العملات</p>
        <p class="nova-stat-value">{{ $total ?? $currencies->total() }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon accent">
            <i class="fas fa-star"></i>
        </div>
        <p class="nova-stat-label">عملة أساسية</p>
        <p class="nova-stat-value">{{ $baseCount ?? 0 }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon secondary">
            <i class="fas fa-layer-group"></i>
        </div>
        <p class="nova-stat-label">عملات أخرى</p>
        <p class="nova-stat-value">{{ $nonBaseCount ?? 0 }}</p>
    </div>
</div>

{{-- Import Section --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h2 class="nova-card-title" style="margin: 0;">
            <i class="fas fa-upload"></i>
            استيراد العملات
        </h2>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('currencies.import') }}" method="POST" enctype="multipart/form-data" class="nova-row cols-3" style="align-items: end;">
            @csrf
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">ملف إكسل (.xlsx, .xls, .csv)</label>
                <input type="file" name="import_file" class="nova-input" required>
                <div class="nova-help-text">العمود المطلوب: <strong>name, code, coin_description, base_currency</strong>. العملة الأساسية: نعم/لا أو 1/0.</div>
            </div>
            <div style="height: fit-content;">
                <button type="submit" class="nova-btn nova-btn-primary nova-btn-block">
                    <i class="fas fa-upload"></i> استيراد البيانات
                </button>
            </div>
            <div style="height: fit-content;">
                <a href="{{ route('currencies.template') }}" class="nova-btn nova-btn-outline nova-btn-block">
                    <i class="fas fa-file-alt"></i> تنزيل القالب
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Export Options (Custom Filters) --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h2 class="nova-card-title" style="margin: 0;">
            <i class="fas fa-download"></i>
            خيارات التصدير (فلاتر مخصصة)
        </h2>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('currencies.export') }}" method="GET" class="nova-row cols-3" style="align-items: end;">
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">بحث</label>
                <input type="text" name="search" class="nova-input" placeholder="كود، اسم، أو وصف العملة...">
            </div>
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">العملة الأساسية</label>
                <select name="base_currency_filter" class="nova-select">
                    <option value="">الكل</option>
                    <option value="yes">العملة الأساسية فقط</option>
                    <option value="no">غير الأساسية فقط</option>
                </select>
            </div>
            <div style="height: fit-content;">
                <button type="submit" class="nova-btn nova-btn-primary nova-btn-block">
                    <i class="fas fa-file-excel"></i> تصدير
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Main Filter (for table) --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-body" style="padding: 1rem 1.5rem;">
        <form id="filterForm" method="GET" action="{{ route('currencies.index') }}" class="nova-row cols-3" style="align-items: end; gap: 0.75rem;">
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">بحث</label>
                <div class="nova-input-group" style="display: flex; gap: 0;">
                    <span class="nova-input-group-text" style="display: flex; align-items: center; padding: 0 0.875rem; background: var(--nova-surface-2); border: 1.5px solid var(--nova-border); border-right: none; border-radius: 10px 0 0 10px; color: var(--nova-text-4);">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" class="nova-input" placeholder="الاسم، الشيفرة، أو الوصف..." value="{{ request('search') }}" style="border-radius: 0 10px 10px 0; border-left: none;">
                </div>
            </div>
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">العملة الأساسية</label>
                <select name="base_currency_filter" class="nova-select">
                    <option value="">الكل</option>
                    <option value="yes" {{ request('base_currency_filter')=='yes' ? 'selected' : '' }}>العملة الأساسية</option>
                    <option value="no" {{ request('base_currency_filter')=='no' ? 'selected' : '' }}>غير الأساسية</option>
                </select>
            </div>
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">عدد السجلات لكل صفحة</label>
                <select name="per_page" class="nova-select">
                    <option value="10" {{ request('per_page')==10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ request('per_page')==20 || !request('per_page') ? 'selected' : '' }}>20</option>
                    <option value="50" {{ request('per_page')==50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page')==100 ? 'selected' : '' }}>100</option>
                    <option value="all" {{ request('per_page')=='all' ? 'selected' : '' }}>الكل</option>
                </select>
            </div>
            <div class="nova-flex nova-gap-2" style="height: fit-content;">
                <button type="submit" class="nova-btn nova-btn-primary">تطبيق</button>
                @if(request('search') || request('base_currency_filter') || request('per_page'))
                    <a href="{{ route('currencies.index') }}" class="nova-btn nova-btn-outline">إعادة ضبط</a>
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
                <i class="fas fa-list"></i> قائمة العملات
            </h3>
        </div>
        <div class="nova-toolbar-actions">
            <a href="{{ route('currencies.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
                <i class="fas fa-plus"></i> إنشاء عملة
            </a>
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الاسم</th>
                    <th>الشيفرة</th>
                    <th>وصف العملة</th>
                    <th>عملة أساسية</th>
                    <th class="nova-text-end">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($currencies as $curr)
                <tr>
                    <td class="nova-fw-semibold">{{ $loop->iteration + ($currencies->currentPage() - 1) * $currencies->perPage() }}</td>
                    <td>{{ $curr->name }}</td>
                    <td><span class="nova-badge secondary">{{ $curr->code }}</span></td>
                    <td>{{ $curr->coin_description ?: '—' }}</td>
                    <td>
                        @if($curr->base_currency)
                            <span class="nova-badge accent"><i class="fas fa-star"></i> أساسية</span>
                        @else
                            —
                        @endif
                    </td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                            <a href="{{ route('currencies.show', $curr) }}" class="nova-icon-btn nova-btn-sm" title="عرض">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('currencies.edit', $curr) }}" class="nova-icon-btn nova-btn-sm" title="تعديل">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger" title="حذف" onclick="confirmDelete({{ $curr->id }}, '{{ addslashes($curr->name) }}')">
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
                                <i class="fas fa-database"></i>
                            </div>
                            <h3 class="nova-empty-title">لا توجد عملات</h3>
                            <p class="nova-empty-text">أنشئ أول عملة الآن.</p>
                            <a href="{{ route('currencies.create') }}" class="nova-btn nova-btn-primary">
                                <i class="fas fa-plus"></i> إنشاء عملة
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
                عرض {{ $currencies->firstItem() ?? 0 }} إلى {{ $currencies->lastItem() ?? 0 }} من أصل {{ $currencies->total() }} سجلاً
            </div>
            <div class="nova-pagination">
                {{ $currencies->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal (Nova) --}}
<div class="nova-modal-backdrop" id="globalDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">تأكيد الحذف</h4>
            <p class="nova-delete-modal-text">هل أنت متأكد أنك تريد حذف العملة <strong id="deleteItemName" class="nova-text-danger"></strong>؟<br>هذا الإجراء لا يمكن التراجع عنه.</p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('globalDeleteModal').classList.remove('show')">إلغاء</button>
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
    const perPageSelect = document.querySelector('select[name="per_page"]');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    }

    window.confirmDelete = function(id, name) {
        document.getElementById('deleteItemName').innerText = name;
        document.getElementById('deleteForm').action = '/currencies/' + id;
        document.getElementById('globalDeleteModal').classList.add('show');
    };

    setTimeout(function() {
        document.querySelectorAll('.nova-alert').forEach(function(alert) {
            alert.style.transition = 'opacity 0.3s, transform 0.3s';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        });
    }, 5000);
});
</script>
@endpush
