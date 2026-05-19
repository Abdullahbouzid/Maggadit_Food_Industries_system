@extends('dashboard.partials.layout')

@section('title', 'Competitors Management')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <!-- Header with stats and buttons -->
                <div class="row mb-4">
                    <div class="col-12 col-lg-8">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="users" class="align-middle me-2"></i>
                            Competitors
                        </h1>
                        <p class="text-muted">Manage competitor data, codes, and associated organizations</p>
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('competitors.create') }}" class="btn btn-primary btn-lg shadow-sm">
                            <i data-feather="plus-circle"></i> Create
                        </a>
                        <a href="{{ route('competitors.export', request()->query()) }}" class="btn btn-secondary btn-lg shadow-sm ms-2">
                            <i data-feather="download"></i> Export Table
                        </a>
                    </div>
                </div>

                <!-- Alerts Section -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i data-feather="check-circle" class="me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i data-feather="alert-circle" class="me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i data-feather="alert-triangle" class="me-2"></i> {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('import_errors'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h6 class="alert-heading"><i data-feather="alert-circle" class="me-2"></i> Import Errors:</h6>
                        <ul class="mb-0">
                            @foreach(session('import_errors') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Statistics Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-sm-6 col-xl-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase text-muted mb-2">Total Competitors</h6>
                                        <h2 class="mb-0">{{ $totalCompetitors ?? $competitors->total() }}</h2>
                                    </div>
                                    <div class="col-auto">
                                        <div class="bg-primary bg-opacity-10 rounded-circle p-3"><i data-feather="users" class="text-primary"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase text-muted mb-2">Linked to Organization</h6>
                                        <h2 class="mb-0">{{ $withOrganization ?? 0 }}</h2>
                                    </div>
                                    <div class="col-auto">
                                        <div class="bg-success bg-opacity-10 rounded-circle p-3"><i data-feather="building" class="text-success"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase text-muted mb-2">Have GLN</h6>
                                        <h2 class="mb-0">{{ $withGln ?? 0 }}</h2>
                                    </div>
                                    <div class="col-auto">
                                        <div class="bg-warning bg-opacity-10 rounded-circle p-3"><i data-feather="hash" class="text-warning"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- =================== -->
                <!-- IMPORT SECTION -->
                <!-- =================== -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-light border-0 py-3">
                        <h6 class="fw-bold mb-0"><i data-feather="upload"></i> Import Competitors</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('competitors.import') }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">
                            @csrf
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Excel File (.xlsx, .xls, .csv)</label>
                                <input type="file" name="import_file" class="form-control" required>
                                <div class="form-text">Required columns: <strong>name, code, organization_id, competitor_type, gln, contact_person, phone, email, address</strong>. Organization ID must exist.</div>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-success w-100">
                                    <i data-feather="upload"></i> Import Data
                                </button>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('competitors.template') }}" class="btn btn-outline-secondary w-100">
                                    <i data-feather="file-text"></i> Download Template
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- =================== -->
                <!-- EXPORT OPTIONS (CUSTOM FILTERS) -->
                <!-- =================== -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-light border-0 py-3">
                        <h6 class="fw-bold mb-0"><i data-feather="download"></i> Export Options (Custom Filters)</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('competitors.export') }}" method="GET" class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Search (name/code/GLN)</label>
                                <input type="text" name="search" class="form-control" placeholder="Enter keyword...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Organization</label>
                                <select name="organization_id" class="form-select">
                                    <option value="">All</option>
                                    @foreach($organizationsList ?? [] as $org)
                                        <option value="{{ $org->id }}">{{ $org->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Competitor Type</label>
                               <select name="competitor_type" class="form-select">
                                    <option value="">All</option>
                                    <option value="direct" {{ request('competitor_type') == 'direct' ? 'selected' : '' }}>Direct Competitor</option>
                                    <option value="indirect" {{ request('competitor_type') == 'indirect' ? 'selected' : '' }}>Indirect Competitor</option>
                                    <option value="potential" {{ request('competitor_type') == 'potential' ? 'selected' : '' }}>Potential Competitor</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i data-feather="file-text"></i> Export Filtered
                                </button>
                            </div>
                            <div class="col-12">
                                <div class="form-text">Use these filters to download Excel with only the matching records.</div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- =================== -->
                <!-- MAIN FILTER (for table) -->
                <!-- =================== -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body">
                        <form id="filterForm" method="GET" action="{{ route('competitors.index') }}" class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Search (table)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i data-feather="search"></i></span>
                                    <input type="text" name="search" class="form-control border-start-0" placeholder="Name, code, or GLN..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Organization</label>
                                <select name="organization_id" class="form-select">
                                    <option value="">All</option>
                                    @foreach($organizationsList ?? [] as $org)
                                        <option value="{{ $org->id }}" {{ request('organization_id') == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Rows per page</label>
                                <select name="per_page" class="form-select">
                                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                                    <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                                    @if(request('search') || request('organization_id') || request('per_page'))
                                        <a href="{{ route('competitors.index') }}" class="btn btn-outline-secondary">Reset</a>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- =================== -->
                <!-- DATA TABLE -->
                <!-- =================== -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold mb-0"><i data-feather="list"></i> Competitors List</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">#</th>
                                        <th>Name</th>
                                        <th>Code</th>
                                        <th>Organization</th>
                                        <th>Type</th>
                                        <th>GLN</th>
                                        <th>Contact Person</th>
                                        <th class="pe-4 text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($competitors as $comp)
                                    <tr>
                                        <td class="ps-4 fw-semibold">{{ $loop->iteration + ($competitors->currentPage() - 1) * $competitors->perPage() }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-info bg-opacity-10 rounded-circle p-2 me-2">
                                                    <i data-feather="user" class="text-info" style="width: 16px;"></i>
                                                </div>
                                                <span>{{ $comp->name }}</span>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-secondary bg-opacity-10 text-dark px-3 py-2">{{ $comp->code }}</span></td>
                                        <td>
                                            @if($comp->organization)
                                                <i data-feather="building" class="text-muted me-1" style="width: 14px;"></i>
                                                {{ $comp->organization->name }}
                                            @else
                                                <span class="text-muted fst-italic">—</span>
                                            @endif
                                         </td>
                                        <td>{{ $comp->competitor_type ?: '—' }}</td>
                                        <td>
                                            @if($comp->gln)
                                                <code class="text-primary bg-light p-1 rounded">{{ $comp->gln }}</code>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                         </td>
                                        <td>{{ $comp->contact_person ?: '—' }}</td>
                                        <td class="pe-4 text-end">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('competitors.show', $comp) }}" class="btn btn-outline-info" title="View"><i data-feather="eye"></i></a>
                                                <a href="{{ route('competitors.edit', $comp) }}" class="btn btn-outline-warning" title="Edit"><i data-feather="edit"></i></a>
                                                <button type="button" class="btn btn-outline-danger" title="Delete" onclick="confirmDelete({{ $comp->id }}, '{{ addslashes($comp->name) }}')">
                                                    <i data-feather="trash-2"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="py-4">
                                                <i data-feather="database" style="width: 48px; height: 48px;" class="text-muted mb-3"></i>
                                                <h5 class="text-muted">No competitors found</h5>
                                                <p class="text-muted">Try adjusting your search or create a new competitor.</p>
                                                <a href="{{ route('competitors.create') }}" class="btn btn-primary"><i data-feather="plus-circle"></i> Create Competitor</a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 py-3 px-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div class="small text-muted">
                                Showing {{ $competitors->firstItem() ?? 0 }} to {{ $competitors->lastItem() ?? 0 }} of {{ $competitors->total() }} entries
                            </div>
                            <div>{{ $competitors->appends(request()->query())->links('pagination::bootstrap-5') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Global Delete Modal -->
<div class="modal fade" id="globalDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center pt-0">
                <div class="bg-danger bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3">
                    <i data-feather="alert-triangle" class="text-danger" style="width: 40px;"></i>
                </div>
                <h4 class="fw-bold">Confirm Deletion</h4>
                <p class="text-muted">Are you sure you want to delete <strong id="deleteCompetitorName" class="text-danger"></strong>?<br>This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger px-4">Delete Permanently</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .rounded-4 { border-radius: 1rem !important; }
    .table thead th { border-bottom-width: 1px; font-weight: 600; }
    .btn-group .btn { transition: all 0.2s ease; }
    .btn-group .btn:hover { transform: translateY(-2px); }
    .badge.bg-secondary.bg-opacity-10 { background-color: rgba(108,117,125,0.1) !important; color: #212529; }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Replace Feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }

        // Auto-submit filter form when per_page changes
        const perPageSelect = document.querySelector('select[name="per_page"]');
        if (perPageSelect) {
            perPageSelect.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        }

        // Global delete confirmation
        window.confirmDelete = function(id, name) {
            document.getElementById('deleteCompetitorName').innerText = name;
            document.getElementById('deleteForm').action = '/competitors/' + id;
            var modalEl = document.getElementById('globalDeleteModal');
            var modal = new bootstrap.Modal(modalEl);
            modal.show();
        };

        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                if (typeof bootstrap !== 'undefined') {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                } else {
                    alert.style.display = 'none';
                }
            });
        }, 5000);
    });
</script>
@endpush