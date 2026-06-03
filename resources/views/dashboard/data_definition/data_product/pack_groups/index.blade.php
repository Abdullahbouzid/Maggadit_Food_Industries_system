@extends('dashboard.partials.layout')

@section('title', 'Pack Groups Management')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12 col-lg-8">
                        <h1 class="display-5 fw-bold"><i data-feather="layers"></i> Pack Groups</h1>
                        <p class="text-muted">Manage pack groups for item packs</p>
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end">
                        <a href="{{ route('pack-groups.create') }}" class="btn btn-primary btn-lg"><i data-feather="plus-circle"></i> Add</a>
                        <a href="{{ route('pack-groups.export', request()->query()) }}" class="btn btn-secondary btn-lg"><i data-feather="download"></i> Export</a>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="row g-4 mb-4">
                    <div class="col-sm-6 col-xl-4">
                        <div class="card"><div class="card-body"><h6 class="text-muted">Total Groups</h6><h2>{{ $total }}</h2></div></div>
                    </div>
                    <div class="col-sm-6 col-xl-4">
                        <div class="card"><div class="card-body"><h6 class="text-muted">Active</h6><h2>{{ $active }}</h2></div></div>
                    </div>
                    <div class="col-sm-6 col-xl-4">
                        <div class="card"><div class="card-body"><h6 class="text-muted">Inactive</h6><h2>{{ $inactive }}</h2></div></div>
                    </div>
                </div>

                {{-- Import Section --}}
                <div class="card mb-4">
                    <div class="card-body">
                        <form action="{{ route('pack-groups.import') }}" method="POST" enctype="multipart/form-data" class="row g-3">
                            @csrf
                            <div class="col-md-6"><input type="file" name="import_file" class="form-control" required></div>
                            <div class="col-md-3"><button type="submit" class="btn btn-success"><i data-feather="upload"></i> Import</button></div>
                            <div class="col-md-3"><a href="{{ route('pack-groups.template') }}" class="btn btn-outline-secondary">Download Template</a></div>
                        </form>
                    </div>
                </div>

                {{-- Filter --}}
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4"><input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}"></div>
                            <div class="col-md-3">
                                <select name="status" class="form-select">
                                    <option value="">All</option>
                                    <option value="active" {{ request('status')=='active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ request('status')=='inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-2"><select name="per_page" class="form-select" onchange="this.form.submit()">
                                <option value="10">10</option><option value="20" selected>20</option><option value="50">50</option><option value="all">All</option>
                            </select></div>
                            <div class="col-md-3"><button type="submit" class="btn btn-primary w-100">Filter</button></div>
                        </form>
                    </div>
                </div>

                {{-- Table --}}
                <div class="card">
                    <div class="card-body p-0">
                        <table class="table table-hover">
                            <thead><tr><th>#</th><th>Code</th><th>Name (EN)</th><th>Name (AR)</th><th>Status</th><th>Sort</th><th>Actions</th></tr></thead>
                            <tbody>
                                @forelse($packGroups as $g)
                                <tr>
                                    <td>{{ $loop->iteration + ($packGroups->currentPage()-1)*$packGroups->perPage() }}</td>
                                    <td><code>{{ $g->code }}</code></td>
                                    <td>{{ $g->name_en }}</td>
                                    <td>{{ $g->name_ar }}</td>
                                    <td>@if($g->is_active) <span class="badge bg-success">Active</span> @else <span class="badge bg-secondary">Inactive</span> @endif</td>
                                    <td>{{ $g->sort_order }}</td>
                                    <td>
                                        <a href="{{ route('pack-groups.show', $g) }}" class="btn btn-sm btn-outline-info"><i data-feather="eye"></i></a>
                                        <a href="{{ route('pack-groups.edit', $g) }}" class="btn btn-sm btn-outline-warning"><i data-feather="edit"></i></a>
                                        <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete({{ $g->id }}, '{{ $g->name_en }}')"><i data-feather="trash-2"></i></button>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center">No records found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">{{ $packGroups->appends(request()->query())->links() }}</div>
                </div>
            </div>
        </main>
    </div>
</div>
<div class="modal fade" id="deleteModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-body">Delete <strong id="deleteName"></strong>?</div><div class="modal-footer"><form id="deleteForm" method="POST"><button type="submit" class="btn btn-danger">Delete</button></form></div></div></div></div>
<script>
function confirmDelete(id, name) {
    document.getElementById('deleteName').innerText = name;
    document.getElementById('deleteForm').action = '/pack-groups/' + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endsection