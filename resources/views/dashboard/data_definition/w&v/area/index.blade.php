@extends('dashboard.partials.layout')

@section('title', 'إدارة المناطق')

@section('content')
{{-- ===== Action Bar ===== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-map" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            المناطق
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">إدارة المناطق الجغرافية (الأحياء، الأقسام)</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('areas.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus"></i> إنشاء
        </a>
        <a href="{{ route('areas.export', request()->query()) }}" class="nova-btn nova-btn-secondary nova-btn-sm">
            <i class="fas fa-download"></i> تصدير
        </a>
    </div>
</div>

{{-- ===== Alerts ===== --}}
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
@if(session('import_errors'))
    <div class="nova-alert danger">
        <i class="fas fa-exclamation-circle"></i>
        <strong>أخطاء الاستيراد:</strong>
        <ul class="nova-mb-0 nova-mt-1">
            @foreach(session('import_errors') as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- ===== Statistics ===== --}}
<div class="nova-row cols-3 nova-mb-4">
    <div class="nova-stat">
        <div class="nova-stat-icon primary">
            <i class="fas fa-grid"></i>
        </div>
        <p class="nova-stat-label">إجمالي المناطق</p>
        <p class="nova-stat-value">{{ $total }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon success">
            <i class="fas fa-check-circle"></i>
        </div>
        <p class="nova-stat-label">نشط</p>
        <p class="nova-stat-value">{{ $active }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon accent">
            <i class="fas fa-eye-منf"></i>
        </div>
        <p class="nova-stat-label">غير نشط</p>
        <p class="nova-stat-value">{{ $inactive }}</p>
    </div>
</div>

{{-- ===== Import Section ===== --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h6 class="nova-fw-bold mb-0">
            <i class="fas fa-upload"></i> استيراد المناطق
        </h6>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('areas.import') }}" method="POST" enctype="multipart/form-data" class="import-form">
            @csrf
            <div class="nova-form-group">
                <label class="nova-label">ملف إكسل (.xlsx, .xls, .csv)</label>
                <input type="file" name="import_file" class="nova-input" required>
                <div class="nova-help-text">
                    العمود المطلوب: <code>code</code>, <code>name_en</code>, <code>name_ar</code>. الاختياري: description, state_id, is_active, sort_order.
                </div>
            </div>
            <div class="import-form-actions">
                <button type="submit" class="nova-btn nova-btn-primary">
                    <i class="fas fa-upload"></i> استيراد البيانات
                </button>
                <a href="{{ route('areas.template') }}" class="nova-btn nova-btn-outline">
                    <i class="fas fa-file-text"></i> تنزيل القالب
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ===== فلتر Section ===== --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-body" style="padding: 1rem 1.5rem;">
        <form id="filterForm" method="GET" action="{{ route('areas.index') }}" class="filter-form">
            {{-- بحث --}}
            <div class="nova-form-group filter-item filter-item-search">
                <label class="nova-label">{{ __('messages.filter.search') }}</label>
                <div class="filter-search-wrapper">
                    <span class="filter-search-icon">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" class="nova-input filter-search-input"
                           placeholder="الكود، الاسم (إنجليزي/عربي)..."
                           value="{{ request('search') }}">
                </div>
            </div>

            {{-- State --}}
            <div class="nova-form-group filter-item">
                <label class="nova-label">الولاية / المنطقة الإدارية</label>
                <select name="state_id" class="nova-select">
                    <option value="">جميع الولايات</option>
                    @foreach($states as $state)
                        <option value="{{ $state->id }}" {{ request('state_id') == $state->id ? 'selected' : '' }}>
                            {{ $state->name_en }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- الحالة --}}
            <div class="nova-form-group filter-item">
                <label class="nova-label">{{ __('messages.filter.status') }}</label>
                <select name="status" class="nova-select">
                    <option value="">{{ __('messages.filter.all') }}</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('messages.filter.status_active') }}</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('messages.filter.status_inactive') }}</option>
                </select>
            </div>

            {{-- لكل صفحة --}}
            <div class="nova-form-group filter-item">
                <label class="nova-label">حسب الصفحة</label>
                <select name="per_page" class="nova-select">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>{{ __('messages.filter.all') }}</option>
                </select>
            </div>

            {{-- إجراءات --}}
            <div class="nova-form-group filter-item filter-item-actions">
                <button type="submit" class="nova-btn nova-btn-primary">
                    <i class="fas fa-filter"></i> تطبيق
                </button>
                <a href="{{ route('areas.index') }}" class="nova-btn nova-btn-outline">
                    <i class="fas fa-rotate-left"></i> {{ __('messages.filter.reset') }}
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ===== Data Table ===== --}}
<div class="nova-table-wrapper">
    <div class="nova-toolbar">
        <div class="nova-toolbar-filters">
            <h3 class="nova-card-title" style="margin: 0;">
                <i class="fas fa-list"></i>
                قائمة المناطق
            </h3>
        </div>
        <div class="nova-toolbar-actions">
            <a href="{{ route('areas.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
                <i class="fas fa-plus"></i> إضافة منطقة
            </a>
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الكود</th>
                    <th>الاسم (إنجليزي)</th>
                    <th>الاسم (عربي)</th>
                    <th>الولاية</th>
                    <th>الوصف</th>
                    <th>الحالة</th>
                    <th>ترتيب الفرز</th>
                    <th class="nova-text-end">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($areas as $area)
                <tr>
                    <td class="nova-fw-semibold">
                        {{ $loop->iteration + ($areas->currentصفحة() - 1) * $areas->perصفحة() }}
                    </td>
                    <td><code>{{ $area->code }}</code></td>
                    <td>{{ $area->name_en }}</td>
                    <td>{{ $area->name_ar }}</td>
                    <td>{{ $area->state?->name_en ?? '—' }}</td>
                    <td>{{ Str::limit($area->description, 50) }}</td>
                    <td>
                        @if($area->is_active)
                            <span class="nova-badge success">نشط</span>
                        @else
                            <span class="nova-badge secondary">غير نشط</span>
                        @endif
                    </td>
                    <td>{{ $area->sort_order }}</td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                            <a href="{{ route('areas.show', $area) }}" class="nova-icon-btn nova-btn-sm" title="عرض">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('areas.edit', $area) }}" class="nova-icon-btn nova-btn-sm" title="تعديل">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger" title="حذف"
                                    onclick="confirmحذف({{ $area->id }}, '{{ addslashes($area->name_en) }}')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div class="nova-empty">
                            <div class="nova-empty-icon">
                                <i class="fas fa-database"></i>
                            </div>
                            <h3 class="nova-empty-title">لم يتم العثور على مناطق</h3>
                            <p class="nova-empty-text">أنشئ منطقتك الأولى.</p>
                            <a href="{{ route('areas.create') }}" class="nova-btn nova-btn-primary">
                                <i class="fas fa-plus-circle"></i> إنشاء منطقة
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
                عرض {{ $areas->firstItem() ?? 0 }} إلى {{ $areas->lastItem() ?? 0 }} من أصل {{ $areas->total() }} إدخال
            </div>
            <div class="nova-pagination">
                {{ $areas->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

{{-- ===== حذف تأكيدation Modal ===== --}}
<div class="nova-modal-backdrop" id="globalحذفModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">تأكيد الحذف</h4>
            <p class="nova-delete-modal-text">
                هل أنت متأكد من رغبتك في حذف المنطقة
                <strong id="deleteItemName" class="nova-text-danger"></strong>؟<br>
                لا يمكن التراجع عن هذا الإجراء.
            </p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline"
                        onclick="document.getElementById('globalحذفModal').classList.remove('show')">
                    إلغاء
                </button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="nova-btn nova-btn-danger">حذف</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* ===== Import Form ===== */
    .import-form {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 1rem;
        align-items: end;
    }

    .import-form .nova-form-group {
        margin-bottom: 0;
    }

    .import-form-actions {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    /* ===== فلتر Form - Grid Layout ===== */
    .filter-form {
        display: grid;
        grid-template-columns: 2fr 1.2fr 1fr 0.8fr auto;
        gap: 0.75rem;
        align-items: end;
    }

    .filter-form .nova-form-group {
        margin-bottom: 0;
        min-width: 0;
    }

    /* بحث Input Wrapper */
    .filter-search-wrapper {
        position: relative;
        display: flex;
        align-items: stretch;
        width: 100%;
    }

    .filter-search-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 0.875rem;
        background: var(--nova-surface-2);
        border: 1.5px solid var(--nova-border);
        border-end: none;
        border-radius: 10px 0 0 10px;
        color: var(--nova-text-4);
        flex-shrink: 0;
    }

    .filter-search-input {
        border-radius: 0 10px 10px 0 !important;
        border-start: none !important;
        flex: 1;
        min-width: 0;
    }

    /* فلتر إجراءات */
    .filter-item-actions {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .filter-item-actions .nova-btn {
        white-space: nowrap;
    }

    /* ===== حذف Modal ===== */
    .nova-modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.6);
        align-items: center;
        justify-content: center;
        z-index: 1050;
    }

    .nova-modal-backdrop.show {
        display: flex;
    }

    .nova-modal {
        background: #fff;
        border-radius: 1rem;
        max-width: 420px;
        width: 90%;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    }

    .nova-delete-modal {
        padding: 2rem;
        text-align: center;
    }

    .nova-delete-modal-icon {
        width: 64px;
        height: 64px;
        background: #fee2e2;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
    }

    .nova-delete-modal-icon i {
        font-size: 1.75rem;
        color: #dc2626;
    }

    .nova-delete-modal-title {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .nova-delete-modal-text {
        color: #6b7280;
        margin-bottom: 1.5rem;
        line-height: 1.5;
    }

    .nova-delete-modal-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
    }

    /* ===== Responsive ===== */
    @media (max-width: 1200px) {
        .filter-form {
            grid-template-columns: 1fr 1fr 1fr;
        }

        .filter-item-search {
            grid-column: span 3;
        }

        .filter-item-actions {
            grid-column: span 3;
            justify-content: flex-end;
        }

        .filter-item {
            grid-column: span 1;
        }
    }

    @media (max-width: 768px) {
        .filter-form {
            grid-template-columns: 1fr;
        }

        .filter-item-search {
            grid-column: span 1;
        }

        .import-form {
            grid-template-columns: 1fr;
        }

        .import-form-actions {
            flex-direction: row;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const perصفحةSelect = document.querySelector('select[name="per_page"]');
        if (perصفحةSelect) perصفحةSelect.addEventListener('change', () => document.getElementById('filterForm').submit());

        window.confirmحذف = function(id, name) {
            document.getElementById('deleteItemName').innerText = name;
            document.getElementById('deleteForm').action = '/areas/' + id;
            document.getElementById('globalحذفModal').classList.add('show');
        };
    });
</script>
@endpush
