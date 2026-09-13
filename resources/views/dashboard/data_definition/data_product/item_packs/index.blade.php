@extends('dashboard.partials.layout')

@section('title', 'Item Packs Management')

@section('content')
{{-- ===== Action Bar ===== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-box" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            Item Packs
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">
            Manage packaging definitions for your items.
        </p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('item-packs.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus-circle"></i> Add Item Pack
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
<div class="nova-row cols-3 nova-mb-4">
    <div class="nova-stat">
        <div class="nova-stat-icon primary">
            <i class="fas fa-box"></i>
        </div>
        <p class="nova-stat-label">Total Item Packs</p>
        <p class="nova-stat-value">{{ $total }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon success">
            <i class="fas fa-check-circle"></i>
        </div>
        <p class="nova-stat-label">Default Packs</p>
        <p class="nova-stat-value">{{ $defaultCount }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon accent">
            <i class="fas fa-weight-hanging"></i>
        </div>
        <p class="nova-stat-label">Weight Priced</p>
        <p class="nova-stat-value">{{ $weightPricedCount }}</p>
    </div>
</div>

{{-- ===== Filter Section ===== --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-body">
        <form method="GET" action="{{ route('item-packs.index') }}" id="filterForm">
            <div class="filter-grid">
                <div class="nova-form-group" style="margin-bottom: 0;">
                    <label class="nova-label">Search</label>
                    <div class="nova-input-group">
                        <span class="nova-input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" id="searchInput" class="nova-input"
                               placeholder="Item name, barcode, UOM..."
                               value="{{ request('search') }}" autocomplete="off">
                    </div>
                </div>
                <div class="nova-form-group" style="margin-bottom: 0;">
                    <label class="nova-label">Per page</label>
                    <select name="per_page" class="nova-select"
                            onchange="document.getElementById('filterForm').submit();">
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
                    @if(request()->anyFilled(['search', 'per_page']))
                        <a href="{{ route('item-packs.index') }}" class="nova-btn nova-btn-outline">
                            <i class="fas fa-times"></i> Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ===== Active Filters Indicator ===== --}}
@if(request()->anyFilled(['search']))
    <div class="active-filters">
        <span class="active-filters-label"><i class="fas fa-filter"></i> الفلاتر النشطة:</span>
        @if(request('search'))
            <span class="nova-badge info">بحث: {{ request('search') }}</span>
        @endif
    </div>
@endif

{{-- ===== Table ===== --}}
<div class="nova-table-wrapper">
    <div class="nova-toolbar">
        <div class="nova-toolbar-filters">
            <h3 class="nova-card-title" style="margin: 0;">
                <i class="fas fa-list"></i>
                Item Packs List
                @if($itemPacks->total())
                    <span class="nova-badge primary" style="margin-left: 0.5rem;">{{ $itemPacks->total() }}</span>
                @endif
            </h3>
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>العنصر</th>
                    <th>الباركود</th>
                    <th>الكمية</th>
                    <th>وحدة القياس</th>
                    <th class="nova-text-center">افتراضي</th>
                    <th class="nova-text-center">سعر بالوزن</th>
                    <th class="nova-text-end">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($itemPacks as $pack)
                <tr>
                    <td class="nova-fw-semibold">
                        {{ $loop->iteration + ($itemPacks->currentPage() - 1) * $itemPacks->perPage() }}
                    </td>
                    <td>
                        @if($pack->item)
                            <a href="{{ route('items.show', $pack->item) }}"
                               class="nova-text-primary nova-fw-bold">
                                {{ $pack->item->name_en ?? $pack->item->name_ar ?? '—' }}
                            </a>
                        @else
                            <span class="nova-text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($pack->barcode)
                            <code class="nova-code">{{ $pack->barcode }}</code>
                        @else
                            <span class="nova-text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="nova-badge info">
                            {{ rtrim(rtrim(number_format($pack->quantity_in_pack, 3, '.', ''), '0'), '.') }}
                        </span>
                    </td>
                    <td>
                        @if($pack->packCapacity?->uom)
                            {{ $pack->packCapacity->uom->name_en ?? $pack->packCapacity->uom->uom_symbol ?? '—' }}
                        @else
                            <span class="nova-text-muted">—</span>
                        @endif
                    </td>
                    <td class="nova-text-center">
                        @if($pack->is_default_pack)
                            <span class="nova-badge success">
                                <i class="fas fa-check-circle"></i> Yes
                            </span>
                        @else
                            <span class="nova-badge secondary">No</span>
                        @endif
                    </td>
                    <td class="nova-text-center">
                        @if($pack->is_weight_priced)
                            <span class="nova-badge success">
                                <i class="fas fa-weight-hanging"></i> Yes
                            </span>
                        @else
                            <span class="nova-badge secondary">No</span>
                        @endif
                    </td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                            <a href="{{ route('item-packs.show', $pack) }}"
                               class="nova-icon-btn nova-btn-sm" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('item-packs.edit', $pack) }}"
                               class="nova-icon-btn nova-btn-sm" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button"
                                    class="nova-icon-btn nova-btn-sm nova-text-danger"
                                    title="Delete"
                                    onclick="confirmDelete({{ $pack->id }}, '{{ addslashes($pack->barcode ?? 'Item Pack #' . $pack->id) }}')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="nova-text-center" style="padding: 2.5rem 1rem;">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-box"></i>
                            </div>
                            <h5 class="nova-text-muted">No item packs found</h5>
                            <p class="nova-text-muted nova-fs-sm">
                                @if(request()->anyFilled(['search']))
                                    Try adjusting your search or filter criteria.
                                @else
                                    Get started by adding your first item pack.
                                @endif
                            </p>
                            @if(request()->anyFilled(['search']))
                                <a href="{{ route('item-packs.index') }}" class="nova-btn nova-btn-outline nova-mt-2">
                                    <i class="fas fa-times"></i> Clear Filters
                                </a>
                            @else
                                <a href="{{ route('item-packs.create') }}" class="nova-btn nova-btn-primary nova-mt-2">
                                    <i class="fas fa-plus-circle"></i> Add Item Pack
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
                Showing {{ $itemPacks->firstItem() ?? 0 }} to {{ $itemPacks->lastItem() ?? 0 }} of {{ $itemPacks->total() }} entries
            </div>
            <div class="nova-pagination">
                {{ $itemPacks->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

{{-- ===== Delete Confirmation Modal (moved inside content) ===== --}}
<div class="nova-modal-backdrop" id="deleteItemPackModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">Confirm Deletion</h4>
            <p class="nova-delete-modal-text">
                Are you sure you want to delete item pack
                <strong id="deleteItemPackName" class="nova-text-danger"></strong>?
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
                        <i class="fas fa-trash-alt"></i> Delete
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
        grid-template-columns: 2fr 1fr auto;
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
    @media (max-width: 992px) {
        .filter-grid {
            grid-template-columns: 1fr 1fr;
        }
        .filter-actions {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 576px) {
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
        document.getElementById('deleteItemPackName').innerText = name;
        document.getElementById('deleteForm').action = '/item-packs/' + id;
        document.getElementById('deleteItemPackModal').classList.add('show');
    };

    window.closeDeleteModal = function() {
        document.getElementById('deleteItemPackModal').classList.remove('show');
    };

    // Close modal on backdrop click
    const modal = document.getElementById('deleteItemPackModal');
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