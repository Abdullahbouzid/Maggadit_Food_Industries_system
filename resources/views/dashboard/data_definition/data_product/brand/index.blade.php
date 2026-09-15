@extends('dashboard.partials.layout')

@section('title', 'إدارة العلامات التجارية')

@section('content')
{{-- ===== Action Bar ===== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-tag" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            Brands
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Manage brands, logos, and visibility in customer app</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('brands.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus-circle"></i> إضافة جديد
        </a>
        <a href="{{ route('brands.export', request()->query()) }}" class="nova-btn nova-btn-secondary nova-btn-sm">
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
            <i class="fas fa-grid"></i>
        </div>
        <p class="nova-stat-label">إجمالي العلامات التجارية</p>
        <p class="nova-stat-value">{{ $totalBrands }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon primary">
            <i class="fas fa-eye"></i>
        </div>
        <p class="nova-stat-label">Shown in App</p>
        <p class="nova-stat-value">{{ $shownInApp }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon accent">
            <i class="fas fa-eye-منf"></i>
        </div>
        <p class="nova-stat-label">Hidden in App</p>
        <p class="nova-stat-value">{{ $hiddenInApp }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon secondary">
            <i class="fas fa-image"></i>
        </div>
        <p class="nova-stat-label">With Logo</p>
        <p class="nova-stat-value">{{ $withLogo }}</p>
    </div>
</div>

{{-- ============================================================
     فلتر Section
     ============================================================ --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-body" style="padding: 1rem 1.5rem;">
        <form id="filterForm" method="GET" action="{{ route('brands.index') }}" class="filter-form">
            {{-- بحث --}}
            <div class="nova-form-group filter-item filter-item-search">
                <label class="nova-label">بحث (Name)</label>
                <div class="filter-search-wrapper">
                    <span class="filter-search-icon">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" class="nova-input filter-search-input"
                           placeholder="Brand name..."
                           value="{{ request('search') }}">
                </div>
            </div>

            {{-- لكل صفحة --}}
            <div class="nova-form-group filter-item">
                <label class="nova-label">لكل صفحة</label>
                <select name="per_page" class="nova-select">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>الكل</option>
                </select>
            </div>

            {{-- إجراءات --}}
            <div class="nova-form-group filter-item filter-item-actions">
                <button type="submit" class="nova-btn nova-btn-primary">
                    <i class="fas fa-filter"></i> فلتر
                </button>
                @if(request('search') || request('per_page'))
                    <a href="{{ route('brands.index') }}" class="nova-btn nova-btn-outline">
                        <i class="fas fa-rotate-left"></i> إعادة تعيين
                    </a>
                @endif
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
                <i class="fas fa-list"></i> قائمة العلامات التجارية
            </h3>
        </div>
        <div class="nova-toolbar-actions">
            <a href="{{ route('brands.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
                <i class="fas fa-plus-circle"></i> Add Brand
            </a>
        </div>
    </div>

    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>Name</th>
                    <th>Logo</th>
                    <th>Show in Customer App</th>
                    <th class="nova-text-end">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($brandsPaginated as $brand)
                <tr>
                    <td class="nova-fw-semibold">
                        {{ $loop->iteration + ($brandsPaginated->currentصفحة() - 1) * $brandsPaginated->perصفحة() }}
                    </td>
                    <td>
                        <span class="nova-fw-semibold">{{ $brand->name }}</span>
                    </td>
                    <td>
                        @if($brand->logo)
                            <img src="{{ asset('uploads/' . $brand->logo) }}" alt="{{ $brand->name }}" width="50" height="50" style="object-fit: cover; border: 1px solid var(--nova-border); border-radius: 10px; box-shadow: var(--nova-shadow-sm);">
                        @else
                            <span class="nova-text-muted" style="font-style: italic;">— لا Logo —</span>
                        @endif
                    </td>
                    <td>
                        @if($brand->show_in_customer_app)
                            <span class="nova-badge success">
                                <i class="fas fa-check"></i> Visible
                            </span>
                        @else
                            <span class="nova-badge secondary">
                                <i class="fas fa-x"></i> Hidden
                            </span>
                        @endif
                    </td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                            <a href="{{ route('brands.show', $brand) }}"
                               class="nova-icon-btn nova-btn-sm" title="عرض">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('brands.edit', $brand) }}"
                               class="nova-icon-btn nova-btn-sm" title="تعديل">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button"
                                    class="nova-icon-btn nova-btn-sm nova-text-danger"
                                    title="حذف"
                                    onclick="confirmحذف({{ $brand->id }}, '{{ addslashes($brand->name) }}')">
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
                                <i class="fas fa-database" style="width: 48px; height: 48px;"></i>
                            </div>
                            <h3 class="nova-empty-title">لا brands found</h3>
                            <p class="nova-empty-text">You can add a new brand using the add button.</p>
                            <a href="{{ route('brands.create') }}" class="nova-btn nova-btn-primary">
                                <i class="fas fa-plus-circle"></i> Add Brand
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
                عرض {{ $brandsPaginated->firstItem() ?? 0 }} to {{ $brandsPaginated->lastItem() ?? 0 }} من {{ $brandsPaginated->total() }} entries
            </div>
            <div class="nova-pagination">
                {{ $brandsPaginated->appends(request()->query())->links() }}
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
                Are you sure you want to delete brand
                <strong id="deleteBrandName" class="nova-text-danger"></strong>?<br>
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
            document.getElementById('deleteBrandName').innerText = name;
            document.getElementById('deleteForm').action = '/brands/' + id;
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

@push('styles')
<style>
    .filter-form {
        display: grid;
        grid-template-columns: 2fr 1fr auto;
        gap: 0.75rem;
        align-items: end;
    }

    .filter-form .nova-form-group {
        margin-bottom: 0;
        min-width: 0;
    }

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

    .filter-item-actions {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .filter-item-actions .nova-btn {
        white-space: nowrap;
    }

    @media (max-width: 992px) {
        .filter-form {
            grid-template-columns: 1fr 1fr;
        }

        .filter-item-search {
            grid-column: span 2;
        }

        .filter-item-actions {
            grid-column: span 2;
            justify-content: flex-end;
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
