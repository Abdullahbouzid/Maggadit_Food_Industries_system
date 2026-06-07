@extends('dashboard.partials.layout')

@section('title', 'Devices Management')

@section('content')
<div class="wrapper"><div class="main"><main class="content"><div class="container-fluid p-0">
    <div class="row mb-4">
        <div class="col-12 col-lg-8">
            <h1 class="display-5 fw-bold mb-2"><i data-feather="monitor" class="align-middle me-2"></i>Devices</h1>
            <p class="text-muted">Manage hardware devices and their details</p>
        </div>
        <div class="col-12 col-lg-4 text-lg-end">
            <a href="{{ route('devices.create') }}" class="btn btn-primary btn-lg shadow-sm"><i data-feather="plus-circle"></i> Create</a>
            <a href="{{ route('devices.export', request()->query()) }}" class="btn btn-secondary btn-lg shadow-sm ms-2"><i data-feather="download"></i> Export</a>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if(session('import_errors'))
        <div class="alert alert-danger"><h6>Import Errors:</h6><ul>@foreach(session('import_errors') as $err)<li>{{ $err }}</li>@endforeach</ul></div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="row align-items-center"><div class="col"><h6 class="text-muted">Total</h6><h2>{{ $total }}</h2></div><div class="col-auto"><div class="bg-primary bg-opacity-10 rounded-circle p-3"><i data-feather="grid" class="text-primary"></i></div></div></div></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="row align-items-center"><div class="col"><h6 class="text-muted">Active</h6><h2>{{ $active }}</h2></div><div class="col-auto"><div class="bg-success bg-opacity-10 rounded-circle p-3"><i data-feather="check-circle" class="text-success"></i></div></div></div></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="row align-items-center"><div class="col"><h6 class="text-muted">Inactive</h6><h2>{{ $inactive }}</h2></div><div class="col-auto"><div class="bg-warning bg-opacity-10 rounded-circle p-3"><i data-feather="eye-off" class="text-warning"></i></div></div></div></div></div></div>
    </div>

    <!-- Import Section -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-light"><h6><i data-feather="upload"></i> Import Devices</h6></div>
        <div class="card-body">
            <form action="{{ route('devices.import') }}" method="POST" enctype="multipart/form-data" class="row g-3">
                @csrf
                <div class="col-md-6"><input type="file" name="import_file" class="form-control" required><small>Required: name_en, name_ar, serial, organization_id</small></div>
                <div class="col-md-3"><button type="submit" class="btn btn-success w-100">Import</button></div>
                <div class="col-md-3"><a href="{{ route('devices.template') }}" class="btn btn-outline-secondary w-100">Template</a></div>
            </form>
        </div>
    </div>

    <!-- Filter -->
    <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4"><input type="text" name="search" class="form-control" placeholder="Search serial/name..." value="{{ request('search') }}"></div>
            <div class="col-md-3"><select name="organization_id" class="form-select"><option value="">All Orgs</option>@foreach($organizations as $org)<option value="{{ $org->id }}" {{ request('organization_id')==$org->id?'selected':'' }}>{{ $org->name }}</option>@endforeach</select></div>
            <div class="col-md-3"><select name="brand_id" class="form-select"><option value="">All Brands</option>@foreach($brands as $brand)<option value="{{ $brand->id }}" {{ request('brand_id')==$brand->id?'selected':'' }}>{{ $brand->name }}</option>@endforeach</select></div>
            <div class="col-md-2"><select name="per_page" class="form-select"><option value="10">10</option><option value="20" selected>20</option><option value="50">50</option><option value="100">100</option><option value="all">All</option></select></div>
            <div class="col-md-12"><button type="submit" class="btn btn-primary">Filter</button><a href="{{ route('devices.index') }}" class="btn btn-outline-secondary ms-2">Reset</a></div>
        </form>
    </div></div>

    <!-- Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0"><h5><i data-feather="list"></i> Devices List</h5></div>
        <div class="card-body p-0"><div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th class="ps-4">#</th><th>Serial</th><th>Name (EN)</th><th>Name (AR)</th><th>Organization</th><th>Model No</th><th>Brand</th><th>Purchase Date</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @forelse($devices as $d)
                    <tr>
                        <td class="ps-4">{{ $loop->iteration + ($devices->currentPage()-1)*$devices->perPage() }}</td>
                        <td><code>{{ $d->serial }}</code></td>
                        <td>{{ $d->name_en }}</td>
                        <td>{{ $d->name_ar }}</td>
                        <td>{{ $d->organization?->name ?? '-' }}</td>
                        <td>{{ $d->model_no ?? '-' }}</td>
                        <td>{{ $d->brand?->name ?? '-' }}</td>
                        <td>{{ $d->purchase_date ? \Carbon\Carbon::parse($d->purchase_date)->format('d-m-Y') : '-' }}</td>
                        <td>{!! $d->is_inactive ? '<span class="badge bg-secondary">Inactive</span>' : '<span class="badge bg-success">Active</span>' !!}</td>
                        <td class="text-end">
                            <a href="{{ route('devices.show', $d) }}" class="btn btn-sm btn-outline-info"><i data-feather="eye"></i></a>
                            <a href="{{ route('devices.edit', $d) }}" class="btn btn-sm btn-outline-warning"><i data-feather="edit"></i></a>
                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete({{ $d->id }}, '{{ addslashes($d->serial) }}')"><i data-feather="trash-2"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center py-5">No devices found. <a href="{{ route('devices.create') }}">Create one</a></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div></div>
        <div class="card-footer bg-white">{{ $devices->appends(request()->query())->links() }}</div>
    </div>
</div></main></div></div>

<div class="modal fade" id="deleteModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body text-center"><i data-feather="alert-triangle"></i><h4>Confirm Deletion</h4><p>Delete <strong id="deleteName"></strong>? This cannot be undone.</p><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><form id="deleteForm" method="POST" style="display:inline-block">@csrf @method('DELETE')<button class="btn btn-danger ms-2">Delete</button></form></div></div></div></div>

<script>
    window.confirmDelete = (id,name)=>{document.getElementById('deleteName').innerText=name;document.getElementById('deleteForm').action='/devices/'+id;new bootstrap.Modal(document.getElementById('deleteModal')).show();}
    document.querySelector('select[name="per_page"]')?.addEventListener('change',e=>e.target.closest('form').submit());
    feather.replace();
</script>
@endsection