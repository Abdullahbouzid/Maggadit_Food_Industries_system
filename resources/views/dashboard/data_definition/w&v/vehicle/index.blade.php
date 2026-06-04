@extends('dashboard.partials.layout')

@section('title', 'Vehicles')

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
                            Vehicles
                        </h1>
                        <p class="text-muted">Manage all fleet vehicles, their types and organizations.</p>
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('vehicles.create') }}" class="btn btn-primary btn-lg shadow-sm">
                            <i data-feather="plus-circle"></i> Create Vehicle
                        </a>
                        <a href="{{ route('vehicles.export', request()->query()) }}" class="btn btn-secondary btn-lg shadow-sm ms-2">
                            <i data-feather="download"></i> Export
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
                @if(session('import_errors'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <strong>Import Errors:</strong>
                        <ul>@foreach(session('import_errors') as $error)<li>{{ $error }}</li>@endforeach</ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Stats Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-sm-6 col-xl-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col"><h6 class="text-muted mb-2">Total Vehicles</h6><h2>{{ $totalVehicles }}</h2></div>
                                    <div class="col-auto"><div class="bg-primary bg-opacity-10 rounded-circle p-3"><i data-feather="truck" class="text-primary"></i></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col"><h6 class="text-muted mb-2">Active</h6><h2>{{ $activeVehicles }}</h2></div>
                                    <div class="col-auto"><div class="bg-success bg-opacity-10 rounded-circle p-3"><i data-feather="check-circle" class="text-success"></i></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col"><h6 class="text-muted mb-2">Inactive</h6><h2>{{ $inactiveVehicles }}</h2></div>
                                    <div class="col-auto"><div class="bg-danger bg-opacity-10 rounded-circle p-3"><i data-feather="eye-off" class="text-danger"></i></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Import Section -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-light border-0 py-3">
                        <h6 class="fw-bold mb-0"><i data-feather="upload"></i> Import Vehicles</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('vehicles.import') }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">
                            @csrf
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Excel File (.xlsx, .xls, .csv)</label>
                                <input type="file" name="import_file" class="form-control" required>
                                <div class="form-text">Required columns: vehicle_name, vehicle_code, plate_no, organization, vehicle_type</div>
                            </div>
                            <div class="col-md-3"><button type="submit" class="btn btn-success w-100"><i data-feather="upload"></i> Import</button></div>
                            <div class="col-md-3"><a href="{{ route('vehicles.template') }}" class="btn btn-outline-secondary w-100"><i data-feather="file-text"></i> Download Template</a></div>
                        </form>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body">
                        <form id="filterForm" method="GET" class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Name, Code, Plate..." value="{{ request('search') }}">
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
                                <label class="form-label small fw-semibold">Vehicle Type</label>
                                <select name="vehicle_type_id" class="form-select">
                                    <option value="">All</option>
                                    @foreach($vehicleTypes as $type)
                                        <option value="{{ $type->id }}" {{ request('vehicle_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name_en }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Vehicles Table -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold mb-0"><i data-feather="list"></i> Vehicles List</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">#</th>
                                        <th>Vehicle Name</th>
                                        <th>Code</th>
                                        <th>Plate No.</th>
                                        <th>Organization</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th class="pe-4 text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($vehicles as $vehicle)
                                    <tr>
                                        <td class="ps-4">{{ $loop->iteration + ($vehicles->currentPage()-1)*$vehicles->perPage() }}</td>
                                        <td class="fw-semibold">{{ $vehicle->vehicle_name }}</td>
                                        <td><code>{{ $vehicle->vehicle_code }}</code></td>
                                        <td>{{ $vehicle->plate_no }}</td>
                                        <td>{{ $vehicle->organization->name ?? '-' }}</td>
                                        <td>{{ $vehicle->vehicleType->name_en ?? '-' }}</td>
                                        <td>{!! $vehicle->inactive ? '<span class="badge bg-danger">Inactive</span>' : '<span class="badge bg-success">Active</span>' !!}</td>
                                        <td class="pe-4 text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-outline-info"><i data-feather="eye"></i></a>
                                                <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-outline-warning"><i data-feather="edit"></i></a>
                                                <button type="button" class="btn btn-outline-danger" onclick="confirmDelete({{ $vehicle->id }}, '{{ addslashes($vehicle->vehicle_name) }}')"><i data-feather="trash-2"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="8" class="text-center py-5">No vehicles found. <a href="{{ route('vehicles.create') }}">Create one</a></td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 py-3 px-4">
                        {{ $vehicles->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="globalDeleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0"><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body text-center pt-0">
                <div class="bg-danger bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3"><i data-feather="alert-triangle" class="text-danger" style="width:40px;"></i></div>
                <h4>Confirm Deletion</h4>
                <p>Delete vehicle <strong id="deleteItemName" class="text-danger"></strong>? This action cannot be undone.</p>
                <button class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display:inline-block">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger px-4 ms-2">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        feather.replace();
        window.confirmDelete = function(id, name) {
            document.getElementById('deleteItemName').innerText = name;
            document.getElementById('deleteForm').action = '/vehicles/' + id;
            new bootstrap.Modal(document.getElementById('globalDeleteModal')).show();
        };
        // Auto-submit on per_page change if exists
        let perPageSelect = document.querySelector('select[name="per_page"]');
        if(perPageSelect) perPageSelect.addEventListener('change', () => document.getElementById('filterForm').submit());
    });
</script>
@endpush