@extends('dashboard.partials.layout')

@section('title', 'Warehouses Management')

@section('content')
{{-- ===== Action Bar ===== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-home" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            Warehouses
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Manage physical storage locations</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('warehouses.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus"></i> Create
        </a>
        <a href="{{ route('warehouses.export', request()->query()) }}" class="nova-btn nova-btn-secondary nova-btn-sm">
            <i class="fas fa-download"></i> Export Table
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
        <strong>Import Errors:</strong>
        <ul>@foreach(session('import_errors') as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

{{-- ===== Statistics ===== --}}
<div class="nova-row cols-3 nova-mb-4">
    <div class="nova-stat">
        <div class="nova-stat-icon primary">
            <i class="fas fa-grid"></i>
        </div>
        <p class="nova-stat-label">Total Warehouses</p>
        <p class="nova-stat-value">{{ $total }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon success">
            <i class="fas fa-check-circle"></i>
        </div>
        <p class="nova-stat-label">Active</p>
        <p class="nova-stat-value">{{ $active }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon accent">
            <i class="fas fa-eye-off"></i>
        </div>
        <p class="nova-stat-label">Inactive</p>
        <p class="nova-stat-value">{{ $inactive }}</p>
    </div>
</div>

{{-- ===== Import Section ===== --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h6 class="nova-fw-bold mb-0">
            <i class="fas fa-upload"></i> Import Warehouses
        </h6>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('warehouses.import') }}" method="POST" enctype="multipart/form-data" class="import-form">
            @csrf
            <div class="nova-form-group">
                <label class="nova-label">Excel File (.xlsx, .xls, .csv)</label>
                <input type="file" name="import_file" class="nova-input" required>
                <div class="nova-help-text">
                    Required columns: <code>code</code>, <code>name_en</code>, <code>name_ar</code>, 
                    <code>warehouse_type_id</code>, <code>organization_id</code>
                </div>
            </div>
            <div class="import-form-actions">
                <button type="submit" class="nova-btn nova-btn-primary">
                    <i class="fas fa-upload"></i> Import Data
                </button>
                <a href="{{ route('warehouses.template') }}" class="nova-btn nova-btn-outline">
                    <i class="fas fa-file-text"></i> Download Template
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ===== Filter Section ===== --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-body" style="padding: 1rem 1.5rem;">
        <form id="filterForm" method="GET" action="{{ route('warehouses.index') }}" class="filter-form">
            {{-- Search --}}
            <div class="nova-form-group filter-item filter-item-search">
                <label class="nova-label">Search</label>
                <div class="filter-search-wrapper">
                    <span class="filter-search-icon">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" class="nova-input filter-search-input"
                           placeholder="Code, Name, Phone..."
                           value="{{ request('search') }}">
                </div>
            </div>

            {{-- Warehouse Type --}}
            <div class="nova-form-group filter-item">
                <label class="nova-label">Warehouse Type</label>
                <select name="warehouse_type_id" class="nova-select">
                    <option value="">All</option>
                    @foreach($warehouseTypes as $type)
                        <option value="{{ $type->id }}" {{ request('warehouse_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->name_en }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Organization --}}
            <div class="nova-form-group filter-item">
                <label class="nova-label">Organization</label>
                <select name="organization_id" class="nova-select">
                    <option value="">All</option>
                    @foreach($organizations as $org)
                        <option value="{{ $org->id }}" {{ request('organization_id') == $org->id ? 'selected' : '' }}>
                            {{ $org->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Status --}}
            <div class="nova-form-group filter-item">
                <label class="nova-label">Status</label>
                <select name="status" class="nova-select">
                    <option value="">All</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            {{-- Per Page --}}
            <div class="nova-form-group filter-item">
                <label class="nova-label">Per Page</label>
                <select name="per_page" class="nova-select">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All</option>
                </select>
            </div>

            {{-- Actions --}}
            <div class="nova-form-group filter-item filter-item-actions">
                <button type="submit" class="nova-btn nova-btn-primary">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <a href="{{ route('warehouses.index') }}" class="nova-btn nova-btn-outline">
                    <i class="fas fa-rotate-left"></i> Reset
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
                Warehouses List
            </h3>
        </div>
        <div class="nova-toolbar-actions">
            <a href="{{ route('warehouses.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
                <i class="fas fa-plus"></i> Add Warehouse
            </a>
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Code</th>
                    <th>Name (EN)</th>
                    <th>Name (AR)</th>
                    <th>Type</th>
                    <th>Organization</th>
                    <th>Phone</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th class="nova-text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($warehouses as $wh)
                <tr>
                    <td class="nova-fw-semibold">
                        {{ $loop->iteration + ($warehouses->currentPage() - 1) * $warehouses->perPage() }}
                    </td>
                    <td><code>{{ $wh->code }}</code></td>
                    <td>{{ $wh->name_en }}</td>
                    <td>{{ $wh->name_ar }}</td>
                    <td>{{ $wh->warehouseType?->name_en ?? '-' }}</td>
                    <td>{{ $wh->organization?->name ?? '-' }}</td>
                    <td>{{ $wh->phone ?? '-' }}</td>
                    <td>
                        @if($wh->city)
                            {{ $wh->city->name }},
                        @endif
                        @if($wh->country)
                            {{ $wh->country->name }}
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        @if($wh->is_active)
                            <span class="nova-badge success">Active</span>
                        @else
                            <span class="nova-badge secondary">Inactive</span>
                        @endif
                    </td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                            <a href="{{ route('warehouses.show', $wh) }}" class="nova-icon-btn nova-btn-sm" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('warehouses.edit', $wh) }}" class="nova-icon-btn nova-btn-sm" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger" title="Delete"
                                    onclick="confirmDelete({{ $wh->id }}, '{{ addslashes($wh->name_en) }}')">
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
                            <h3 class="nova-empty-title">No warehouses found</h3>
                            <p class="nova-empty-text">Create your first warehouse to get started.</p>
                            <a href="{{ route('warehouses.create') }}" class="nova-btn nova-btn-primary">
                                <i class="fas fa-plus-circle"></i> Create Warehouse
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
                Showing {{ $warehouses->firstItem() ?? 0 }} to {{ $warehouses->lastItem() ?? 0 }} of {{ $warehouses->total() }} entries
            </div>
            <div class="nova-pagination">
                {{ $warehouses->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

{{-- ===== Delete Confirmation Modal ===== --}}
<div class="nova-modal-backdrop" id="globalDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">Confirm Deletion</h4>
            <p class="nova-delete-modal-text">
                Are you sure you want to delete warehouse
                <strong id="deleteItemName" class="nova-text-danger"></strong>?<br>
                This action cannot be undone.
            </p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline"
                        onclick="document.getElementById('globalDeleteModal').classList.remove('show')">
                    Cancel
                </button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="nova-btn nova-btn-danger">Delete</button>
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

    /* ===== Filter Form - Grid Layout ===== */
    .filter-form {
        display: grid;
        grid-template-columns: 2fr 1.2fr 1.2fr 1fr 0.8fr auto;
        gap: 0.75rem;
        align-items: end;
    }

    .filter-form .nova-form-group {
        margin-bottom: 0;
        min-width: 0;
    }

    /* Search Input Wrapper */
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
        border-right: none;
        border-radius: 10px 0 0 10px;
        color: var(--nova-text-4);
        flex-shrink: 0;
    }

    .filter-search-input {
        border-radius: 0 10px 10px 0 !important;
        border-left: none !important;
        flex: 1;
        min-width: 0;
    }

    /* Filter Actions */
    .filter-item-actions {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .filter-item-actions .nova-btn {
        white-space: nowrap;
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
        // Auto-submit on per_page change
        const perPageSelect = document.querySelector('select[name="per_page"]');
        if (perPageSelect) {
            perPageSelect.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        }

        // Delete confirmation
        window.confirmDelete = function(id, name) {
            document.getElementById('deleteItemName').innerText = name;
            document.getElementById('deleteForm').action = '/warehouses/' + id;
            document.getElementById('globalDeleteModal').classList.add('show');
        };

        // Auto-dismiss alerts
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