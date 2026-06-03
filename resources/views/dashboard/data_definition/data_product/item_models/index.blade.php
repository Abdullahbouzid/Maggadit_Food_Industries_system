@extends('dashboard.partials.layout')

@section('title', 'Item Models')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12 col-lg-8">
                        <h1 class="display-5 fw-bold"><i data-feather="layers"></i> Item Models</h1>
                        <p class="text-muted">Manage item models used across products.</p>
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('item-models.create') }}" class="btn btn-primary btn-lg"><i data-feather="plus-circle"></i> Add Model</a>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" action="{{ route('item-models.index') }}" class="row g-3 align-items-end">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Search code or name" value="{{ request('search') }}">
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
                                <label class="form-label small fw-semibold">Per page</label>
                                <select name="per_page" class="form-select" onchange="this.form.submit()">
                                    <option value="10">10</option>
                                    <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Code</th>
                                    <th>Name (EN)</th>
                                    <th>Name (AR)</th>
                                    <th>Status</th>
                                    <th>Sort Order</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($models as $model)
                                <tr>
                                    <td>{{ $loop->iteration + ($models->currentPage() - 1) * $models->perPage() }}</td>
                                    <td><code>{{ $model->code }}</code></td>
                                    <td>{{ $model->name_en }}</td>
                                    <td>{{ $model->name_ar }}</td>
                                    <td>{!! $model->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td>
                                    <td>{{ $model->sort_order ?? '—' }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('item-models.show', $model) }}" class="btn btn-sm btn-outline-info"><i data-feather="eye"></i></a>
                                        <a href="{{ route('item-models.edit', $model) }}" class="btn btn-sm btn-outline-warning"><i data-feather="edit"></i></a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">No item models found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">{{ $models->appends(request()->query())->links() }}</div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection
