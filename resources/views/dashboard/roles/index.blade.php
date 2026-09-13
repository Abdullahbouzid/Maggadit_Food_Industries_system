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
                            <a href="{{ route('roles.edit', $role) }}" class="nova-icon-btn nova-btn-sm" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger" title="Delete" onclick="confirmDelete('{{ $role->id }}', '{{ addslashes($role->name) }}')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
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

{{-- Delete Confirmation Modal (Nova) --}}
<div class="nova-modal-backdrop" id="deleteRoleModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">Confirm Deletion</h4>
            <p class="nova-delete-modal-text">Are you sure you want to delete role <strong id="deleteRoleName" class="nova-text-danger"></strong>?<br>This action cannot be undone.</p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('deleteRoleModal').classList.remove('show')">Cancel</button>
                <form id="deleteRoleForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="nova-btn nova-btn-danger">Delete Forever</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.confirmDelete = function(id, name) {
        document.getElementById('deleteRoleName').innerText = name;
        document.getElementById('deleteRoleForm').action = '/roles/' + id;
        document.getElementById('deleteRoleModal').classList.add('show');
    };

    setTimeout(function() {
        document.querySelectorAll('.nova-alert').forEach(function(alert) {
            alert.style.transition = 'opacity 0.3s, transform 0.3s';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        });
    }, 5000);
});
</script>
@endpush