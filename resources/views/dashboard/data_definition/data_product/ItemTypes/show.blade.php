@extends('dashboard.partials.layout')

@section('title', 'Item Type Details: ' . $itemType->name)

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold">
                            <i data-feather="info"></i> Item Type Details
                        </h1>
                        <p class="text-muted">Viewing information for <strong>{{ $itemType->name }}</strong></p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-1">Code</h6>
                                <p class="fs-5"><code>{{ $itemType->code }}</code></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-1">Name</h6>
                                <p class="fs-5">{{ $itemType->name }}</p>
                            </div>
                            <div class="col-12">
                                <h6 class="text-muted mb-1">Description</h6>
                                <p>{{ $itemType->description ?? '— No description —' }}</p>
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-muted mb-1">Image</h6>
                                @if($itemType->image)
                                    <img src="{{ asset('storage/' . $itemType->image) }}" alt="{{ $itemType->name }}" class="img-thumbnail" style="max-height: 200px;">
                                @else
                                    <p class="text-muted">— No image —</p>
                                @endif
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-muted mb-1">Status</h6>
                                <p>
                                    @if($itemType->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-muted mb-1">Sort Order</h6>
                                <p>{{ $itemType->sort_order ?? '—' }}</p>
                            </div>
                            <div class="col-12">
                                <hr>
                                <a href="{{ route('item-types.edit', $itemType) }}" class="btn btn-warning"><i data-feather="edit"></i> Edit</a>
                                <a href="{{ route('item-types.index') }}" class="btn btn-secondary">Back to List</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection