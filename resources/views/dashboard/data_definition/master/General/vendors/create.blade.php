@extends('dashboard.partials.layout')

@section('title', 'Create Vendor')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold mb-2"><i data-feather="truck"></i> Create Vendor</h1>
                    </div>
                </div>
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <form action="{{ route('vendors.store') }}" method="POST">
                            @csrf
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Vendor Name <span class="text-danger">*</span></label>
                                    <input type="text" name="vendor_name" class="form-control @error('vendor_name') is-invalid @enderror" value="{{ old('vendor_name') }}">
                                    @error('vendor_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Code <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}">
                                    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Phone</label>
                                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Fax</label>
                                    <input type="text" name="fax" class="form-control" value="{{ old('fax') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Contact Person</label>
                                    <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Contact Number</label>
                                    <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number') }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Street Address</label>
                                    <textarea name="street" class="form-control" rows="2">{{ old('street') }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Commission Percentage (%)</label>
                                    <input type="number" step="0.01" name="commission_percentage" class="form-control" value="{{ old('commission_percentage', 0) }}">
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" name="inactive" value="1" id="inactive" {{ old('inactive') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="inactive">Inactive</label>
                                        <div class="form-text">If checked, vendor will be inactive.</div>
                                    </div>
                                </div>
                                <div class="col-12 mt-4">
                                    <hr>
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="{{ route('vendors.index') }}" class="btn btn-light px-4">Cancel</a>
                                        <button type="submit" class="btn btn-primary px-4">Create Vendor</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection