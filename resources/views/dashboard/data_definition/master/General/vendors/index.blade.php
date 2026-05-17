@extends('dashboard.partials.layout')

@section('title', 'Vendors Management')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <!-- Header -->
                <div class="row mb-4">
                    <div class="col-12 col-lg-8">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="truck" class="align-middle me-2"></i>
                            Vendors
                        </h1>
                        <p class="text-muted">Manage vendor information, contacts, and commission rates</p>
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('vendors.create') }}" class="btn btn-primary btn-lg shadow-sm">
                            <i data-feather="plus-circle"></i> Create
                        </a>
                        <a href="{{ route('vendors.export', request()->query()) }}" class="btn btn-secondary btn-lg shadow-sm ms-2">
                            <i data-feather="download"></i> Export Table
                        </a>
                    </div>
                </div>

                <!-- Alerts -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i data-feather="check-circle"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i data-feather="alert-circle"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Statistics Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase text-muted mb-2">Total Vendors</h6>
                                        <h2 class="mb-0">{{ $totalVendors }}</h2>
                                    </div>
                                    <div class="col-auto">
                                        <div class="bg-primary bg-opacity-10 rounded-circle p-3"><i data-feather="truck" class="text-primary"></i></div>
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
                                        <h6 class="text-uppercase text-muted mb-2">Active</h6>
                                        <h2 class="mb-0">{{ $activeVendors }}</h2>
                                    </div>
                                    <div class="col-auto">
                                        <div class="bg-success bg-opacity-10 rounded-circle p-3"><i data-feather="check-circle" class="text-success"></i></div>
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
                                        <h6 class="text-uppercase text-muted mb-2">Inactive</h6>
                                        <h2 class="mb-0">{{ $inactiveVendors }}</h2>
                                    </div>
                                    <div class="col-auto">
                                        <div class="bg-danger bg-opacity-10 rounded-circle p-3"><i data-feather="x-circle" class="text-danger"></i></div>
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
                                        <h6 class="text-uppercase text-muted mb-2">Total Commission %</h6>
                                        <h2 class="mb-0">{{ number_format($totalCommission, 2) }}%</h2>
                                    </div>
                                    <div class="col-auto">
                                        <div class="bg-info bg-opacity-10 rounded-circle p-3"><i data-feather="percent" class="text-info"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Import Section -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-light border-0 py-3">
                        <h6 class="fw-bold mb-0"><i data-feather="upload"></i> Import Vendors</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('vendors.import') }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">
                            @csrf
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Excel File (.xlsx, .xls, .csv)</label>
                                <input type="file" name="import_file" class="form-control" required>
                                <div class="form-text">Required columns: vendor_name, code, phone, fax, contact_person, contact_number, street, commission_percentage, inactive</div>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-success w-100"><i data-feather="upload"></i> Import Data</button>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('vendors.template') }}" class="btn btn-outline-secondary w-100"><i data-feather="file-text"></i> Download Template</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Export Filter Section -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-light border-0 py-3">
                        <h6 class="fw-bold mb-0"><i data-feather="download"></i> Export Options</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('vendors.export') }}" method="GET" class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Search (name/code)</label>
                                <input type="text" name="search" class="form-control" placeholder="Keyword...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Status</label>
                                <select name="inactive" class="form-select">
                                    <option value="">All</option>
                                    <option value="0">Active</option>
                                    <option value="1">Inactive</option>
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
                        <form id="filterForm" method="GET" action="{{ route('vendors.index') }}" class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Name, code, phone..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Status</label>
                                <select name="inactive" class="form-select">
                                    <option value="">All</option>
                                    <option value="0" {{ request('inactive') == '0' ? 'selected' : '' }}>Active</option>
                                    <option value="1" {{ request('inactive') == '1' ? 'selected' : '' }}>Inactive</option>
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
                                        <a href="{{ route('vendors.index') }}" class="btn btn-outline-secondary">Reset</a>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold mb-0"><i data-feather="list"></i> Vendors List</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">#</th>
                                        <th>Vendor Name</th>
                                        <th>Code</th>
                                        <th>Phone</th>
                                        <th>Contact Person</th>
                                        <th>Commission %</th>
                                        <th>Status</th>
                                        <th class="pe-4 text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($vendors as $vendor)
                                    <tr>
                                        <td class="ps-4">{{ $loop->iteration + ($vendors->currentPage() - 1) * $vendors->perPage() }}</td>
                                        <td>{{ $vendor->vendor_name }}</td>
                                        <td><span class="badge bg-secondary bg-opacity-10 text-dark">{{ $vendor->code }}</span></td>
                                        <td>{{ $vendor->phone ?? '—' }}</td>
                                        <td>{{ $vendor->contact_person ?? '—' }}（{{ $vendor->contact_number ?? '' }}）</td>
                                        <td>{{ $vendor->commission_percentage ? $vendor->commission_percentage.'%' : '0%' }}</td>
                                        <td>{!! $vendor->inactive ? '<span class="badge bg-danger bg-opacity-10 text-danger">Inactive</span>' : '<span class="badge bg-success bg-opacity-10 text-success">Active</span>' !!}</td>
                                        <td class="pe-4 text-end">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('vendors.show', $vendor) }}" class="btn btn-outline-info" title="View"><i data-feather="eye"></i></a>
                                                <a href="{{ route('vendors.edit', $vendor) }}" class="btn btn-outline-warning" title="Edit"><i data-feather="edit"></i></a>
                                                <button type="button" class="btn btn-outline-danger" title="Delete" onclick="confirmDelete({{ $vendor->id }}, '{{ addslashes($vendor->vendor_name) }}')">
                                                    <i data-feather="trash-2"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="py-4">
                                                <i data-feather="database" style="width: 48px;" class="text-muted mb-3"></i>
                                                <h5 class="text-muted">No vendors found</h5>
                                                <a href="{{ route('vendors.create') }}" class="btn btn-primary mt-2"><i data-feather="plus-circle"></i> Create Vendor</a>
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
                            <div class="small text-muted">Showing {{ $vendors->firstItem() ?? 0 }} to {{ $vendors->lastItem() ?? 0 }} of {{ $vendors->total() }} entries</div>
                            <div>{{ $vendors->appends(request()->query())->links('pagination::bootstrap-5') }}</div>
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
            <div class="modal-header border-0 pb-0"><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body text-center pt-0">
                <div class="bg-danger bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3"><i data-feather="alert-triangle" class="text-danger" style="width: 40px;"></i></div>
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
            document.getElementById('deleteForm').action = '/vendors/' + id;
            var modal = new bootstrap.Modal(document.getElementById('globalDeleteModal'));
            modal.show();
        };
    });
</script>
@endpush