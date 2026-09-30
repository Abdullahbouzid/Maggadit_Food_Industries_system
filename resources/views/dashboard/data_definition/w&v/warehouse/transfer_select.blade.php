@extends('dashboard.partials.layout')

@section('title', 'اختيار المنتجات للنقل من المخزن: ' . $warehouse->name_en)

@section('content')
{{-- ===== Action Bar ===== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-check-square" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            اختيار المنتجات للنقل من المخزن: {{ $warehouse->code }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">
            اختر المنتجات التي ترغب في نقلها من المخزن المصدر
            <strong>{{ $warehouse->name_en }}</strong>،
            ثم اختر المخزن الهدف وحدد الكميات في الخطوة التالية.
        </p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('warehouses.show', $warehouse) }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> Back to Warehouse
        </a>
    </div>
</div>

{{-- ===== Validation Errors ===== --}}
@if ($errors->any())
    <div class="nova-alert danger">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <strong>Please fix the following errors:</strong>
            <ul class="nova-mb-0 nova-mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

{{-- ===== Main Grid: Filters + Products ===== --}}
<div class="selection-layout">
    {{-- ============================================================
         Filter Sidebar
         ============================================================ --}}
    <aside class="selection-sidebar">
        <div class="nova-card">
            <div class="nova-card-header nova-bg-light">
                <h6 class="nova-fw-bold mb-0">
                    <i class="fas fa-filter me-1"></i> تصفية المنتجات
                </h6>
            </div>
            <div class="nova-card-body">
                <form action="{{ route('warehouses.transfer.select', $warehouse) }}" method="GET" id="filterForm">
                    <div class="filter-stack">
                        <div class="nova-form-group">
                            <label class="nova-label">
                                <i class="fas fa-search filter-label-icon"></i>
                                بحث بالمنتج أو الكود أو الدفعة
                            </label>
                            <input type="text" name="search" class="nova-input"
                                   value="{{ request('search') }}"
                                   placeholder="ابحث عن منتج...">
                        </div>

                        <div class="nova-form-group">
                            <label class="nova-label">
                                <i class="fas fa-tags filter-label-icon"></i>
                                التصنيف
                            </label>
                            <select name="category_id" class="nova-select">
                                <option value="">جميع التصنيفات</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="nova-form-group">
                            <label class="nova-label">
                                <i class="fas fa-copyright filter-label-icon"></i>
                                العلامة التجارية
                            </label>
                            <select name="brand_id" class="nova-select">
                                <option value="">جميع العلامات</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}"
                                        {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                                        {{ $brand->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-actions">
                            <button type="submit" class="nova-btn nova-btn-primary nova-btn-sm">
                                <i class="fas fa-check"></i> تطبيق
                            </button>
                            <a href="{{ route('warehouses.transfer.select', $warehouse) }}"
                               class="nova-btn nova-btn-outline nova-btn-sm">
                                <i class="fas fa-rotate-left"></i> إعادة تعيين
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </aside>

    {{-- ============================================================
         Products List
         ============================================================ --}}
    <main class="selection-content">
        <div class="nova-card">
            <div class="nova-card-header nova-bg-light nova-flex nova-justify-between nova-items-center">
                <h6 class="nova-fw-bold mb-0">
                    <i class="fas fa-shopping-bag me-1"></i> المنتجات المتوفرة في المخزن المصدر
                </h6>
                <span class="nova-badge info">
                    <i class="fas fa-boxes"></i>
                    {{ $batchItems->count() }} منتج
                </span>
            </div>
            <div class="nova-card-body">
                @if($batchItems->isEmpty())
                    <div class="nova-empty">
                        <div class="nova-empty-icon">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <h3 class="nova-empty-title">لا توجد منتجات</h3>
                        <p class="nova-empty-text">
                            لا توجد منتجات متوفرة في هذا المخزن مع الشروط المحددة.
                        </p>
                    </div>
                @else
                    <form action="{{ route('warehouses.transfer.store-selection', $warehouse) }}"
                          method="POST" id="selectionForm">
                        @csrf

                        <div class="nova-table-wrapper">
                            <div class="nova-table-scroll">
                                <table class="nova-table selection-table">
                                    <thead>
                                        <tr>
                                            <th class="nova-text-center" style="width: 40px;">
                                                <input type="checkbox" id="selectAll" class="nova-form-check-input">
                                            </th>
                                            <th>المنتج</th>
                                            <th>الرمز</th>
                                            <th>التصنيف</th>
                                            <th>العلامة التجارية</th>
                                            <th>الدفعة</th>
                                            <th>العبوة</th>
                                            <th class="nova-text-end">الكمية المتوفرة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($batchItems as $item)
                                            <tr class="selection-row {{ $item->available_qty <= 0 ? 'selection-row-disabled' : '' }}">
                                                <td class="nova-text-center">
                                                    <input type="checkbox"
                                                           name="selected_items[]"
                                                           value="{{ $item->batch_item_id }}"
                                                           class="nova-form-check-input item-checkbox"
                                                           {{ $item->available_qty > 0 ? '' : 'disabled' }}>
                                                </td>
                                                <td>
                                                    <strong class="selection-product-name">
                                                        {{ $item->item_name }}
                                                    </strong>
                                                </td>
                                                <td>
                                                    <code class="selection-code">{{ $item->item_code }}</code>
                                                </td>
                                                <td>
                                                    @if($item->category)
                                                        <span class="nova-badge secondary">
                                                            {{ $item->category }}
                                                        </span>
                                                    @else
                                                        <span class="nova-text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($item->brand)
                                                        <span class="nova-badge info">
                                                            {{ $item->brand }}
                                                        </span>
                                                    @else
                                                        <span class="nova-text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($item->batch_number)
                                                        <span class="nova-badge dark">
                                                            <i class="fas fa-layer-group"></i>
                                                            {{ $item->batch_number }}
                                                        </span>
                                                    @else
                                                        <span class="nova-text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($item->pack_info)
                                                        <span class="nova-text-muted nova-fs-sm">
                                                            {{ $item->pack_info }}
                                                        </span>
                                                    @else
                                                        <span class="nova-text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td class="nova-text-end">
                                                    <strong class="selection-qty {{ $item->available_qty > 0 ? 'nova-text-primary' : 'nova-text-muted' }}">
                                                        {{ number_format($item->available_qty, 3) }}
                                                    </strong>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- ===== Selection Summary + Submit ===== --}}
                        <div class="selection-footer">
                            <div class="selection-footer-info">
                                <i class="fas fa-info-circle"></i>
                                <span>
                                    تم تحديد
                                    <strong id="selectedCount">0</strong>
                                    من
                                    <strong>{{ $batchItems->count() }}</strong>
                                    منتج
                                </span>
                            </div>
                            <button type="submit" class="nova-btn nova-btn-success nova-btn-lg nova-shadow-sm">
                                <i class="fas fa-arrow-right"></i> متابعة لتحديد المخزن الهدف
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </main>
</div>

@endsection

@push('styles')
<style>
    /* ============================================================
       Selection Layout (Sidebar + Content)
       ============================================================ */
    .selection-layout {
        display: grid;
        grid-template-columns: 320px 1fr;
        gap: 1.5rem;
        align-items: start;
    }

    .selection-sidebar {
        position: sticky;
        top: 1rem;
    }

    /* ============================================================
       Filter Stack
       ============================================================ */
    .filter-stack {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .filter-stack .nova-form-group {
        margin-bottom: 0;
    }

    .filter-label-icon {
        color: #b7b9cc;
        font-size: 0.75rem;
        margin-right: 0.25rem;
    }

    .filter-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        padding-top: 0.5rem;
        border-top: 1px solid var(--nova-border);
    }

    .filter-actions .nova-btn {
        flex: 1;
        justify-content: center;
    }

    /* ============================================================
       Selection Table
       ============================================================ */
    .selection-table {
        min-width: 900px;
    }

    .selection-table th {
        background: #f8f9fc;
        font-weight: 600;
        color: #5a5c69;
        vertical-align: middle;
        white-space: nowrap;
    }

    .selection-table td {
        vertical-align: middle;
    }

    .selection-row {
        transition: background-color 0.15s ease;
    }

    .selection-row:hover {
        background-color: #f8f9fc;
    }

    .selection-row-disabled {
        opacity: 0.55;
    }

    .selection-row-disabled:hover {
        background-color: transparent;
    }

    .selection-product-name {
        font-size: 0.875rem;
        color: var(--nova-text-1);
        line-height: 1.3;
    }

    .selection-code {
        background: #f1f3f9;
        padding: 0.15rem 0.4rem;
        border-radius: 0.25rem;
        font-size: 0.78rem;
        color: #5a5c69;
    }

    .selection-qty {
        font-size: 0.95rem;
    }

    /* ============================================================
       Selection Footer
       ============================================================ */
    .selection-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-top: 1.25rem;
        padding-top: 1.25rem;
        border-top: 1px solid var(--nova-border);
        flex-wrap: wrap;
    }

    .selection-footer-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.875rem;
        background: #f8f9fc;
        border-radius: 0.5rem;
        font-size: 0.85rem;
        color: #5a5c69;
    }

    .selection-footer-info i {
        color: var(--nova-primary);
    }

    .selection-footer-info strong {
        color: var(--nova-primary);
        font-weight: 700;
    }

    /* ============================================================
       Responsive
       ============================================================ */
    @media (max-width: 992px) {
        .selection-layout {
            grid-template-columns: 1fr;
        }

        .selection-sidebar {
            position: static;
        }
    }

    @media (max-width: 768px) {
        .selection-footer {
            flex-direction: column;
            align-items: stretch;
        }

        .selection-footer-info {
            justify-content: center;
        }

        .selection-footer .nova-btn {
            width: 100%;
            justify-content: center;
        }

        .filter-actions {
            flex-direction: column;
        }

        .filter-actions .nova-btn {
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const filterForm = document.getElementById('filterForm');
    const selectedCountEl = document.getElementById('selectedCount');

    /* ============================================================
       Update Select-All State
       ============================================================ */
    function updateSelectAllState() {
        const allEnabled = document.querySelectorAll('.item-checkbox:not(:disabled)');
        const checkedCount = document.querySelectorAll('.item-checkbox:checked:not(:disabled)').length;
        const totalCount = allEnabled.length;

        if (selectAllCheckbox) {
            selectAllCheckbox.checked = (totalCount > 0 && checkedCount === totalCount);
            selectAllCheckbox.indeterminate = (checkedCount > 0 && checkedCount < totalCount);
        }

        // Update selected count display
        if (selectedCountEl) {
            selectedCountEl.textContent = checkedCount;
        }

        // Highlight selected rows
        itemCheckboxes.forEach(cb => {
            const row = cb.closest('tr');
            if (row) {
                if (cb.checked) {
                    row.classList.add('selection-row-selected');
                } else {
                    row.classList.remove('selection-row-selected');
                }
            }
        });
    }

    /* ============================================================
       Select-All Checkbox
       ============================================================ */
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            itemCheckboxes.forEach(checkbox => {
                if (!checkbox.disabled) {
                    checkbox.checked = this.checked;
                }
            });
            updateSelectAllState();
        });
    }

    /* ============================================================
       Individual Checkboxes
       ============================================================ */
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectAllState();
        });
    });

    updateSelectAllState();

    /* ============================================================
       Auto-submit Filters
       ============================================================ */
    if (filterForm) {
        const filterInputs = filterForm.querySelectorAll('select');
        filterInputs.forEach(input => {
            input.addEventListener('change', function() {
                filterForm.submit();
            });
        });
    }

    /* ============================================================
       Focus Search Input
       ============================================================ */
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput && searchInput.value) {
        searchInput.focus();
    }

    /* ============================================================
       Form Submit Validation
       ============================================================ */
    const selectionForm = document.getElementById('selectionForm');
    if (selectionForm) {
        selectionForm.addEventListener('submit', function(e) {
            const checkedCount = document.querySelectorAll('.item-checkbox:checked:not(:disabled)').length;
            if (checkedCount === 0) {
                e.preventDefault();
                alert('يرجى اختيار منتج واحد على الأقل للنقل.');
            }
        });
    }
});
</script>
@endpush

@push('styles')
<style>
    /* Selected row highlight */
    .selection-row-selected {
        background-color: #f0fdf4 !important;
        border-right: 3px solid #16a34a;
    }

    .selection-row-selected:hover {
        background-color: #dcfce7 !important;
    }
</style>
@endpush