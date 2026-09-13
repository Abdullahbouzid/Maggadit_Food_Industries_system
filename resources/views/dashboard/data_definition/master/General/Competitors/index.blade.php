@extends('dashboard.partials.layout')

@section('title', 'Competitors Management')

@section('content')
@php
    $typeLabels = ['direct' => 'Direct', 'indirect' => 'Indirect', 'potential' => 'Potential'];
    $typeBadgeClasses = ['direct' => 'primary', 'indirect' => 'info', 'potential' => 'secondary'];
@endphp

{{-- ===== Action Bar ===== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-users" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            Competitors
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">
            Manage competitor data, codes, and associated organizations
        </p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('competitors.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus"></i> Create
        </a>
        <a href="{{ route('competitors.export', request()->query()) }}" class="nova-btn nova-btn-secondary nova-btn-sm">
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
@if(session('warning'))
    <div class="nova-alert warning">
        <i class="fas fa-exclamation-triangle"></i>
        {{ session('warning') }}
    </div>
@endif
@if(session('import_errors'))
    <div class="nova-alert danger">
        <i class="fas fa-exclamation-circle"></i>
        <div>
            <strong>Import Errors:</strong>
            <ul class="nova-mb-0 nova-mt-1">
                @foreach(session('import_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

{{-- ===== Statistics ===== --}}
<div class="nova-row cols-3 nova-mb-4">
    <div class="nova-stat">
        <div class="nova-stat-icon primary">
            <i class="fas fa-users"></i>
        </div>
        <p class="nova-stat-label">Total Competitors</p>
        <p class="nova-stat-value">{{ $totalCompetitors ?? $competitors->total() }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon success">
            <i class="fas fa-building"></i>
        </div>
        <p class="nova-stat-label">Linked to Organization</p>
        <p class="nova-stat-value">{{ $withOrganization ?? 0 }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon accent">
            <i class="fas fa-hash"></i>
        </div>
        <p class="nova-stat-label">Have GLN</p>
        <p class="nova-stat-value">{{ $withGln ?? 0 }}</p>
    </div>
</div>

{{-- ============================================================
     Import Section
     ============================================================ --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h6 class="nova-fw-bold nova-mb-0">
            <i class="fas fa-upload"></i> Import Competitors
        </h6>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('competitors.import') }}" method="POST" enctype="multipart/form-data" class="import-form">
            @csrf
            <div class="nova-form-group import-form-input">
                <label class="nova-label">Excel File (.xlsx, .xls, .csv)</label>
                <input type="file" name="import_file" class="nova-input" required>
                <div class="nova-help-text">
                    Required columns: <strong>name, code, organization_id, competitor_type, gln, contact_person, phone, email, address</strong>. Organization ID must exist.
                </div>
            </div>
            <div class="import-form-actions">
                <button type="submit" class="nova-btn nova-btn-success">
                    <i class="fas fa-upload"></i> Import Data
                </button>
                <a href="{{ route('competitors.template') }}" class="nova-btn nova-btn-outline">
                    <i class="fas fa-file-text"></i> Download Template
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ============================================================
     Export Options (Custom Filters)
     ============================================================ --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h6 class="nova-fw-bold nova-mb-0">
            <i class="fas fa-download"></i> Export Options (Custom Filters)
        </h6>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('competitors.export') }}" method="GET" class="nova-row nova-gap-3 nova-items-end">
            <div class="nova-col nova-col-md-4">
                <label class="nova-label nova-fs-sm nova-fw-semibold">Search (name/code/GLN)</label>
                <input type="text" name="search" class="nova-input" placeholder="Enter keyword...">
            </div>
            <div class="nova-col nova-col-md-3">
                <label class="nova-label nova-fs-sm nova-fw-semibold">Organization</label>
                <select name="organization_id" class="nova-select">
                    <option value="">All</option>
                    @foreach($organizationsList ?? [] as $org)
                        <option value="{{ $org->id }}">{{ $org->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="nova-col nova-col-md-3">
                <label class="nova-label nova-fs-sm nova-fw-semibold">Competitor Type</label>
                <select name="competitor_type" class="nova-select">
                    <option value="">All</option>
                    <option value="direct" {{ request('competitor_type') == 'direct' ? 'selected' : '' }}>Direct Competitor</option>
                    <option value="indirect" {{ request('competitor_type') == 'indirect' ? 'selected' : '' }}>Indirect Competitor</option>
                    <option value="potential" {{ request('competitor_type') == 'potential' ? 'selected' : '' }}>Potential Competitor</option>
                </select>
            </div>
            <div class="nova-col nova-col-md-2">
                <button type="submit" class="nova-btn nova-btn-primary nova-w-100">
                    <i class="fas fa-file-text"></i> Export Filtered
                </button>
            </div>
            <div class="nova-col nova-col-12">
                <div class="nova-help-text">Use these filters to download Excel with only the matching records.</div>
            </div>
        </form>
    </div>
</div>

{{-- ============================================================
     Filter Section
     ============================================================ --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-body">
        <form id="filterForm" method="GET" action="{{ route('competitors.index') }}" class="filter-form">
            <div class="nova-form-group filter-item filter-item-search">
                <label class="nova-label nova-fs-sm nova-fw-semibold">Search (table)</label>
                <div class="filter-search-wrapper">
                    <span class="filter-search-icon">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" class="nova-input filter-search-input"
                           placeholder="Name, code, or GLN..."
                           value="{{ request('search') }}">
                </div>
            </div>

            <div class="nova-form-group filter-item">
                <label class="nova-label nova-fs-sm nova-fw-semibold">Organization</label>
                <select name="organization_id" class="nova-select">
                    <option value="">All</option>
                    @foreach($organizationsList ?? [] as $org)
                        <option value="{{ $org->id }}" {{ request('organization_id') == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="nova-form-group filter-item">
                <label class="nova-label nova-fs-sm nova-fw-semibold">Rows per page</label>
                <select name="per_page" class="nova-select">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All</option>
                </select>
            </div>

            <div class="nova-form-group filter-item filter-item-actions">
                <button type="submit" class="nova-btn nova-btn-primary">
                    <i class="fas fa-filter"></i> Filter
                </button>
                @if(request('search') || request('organization_id') || request('per_page'))
                    <a href="{{ route('competitors.index') }}" class="nova-btn nova-btn-outline">
                        <i class="fas fa-rotate-left"></i> Reset
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
                <i class="fas fa-list"></i>
                Competitors List
            </h3>
        </div>
        <div class="nova-toolbar-actions">
            <a href="{{ route('competitors.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
                <i class="fas fa-plus"></i> Add Competitor
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
                    <th>Organization</th>
                    <th>Type</th>
                    <th>GLN</th>
                    <th>Contact Person</th>
                    <th class="nova-text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($competitors as $comp)
                <tr>
                    <td class="nova-fw-semibold">
                        {{ $loop->iteration + ($competitors->currentPage() - 1) * $competitors->perPage() }}
                    </td>
                    <td>
                        <div class="nova-flex nova-items-center">
                            <div class="nova-bg-light nova-rounded-full nova-p-2 nova-me-2">
                                <i class="fas fa-user nova-text-info"></i>
                            </div>
                            <span class="table-name">{{ $comp->name }}</span>
                        </div>
                    </td>
                    <td>
                        <code class="table-code">{{ $comp->code }}</code>
                    </td>
                    <td>
                        @if($comp->organization)
                            <i class="fas fa-building nova-text-muted nova-me-1"></i>
                            {{ $comp->organization->name }}
                        @else
                            <span class="nova-text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($comp->competitor_type && isset($typeLabels[$comp->competitor_type]))
                            <span class="nova-badge {{ $typeBadgeClasses[$comp->competitor_type] }}">{{ $typeLabels[$comp->competitor_type] }}</span>
                        @else
                            <span class="nova-text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($comp->gln)
                            <code class="table-code">{{ $comp->gln }}</code>
                        @else
                            <span class="nova-text-muted">—</span>
                        @endif
                    </td>
                    <td>{{ $comp->contact_person ?: '—' }}</td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                            <a href="{{ route('competitors.show', $comp) }}"
                               class="nova-icon-btn nova-btn-sm" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('competitors.edit', $comp) }}"
                               class="nova-icon-btn nova-btn-sm" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button"
                                    class="nova-icon-btn nova-btn-sm nova-text-danger"
                                    title="Delete"
                                    onclick="confirmDelete({{ $comp->id }}, '{{ addslashes($comp->name) }}')">
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
                                <i class="fas fa-database"></i>
                            </div>
                            <h3 class="nova-empty-title">No competitors found</h3>
                            <p class="nova-empty-text">Try adjusting your search or create a new competitor.</p>
                            <a href="{{ route('competitors.create') }}" class="nova-btn nova-btn-primary">
                                <i class="fas fa-plus"></i> Create Competitor
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
                Showing {{ $competitors->firstItem() ?? 0 }} to {{ $competitors->lastItem() ?? 0 }} of {{ $competitors->total() }} entries
            </div>
            <div class="nova-pagination">
                {{ $competitors->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

{{-- ============================================================
     Delete Confirmation Modal
     ============================================================ --}}
<div class="nova-modal-backdrop" id="globalDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">Confirm Deletion</h4>
            <p class="nova-delete-modal-text">
                Are you sure you want to delete competitor
                <strong id="deleteCompetitorName" class="nova-text-danger"></strong>?<br>
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
       Filter Form - Grid Layout
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
        const perPageSelect = document.querySelector('select[name="per_page"]');
        if (perPageSelect) {
            perPageSelect.addEventListener('change', () => {
                document.getElementById('filterForm').submit();
            });
        }

        /* ============================================================
           Delete Confirmation
           ============================================================ */
        window.confirmDelete = function(id, name) {
            document.getElementById('deleteCompetitorName').innerText = name;
            document.getElementById('deleteForm').action = '/competitors/' + id;
            document.getElementById('globalDeleteModal').classList.add('show');
        };

        /* ============================================================
           Auto-dismiss Alerts
           ============================================================ */
        setTimeout(() => {
            document.querySelectorAll('.nova-alert').forEach(alert => {
                alert.style.transition = 'opacity 0.3s, transform 0.3s';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    });
</script>
@endpush
