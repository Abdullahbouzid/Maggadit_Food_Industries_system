{{-- resources/views/dashboard/data_definition/data_product/uoms/index.blade.php --}}
@extends('dashboard.partials.layout')

@section('title', 'Units of Measure (UOM) Management')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                {{-- Header --}}
                <div class="row mb-4">
                    <div class="col-12 col-lg-8">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="layers" class="align-middle me-2"></i>
                            Units of Measure (UOM)
                        </h1>
                        <p class="text-muted">Manage UOMs, symbols, multilingual names, and activation</p>
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('uoms.create') }}" class="btn btn-primary btn-lg shadow-sm">
                            <i data-feather="plus-circle"></i> Add New UOM
                        </a>
                        <a href="{{ route('uoms.export', request()->query()) }}" class="btn btn-secondary btn-lg shadow-sm ms-2">
                            <i data-feather="download"></i> Export Table
                        </a>
                    </div>
                </div>

                {{-- Alert Messages --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i data-feather="check-circle" class="me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i data-feather="alert-circle" class="me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- Statistics Cards --}}
                <div class="row g-4 mb-4">
                    <div class="col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase text-muted mb-2">Total UOMs</h6>
                                        <h2 class="mb-0">{{ $totalUoms }}</h2>
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
                                        <h6 class="text-uppercase text-muted mb-2">Active</h6>
                                        <h2 class="mb-0">{{ $activeCount }}</h2>
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
                                        <h2 class="mb-0">{{ $inactiveCount }}</h2>
                                    </div>
                                    <div class="col-auto">
                                        <div class="bg-warning bg-opacity-10 rounded-circle p-3"><i data-feather="eye-off" class="text-warning"></i></div>
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
                                        <h6 class="text-uppercase text-muted mb-2">With Image</h6>
                                        <h2 class="mb-0">{{ $withImageCount }}</h2>
                                    </div>
                                    <div class="col-auto">
                                        <div class="bg-info bg-opacity-10 rounded-circle p-3"><i data-feather="image" class="text-info"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Import Section --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-light border-0 py-3">
                        <h6 class="fw-bold mb-0"><i data-feather="upload"></i> Import UOMs</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('uoms.import') }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">
                            @csrf
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Excel File (.xlsx, .xls, .csv)</label>
                                <input type="file" name="import_file" class="form-control" required>
                                <div class="form-text">Required columns: <strong>code, name_en, name_ar</strong> | Optional: uom_symbol, description, is_active, sort_order</div>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-success w-100">
                                    <i data-feather="upload"></i> Import Data
                                </button>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('uoms.template') }}" class="btn btn-outline-secondary w-100">
                                    <i data-feather="file-text"></i> Download Template
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Filters --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body">
                        <form id="filterForm" method="GET" action="{{ route('uoms.index') }}" class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label small fw-semibold">Search</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i data-feather="search"></i></span>
                                    <input type="text" name="search" class="form-control border-start-0" placeholder="Code, Name (EN/AR), Symbol..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold">Per Page</label>
                                <select name="per_page" class="form-select" onchange="this.form.submit()">
                                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                                    <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                            @if(request()->anyFilled(['search', 'status', 'per_page']))
                                <div class="col-md-2">
                                    <a href="{{ route('uoms.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>

                {{-- Data Table --}}
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold mb-0"><i data-feather="list"></i> UOMs List</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">#</th>
                                        <th>Code</th>
                                        <th>Name (EN)</th>
                                        <th>Name (AR)</th>
                                        <th>Symbol</th>
                                        <th>Image</th>
                                        <th>Status</th>
                                        <th class="pe-4 text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($uoms as $uom)
                                    <tr>
                                        <td class="ps-4 fw-semibold">{{ $loop->iteration + ($uoms->currentPage() - 1) * $uoms->perPage() }}</td>
                                        <td><code>{{ $uom->code }}</code></td>
                                        <td>{{ $uom->name_en }}</td>
                                        <td>{{ $uom->name_ar }}</td>
                                        <td>@if($uom->uom_symbol)<span class="badge bg-info bg-opacity-10 text-dark px-3 py-2">{{ $uom->uom_symbol }}</span>@else<span class="text-muted">—</span>@endif</td>
                                        <td>
                                            @if($uom->image)
                                                <img src="{{ asset('storage/' . $uom->image) }}" alt="{{ $uom->name_en }}" width="50" height="50" class="rounded border shadow-sm">
                                            @else
                                                <span class="text-muted fst-italic">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($uom->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="pe-4 text-end">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('uoms.show', $uom) }}" class="btn btn-outline-info" title="View"><i data-feather="eye"></i></a>
                                                <a href="{{ route('uoms.edit', $uom) }}" class="btn btn-outline-warning" title="Edit"><i data-feather="edit"></i></a>
                                                <button type="button" class="btn btn-outline-danger" title="Delete" onclick="confirmDelete({{ $uom->id }}, '{{ addslashes($uom->name_en) }}')">
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
                                                <h5 class="text-muted">No UOMs found</h5>
                                                <a href="{{ route('uoms.create') }}" class="btn btn-primary mt-2"><i data-feather="plus-circle"></i> Add UOM</a>
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
                                Showing {{ $uoms->firstItem() ?? 0 }} to {{ $uoms->lastItem() ?? 0 }} of {{ $uoms->total() }} entries
                            </div>
                            <div>{{ $uoms->appends(request()->query())->links('pagination::bootstrap-5') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

{{-- Delete Modal --}}
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
                <p class="text-muted">Are you sure you want to delete UOM <strong id="deleteUomName" class="text-danger"></strong>?<br>This action cannot be undone.</p>
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof feather !== 'undefined') feather.replace();

        window.confirmDelete = function(id, name) {
            document.getElementById('deleteUomName').innerText = name;
            document.getElementById('deleteForm').action = '/uoms/' + id;
            new bootstrap.Modal(document.getElementById('globalDeleteModal')).show();
        };

        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                let bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    });
</script>
@endpush