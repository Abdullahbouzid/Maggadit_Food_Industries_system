{{-- resources/views/dashboard/data_definition/data_product/pack_operations/show.blade.php --}}
@extends('dashboard.partials.layout')

@section('title', 'Pack Operation Details: ' . $packOperation->name_en)

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold">
                            <i data-feather="info"></i> Pack Operation Details
                        </h1>
                        <p class="text-muted">Viewing information for <strong>{{ $packOperation->name_en }} ({{ $packOperation->name_ar }})</strong></p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-1">Code</h6>
                                <p class="fs-5"><code>{{ $packOperation->code }}</code></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-1">Sort Order</h6>
                                <p class="fs-5">{{ $packOperation->sort_order ?? '—' }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-1">Name (English)</h6>
                                <p class="fs-5">{{ $packOperation->name_en }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-1">Name (Arabic)</h6>
                                <p class="fs-5">{{ $packOperation->name_ar }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-1">Status</h6>
                                <p>
                                    @if($packOperation->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-12">
                                <hr>
                                <a href="{{ route('pack-operations.edit', $packOperation) }}" class="btn btn-warning"><i data-feather="edit"></i> Edit</a>
                                <a href="{{ route('pack-operations.index') }}" class="btn btn-secondary">Back to List</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection