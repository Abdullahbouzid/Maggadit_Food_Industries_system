@extends('dashboard.partials.layout')

@section('title', 'Roles Management')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-shield-alt" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            Roles & Permissions
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Manage user roles and assign permissions</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('roles.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus"></i> New Role
        </a>
    </div>
</div>

@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="nova-alert danger">
        <i class="fas fa-exclamation-circle"></i>
        {{ session('error') }}
    </div>
@endif

{{-- Statistics --}}
<div class="nova-row cols-3">
    <div class="nova-stat">
        <div class="nova-stat-icon primary">
            <i class="fas fa-users"></i>
        </div>
        <p class="nova-stat-label">Total Roles</p>
        <p class="nova-stat-value">{{ $roles->total() }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon secondary">
            <i class="fas fa-key"></i>
        </div>
        <p class="nova-stat-label">Total Permissions</p>
        <p class="nova-stat-value">{{ \Spatie\Permission\Models\Permission::count() }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon accent">
            <i class="fas fa-star"></i>
        </div>
        <p class="nova-stat-label">Admins</p>
        <p class="nova-stat-value">
            @php
                try {
                    echo \App\Models\User::role('Admin')->count();
                } catch (\Exception $e) {
                    echo 0;
                }
            @endphp
        </p>
    </div>
</div>

{{-- Roles Table --}}
<div class="nova-table-wrapper">
    <div class="nova-toolbar">
        <div class="nova-toolbar-filters">
            <h3 class="nova-card-title" style="margin: 0;">
                <i class="fas fa-list"></i>
                Roles List
            </h3>
        </div>
        <div class="nova-toolbar-search">
            <i class="fas fa-search"></i>
            <input type="search" placeholder="Search roles...">
        </div>
        <div class="nova-toolbar-actions">
            <a href="{{ route('roles.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
                <i class="fas fa-plus"></i> Add Role
            </a>
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Role Name</th>
                    <th>Permissions</th>
                    <th>Guard</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $role)
                <tr>
                    <td>{{ $loop->iteration + ($roles->currentPage() - 1) * $roles->perPage() }}</td>
                    <td>
                        <div class="nova-flex nova-items-center">
                            <div class="nova-stat-icon secondary nova-btn-sm" style="width: 32px; height: 32px; margin-right: 0.5rem;">
                                <i class="fas fa-lock"></i>
                            </div>
                            <span class="nova-fw-semibold">{{ $role->name }}</span>
                        </div>
                    </td>
                    <td>
                        @if($role->permissions->count() > 0)
                            <span class="nova-badge secondary">
                                {{ $role->permissions->pluck('name')->take(3)->join(', ') }}
                                @if($role->permissions->count() > 3) ... @endif
                            </span>
                        @else
                            <span class="nova-text-muted">No permissions</span>
                        @endif
                    </td>
                    <td><code>web</code></td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                            <x-table-actions
                                :view-url="null"
                                :edit-url="route('roles.edit', $role)"
                                :delete-url="route('roles.destroy', $role)"
                                :delete-name="$role->name"
                                delete-modal="deleteRoleModal" />
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="nova-empty">
                            <div class="nova-empty-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h3 class="nova-empty-title">No roles found</h3>
                            <p class="nova-empty-text">Create your first role to get started.</p>
                            <a href="{{ route('roles.create') }}" class="nova-btn nova-btn-primary">
                                <i class="fas fa-plus"></i> Create Role
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="nova-card-footer">
        <div class="nova-flex nova-justify-between nova-items-center nova-flex-wrap nova-gap-2">
            <div class="nova-text-muted nova-fs-sm">
                Showing {{ $roles->firstItem() ?? 0 }} to {{ $roles->lastItem() ?? 0 }} of {{ $roles->total() }} entries
            </div>
            <div class="nova-pagination">
                {{ $roles->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

<x-delete-modal modal-id="deleteRoleModal" />