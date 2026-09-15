@extends('dashboard.partials.layout')

@section('title', 'Edit Role')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-{{ __('messages.app.page_') }}">
            <i class="fas fa-edit" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            Edit Role: {{ $role->name }}
        </h2>
        <p class="nova-page-sub{{ __('messages.app.page_') }}" style="margin: 0.25rem 0 0;">{{ __('messages.app.page__1') }}</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('roles.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> {{ __('messages.app.page__3') }}
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
        <form method="POST" action="{{ route('roles.update', $role) }}">
            @csrf
            @method('PUT')
            <div class="nova-form-group">
                <label class="nova-label" for="name">
                    <i class="fas fa-tag me-1"></i> {{ __('messages.app.page__5') }}
                </label>
                <input type="text" name="name" id="name" class="nova-input nova-input-lg @error('name') nova-input-error @enderror" value="{{ old('name', $role->name) }}" required>
                @error('name')
                    <div class="nova-form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="nova-form-group mt-4">
                <label class="nova-label">
                    <i class="fas fa-key me-1"></i> {{ __('messages.app.page__6') }}
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
                                            <input class="nova-form-check-input" type="checkbox" name="permissions[]" value="{{ $perm->name }}" id="perm_{{ $perm->id }}"
                                                {{ in_array($perm->name, $rolePermissions) ? 'checked' : '' }}>
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
                    <i class="fas fa-x"></i> {{ __('messages.app.page__4') }}
                </a>
                <button type="submit" class="nova-btn nova-btn-primary">
                    <i class="fas fa-save"></i> {{ __('messages.app.page__2') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
