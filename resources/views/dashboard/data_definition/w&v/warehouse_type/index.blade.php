@extends('dashboard.partials.layout')

@section('title', 'Warehouse Types')

@section('content')
{{-- ===== Action Bar ===== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-box-open" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            Warehouse Types
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">
            Manage warehouse classifications (e.g., cold storage, dry, hazardous)
        </p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('warehouse-types.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus"></i> Create
        </a>
        <a href="{{ route('warehouse-types.export', request()->query()) }}" class="nova-btn nova-btn-secondary nova-btn-sm">
            <i class="fas fa-download"></i> تصدير Table
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
        <div>
            <strong>أخطاء الاستيراد:</strong>
            <ul class="nova-mb-0 nova-mt-1">
                @foreach(session('import_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

{{-- ===== Statistics ===== --}}
<div class="nova-row cols-4 nova-mb-4">
    <div class="nova-stat">
        <div class="nova-stat-icon primary">
            <i class="fas fa-layer-group"></i>
        </div>
        <p class="nova-stat-label">Total Types</p>
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
    <div class="nova-stat">
        <div class="nova-stat-icon secondary">
            <i class="fas fa-clock"></i>
        </div>
        <p class="nova-stat-label">الأخير Updated</p>
        <p class="nova-stat-value">{{ now()->format('d M Y') }}</p>
    </div>
</div>

{{-- ============================================================
     Import Section
     ============================================================ --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h6 class="nova-fw-bold mb-0">
            <i class="fas fa-upload"></i> Import Warehouse Types
        </h6>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('warehouse-types.import') }}" method="POST" enctype="multipart/form-data" class="import-form">
            @csrf
            <div class="nova-form-group import-form-input">
                <label class="nova-label">ملف Excel</label>
                <input type="file" name="import_file" class="nova-input" required>
                <div class="nova-help-text">
                    الأعمدة المطلوبة.
                    اختياري: description, is_active, sort_order.
                </div>
            </div>
            <div class="import-form-actions">
                <button type="submit" class="nova-btn nova-btn-primary">
                    <i class="fas fa-upload"></i> استيراد البيانات
                </button>
                <a href="{{ route('warehouse-types.template') }}" class="nova-btn nova-btn-outline">
                    <i class="fas fa-file-text"></i> تحميل القالب
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ============================================================
     فلتر Section
     ============================================================ --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-body" style="padding: 1rem 1.5rem;">
        <form id="filterForm" method="GET" action="{{ route('warehouse-types.index') }}" class="filter-form">
            {{-- بحث --}}
            <div class="nova-form-group filter-item filter-item-search">
                <label class="nova-label">{{ __('messages.filter.search') }}</label>
                <div class="filter-search-wrapper">
                    <span class="filter-search-icon">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" class="nova-input filter-search-input"
                           placeholder="Code, Name (E—R)..."
                           value="{{ request('search') }}">
                </div>
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
                <label class="nova-label">{{ __('messages.filter.per_page') }}</label>
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
                    <i class="fas fa-filter"></i> فلتر
                </button>
                <a href="{{ route('warehouse-types.index') }}" class="nova-btn nova-btn-outline">
                    <i class="fas fa-rotate-left"></i> إعادة تعيين
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ============================================================
     Data Table
     ============================================================ --}}
<div class="nova-table-wrapper">
    <div class="nova-toolbar">
        <div class="nova-toolbar-filters">
            <h3 class="nova-card-title" style="margin: 0;">
                <i class="fas fa-list"></i>
                قائمة أنواع المخازن
            </h3>
        </div>
        <div class="nova-toolbar-actions">
            <a href="{{ route('warehouse-types.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
                <i class="fas fa-plus"></i> Add Type
            </a>
        </div>
    </div>

    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>Code</th>
                    <th>Name (EN)</th>
                    <th>Name (AR)</th>
                    <th>الوصف</th>
                    <th>الحالة</th>
                    <th class="nova-text-center">ترتيب الفرز</th>
                    <th class="nova-text-end">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($warehouseTypes as $type)
                <tr>
                    <td class="nova-fw-semibold">
                        {{ $loop->iteration + ($warehouseTypes->currentصفحة() - 1) * $warehouseTypes->perصفحة() }}
                    </td>
                    <td>
                        <code class="table-code">{{ $type->code }}</code>
                    </td>
                    <td>
                        <strong class="table-name">{{ $type->name_en }}</strong>
                    </td>
                    <td>{{ $type->name_ar }}</td>
                    <td>
                        @if($type->description)
                            <span class="nova-text-muted nova-fs-sm" title="{{ $type->description }}">
                                {{ Str::limit($type->description, 50) }}
                            </span>
                        @else
                            <span class="nova-text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($type->is_active)
                            <span class="nova-badge success">
                                <i class="fas fa-check"></i> نشط
                            </span>
                        @else
                            <span class="nova-badge secondary">
                                <i class="fas fa-x"></i> غير نشط
                            </span>
                        @endif
                    </td>
                    <td class="nova-text-center">
                        <span class="sort-order-badge">{{ $type->sort_order }}</span>
                    </td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                            <a href="{{ route('warehouse-types.show', $type) }}"
                               class="nova-icon-btn nova-btn-sm" title="عرض">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('warehouse-types.edit', $type) }}"
                               class="nova-icon-btn nova-btn-sm" title="تعديل">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button"
                                    class="nova-icon-btn nova-btn-sm nova-text-danger"
                                    title="حذف"
                                    onclick="confirmحذف({{ $type->id }}, '{{ addslashes($type->name_en) }}')">
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
                                <i class="fas fa-box-open"></i>
                            </div>
                            <h3 class="nova-empty-title">لا warehouse types found</h3>
                            <p class="nova-empty-text">Create your first warehouse type.</p>
                            <a href="{{ route('warehouse-types.create') }}" class="nova-btn nova-btn-primary">
                                <i class="fas fa-plus"></i> Create Type
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
                عرض {{ $warehouseTypes->firstItem() ?? 0 }} to {{ $warehouseTypes->lastItem() ?? 0 }} من {{ $warehouseTypes->total() }} entries
            </div>
            <div class="nova-pagination">
                {{ $warehouseTypes->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

{{-- ============================================================
     حذف تأكيدation Modal
     ============================================================ --}}
<div class="nova-modal-backdrop" id="globalحذفModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">تأكيد الحذف</h4>
            <p class="nova-delete-modal-text">
                Are you sure you want to delete warehouse type
                <strong id="deleteItemName" class="nova-text-danger"></strong>?<br>
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
    /* ============================================================
       Import Form
       ============================================================ */
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

    /* ============================================================
       فلتر Form - Grid Layout
       ============================================================ */
    .filter-form {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr auto;
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

    /* ============================================================
       Table Enhancements
       ============================================================ */
    .table-code {
        background: #f1f3f9;
        padding: 0.15rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.8rem;
        color: #5a5c69;
        font-weight: 500;
    }

    .table-name {
        font-size: 0.875rem;
        color: var(--nova-text-1);
    }

    .sort-order-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        padding: 0.2rem 0.5rem;
        background: #f8f9fc;
        border: 1px solid var(--nova-border);
        border-radius: 0.35rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: #5a5c69;
    }

    /* ============================================================
       Responsive
       ============================================================ */
    @media (max-width: 992px) {
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
    }

    @media (max-width: 768px) {
        .import-form {
            grid-template-columns: 1fr;
        }

        .import-form-actions {
            flex-direction: column;
            width: 100%;
        }

        .import-form-actions .nova-btn {
            width: 100%;
            justify-content: center;
        }

        .filter-form {
            grid-template-columns: 1fr 1fr;
        }

        .filter-item-search {
            grid-column: span 2;
        }

        .filter-item-actions {
            grid-column: span 2;
            justify-content: stretch;
        }

        .filter-item-actions .nova-btn {
            flex: 1;
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .filter-form {
            grid-template-columns: 1fr;
        }

        .filter-item-search,
        .filter-item-actions {
            grid-column: span 1;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        /* ============================================================
           Auto-submit on per_page change
           ============================================================ */
        const perصفحةSelect = document.querySelector('select[name="per_page"]');
        if (perصفحةSelect) {
            perصفحةSelect.addEventListener('change', () => {
                document.getElementById('filterForm').submit();
            });
        }

        /* ============================================================
           حذف تأكيدation
           ============================================================ */
        window.confirmحذف = function(id, name) {
            document.getElementById('deleteItemName').innerText = name;
            document.getElementById('deleteForm').action = '/warehouse-types/' + id;
            document.getElementById('globalحذفModal').classList.add('show');
        };

        /* ============================================================
           Auto-dismiss Alerts
           ============================================================ */
        setTimeout(() => {
            document.querySelectorالكل('.nova-alert').forEach(alert => {
                alert.style.transition = 'opacity 0.3s, transform 0.3s';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    });
</script>
@endpush