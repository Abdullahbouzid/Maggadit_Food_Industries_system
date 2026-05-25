@extends('dashboard.partials.layout')

@section('title', 'Brands Management')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">

                {{-- Header --}}
                <div class="row mb-4">
                    <div class="col-12 col-lg-8">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="tag" class="align-middle me-2"></i>
                            Brands
                        </h1>
                        <p class="text-muted">Manage brands, logos, and visibility in customer app</p>
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('brands.create') }}" class="btn btn-primary btn-lg shadow-sm">
                            <i data-feather="plus-circle"></i> Add New
                        </a>
                        <a href="{{ route('brands.export', request()->query()) }}" class="btn btn-secondary btn-lg shadow-sm ms-2">
                            <i data-feather="download"></i> Export Table
                        </a>
                    </div>
                </div>

                {{-- Alert Messages --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i data-feather="check-circle" class="me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Statistics Cards --}}
                <div class="row g-4 mb-4">
                    <div class="col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase text-muted mb-2">Total Brands</h6>
                                        <h2 class="mb-0">{{ $totalBrands }}</h2>
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
                                        <h6 class="text-uppercase text-muted mb-2">Shown in App</h6>
                                        <h2 class="mb-0">{{ $shownInApp }}</h2>
                                    </div>
                                    <div class="col-auto">
                                        <div class="bg-success bg-opacity-10 rounded-circle p-3"><i data-feather="eye" class="text-success"></i></div>
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
                                        <h6 class="text-uppercase text-muted mb-2">Hidden in App</h6>
                                        <h2 class="mb-0">{{ $hiddenInApp }}</h2>
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
                                        <h6 class="text-uppercase text-muted mb-2">With Logo</h6>
                                        <h2 class="mb-0">{{ $withLogo }}</h2>
                                    </div>
                                    <div class="col-auto">
                                        <div class="bg-info bg-opacity-10 rounded-circle p-3"><i data-feather="image" class="text-info"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Search Filter & Per Page --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body">
                        <form id="filterForm" method="GET" action="{{ route('brands.index') }}" class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label small fw-semibold">Search (Name)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i data-feather="search"></i></span>
                                    <input type="text" name="search" class="form-control border-start-0" placeholder="Brand name..." value="{{ request('search') }}">
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
                                        <a href="{{ route('brands.index') }}" class="btn btn-outline-secondary">Reset</a>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Data Table --}}
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold mb-0"><i data-feather="list"></i> Brands List</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">#</th>
                                        <th>Name</th>
                                        <th>Logo</th>
                                        <th>Show in Customer App</th>
                                        <th class="pe-4 text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($brandsPaginated as $brand)
                                    <tr>
                                        <td class="ps-4 fw-semibold">{{ $loop->iteration + ($brandsPaginated->currentPage() - 1) * $brandsPaginated->perPage() }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span>{{ $brand->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @if($brand->logo)
                                                <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" width="50" height="50" class="rounded border shadow-sm">
                                            @else
                                                <span class="text-muted fst-italic">— No Logo —</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($brand->show_in_customer_app)
                                                <span class="badge bg-success">Visible</span>
                                            @else
                                                <span class="badge bg-secondary">Hidden</span>
                                            @endif
                                        </td>
                                        <td class="pe-4 text-end">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('brands.show', $brand) }}" class="btn btn-outline-info" title="View"><i data-feather="eye"></i></a>
                                                <a href="{{ route('brands.edit', $brand) }}" class="btn btn-outline-warning" title="Edit"><i data-feather="edit"></i></a>
                                                <button type="button" class="btn btn-outline-danger" title="Delete" onclick="confirmDelete({{ $brand->id }}, '{{ addslashes($brand->name) }}')">
                                                    <i data-feather="trash-2"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="py-4">
                                                <i data-feather="database" style="width: 48px; height: 48px;" class="text-muted mb-3"></i>
                                                <h5 class="text-muted">No brands found</h5>
                                                <p class="text-muted">You can add a new brand using the add button.</p>
                                                <a href="{{ route('brands.create') }}" class="btn btn-primary"><i data-feather="plus-circle"></i> Add Brand</a>
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
                                Showing {{ $brandsPaginated->firstItem() ?? 0 }} to {{ $brandsPaginated->lastItem() ?? 0 }} of {{ $brandsPaginated->total() }} entries
                            </div>
                            <div>{{ $brandsPaginated->appends(request()->query())->links('pagination::bootstrap-5') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

{{-- Global Delete Modal --}}
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
                <p class="text-muted">Are you sure you want to delete the brand <strong id="deleteBrandName" class="text-danger"></strong>?<br>This action cannot be undone.</p>
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
</style>
@endpush

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
            document.getElementById('deleteBrandName').innerText = name;
            document.getElementById('deleteForm').action = '/brands/' + id;
            const modal = new bootstrap.Modal(document.getElementById('globalDeleteModal'));
            modal.show();
        };

        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                if (typeof bootstrap !== 'undefined') {
                    let bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                } else {
                    alert.style.display = 'none';
                }
            });
        }, 5000);
    });
</script>
@endpush