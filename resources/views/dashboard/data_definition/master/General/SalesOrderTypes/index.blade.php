@extends('dashboard.partials.layout')

@section('title', 'Sales Order Types Management')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
             <i class="fas fa-shopping-cart" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            Sales Order Types
        </h2>
        <p class="nova-page-subtitle">Manage sales order types, codes, and default settings</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('sales-order-types.export', request()->query()) }}" class="nova-btn nova-btn-secondary nova-btn-sm">
            <i class="fas fa-download"></i> Export Table
        </a>
        <a href="{{ route('sales-order-types.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus"></i> Create
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
        <h6 class="nova-fw-bold" style="margin: 0 0 0.25rem;"><i class="fas fa-exclamation-circle"></i> Import Errors:</h6>
        <ul style="margin: 0.25rem 0 0; padding-left: 1.25rem;">
            @foreach(session('import_errors') as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Statistics Cards --}}
<div class="nova-row cols-4">
    <div class="nova-stat">
        <div class="nova-stat-icon primary">
            <i class="fas fa-grid-2"></i>
        </div>
        <p class="nova-stat-label">Total Types</p>
        <p class="nova-stat-value">{{ $total ?? $salesOrderTypes->total() }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon secondary">
            <i class="fas fa-check-circle"></i>
        </div>
        <p class="nova-stat-label">Active Types</p>
        <p class="nova-stat-value">{{ $activeCount ?? 0 }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon danger">
            <i class="fas fa-x-circle"></i>
        </div>
        <p class="nova-stat-label">Inactive Types</p>
        <p class="nova-stat-value">{{ $inactiveCount ?? 0 }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon accent">
            <i class="fas fa-star"></i>
        </div>
        <p class="nova-stat-label">Default Type</p>
        <p class="nova-stat-value">{{ $defaultCount ?? 0 }}</p>
    </div>
</div>

{{-- Import Section --}}
<div class="nova-card mb-4">
    <div class="nova-card-header">
        <h2 class="nova-card-title" style="margin: 0;">
            <i class="fas fa-upload"></i>
            Import Sales Order Types
        </h2>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('sales-order-types.import') }}" method="POST" enctype="multipart/form-data" class="nova-row cols-3" style="align-items: end;">
            @csrf
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">Excel File (.xlsx, .xls, .csv)</label>
                <input type="file" name="import_file" class="nova-input" required>
                <div class="nova-help-text">Required columns: <strong>name, code, active, default</strong>. Active/Default: Yes/No or 1/0 or true/false.</div>
            </div>
            <div style="height: fit-content;">
                <button type="submit" class="nova-btn nova-btn-primary nova-btn-block">
                    <i class="fas fa-upload"></i> Import Data
                </button>
            </div>
            <div style="height: fit-content;">
                <a href="{{ route('sales-order-types.template') }}" class="nova-btn nova-btn-outline nova-btn-block">
                    <i class="fas fa-file-alt"></i> Download Template
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Export Options (Custom Filters) --}}
<div class="nova-card mb-4">
    <div class="nova-card-header">
        <h2 class="nova-card-title" style="margin: 0;">
            <i class="fas fa-download"></i>
            Export Options (Custom Filters)
        </h2>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('sales-order-types.export') }}" method="GET" class="nova-row cols-3" style="align-items: end;">
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">Search (name/code)</label>
                <input type="text" name="search" class="nova-input" placeholder="Enter keyword...">
            </div>
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">Status</label>
                <select name="active_status" class="nova-select">
                    <option value="">All</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div style="height: fit-content;">
                <button type="submit" class="nova-btn nova-btn-primary nova-btn-block">
                    <i class="fas fa-file-excel"></i> Export Filtered
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Main Filter (for table) --}}
<div class="nova-card mb-4">
    <div class="nova-card-body" style="padding: 1rem 1.5rem;">
        <form id="filterForm" method="GET" action="{{ route('sales-order-types.index') }}" class="nova-row cols-3" style="align-items: end; gap: 0.75rem;">
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">Search (name/code)</label>
                <div class="nova-input-group" style="display: flex; gap: 0;">
                     <span class="nova-input-group-text" style="display: flex; align-items: center; padding: 0 0.875rem; background: var(--nova-surface-2); border: 1.5px solid var(--nova-border); border-right: none; border-radius: 10px 0 0 10px; color: var(--nova-text-4);">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" class="nova-input" placeholder="Name or code..." value="{{ request('search') }}" style="border-radius: 0 10px 10px 0; border-left: none;">
                </div>
            </div>
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">Status</label>
                <select name="active_status" class="nova-select">
                    <option value="">All</option>
                    <option value="active" {{ request('active_status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('active_status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">Rows per page</label>
                <select name="per_page" class="nova-select">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All</option>
                </select>
            </div>
            <div class="nova-flex nova-gap-2" style="height: fit-content;">
                <button type="submit" class="nova-btn nova-btn-primary">Filter</button>
                @if(request('search') || request('active_status') || request('per_page'))
                    <a href="{{ route('sales-order-types.index') }}" class="nova-btn nova-btn-outline">Reset</a>
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
                Sales Order Types List
            </h3>
        </div>
        <div class="nova-toolbar-actions">
            <a href="{{ route('sales-order-types.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
                <i class="fas fa-plus"></i> Create Type
            </a>
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Status</th>
                    <th>Default</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salesOrderTypes as $type)
                <tr>
                    <td class="nova-fw-semibold">{{ $loop->iteration + ($salesOrderTypes->currentPage() - 1) * $salesOrderTypes->perPage() }}</td>
                    <td>{{ $type->name }}</td>
                    <td><span class="nova-badge secondary">{{ $type->code }}</span></td>
                    <td>
                        @if($type->active)
                            <span class="nova-badge success">Active</span>
                        @else
                            <span class="nova-badge danger">Inactive</span>
                        @endif
                    </td>
                    <td>
                        @if($type->default)
                            <span class="nova-badge accent"><i class="fas fa-star"></i> Default</span>
                        @else
                            —
                        @endif
                    </td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                <a href="{{ route('sales-order-types.show', $type) }}" class="nova-icon-btn nova-btn-sm" title="View">
                    <i class="fas fa-eye"></i>
                </a>
                <a href="{{ route('sales-order-types.edit', $type) }}" class="nova-icon-btn nova-btn-sm" title="Edit">
                    <i class="fas fa-edit"></i>
                </a>
                <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger" title="Delete" onclick="confirmDelete({{ $type->id }}, '{{ addslashes($type->name) }}')">
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
                                <i class="fas fa-database" style="width: 40px; height: 40px;"></i>
                            </div>
                            <h5 class="nova-text-muted">No sales order types found</h5>
                            <a href="{{ route('sales-order-types.create') }}" class="nova-btn nova-btn-primary">Create one</a>
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
                عرض {{ $salesOrderTypes->firstItem() ?? 0 }} إلى {{ $salesOrderTypes->lastItem() ?? 0 }} من أصل {{ $salesOrderTypes->total() }} سجلاً
            </div>
            <div class="nova-pagination">
                {{ $salesOrderTypes->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

{{-- Delete Confirmation Modal (Nova) --}}
<div class="nova-modal-backdrop" id="globalDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">تأكيد الحذف</h4>
            <p class="nova-delete-modal-text">هل أنت متأكد أنك تريد حذف <strong id="deleteItemName" class="nova-text-danger"></strong>؟<br>هذا الإجراء لا يمكن التراجع عنه.</p>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const perPageSelect = document.querySelector('select[name="per_page"]');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    }

    window.confirmDelete = (id, name) => {
        document.getElementById('deleteItemName').innerText = name;
        document.getElementById('deleteForm').action = '/sales-order-types/' + id;
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
