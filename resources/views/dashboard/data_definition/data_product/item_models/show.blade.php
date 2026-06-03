@extends('dashboard.partials.layout')

@section('title', 'Item Model Details')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold"><i data-feather="info"></i> Item Model Details</h1>
                        <p class="text-muted">View details for this item model.</p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <h6 class="text-muted">Code</h6>
                                <p class="fs-5"><code>{{ $itemModel->code }}</code></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Sort Order</h6>
                                <p class="fs-5">{{ $itemModel->sort_order ?? '—' }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Name (English)</h6>
                                <p class="fs-5">{{ $itemModel->name_en }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Name (Arabic)</h6>
                                <p class="fs-5">{{ $itemModel->name_ar }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Status</h6>
                                <p>{!! $itemModel->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</p>
                            </div>
                            <div class="col-12">
                                <hr>
                                <a href="{{ route('item-models.edit', $itemModel) }}" class="btn btn-warning"><i data-feather="edit"></i> Edit</a>
                                <a href="{{ route('item-models.index') }}" class="btn btn-secondary">Back to list</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection
