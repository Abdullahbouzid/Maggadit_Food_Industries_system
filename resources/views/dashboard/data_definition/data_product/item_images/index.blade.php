@extends('dashboard.partials.layout')

@section('title', 'Item Images')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12 col-lg-8">
                        <h1 class="display-5 fw-bold"><i data-feather="image"></i> Item Images</h1>
                        <p class="text-muted">Manage product images used in item definitions.</p>
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('item-images.create') }}" class="btn btn-primary btn-lg"><i data-feather="plus-circle"></i> Add Image</a>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" action="{{ route('item-images.index') }}" class="row g-3 align-items-end">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Search by item or code" value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Per page</label>
                                <select name="per_page" class="form-select" onchange="this.form.submit()">
                                    <option value="10">10</option>
                                    <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-primary w-100">Filter</button>
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
                                    <th>Item</th>
                                    <th>Image</th>
                                    <th>Alt Text</th>
                                    <th>Sort Order</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($images as $image)
                                <tr>
                                    <td>{{ $loop->iteration + ($images->currentPage() - 1) * $images->perPage() }}</td>
                                    <td>{{ $image->item->name_en ?? $image->item->name_ar ?? '—' }}</td>
                                    <td><img src="{{ $image->url }}" alt="{{ $image->alt_text }}" class="img-thumbnail" style="max-width: 100px;"></td>
                                    <td>{{ $image->alt_text ?? '—' }}</td>
                                    <td>{{ $image->sort_order }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('item-images.show', $image) }}" class="btn btn-sm btn-outline-info"><i data-feather="eye"></i></a>
                                        <a href="{{ route('item-images.edit', $image) }}" class="btn btn-sm btn-outline-warning"><i data-feather="edit"></i></a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">No item images found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">{{ $images->appends(request()->query())->links() }}</div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection
