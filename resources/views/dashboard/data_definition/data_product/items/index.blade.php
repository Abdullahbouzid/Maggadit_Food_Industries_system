@extends('dashboard.partials.layout')

@section('title', 'Items Management')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-box" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            Items
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Manage products, packs, and their attributes</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('items.export', request()->query()) }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-download"></i> Export
        </a>
        <a href="{{ route('items.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus-circle"></i> Add Item
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

{{-- ===== Stats Cards ===== --}}
<div class="nova-row cols-4 nova-mb-4">
    <div class="nova-stat">
        <div class="nova-stat-icon primary">
            <i class="fas fa-box"></i>
        </div>
        <p class="nova-stat-label">Total Items</p>
        <p class="nova-stat-value">{{ $totalItems }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon success">
            <i class="fas fa-check-circle"></i>
        </div>
        <p class="nova-stat-label">Active</p>
        <p class="nova-stat-value">{{ $activeItems }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon warning">
            <i class="fas fa-eye-slash"></i>
        </div>
        <p class="nova-stat-label">Inactive</p>
        <p class="nova-stat-value">{{ $inactiveItems }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon accent">
            <i class="fas fa-image"></i>
        </div>
        <p class="nova-stat-label">With Image</p>
        <p class="nova-stat-value">{{ $withImage }}</p>
    </div>
</div>

{{-- ===== Filter Section ===== --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-body">
        <form method="GET" action="{{ route('items.index') }}" id="filterForm">
            <div class="filter-grid">
                <div class="nova-form-group" style="margin-bottom: 0;">
                    <label class="nova-label">Search</label>
                    <div class="nova-input-group">
                        <span class="nova-input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" id="searchInput" class="nova-input"
                               placeholder="Code, Name..." value="{{ request('search') }}" autocomplete="off">
                    </div>
                </div>
                <div class="nova-form-group" style="margin-bottom: 0;">
                    <label class="nova-label">Item Type</label>
                    <select name="item_type_id" class="nova-select" onchange="document.getElementById('filterForm').submit();">
                        <option value="">All</option>
                        @foreach($itemTypes as $type)
                            <option value="{{ $type->id }}" {{ request('item_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="nova-form-group" style="margin-bottom: 0;">
                    <label class="nova-label">Category</label>
                    <select name="category_id" class="nova-select" onchange="document.getElementById('filterForm').submit();">
                        <option value="">All</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="nova-form-group" style="margin-bottom: 0;">
                    <label class="nova-label">Status</label>
                    <select name="status" class="nova-select" onchange="document.getElementById('filterForm').submit();">
                        <option value="">All</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="nova-form-group" style="margin-bottom: 0;">
                    <label class="nova-label">Per Page</label>
                    <select name="per_page" class="nova-select" onchange="document.getElementById('filterForm').submit();">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="nova-btn nova-btn-primary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    @if(request()->anyFilled(['search', 'item_type_id', 'category_id', 'status', 'per_page']))
                        <a href="{{ route('items.index') }}" class="nova-btn nova-btn-outline">
                            <i class="fas fa-times"></i> Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ===== Active Filters Indicator ===== --}}
@if(request()->anyFilled(['search', 'item_type_id', 'category_id', 'status']))
    <div class="active-filters">
        <span class="active-filters-label"><i class="fas fa-filter"></i> الفلاتر النشطة:</span>
        @if(request('search'))
            <span class="nova-badge info">بحث: {{ request('search') }}</span>
        @endif
        @if(request('item_type_id'))
            <span class="nova-badge info">
                النوع: {{ $itemTypes->firstWhere('id', request('item_type_id'))->name ?? request('item_type_id') }}
            </span>
        @endif
        @if(request('category_id'))
            <span class="nova-badge info">
                الفئة: {{ $categories->firstWhere('id', request('category_id'))->name ?? request('category_id') }}
            </span>
        @endif
        @if(request('status'))
            <span class="nova-badge info">الحالة: {{ request('status') }}</span>
        @endif
    </div>
@endif

{{-- ===== Table ===== --}}
<div class="nova-table-wrapper">
    <div class="nova-toolbar">
        <div class="nova-toolbar-filters">
            <h3 class="nova-card-title" style="margin: 0;">
                <i class="fas fa-list"></i>
                Items List
                @if($items->total())
                    <span class="nova-badge primary" style="margin-left: 0.5rem;">{{ $items->total() }}</span>
                @endif
            </h3>
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الصورة</th>
                    <th>الكود</th>
                    <th>الاسم (إنجليزي)</th>
                    <th>الاسم (عربي)</th>
                    <th>النوع</th>
                    <th>الفئة</th>
                    <th>الحالة</th>
                    <th class="nova-text-end">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr>
                    <td class="nova-fw-semibold">
                        {{ $loop->iteration + ($items->currentPage() - 1) * $items->perPage() }}
                    </td>
                    <td>
                        @if($item->image)
                            <img src="{{ asset('uploads/' . $item->image) }}"
                                 alt="{{ $item->name_en }}"
                                 class="nova-table-thumb"
                                 loading="lazy">
                        @else
                            <span class="nova-text-muted">—</span>
                        @endif
                    </td>
                    <td><code class="nova-code">{{ $item->code }}</code></td>
                    <td><span class="nova-fw-bold">{{ $item->name_en }}</span></td>
                    <td>{{ $item->name_ar ?: '—' }}</td>
                    <td>
                        @if($item->itemType)
                            <span class="nova-badge secondary">{{ $item->itemType->name }}</span>
                        @else
                            <span class="nova-text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($item->category)
                            <span class="nova-badge secondary">{{ $item->category->name }}</span>
                        @else
                            <span class="nova-text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($item->is_inactive)
                            <span class="nova-badge secondary">
                                <i class="fas fa-eye-slash"></i> Inactive
                            </span>
                        @else
                            <span class="nova-badge success">
                                <i class="fas fa-check-circle"></i> Active
                            </span>
                        @endif
                    </td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                            <a href="{{ route('items.show', $item) }}"
                               class="nova-icon-btn nova-btn-sm" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('items.edit', $item) }}"
                               class="nova-icon-btn nova-btn-sm" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button"
                                    class="nova-icon-btn nova-btn-sm nova-text-danger"
                                    title="Delete"
                                    onclick="confirmDelete({{ $item->id }}, '{{ addslashes($item->name_en) }}')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="nova-text-center" style="padding: 2.5rem 1rem;">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-box"></i>
                            </div>
                            <h5 class="nova-text-muted">No items found</h5>
                            <p class="nova-text-muted nova-fs-sm">
                                @if(request()->anyFilled(['search', 'item_type_id', 'category_id', 'status']))
                                    Try adjusting your search or filter criteria.
                                @else
                                    Get started by adding your first item.
                                @endif
                            </p>
                            @if(request()->anyFilled(['search', 'item_type_id', 'category_id', 'status']))
                                <a href="{{ route('items.index') }}" class="nova-btn nova-btn-outline nova-mt-2">
                                    <i class="fas fa-times"></i> Clear Filters
                                </a>
                            @else
                                <a href="{{ route('items.create') }}" class="nova-btn nova-btn-primary nova-mt-2">
                                    <i class="fas fa-plus-circle"></i> Add Item
                                </a>
                            @endif
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
                Showing {{ $items->firstItem() ?? 0 }} to {{ $items->lastItem() ?? 0 }} of {{ $items->total() }} entries
            </div>
            <div class="nova-pagination">
                {{ $items->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

{{-- ===== Delete Confirmation Modal (moved inside content) ===== --}}
<div class="nova-modal-backdrop" id="deleteItemModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">Confirm Deletion</h4>
            <p class="nova-delete-modal-text">
                Are you sure you want to delete
                <strong id="deleteItemName" class="nova-text-danger"></strong>?
                <br>This action cannot be undone.
            </p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="closeDeleteModal()">
                    Cancel
                </button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="nova-btn nova-btn-danger">
                        <i class="fas fa-trash-alt"></i> Delete Permanently
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* ===== Filter Grid ===== */
    .filter-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto;
        gap: 1rem;
        align-items: end;
    }

    .filter-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    /* ===== Active Filters ===== */
    .active-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
        padding: 0.6rem 0.85rem;
        background: #f8f9fc;
        border: 1px solid #e3e6f0;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
    }

    .active-filters-label {
        font-size: 0.8rem;
        color: #5a5c69;
        font-weight: 600;
    }

    /* ===== Table Thumb ===== */
    .nova-table-thumb {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 0.35rem;
        border: 1px solid #e3e6f0;
    }

    /* ===== Code Style ===== */
    .nova-code {
        background: #f8f9fc;
        padding: 0.15rem 0.45rem;
        border-radius: 0.25rem;
        font-size: 0.8rem;
        color: #4e73df;
        border: 1px solid #e3e6f0;
    }

    /* ===== Empty State ===== */
    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
    }

    .empty-state-icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: #f8f9fc;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #858796;
        margin-bottom: 0.5rem;
    }

    /* ===== Modal ===== */
    .nova-modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .nova-modal-backdrop.show {
        display: flex;
    }

    .nova-modal-backdrop .nova-modal {
        max-width: 480px;
        width: 100%;
        background: #fff;
        border-radius: 0.5rem;
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
        animation: modalFadeIn 0.2s ease;
    }

    @keyframes modalFadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .nova-delete-modal {
        padding: 1.75rem;
        text-align: center;
    }

    .nova-delete-modal-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 1rem;
        border-radius: 50%;
        background: rgba(231, 74, 59, 0.1);
        color: #e74a3b;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
    }

    .nova-delete-modal-title {
        font-size: 1.15rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .nova-delete-modal-text {
        color: #858796;
        font-size: 0.9rem;
        margin-bottom: 1.5rem;
    }

    .nova-delete-modal-actions {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    /* ===== Responsive ===== */
    @media (max-width: 1200px) {
        .filter-grid {
            grid-template-columns: repeat(3, 1fr);
        }
        .filter-actions {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 768px) {
        .filter-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ===== Delete Modal =====
    window.confirmDelete = function(id, name) {
        document.getElementById('deleteItemName').innerText = name;
        document.getElementById('deleteForm').action = '/items/' + id;
        document.getElementById('deleteItemModal').classList.add('show');
    };

    window.closeDeleteModal = function() {
        document.getElementById('deleteItemModal').classList.remove('show');
    };

    // Close modal on backdrop click
    const modal = document.getElementById('deleteItemModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeDeleteModal();
            }
        });
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal && modal.classList.contains('show')) {
            closeDeleteModal();
        }
    });

    // ===== Auto-search (debounced) =====
    const searchInput = document.getElementById('searchInput');
    const filterForm = document.getElementById('filterForm');
    let timeout = null;

    if (searchInput && filterForm) {
        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                filterForm.submit();
            }, 500);
        });
    }

    // ===== Auto-dismiss alerts =====
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