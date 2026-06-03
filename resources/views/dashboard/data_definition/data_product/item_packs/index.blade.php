@extends('dashboard.partials.layout')

@section('title', 'Item Packs Management')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12 col-lg-8">
                        <h1 class="display-5 fw-bold"><i data-feather="box"></i> Item Packs</h1>
                        <p class="text-muted">Manage packaging definitions for your items.</p>
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('item-packs.create') }}" class="btn btn-primary btn-lg shadow-sm">
                            <i data-feather="plus-circle"></i> Add Item Pack
                        </a>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="row g-4 mb-4">
                    <div class="col-sm-6 col-xl-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted">Total Item Packs</h6>
                                <h2>{{ $total }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted">Default Packs</h6>
                                <h2>{{ $defaultCount }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted">Weight Priced</h6>
                                <h2>{{ $weightPricedCount }}</h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" action="{{ route('item-packs.index') }}" class="row g-3 align-items-end">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Item name, barcode, UOM..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Per page</label>
                                <select name="per_page" class="form-select" onchange="this.form.submit()">
                                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                                    <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-primary w-100">Filter</button>
                            </div>
                            @if(request()->filled('search') || request()->filled('per_page'))
                                <div class="col-md-2">
                                    <a href="{{ route('item-packs.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Item</th>
                                    <th>Barcode</th>
                                    <th>Quantity</th>
                                    <th>UOM</th>
                                    <th>Default</th>
                                    <th>Weight Priced</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($itemPacks as $pack)
                                <tr>
                                    <td>{{ $loop->iteration + ($itemPacks->currentPage() - 1) * $itemPacks->perPage() }}</td>
                                    <td>{{ $pack->item->name_en ?? $pack->item->name_ar ?? '—' }}</td>
                                    <td><code>{{ $pack->barcode }}</code></td>
                                    <td>{{ rtrim(rtrim(number_format($pack->quantity, 3, '.', ''), '0'), '.') }}</td>
                                    <td>{{ $pack->uom->name_en ?? $pack->uom->uom_symbol ?? '—' }}</td>
                                    <td>{!! $pack->is_default_pack ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' !!}</td>
                                    <td>{!! $pack->is_weight_priced ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' !!}</td>
                                    <td class="text-end">
                                        <a href="{{ route('item-packs.show', $pack) }}" class="btn btn-sm btn-outline-info" title="View"><i data-feather="eye"></i></a>
                                        <a href="{{ route('item-packs.edit', $pack) }}" class="btn btn-sm btn-outline-warning" title="Edit"><i data-feather="edit"></i></a>
                                        <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete({{ $pack->id }}, '{{ addslashes($pack->barcode) }}')" title="Delete"><i data-feather="trash-2"></i></button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">No item packs found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">{{ $itemPacks->appends(request()->query())->links() }}</div>
                </div>

                <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Confirm Delete</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to delete <strong id="deleteName"></strong>?</p>
                            </div>
                            <div class="modal-footer">
                                <form id="deleteForm" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    document.getElementById('deleteName').innerText = name;
    document.getElementById('deleteForm').action = '/item-packs/' + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endsection
