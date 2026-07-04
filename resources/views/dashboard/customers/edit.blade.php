@extends('dashboard.partials.layout')

@section('title', 'Edit Customer')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <!-- Header -->
                <div class="row mb-4">
                    <div class="col-12 col-lg-8">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="edit" class="align-middle me-2"></i>
                            Edit Customer
                        </h1>
                        <p class="text-muted">Update customer details for: <strong>{{ $customer->name }}</strong></p>
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary btn-lg shadow-sm">
                            <i data-feather="arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>

                <!-- Alerts for validation errors -->
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i data-feather="alert-circle" class="me-2"></i>
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Form Card -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0"><i data-feather="edit-2"></i> Customer Information</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('customers.update', $customer->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row g-4">
                                <!-- Name -->
                                <div class="col-md-6">
                                    <label for="name" class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $customer->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Username -->
                                <div class="col-md-6">
                                    <label for="username" class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username', $customer->username) }}" required>
                                    @error('username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div class="col-md-6">
                                    <label for="email" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $customer->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Password -->
                                <div class="col-md-6">
                                    <label for="password" class="form-label fw-semibold">Password</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" minlength="8" placeholder="Leave blank to keep current">
                                    <div class="form-text">Leave blank to keep the current password.</div>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Confirm Password -->
                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label fw-semibold">Confirm Password</label>
                                    <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" id="password_confirmation" name="password_confirmation" minlength="8">
                                    @error('password_confirmation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Avatar -->
                                <div class="col-md-6">
                                    <label for="avatar" class="form-label fw-semibold">Avatar</label>
                                    @if($customer->avatar)
                                        <div class="mb-2">
                                            <img src="{{ $customer->avatar_url }}" alt="Current Avatar" width="80" height="80" class="rounded-circle img-thumbnail">
                                            <span class="ms-2 text-muted small">Current avatar</span>
                                        </div>
                                    @endif
                                    <input type="file" class="form-control @error('avatar') is-invalid @enderror" id="avatar" name="avatar" accept="image/*">
                                    <div class="form-text">Upload a new image to replace the current one.</div>
                                    @error('avatar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Active Organization -->
                                <div class="col-md-6">
                                    <label for="active_organization_id" class="form-label fw-semibold">Active Organization</label>
                                    <select class="form-select @error('active_organization_id') is-invalid @enderror" id="active_organization_id" name="active_organization_id">
                                        <option value="">None</option>
                                        @foreach($organizations as $id => $name)
                                            <option value="{{ $id }}" {{ old('active_organization_id', $customer->active_organization_id) == $id ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('active_organization_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Buttons -->
                                <div class="col-12">
                                    <hr>
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary px-4">
                                            <i data-feather="x"></i> Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary px-5">
                                            <i data-feather="save"></i> Update Customer
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof feather !== 'undefined') feather.replace();
        // Auto-dismiss alerts
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                let bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    });
</script>
@endpush