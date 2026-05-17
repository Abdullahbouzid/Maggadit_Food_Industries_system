@extends('dashboard.partials.layout')

@section('title', 'Vendor Details')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold mb-2"><i data-feather="info"></i> Vendor Details</h1>
                    </div>
                </div>
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6"><strong>Vendor Name:</strong> {{ $vendor->vendor_name }}</div>
                            <div class="col-md-6"><strong>Code:</strong> {{ $vendor->code }}</div>
                            <div class="col-md-6"><strong>Phone:</strong> {{ $vendor->phone ?? '—' }}</div>
                            <div class="col-md-6"><strong>Fax:</strong> {{ $vendor->fax ?? '—' }}</div>
                            <div class="col-md-6"><strong>Contact Person:</strong> {{ $vendor->contact_person ?? '—' }}</div>
                            <div class="col-md-6"><strong>Contact Number:</strong> {{ $vendor->contact_number ?? '—' }}</div>
                            <div class="col-12"><strong>Street:</strong> {{ $vendor->street ?? '—' }}</div>
                            <div class="col-md-6"><strong>Commission Percentage:</strong> {{ $vendor->commission_percentage ?? 0 }}%</div>
                            <div class="col-md-6"><strong>Status:</strong> {!! $vendor->inactive ? '<span class="badge bg-danger">Inactive</span>' : '<span class="badge bg-success">Active</span>' !!}</div>
                        </div>
                        <hr class="my-4">
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('vendors.index') }}" class="btn btn-light">Back</a>
                            <a href="{{ route('vendors.edit', $vendor) }}" class="btn btn-warning">Edit</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection