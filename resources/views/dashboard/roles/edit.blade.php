@extends('dashboard.partials.layout')

@section('title', 'Edit Role')

@section('content')
<div class="container-fluid p-0">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-5 fw-bold">
                <i data-feather="edit-2"></i> Edit Role: {{ $role->name }}
            </h1>
            <p class="text-muted">Modify role name and permissions</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body">
            <form method="POST" action="{{ route('roles.update', $role) }}">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="form-label fw-semibold">Role Name</label>
                    <input type="text" name="name" class="form-control form-control-lg @error('name') is-invalid @enderror" value="{{ old('name', $role->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Assign Permissions</label>
                    <div class="row">
                        @php
                            $permissionsGrouped = $permissions->groupBy(function($perm) {
                                return explode('_', $perm->name)[0] ?? 'general';
                            });
                        @endphp
                        @foreach($permissionsGrouped as $group => $perms)
                            <div class="col-md-4 mb-3">
                                <div class="card bg-light border-0 h-100">
                                    <div class="card-body">
                                        <h6 class="fw-bold text-capitalize">{{ $group }}</h6>
                                        @foreach($perms as $perm)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $perm->name }}" id="perm_{{ $perm->id }}"
                                                    {{ in_array($perm->name, $rolePermissions) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="perm_{{ $perm->id }}">
                                                    {{ $perm->name }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('roles.index') }}" class="btn btn-light px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary px-5">Update Role</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>document.addEventListener('DOMContentLoaded', () => { if (typeof feather !== 'undefined') feather.replace(); });</script>
@endpush