@extends('dashboard.partials.layout')

@section('title', 'Create Organization')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <!-- Header -->
                <div class="row mb-4">
                    <div class="col-12 col-lg-8">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="plus-circle" class="align-middle me-2" style="width: 32px; height: 32px;"></i>
                            Create Organization
                        </h1>
                        <p class="text-muted">Add a new organization to the hierarchy. All fields marked with <span class="text-danger">*</span> are required.</p>
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('organizations.index') }}" class="btn btn-outline-secondary shadow-sm">
                            <i data-feather="arrow-left" class="align-middle me-1"></i> Back to List
                        </a>
                    </div>
                </div>

                <!-- Form Card -->
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                            <div class="card-header bg-white border-0 py-3 px-4">
                                <h5 class="fw-bold mb-0">
                                    <i data-feather="file-text" class="align-middle me-1"></i>
                                    Organization Information
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <form action="{{ route('organizations.store') }}" method="POST">
                                    @csrf

                                    <!-- Organization Name -->
                                    <div class="mb-4">
                                        <label for="name" class="form-label fw-semibold">
                                            Organization Name <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group shadow-sm">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i data-feather="building" class="text-muted" style="width: 18px;"></i>
                                            </span>
                                            <input type="text" 
                                                   class="form-control border-start-0 @error('name') is-invalid @enderror" 
                                                   id="name" 
                                                   name="name" 
                                                   value="{{ old('name') }}" 
                                                   placeholder="e.g., Acme Corporation"
                                                   required>
                                        </div>
                                        @error('name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>

                                    <!-- Organization Code -->
                                    <div class="mb-4">
                                        <label for="code" class="form-label fw-semibold">
                                            Organization Code <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group shadow-sm">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i data-feather="hash" class="text-muted" style="width: 18px;"></i>
                                            </span>
                                            <input type="text" 
                                                   class="form-control border-start-0 @error('code') is-invalid @enderror" 
                                                   id="code" 
                                                   name="code" 
                                                   value="{{ old('code') }}" 
                                                   placeholder="Unique identifier, e.g., ORG-001"
                                                   required>
                                        </div>
                                        @error('code') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>

                                    <!-- Parent Organization -->
                                    <div class="mb-4">
                                        <label for="parent_id" class="form-label fw-semibold">
                                            Parent Organization
                                        </label>
                                        <div class="input-group shadow-sm">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i data-feather="layers" class="text-muted" style="width: 18px;"></i>
                                            </span>
                                            <select class="form-select border-start-0 @error('parent_id') is-invalid @enderror" 
                                                    id="parent_id" 
                                                    name="parent_id">
                                                <option value="">— None (Root Organization) —</option>
                                                @foreach($organizations as $org)
                                                    <option value="{{ $org->id }}" {{ old('parent_id') == $org->id ? 'selected' : '' }}>
                                                        {{ $org->name }} ({{ $org->code }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-text text-muted">
                                            <i data-feather="info" class="align-middle me-1" style="width: 12px;"></i>
                                            Leave empty if this is a top-level organization.
                                        </div>
                                        @error('parent_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>

                                    <!-- Organization Type -->
                                    <div class="mb-4">
                                        <label for="organization_type" class="form-label fw-semibold">
                                            Organization Type
                                        </label>
                                        <div class="input-group shadow-sm">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i data-feather="tag" class="text-muted" style="width: 18px;"></i>
                                            </span>
                                            <input type="text" 
                                                   class="form-control border-start-0 @error('organization_type') is-invalid @enderror" 
                                                   id="organization_type" 
                                                   name="organization_type" 
                                                   value="{{ old('organization_type') }}" 
                                                   placeholder="e.g., Corporate, Branch, Department">
                                        </div>
                                        @error('organization_type') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>

                                    <!-- GLN -->
                                    <div class="mb-4">
                                        <label for="gln" class="form-label fw-semibold">
                                            GLN (Global Location Number)
                                        </label>
                                        <div class="input-group shadow-sm">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i data-feather="globe" class="text-muted" style="width: 18px;"></i>
                                            </span>
                                            <input type="text" 
                                                   class="form-control border-start-0 @error('gln') is-invalid @enderror" 
                                                   id="gln" 
                                                   name="gln" 
                                                   value="{{ old('gln') }}" 
                                                   placeholder="13-digit GS1 identifier">
                                        </div>
                                        @error('gln') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="d-flex gap-2 justify-content-end mt-4 pt-2 border-top">
                                        <a href="{{ route('organizations.index') }}" class="btn btn-light px-4 shadow-sm">
                                            Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary px-5 shadow-sm">
                                            <i data-feather="save" class="align-middle me-1"></i> Save Organization
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection

@push('styles')
<style>
    .rounded-4 {
        border-radius: 1rem !important;
    }
    .input-group-text {
        background-color: #fff;
    }
    .form-control:focus, .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13,110,253,.15);
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        feather.replace();
    });
</script>
@endpush