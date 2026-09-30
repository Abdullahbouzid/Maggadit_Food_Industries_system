@extends('dashboard.partials.layout')

@section('title', 'Permissions Management')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-key" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            System Permissions
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Manage all available permissions in the system</p>
    </div>
    <div class="nova-actionbar-actions">
        @can('create_permissions')
            <a href="{{ route('permissions.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
                <i class="fas fa-plus"></i> New Permission
            </a>
        @endcan
    </div>
</div>

@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

{{-- Statistics --}}
<div class="nova-row cols-3">
    <div class="nova-stat">
        <div class="nova-stat-icon primary">
            <i class="fas fa-key"></i>
        </div>
        <p class="nova-stat-label">Total Permissions</p>
        <p class="nova-stat-value">{{ $permissions->total() }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon secondary">
            <i class="fas fa-users"></i>
        </div>
        <p class="nova-stat-label">Assigned Roles</p>
        <p class="nova-stat-value">
            @php $count = 0; foreach($permissions as $p) { $count += $p->roles->count(); } echo $count; @endphp>
        </p>
    </div>
</div>

{{-- Permissions Table --}}
<div class="nova-table-wrapper">
    <div class="nova-toolbar">
        <div class="nova-toolbar-filters">
            <h3 class="nova-card-title" style="margin: 0;">
                <i class="fas fa-list"></i>
                Permissions List
            </h3>
        </div>
        <div class="nova-toolbar-search">
            <i class="fas fa-search"></i>
            <input type="search" placeholder="Search permissions...">
        </div>
        <div class="nova-toolbar-actions">
            @can('create_permissions')
                <a href="{{ route('permissions.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
                    <i class="fas fa-plus"></i> Add Permission
                </a>
            @endcan
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Permission Name</th>
                    <th>Guard</th>
                    <th style="text-align: center;">Assigned to Roles</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($permissions as $perm)
                <tr>
                    <td>{{ $loop->iteration + ($permissions->currentPage() - 1) * $permissions->perPage() }}</td>
                    <td>
                        <div class="nova-flex nova-items-center">
                            <div class="nova-stat-icon primary nova-btn-sm" style="width: 32px; height: 32px; margin-right: 0.5rem;">
                                <i class="fas fa-key"></i>
                            </div>
                            <span class="nova-fw-semibold"><code>{{ $perm->name }}</code></span>
                        </div>
                    </td>
                    <td>
                        @php $rolesList = $perm->roles->pluck('name')->take(3)->join(', '); @endphp
                        @if($rolesList)
                            <span class="nova-badge secondary">{{ $rolesList }}</span>
                            @if($perm->roles->count() > 3) <span class="nova-text-muted">...</span> @endif
                        @else
                            <span class="nova-text-muted">No roles</span>
                        @endif
                    </td>
                    <td><code>{{ $perm->guard_name }}</code></td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                            @can('edit_permissions')
                                <a href="{{ route('permissions.edit', $perm) }}" class="nova-icon-btn nova-btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endcan
                            @can('delete_permissions')
                                <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger" title="Delete" onclick="confirmDelete('{{ $perm->id }}', '{{ addslashes($perm->name) }}')">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="nova-empty">
                            <div class="nova-empty-icon">
                                <i class="fas fa-key"></i>
                            </div>
                            <h3 class="nova-empty-title">No permissions found</h3>
                            <p class="nova-empty-text">Create your first permission to get started.</p>
                            @can('create_permissions')
                                <a href="{{ route('permissions.create') }}" class="nova-btn nova-btn-primary">
                                    <i class="fas fa-plus"></i> Create Permission
                                </a>
                            @endcan
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
                Showing {{ $permissions->firstItem() ?? 0 }} to {{ $permissions->lastItem() ?? 0 }} of {{ $permissions->total() }} entries
            </div>
            <div class="nova-pagination">
                {{ $permissions->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

{{-- Delete Confirmation Modal (Nova) --}}
<div class="nova-modal-backdrop" id="deletePermissionModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">Confirm Deletion</h4>
            <p class="nova-delete-modal-text">Are you sure you want to delete permission <strong id="deletePermName" class="nova-text-danger"></strong>?<br>This might affect roles assigned to it.</p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('deletePermissionModal').classList.remove('show')">Cancel</button>
                <form id="deletePermForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="nova-btn nova-btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.confirmDelete = function(id, name) {
        document.getElementById('deletePermName').innerText = name;
        document.getElementById('deletePermForm').action = '/permissions/' + id;
        document.getElementById('deletePermissionModal').classList.add('show');
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