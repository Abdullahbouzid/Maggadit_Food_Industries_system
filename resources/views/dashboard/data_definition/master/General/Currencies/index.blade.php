@extends('dashboard.partials.layout')

@section('title', 'Currencies Management')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <!-- Header -->
                <div class="row mb-4">
                    <div class="col-12 col-lg-8">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="dollar-sign" class="align-middle me-2"></i>
                            Currencies
                        </h1>
                        <p class="text-muted">Manage currency names, codes, coin descriptions and base currency</p>
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('currencies.create') }}" class="btn btn-primary btn-lg shadow-sm">
                            <i data-feather="plus-circle"></i> Create
                        </a>
                        <a href="{{ route('currencies.export', request()->query()) }}" class="btn btn-secondary btn-lg shadow-sm ms-2">
                            <i data-feather="download"></i> Export Table
                        </a>
                    </div>
                </div>

                <!-- Alerts -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show"><i data-feather="check-circle"></i> {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show"><i data-feather="alert-circle"></i> {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif
                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show"><i data-feather="alert-triangle"></i> {{ session('warning') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif
                @if(session('import_errors'))
                    <div class="alert alert-danger"><h6><i data-feather="alert-circle"></i> Import Errors:</h6><ul>@foreach(session('import_errors') as $error) <li>{{ $error }}</li> @endforeach</ul><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif

                <!-- Statistics Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-sm-6 col-xl-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col"><h6 class="text-uppercase text-muted mb-2">Total Currencies</h6><h2 class="mb-0">{{ $total ?? $currencies->total() }}</h2></div>
                                    <div class="col-auto"><div class="bg-primary bg-opacity-10 rounded-circle p-3"><i data-feather="grid" class="text-primary"></i></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col"><h6 class="text-uppercase text-muted mb-2">Base Currency</h6><h2 class="mb-0">{{ $baseCount ?? 0 }}</h2></div>
                                    <div class="col-auto"><div class="bg-warning bg-opacity-10 rounded-circle p-3"><i data-feather="star" class="text-warning"></i></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col"><h6 class="text-uppercase text-muted mb-2">Other Currencies</h6><h2 class="mb-0">{{ $nonBaseCount ?? 0 }}</h2></div>
                                    <div class="col-auto"><div class="bg-info bg-opacity-10 rounded-circle p-3"><i data-feather="layers" class="text-info"></i></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Import Section -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-light border-0 py-3"><h6 class="fw-bold mb-0"><i data-feather="upload"></i> Import Currencies</h6></div>
                    <div class="card-body">
                        <form action="{{ route('currencies.import') }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">
                            @csrf
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Excel File (.xlsx, .xls, .csv)</label>
                                <input type="file" name="import_file" class="form-control" required>
                                <div class="form-text">Required columns: <strong>name, code, coin_description, base_currency</strong>. Base currency: Yes/No or 1/0.</div>
                            </div>
                            <div class="col-md-3"><button type="submit" class="btn btn-success w-100"><i data-feather="upload"></i> Import Data</button></div>
                            <div class="col-md-3"><a href="{{ route('currencies.template') }}" class="btn btn-outline-secondary w-100"><i data-feather="file-text"></i> Download Template</a></div>
                        </form>
                    </div>
                </div>

                <!-- Export Options (Custom Filters) -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-light border-0 py-3"><h6 class="fw-bold mb-0"><i data-feather="download"></i> Export Options (Custom Filters)</h6></div>
                    <div class="card-body">
                        <form action="{{ route('currencies.export') }}" method="GET" class="row g-3 align-items-end">
                            <div class="col-md-4"><label>Search (name/code/description)</label><input type="text" name="search" class="form-control" placeholder="Enter keyword..."></div>
                            <div class="col-md-3"><label>Base Currency</label><select name="base_currency_filter" class="form-select"><option value="">All</option><option value="yes">Base Currency Only</option><option value="no">Non-Base Only</option></select></div>
                            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i data-feather="file-text"></i> Export Filtered</button></div>
                        </form>
                    </div>
                </div>

                <!-- Main Filter (for table) -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body">
                        <form id="filterForm" method="GET" action="{{ route('currencies.index') }}" class="row g-3 align-items-end">
                            <div class="col-md-4"><label>Search</label><input type="text" name="search" class="form-control" placeholder="Name, code, description..." value="{{ request('search') }}"></div>
                            <div class="col-md-3"><label>Base Currency</label><select name="base_currency_filter" class="form-select"><option value="">All</option><option value="yes" {{ request('base_currency_filter')=='yes'?'selected':'' }}>Base Currency</option><option value="no" {{ request('base_currency_filter')=='no'?'selected':'' }}>Non-Base</option></select></div>
                            <div class="col-md-2"><label>Rows per page</label><select name="per_page" class="form-select"><option value="10" {{ request('per_page')==10?'selected':'' }}>10</option><option value="20" {{ request('per_page')==20||!request('per_page')?'selected':'' }}>20</option><option value="50" {{ request('per_page')==50?'selected':'' }}>50</option><option value="100" {{ request('per_page')==100?'selected':'' }}>100</option><option value="all" {{ request('per_page')=='all'?'selected':'' }}>All</option></select></div>
                            <div class="col-md-3"><button type="submit" class="btn btn-primary w-100">Filter</button>@if(request('search')||request('base_currency_filter')||request('per_page')) <a href="{{ route('currencies.index') }}" class="btn btn-outline-secondary w-100 mt-2">Reset</a>@endif</div>
                        </form>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0 px-4"><h5 class="fw-bold mb-0"><i data-feather="list"></i> Currencies List</h5></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light"><tr><th class="ps-4">#</th><th>Name</th><th>Code</th><th>Coin Description</th><th>Base Currency</th><th class="pe-4 text-end">Actions</th></tr></thead>
                                <tbody>
                                    @forelse($currencies as $curr)
                                    <tr>
                                        <td class="ps-4 fw-semibold">{{ $loop->iteration + ($currencies->currentPage() - 1) * $currencies->perPage() }}</td>
                                        <td>{{ $curr->name }}</td>
                                        <td><span class="badge bg-secondary bg-opacity-10 text-dark px-3 py-2">{{ $curr->code }}</span></td>
                                        <td>{{ $curr->coin_description ?: '—' }}</td>
                                        <td>@if($curr->base_currency) <span class="badge bg-warning text-dark"><i data-feather="star"></i> Base</span> @else — @endif</td>
                                        <td class="pe-4 text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('currencies.show', $curr) }}" class="btn btn-outline-info"><i data-feather="eye"></i></a>
                                                <a href="{{ route('currencies.edit', $curr) }}" class="btn btn-outline-warning"><i data-feather="edit"></i></a>
                                                <button type="button" class="btn btn-outline-danger" onclick="confirmDelete({{ $curr->id }}, '{{ addslashes($curr->name) }}')"><i data-feather="trash-2"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="6" class="text-center py-5"><i data-feather="database"></i><h5 class="text-muted">No currencies found</h5><a href="{{ route('currencies.create') }}" class="btn btn-primary">Create one</a></td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 py-3 px-4">
                        <div class="d-flex justify-content-between align-items-center"><div class="small text-muted">Showing {{ $currencies->firstItem() ?? 0 }} to {{ $currencies->lastItem() ?? 0 }} of {{ $currencies->total() }} entries</div><div>{{ $currencies->appends(request()->query())->links('pagination::bootstrap-5') }}</div></div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="globalDeleteModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow rounded-4"><div class="modal-header border-0 pb-0"><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body text-center pt-0"><div class="bg-danger bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3"><i data-feather="alert-triangle" class="text-danger"></i></div><h4 class="fw-bold">Confirm Deletion</h4><p>Delete <strong id="deleteItemName" class="text-danger"></strong>?<br>This action cannot be undone.</p></div><div class="modal-footer border-0 pt-0 justify-content-center gap-2"><button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button><form id="deleteForm" method="POST">@csrf @method('DELETE')<button type="submit" class="btn btn-danger px-4">Delete Permanently</button></form></div></div></div></div>
@endsection

@push('styles')
<style>.rounded-4{border-radius:1rem!important;}.table thead th{border-bottom-width:1px;font-weight:600;}.btn-group .btn{transition:all 0.2s ease;}.btn-group .btn:hover{transform:translateY(-2px);}</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded',function(){if(typeof feather!=='undefined')feather.replace();document.querySelector('select[name="per_page"]')?.addEventListener('change',()=>document.getElementById('filterForm').submit());window.confirmDelete=(id,name)=>{document.getElementById('deleteItemName').innerText=name;document.getElementById('deleteForm').action='/currencies/'+id;new bootstrap.Modal(document.getElementById('globalDeleteModal')).show();};setTimeout(()=>{document.querySelectorAll('.alert').forEach(a=>{new bootstrap.Alert(a).close();});},5000);});
</script>
@endpush