@extends('dashboard.partials.layout')

@section('title', 'Create Role')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-plus-circle" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            Create New Role
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Define a new role and assign permissions</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('roles.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

@if($errors->any())
    <div class="nova-alert danger">
        <i class="fas fa-exclamation-triangle"></i>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="nova-card">
    <div class="nova-card-body">
        <form method="POST" action="{{ route('roles.store') }}">
            @csrf
            <div class="nova-form-group">
                <label class="nova-label" for="name">
                    <i class="fas fa-tag me-1"></i> Role Name
                </label>
                <input type="text" name="name" id="name" class="nova-input nova-input-lg @error('name') nova-input-error @enderror" placeholder="e.g., Editor, Viewer, Manager" required>
                @error('name')
                    <div class="nova-form-error">{{ $message }}</div>
                @enderror
                <div class="nova-form-help">Use a unique, descriptive name.</div>
            </div>

            <div class="nova-form-group mt-4">
                <label class="nova-label">
                    <i class="fas fa-key me-1"></i> Permissions
                </label>
                <div class="nova-row cols-3">
                    @php
                        $permissionsGrouped = $permissions->groupBy(function($perm) {
                            return explode('_', $perm->name)[0] ?? 'general';
                        });
                    @endphp
                    @foreach($permissionsGrouped as $group => $perms)
                        <div class="nova-col-lg-12 nova-col-md-12 mb-3">
                            <div class="nova-form-card h-100">
                                <div class="nova-form-card-header">
                                    <h6 class="nova-fw-bold text-capitalize">{{ $group }}</h6>
                                </div>
                                <div class="nova-form-card-body">
                                    @foreach($perms as $perm)
                                        <div class="nova-form-check">
                                            <input class="nova-form-check-input" type="checkbox" name="permissions[]" value="{{ $perm->name }}" id="perm_{{ $perm->id }}">
                                            <label class="nova-form-check-label" for="perm_{{ $perm->id }}">
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

            <div class="nova-form-actions">
                <a href="{{ route('roles.index') }}" class="nova-btn nova-btn-outline">
                    <i class="fas fa-x"></i> Cancel
                </a>
                <button type="submit" class="nova-btn nova-btn-primary">
                    <i class="fas fa-save"></i> Create Role
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
