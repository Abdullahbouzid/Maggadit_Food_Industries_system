@extends('dashboard.partials.layout')

@section('title', 'Item Models')

@section('content')
<div class="wrapper"><div class="main"><main class="content">
<div class="container-fluid p-0">
    <div class="row mb-4">
        <div class="col-12 col-lg-8"><h1 class="display-5 fw-bold"><i data-feather="cpu"></i> Item Models</h1><p class="text-muted">Manage product models</p></div>
        <div class="col-12 col-lg-4 text-lg-end"><a href="{{ route('item-models.create') }}" class="btn btn-primary btn-lg"><i data-feather="plus-circle"></i> Add New</a><a href="{{ route('item-models.export', request()->query()) }}" class="btn btn-secondary btn-lg ms-2"><i data-feather="download"></i> Export</a></div>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('import_errors'))<div class="alert alert-danger"><ul>@foreach(session('import_errors') as $err)<li>{{ $err }}</li>@endforeach</ul></div>@endif

    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-4"><div class="card border-0 shadow-sm"><div class="card-body"><h6 class="text-muted">Total</h6><h2>{{ $total }}</h2></div></div></div>
        <div class="col-sm-6 col-xl-4"><div class="card border-0 shadow-sm"><div class="card-body"><h6 class="text-muted">Active</h6><h2>{{ $active }}</h2></div></div></div>
        <div class="col-sm-6 col-xl-4"><div class="card border-0 shadow-sm"><div class="card-body"><h6 class="text-muted">Inactive</h6><h2>{{ $inactive }}</h2></div></div></div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
        <form action="{{ route('item-models.import') }}" method="POST" enctype="multipart/form-data" class="row g-3">
            @csrf
            <div class="col-md-6"><input type="file" name="import_file" class="form-control" required></div>
            <div class="col-md-3"><button type="submit" class="btn btn-success">Import</button></div>
            <div class="col-md-3"><a href="{{ route('item-models.template') }}" class="btn btn-outline-secondary">Template</a></div>
        </form>
    </div></div>

    <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-5"><input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}"></div>
            <div class="col-md-3"><select name="status" class="form-select"><option value="">All</option><option value="active" {{ request('status')=='active' ? 'selected' : '' }}>Active</option><option value="inactive" {{ request('status')=='inactive' ? 'selected' : '' }}>Inactive</option></select></div>
            <div class="col-md-2"><select name="per_page" class="form-select" onchange="this.form.submit()"><option value="10">10</option><option value="20" selected>20</option><option value="50">50</option><option value="all">All</option></select></div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Filter</button></div>
        </form>
    </div></div>

    <div class="card border-0 shadow-sm rounded-4"><div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th class="ps-4">#</th><th>Code</th><th>Name (EN)</th><th>Name (AR)</th><th>Status</th><th>Sort</th><th class="pe-4 text-end">Actions</th></tr></thead>
            <tbody>
                @forelse($models as $m)
                <tr><td class="ps-4">{{ $loop->iteration + ($models->currentPage()-1)*$models->perPage() }}</td><td><code>{{ $m->code }}</code></td><td>{{ $m->name_en }}</td><td>{{ $m->name_ar }}</td><td>@if($m->is_active)<span class="badge bg-success">Active</span>@else<span class="badge bg-secondary">Inactive</span>@endif</td><td>{{ $m->sort_order ?? '-' }}</td>
                <td class="pe-4 text-end"><div class="btn-group btn-group-sm"><a href="{{ route('item-models.show', $m) }}" class="btn btn-outline-info"><i data-feather="eye"></i></a><a href="{{ route('item-models.edit', $m) }}" class="btn btn-outline-warning"><i data-feather="edit"></i></a><button class="btn btn-outline-danger" onclick="confirmDelete({{ $m->id }}, '{{ $m->name_en }}')"><i data-feather="trash-2"></i></button></div></td></tr>
                @empty <tr><td colspan="7" class="text-center">No records</td></tr> @endforelse
            </tbody>
        </table>
    </div><div class="card-footer">{{ $models->appends(request()->query())->links('pagination::bootstrap-5') }}</div></div>
</div>
</main></div></div>

<script>function confirmDelete(id,name){ document.getElementById('deleteName')?.setAttribute('value',name); let form = document.getElementById('deleteForm'); if(form) form.action='/item-models/'+id; new bootstrap.Modal(document.getElementById('deleteModal')).show(); }</script>
<div class="modal fade" id="deleteModal">...</div>
@endsection