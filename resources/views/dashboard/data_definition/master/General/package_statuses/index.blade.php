@extends('dashboard.partials.layout')

@section('title', 'Package Statuses Management')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <!-- Header -->
                <div class="row mb-4">
                    <div class="col-12 col-lg-8">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="layers" class="align-middle me-2"></i>
                            Package Statuses
                        </h1>
                        <p class="text-muted">Manage package status definitions for each organization</p>
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('package-statuses.create') }}" class="btn btn-primary btn-lg shadow-sm">
                            <i data-feather="plus-circle"></i> Create
                        </a>
                        <a href="{{ route('package-statuses.export', request()->query()) }}" class="btn btn-secondary btn-lg shadow-sm ms-2">
                            <i data-feather="download"></i> Export Table
                        </a>
                    </div>
                </div>

                <!-- Alerts -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i data-feather="check-circle" class="me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('import_errors'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <h6 class="alert-heading fw-bold mb-1"><i data-feather="alert-triangle" class="me-2"></i> Import completed with errors in the following rows:</h6>
                        <ul class="mb-0 mt-2 text-sm ps-3">
                            @foreach(session('import_errors') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i data-feather="x-circle" class="me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Import Section -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-light border-0 py-3">
                        <h6 class="fw-bold mb-0"><i data-feather="upload"></i> Import Package Statuses</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('package-statuses.import') }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">
                            @csrf
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Excel File (.xlsx, .xls, .csv)</label>
                                <input type="file" name="import_file" class="form-control" required>
                                <div class="form-text">Required columns: status_name, organization_id, resellable, expired, default, lpo_required, is_related_to_cn_request, is_price_editable</div>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-success w-100"><i data-feather="upload"></i> Import Data</button>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('package-statuses.template') }}" class="btn btn-outline-secondary w-100"><i data-feather="file-text"></i> Download Template</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Export Filter Section -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-light border-0 py-3">
                        <h6 class="fw-bold mb-0"><i data-feather="download"></i> Export Options (Custom Filters)</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('package-statuses.export') }}" method="GET" class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Status Name</label>
                                <input type="text" name="search" class="form-control" placeholder="Keyword...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Organization</label>
                                <select name="organization_id" class="form-select">
                                    <option value="">All</option>
                                    @foreach($organizations as $org)
                                        <option value="{{ $org->id }}">{{ $org->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold">Resellable</label>
                                <select name="resellable" class="form-select">
                                    <option value="">All</option>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold">Default</label>
                                <select name="default" class="form-select">
                                    <option value="">All</option>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100"><i data-feather="file-text"></i> Export</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Main Filter (for table) -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body">
                        <form id="filterForm" method="GET" action="{{ route('package-statuses.index') }}" class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Status name or organization..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Organization</label>
                                <select name="organization_id" class="form-select">
                                    <option value="">All</option>
                                    @foreach($organizations as $org)
                                        <option value="{{ $org->id }}" {{ request('organization_id') == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                                    @endforeach
                                </select>
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
                            <div class="col-md-2">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                                    @if(request()->query())
                                        <a href="{{ route('package-statuses.index') }}" class="btn btn-outline-secondary">Reset</a>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold mb-0"><i data-feather="list"></i> Package Statuses List</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">#</th>
                                        <th>Status Name</th>
                                        <th>Organization</th>
                                        <th>Resellable</th>
                                        <th>Expired</th>
                                        <th>Default</th>
                                        <th>LPO Required</th>
                                        <th>Related to CN</th>
                                        <th>Price Editable</th>
                                        <th class="pe-4 text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($packageStatuses as $status)
                                    <tr>
                                        <td class="ps-4">{{ $loop->iteration + ($packageStatuses->currentPage() - 1) * $packageStatuses->perPage() }}</td>
                                        <td>{{ $status->status_name }}</td>
                                        <td>{{ $status->organization->name ?? '—' }}</td>
                                        <td>{!! $status->resellable ? '<span class="badge bg-success bg-opacity-10 text-success">Yes</span>' : '<span class="badge bg-secondary bg-opacity-10 text-secondary">No</span>' !!}</td>
                                        <td>{!! $status->expired ? '<span class="badge bg-danger bg-opacity-10 text-danger">Yes</span>' : '<span class="badge bg-secondary bg-opacity-10 text-secondary">No</span>' !!}</td>
                                        <td>{!! $status->default ? '<span class="badge bg-info bg-opacity-10 text-info">Yes</span>' : '<span class="badge bg-secondary bg-opacity-10 text-secondary">No</span>' !!}</td>
                                        <td>{!! $status->lpo_required ? '<span class="badge bg-warning bg-opacity-10 text-warning">Yes</span>' : '<span class="badge bg-secondary bg-opacity-10 text-secondary">No</span>' !!}</td>
                                        <td>{!! $status->is_related_to_cn_request ? '<span class="badge bg-primary bg-opacity-10 text-primary">Yes</span>' : '<span class="badge bg-secondary bg-opacity-10 text-secondary">No</span>' !!}</td>
                                        <td>{!! $status->is_price_editable ? '<span class="badge bg-dark bg-opacity-10 text-dark">Yes</span>' : '<span class="badge bg-secondary bg-opacity-10 text-secondary">No</span>' !!}</td>
                                        <td class="pe-4 text-end">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('package-statuses.show', $status) }}" class="btn btn-outline-info" title="View"><i data-feather="eye"></i></a>
                                                <a href="{{ route('package-statuses.edit', $status) }}" class="btn btn-outline-warning" title="Edit"><i data-feather="edit"></i></a>
                                                <button type="button" class="btn btn-outline-danger" title="Delete" onclick="confirmDelete({{ $status->id }}, '{{ addslashes($status->status_name) }}')">
                                                    <i data-feather="trash-2"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-5">
                                            <div class="py-4">
                                                <i data-feather="database" style="width: 48px; height: 48px;" class="text-muted mb-3"></i>
                                                <h5 class="text-muted">No package statuses found</h5>
                                                <a href="{{ route('package-statuses.create') }}" class="btn btn-primary mt-2"><i data-feather="plus-circle"></i> Create Status</a>
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
                            <div class="small text-muted">Showing {{ $packageStatuses->firstItem() ?? 0 }} to {{ $packageStatuses->lastItem() ?? 0 }} of {{ $packageStatuses->total() }} entries</div>
                            <div>{{ $packageStatuses->appends(request()->query())->links('pagination::bootstrap-5') }}</div>
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
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center pt-0">
                <div class="bg-danger bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3">
                    <i data-feather="alert-triangle" class="text-danger" style="width: 40px;"></i>
                </div>
                <h4 class="fw-bold">Confirm Deletion</h4>
                <p class="text-muted">Are you sure you want to delete <strong id="deleteItemName" class="text-danger"></strong>?<br>This action cannot be undone.</p>
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof feather !== 'undefined') feather.replace();

        const perPageSelect = document.querySelector('select[name="per_page"]');
        if (perPageSelect) {
            perPageSelect.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        }

        window.confirmDelete = function(id, name) {
            document.getElementById('deleteItemName').innerText = name;
            document.getElementById('deleteForm').action = '/package-statuses/' + id;
            var modal = new bootstrap.Modal(document.getElementById('globalDeleteModal'));
            modal.show();
        };
    });
</script>
@endpush