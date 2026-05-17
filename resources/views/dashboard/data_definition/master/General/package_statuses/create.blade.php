@extends('dashboard.partials.layout')

@section('title', 'Create Package Status')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <!-- Header -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="layers" class="align-middle me-2"></i>
                            Create Package Status
                        </h1>
                        <p class="text-muted">Define a new package status for an organization</p>
                    </div>
                </div>

                <!-- Form Card -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <form action="{{ route('package-statuses.store') }}" method="POST">
                            @csrf

                            <div class="row g-4">
                                <!-- Status Name -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Status Name <span class="text-danger">*</span></label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text bg-white"><i data-feather="tag"></i></span>
                                        <input type="text" name="status_name" class="form-control @error('status_name') is-invalid @enderror" 
                                               value="{{ old('status_name') }}" placeholder="e.g., Active, Pending, Shipped">
                                    </div>
                                    @error('status_name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Organization -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Organization <span class="text-danger">*</span></label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text bg-white"><i data-feather="building"></i></span>
                                        <select name="organization_id" class="form-select @error('organization_id') is-invalid @enderror">
                                            <option value="">Select Organization</option>
                                            @foreach($organizations as $org)
                                                <option value="{{ $org->id }}" {{ old('organization_id') == $org->id ? 'selected' : '' }}>
                                                    {{ $org->name }} ({{ $org->code }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('organization_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Boolean Flags in a Grid -->
                                <div class="col-12">
                                    <hr class="my-2">
                                    <h6 class="fw-semibold mb-3"><i data-feather="settings"></i> Status Properties</h6>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="resellable" value="1" id="resellable" 
                                                       {{ old('resellable') ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold" for="resellable">
                                                    Resellable
                                                </label>
                                                <div class="form-text small">Can this status be resold?</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="expired" value="1" id="expired"
                                                       {{ old('expired') ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold" for="expired">
                                                    Expired
                                                </label>
                                                <div class="form-text small">Mark as expired status</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="default" value="1" id="default"
                                                       {{ old('default') ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold" for="default">
                                                    Default
                                                </label>
                                                <div class="form-text small">Set as default status</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="lpo_required" value="1" id="lpo_required"
                                                       {{ old('lpo_required') ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold" for="lpo_required">
                                                    LPO Required
                                                </label>
                                                <div class="form-text small">LPO mandatory for this status</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="is_related_to_cn_request" value="1" id="is_related_to_cn_request"
                                                       {{ old('is_related_to_cn_request') ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold" for="is_related_to_cn_request">
                                                    Related to CN Request
                                                </label>
                                                <div class="form-text small">Linked to CN requests</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="is_price_editable" value="1" id="is_price_editable"
                                                       {{ old('is_price_editable') ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold" for="is_price_editable">
                                                    Price Editable
                                                </label>
                                                <div class="form-text small">Allow price changes</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="col-12 mt-4">
                                    <hr>
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="{{ route('package-statuses.index') }}" class="btn btn-light px-4 shadow-sm">
                                            <i data-feather="x"></i> Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                            <i data-feather="save"></i> Create Status
                                        </button>
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

@push('styles')
<style>
    .rounded-4 { border-radius: 1rem !important; }
    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof feather !== 'undefined') feather.replace();
    });
</script>
@endpush