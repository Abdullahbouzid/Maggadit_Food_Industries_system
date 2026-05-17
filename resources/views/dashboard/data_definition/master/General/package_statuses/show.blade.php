@extends('dashboard.partials.layout')

@section('title', 'Package Status Details')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="layers" class="align-middle me-2"></i>
                            Package Status Details
                        </h1>
                        <p class="text-muted">View complete information</p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="border-bottom pb-2 mb-3">
                                    <small class="text-muted text-uppercase">Status Name</small>
                                    <h5 class="mb-0">{{ $packageStatus->status_name }}</h5>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border-bottom pb-2 mb-3">
                                    <small class="text-muted text-uppercase">Organization</small>
                                    <h5 class="mb-0">{{ $packageStatus->organization->name ?? '—' }}</h5>
                                </div>
                            </div>
                            <div class="col-12">
                                <hr>
                                <h6 class="fw-semibold">Properties</h6>
                                <div class="row mt-3">
                                    <div class="col-md-4 mb-2">
                                        <span class="badge bg-{{ $packageStatus->resellable ? 'success' : 'secondary' }} bg-opacity-10 p-2">
                                            Resellable: {{ $packageStatus->resellable ? 'Yes' : 'No' }}
                                        </span>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <span class="badge bg-{{ $packageStatus->expired ? 'danger' : 'secondary' }} bg-opacity-10 p-2">
                                            Expired: {{ $packageStatus->expired ? 'Yes' : 'No' }}
                                        </span>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <span class="badge bg-{{ $packageStatus->default ? 'info' : 'secondary' }} bg-opacity-10 p-2">
                                            Default: {{ $packageStatus->default ? 'Yes' : 'No' }}
                                        </span>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <span class="badge bg-{{ $packageStatus->lpo_required ? 'warning' : 'secondary' }} bg-opacity-10 p-2">
                                            LPO Required: {{ $packageStatus->lpo_required ? 'Yes' : 'No' }}
                                        </span>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <span class="badge bg-{{ $packageStatus->is_related_to_cn_request ? 'primary' : 'secondary' }} bg-opacity-10 p-2">
                                            Related to CN: {{ $packageStatus->is_related_to_cn_request ? 'Yes' : 'No' }}
                                        </span>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <span class="badge bg-{{ $packageStatus->is_price_editable ? 'dark' : 'secondary' }} bg-opacity-10 p-2">
                                            Price Editable: {{ $packageStatus->is_price_editable ? 'Yes' : 'No' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 mt-4">
                                <hr>
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="{{ route('package-statuses.index') }}" class="btn btn-light px-4">
                                        <i data-feather="arrow-left"></i> Back
                                    </a>
                                    <a href="{{ route('package-statuses.edit', $packageStatus) }}" class="btn btn-warning px-4">
                                        <i data-feather="edit"></i> Edit
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection