@extends('dashboard.partials.layout')

@section('title', 'Items Management')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                {{-- Header --}}
                <div class="row mb-4">
                    <div class="col-12 col-lg-8">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="box" class="align-middle me-2"></i>
                            Items
                        </h1>
                        <p class="text-muted">Manage products, packs, and their attributes</p>
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('items.create') }}" class="btn btn-primary btn-lg shadow-sm">
                            <i data-feather="plus-circle"></i> Add Item
                        </a>
                        <a href="{{ route('items.export', request()->query()) }}" class="btn btn-secondary btn-lg shadow-sm ms-2">
                            <i data-feather="download"></i> Export
                        </a>
                    </div>
                </div>

                {{-- Alerts --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i data-feather="check-circle"></i> {{ session('success') }}
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
                                        <h6 class="text-uppercase text-muted mb-2">Total Items</h6>
                                        <h2 class="mb-0">{{ $totalItems }}</h2>
                                    </div>
                                    <div class="col-auto">
                                        <div class="bg-primary bg-opacity-10 rounded-circle p-3"><i data-feather="box" class="text-primary"></i></div>
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
                                        <h2 class="mb-0">{{ $activeItems }}</h2>
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
                                        <h2 class="mb-0">{{ $inactiveItems }}</h2>
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
                                        <h2 class="mb-0">{{ $withImage }}</h2>
                                    </div>
                                    <div class="col-auto">
                                        <div class="bg-info bg-opacity-10 rounded-circle p-3"><i data-feather="image" class="text-info"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Filters --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body">
                        <form method="GET" action="{{ route('items.index') }}" class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Code, Name..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Item Type</label>
                                <select name="item_type_id" class="form-select">
                                    <option value="">All</option>
                                    @foreach($itemTypes as $type)
                                        <option value="{{ $type->id }}" {{ request('item_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Category</label>
                                <select name="category_id" class="form-select">
                                    <option value="">All</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
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
                        </form>
                    </div>
                </div>

                {{-- Table --}}
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold mb-0"><i data-feather="list"></i> Items List</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">#</th>
                                        <th>Image</th>
                                        <th>Code</th>
                                        <th>Name (EN)</th>
                                        <th>Name (AR)</th>
                                        <th>Type</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th class="pe-4 text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($items as $item)
                                    <tr>
                                        <td class="ps-4">{{ $loop->iteration + ($items->currentPage() - 1) * $items->perPage() }}</td>
                                        <td>
                                            @if($item->image)
                                                <img src="{{ asset('uploads/' . $item->image) }}" width="50" height="50" class="rounded border">
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td><code>{{ $item->code }}</code></td>
                                        <td>{{ $item->name_en }}</td>
                                        <td>{{ $item->name_ar }}</td>
                                        <td>{{ $item->itemType->name ?? '-' }}</td>
                                        <td>{{ $item->category->name ?? '-' }}</td>
                                        <td>
                                            @if($item->is_inactive)
                                                <span class="badge bg-secondary">Inactive</span>
                                            @else
                                                <span class="badge bg-success">Active</span>
                                            @endif
                                        </td>
                                        <td class="pe-4 text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('items.show', $item) }}" class="btn btn-outline-info"><i data-feather="eye"></i></a>
                                                <a href="{{ route('items.edit', $item) }}" class="btn btn-outline-warning"><i data-feather="edit"></i></a>
                                                <button type="button" class="btn btn-outline-danger" onclick="confirmDelete({{ $item->id }}, '{{ addslashes($item->name_en) }}')"><i data-feather="trash-2"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-5">No items found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 py-3 px-4">
                        {{ $items->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

{{-- Delete Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <h5 class="fw-bold">Confirm Deletion</h5>
                <p>Delete <strong id="deleteItemName"></strong>? This action cannot be undone.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" action="">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function confirmDelete(id, name) {
        document.getElementById('deleteItemName').innerText = name;
        document.getElementById('deleteForm').action = '/items/' + id;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>
@endpush
@endsection