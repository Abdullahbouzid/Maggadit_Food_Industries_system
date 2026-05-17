@extends('dashboard.partials.layout')

@section('title', 'Organizations Management')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <!-- Header with stats and buttons -->
                <div class="row mb-4">
                    <div class="col-12 col-lg-8">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="building" class="align-middle me-2"></i>
                            Organizations
                        </h1>
                        <p class="text-muted">Manage organizational hierarchy, codes, and GLN information</p>
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('organizations.create') }}" class="btn btn-primary btn-lg shadow-sm">
                            <i data-feather="plus-circle"></i> Create
                        </a>
                        <a href="{{ route('organizations.export', request()->query()) }}" class="btn btn-secondary btn-lg shadow-sm ms-2">
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
                        <h6 class="alert-heading"><i data-feather="alert-circle" class="me-2"></i> أخطاء الاستيراد:</h6>
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
                    <div class="col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase text-muted mb-2">Total Organizations</h6>
                                        <h2 class="mb-0">{{ $totalOrganizations ?? $organizations->total() }}</h2>
                                    </div>
                                    <div class="col-auto">
                                        <div class="bg-primary bg-opacity-10 rounded-circle p-3"><i data-feather="grid" class="text-primary"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase text-muted mb-2">Root Organizations</h6>
                                        <h2 class="mb-0">{{ $rootOrganizations ?? 0 }}</h2>
                                    </div>
                                    <div class="col-auto">
                                        <div class="bg-success bg-opacity-10 rounded-circle p-3"><i data-feather="home" class="text-success"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase text-muted mb-2">Sub-Organizations</h6>
                                        <h2 class="mb-0">{{ $subOrganizations ?? 0 }}</h2>
                                    </div>
                                    <div class="col-auto">
                                        <div class="bg-info bg-opacity-10 rounded-circle p-3"><i data-feather="layers" class="text-info"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase text-muted mb-2">With GLN</h6>
                                        <h2 class="mb-0">{{ $withGlnCount ?? 0 }}</h2>
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
                        <h6 class="fw-bold mb-0"><i data-feather="upload"></i> Import Organizations</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('organizations.import') }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">
                            @csrf
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Excel File (.xlsx, .xls, .csv)</label>
                                <input type="file" name="import_file" class="form-control" required>
                                <div class="form-text">Required columns: <strong>name, code, parent_id, organization_type, gln</strong>. Parent ID must exist.</div>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-success w-100">
                                    <i data-feather="upload"></i> Import Data
                                </button>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('organizations.template') }}" class="btn btn-outline-secondary w-100">
                                    <i data-feather="file-text"></i> Download Template
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- =================== -->
                <!-- EXPORT FILTER SECTION -->
                <!-- =================== -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-light border-0 py-3">
                        <h6 class="fw-bold mb-0"><i data-feather="download"></i> Export Options (Custom Filters)</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('organizations.export') }}" method="GET" class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Search (name/code/GLN)</label>
                                <input type="text" name="search" class="form-control" placeholder="Enter keyword...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Organization Type</label>
                                <select name="organization_type" class="form-select">
                                    <option value="">All</option>
                                    <option value="headquarters">Headquarters</option>
                                    <option value="branch">Branch</option>
                                    <option value="warehouse">Warehouse</option>
                                    <!-- Add your own types as needed -->
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">GLN Status</label>
                                <select name="gln_status" class="form-select">
                                    <option value="">All</option>
                                    <option value="with">Has GLN</option>
                                    <option value="without">No GLN</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i data-feather="file-text"></i> Export Filtered
                                </button>
                            </div>
                        </form>
                        <div class="form-text mt-2">Use these filters to download Excel with only the matching records.</div>
                    </div>
                </div>

                <!-- =================== -->
                <!-- MAIN FILTER (for table) -->
                <!-- =================== -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body">
                        <form id="filterForm" method="GET" action="{{ route('organizations.index') }}" class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label small fw-semibold">Search (table)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i data-feather="search"></i></span>
                                    <input type="text" name="search" class="form-control border-start-0" placeholder="Name, code, or GLN..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Records per page</label>
                                <select name="per_page" class="form-select">
                                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                                    <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                                    @if(request('search') || request('per_page'))
                                        <a href="{{ route('organizations.index') }}" class="btn btn-outline-secondary">Reset</a>
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
                        <h5 class="fw-bold mb-0"><i data-feather="list"></i> Organizations List</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">#</th>
                                        <th>Organization Name</th>
                                        <th>Code</th>
                                        <th>Parent Organization</th>
                                        <th>Type</th>
                                        <th>GLN</th>
                                        <th class="pe-4 text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($organizations as $org)
                                    <tr>
                                        <td class="ps-4 fw-semibold">{{ $loop->iteration + ($organizations->currentPage() - 1) * $organizations->perPage() }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2">
                                                    <i data-feather="building" class="text-primary" style="width: 16px;"></i>
                                                </div>
                                                <span>{{ $org->name }}</span>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-secondary bg-opacity-10 text-dark px-3 py-2">{{ $org->code }}</span></td>
                                        <td>
                                            @if($org->parent)
                                                <i data-feather="git-branch" class="text-muted me-1" style="width: 14px;"></i>
                                                {{ $org->parent->name }}
                                            @else
                                                <span class="text-muted fst-italic">— Root —</span>
                                            @endif
                                         </td>
                                        <td>{{ $org->organization_type ?: '—' }}</td>
                                        <td>
                                            @if($org->gln)
                                                <code class="text-primary bg-light p-1 rounded">{{ $org->gln }}</code>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                         </td>
                                        <td class="pe-4 text-end">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('organizations.show', $org) }}" class="btn btn-outline-info" title="View"><i data-feather="eye"></i></a>
                                                <a href="{{ route('organizations.edit', $org) }}" class="btn btn-outline-warning" title="Edit"><i data-feather="edit"></i></a>
                                                <button type="button" class="btn btn-outline-danger" title="Delete" onclick="confirmDelete ({{ $org->id }}, '{{ addslashes($org->name) }}')">
                                                    <i data-feather="trash-2"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="py-4">
                                                <i data-feather="database" style="width: 48px; height: 48px;" class="text-muted mb-3"></i>
                                                <h5 class="text-muted">No organizations found</h5>
                                                <p class="text-muted">Try adjusting your search or create a new organization.</p>
                                                <a href="{{ route('organizations.create') }}" class="btn btn-primary"><i data-feather="plus-circle"></i> Create Organization</a>
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
                                Showing {{ $organizations->firstItem() ?? 0 }} to {{ $organizations->lastItem() ?? 0 }} of {{ $organizations->total() }} entries
                            </div>
                            <div>{{ $organizations->appends(request()->query())->links('pagination::bootstrap-5') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- =================== -->
<!-- GLOBAL DELETE MODAL -->
<!-- =================== -->
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
                <p class="text-muted">Are you sure you want to delete <strong id="deleteOrgName" class="text-danger"></strong>?<br>This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger px-4">Delete Forever</button>
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
            document.getElementById('deleteOrgName').innerText = name;
            document.getElementById('deleteForm').action = '/organizations/' + id;
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