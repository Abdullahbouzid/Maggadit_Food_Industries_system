@extends('dashboard.partials.layout')

@section('title', 'Permissions Management')

@section('content')

@php
    // حماية ضد اختلاف نوع $permissions (Collection vs Paginator)
    $isPaginated = $permissions instanceof \Illuminate\Pagination\LengthAwarePaginator;
    $totalCount  = $isPaginated ? $permissions->total() : $permissions->count();
    $perPage     = $isPaginated ? $permissions->perPage() : max($permissions->count(), 1);
    $currentPage = $isPaginated ? $permissions->currentPage() : 1;
    $firstItem   = $isPaginated ? ($permissions->firstItem() ?? 0) : ($permissions->count() ? 1 : 0);
    $lastItem    = $isPaginated ? ($permissions->lastItem()  ?? 0) : $permissions->count();
@endphp

{{-- ===== Action Bar ===== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-key" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
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

{{-- ===== Statistics (2 columns now) ===== --}}
<div class="nova-row cols-2">
    <div class="nova-stat">
        <div class="nova-stat-icon primary">
            <i class="fas fa-key"></i>
        </div>
        <p class="nova-stat-label">Total Permissions</p>
        <p class="nova-stat-value">{{ $totalCount }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon secondary">
            <i class="fas fa-users"></i>
        </div>
        <p class="nova-stat-label">Assigned Roles <small class="nova-text-muted">(this page)</small></p>
        <p class="nova-stat-value">
            {{ $permissions->sum(fn($p) => $p->roles->count()) }}
        </p>
    </div>
</div>

{{-- ===== Permissions Table ===== --}}
<div class="nova-table-wrapper">
    <div class="nova-toolbar">
        <div class="nova-toolbar-filters">
            <h3 class="nova-card-title" style="margin: 0;">
                <i class="fas fa-list"></i>
                Permissions List
            </h3>
        </div>

        {{-- Search — wrapped in GET form so it actually works --}}
        <form method="GET" action="{{ route('permissions.index') }}" class="nova-toolbar-search">
            <i class="fas fa-search"></i>
            <input type="search"
                   name="search"
                   placeholder="Search permissions..."
                   value="{{ request('search') }}">
        </form>

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
                    <th style="text-align: end;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($permissions as $perm)
                <tr>
                    {{-- # --}}
                    <td>
                        {{ $loop->iteration + ($currentPage - 1) * $perPage }}
                    </td>

                    {{-- Permission Name --}}
                    <td>
                        <div style="display: inline-flex; align-items: center; gap: .5rem;">
                            <div class="nova-stat-icon primary" style="width: 32px; height: 32px;">
                                <i class="fas fa-key" style="font-size: .8rem;"></i>
                            </div>
                            <span class="nova-fw-semibold"><code>{{ $perm->name }}</code></span>
                        </div>
                    </td>

                    {{-- Guard (كان معكوساً) --}}
                    <td>
                        <code>{{ $perm->guard_name }}</code>
                    </td>

                    {{-- Assigned to Roles (كان معكوساً) --}}
                    <td style="text-align: center;">
                        @if($perm->roles->count())
                            <div class="nova-chip-list" style="justify-content: center; display: inline-flex; flex-wrap: wrap; gap: .25rem;">
                                @foreach($perm->roles->take(3) as $role)
                                    <span class="nova-badge secondary">{{ $role->name }}</span>
                                @endforeach
                                @if($perm->roles->count() > 3)
                                    <span class="nova-badge info">+{{ $perm->roles->count() - 3 }}</span>
                                @endif
                            </div>
                        @else
                            <span class="nova-text-muted">No roles</span>
                        @endif
                    </td>

                    {{-- Actions: edit + delete منفصلان، كل واحد بـ @can خاص به --}}
<td style="text-align: end;">
                        <x-table-actions
                            :edit-url="route('permissions.edit', $perm)"
                            :delete-url="route('permissions.destroy', $perm)"
                            :delete-name="$perm->name"
                            delete-modal="deleteModal" />
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

    {{-- ===== Footer: count + pagination ===== --}}
    <div class="nova-card-footer">
        <div class="nova-flex nova-justify-between nova-items-center nova-flex-wrap nova-gap-2">
            <div class="nova-text-muted nova-fs-sm">
                Showing {{ $firstItem }} to {{ $lastItem }} of {{ $totalCount }} entries
            </div>
            <div class="nova-pagination">
                @if ($isPaginated)
                    {{ $permissions->appends(request()->query())->links() }}
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

{{-- ===== Delete Modal — استخدام id بدل modal-id ===== --}}
<x-delete-modal id="deleteModal" title="Delete Permission?" />

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts
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