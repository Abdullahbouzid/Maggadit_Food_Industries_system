@extends('dashboard.partials.layout')

@section('title', 'Permissions Management')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-{{ __('messages.app.page_') }}">
            <i class="fas fa-key" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            {{ __('messages.app.page__1') }}
        </h2>
        <p class="nova-page-sub{{ __('messages.app.page_') }}" style="margin: 0.25rem 0 0;">{{ __('messages.app.page__5') }}</p>
    </div>
    <div class="nova-actionbar-actions">
        @can('create_permissions')
            <a href="{{ route('permissions.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
                <i class="fas fa-plus"></i> {{ __('messages.app.page__13') }}
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
        <p class="nova-stat-label">{{ __('messages.app.page__6') }}</p>
        <p class="nova-stat-value">{{ $permissions->total() }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon secondary">
            <i class="fas fa-users"></i>
        </div>
        <p class="nova-stat-label">{{ __('messages.app.page__7') }}</p>
        <p class="nova-stat-value">
            @php $count = 0; foreach($permissions as $p) { $count += $p->roles->count(); } echo $count; @endphp
        </p>
    </div>
</div>

{{-- Permissions Table --}}
<div class="nova-table-wrapper">
    <div class="nova-toolbar">
        <div class="nova-toolbar-filters">
            <h3 class="nova-card-{{ __('messages.app.page_') }}" style="margin: 0;">
                <i class="fas fa-list"></i>
                {{ __('messages.app.page__2') }}
            </h3>
        </div>
        <div class="nova-toolbar-search">
            <i class="fas fa-search"></i>
            <input type="search" placeholder="Search permissions...">
        </div>
        <div class="nova-toolbar-actions">
            @can('create_permissions')
                <a href="{{ route('permissions.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
                    <i class="fas fa-plus"></i> {{ __('messages.app.page__14') }}
                </a>
            @endcan
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('messages.app.page__16') }}</th>
                    <th>{{ __('messages.app.page__17') }}</th>
                    <th style="text-align: center;">{{ __('messages.app.page__18') }}</th>
                    <th style="text-align: right;">{{ __('messages.app.page__19') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($permissions as $perm)
                <tr>
                    <td>{{ $loop->iteration + ($permissions->currentPage() - 1) * $permissions->perPage() }}</td>
                    <td>
                        <div class="nova-flex nova-items-center">
                            <div class="nova-stat-icon primary nova-btn-sm" style="width: 32px; height: 32px; margin-inline-end: 0.5rem;">
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
                            <span class="nova-text-muted">{{ __('messages.app.page__10') }}</span>
                        @endif
                    </td>
                    <td><code>{{ $perm->guard_name }}</code></td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                            @can('edit_permissions')
                                <a href="{{ route('permissions.edit', $perm) }}" class="nova-icon-btn nova-btn-sm" {{ __('messages.app.page_') }}="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endcan
                            @can('delete_permissions')
                                <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger" {{ __('messages.app.page_') }}="{{ __('messages.app.page__12') }}" onclick="confirm{{ __('messages.app.page__12') }}('{{ $perm->id }}', '{{ addslashes($perm->name) }}')">
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
                            <h3 class="nova-empty-{{ __('messages.app.page_') }}">{{ __('messages.app.page__3') }}</h3>
                            <p class="nova-empty-text">{{ __('messages.app.page__8') }}</p>
                            @can('create_permissions')
                                <a href="{{ route('permissions.create') }}" class="nova-btn nova-btn-primary">
                                    <i class="fas fa-plus"></i> {{ __('messages.app.page__15') }}
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

{{-- {{ __('messages.app.page__12') }} Confirmation Modal (Nova) --}}
<div class="nova-modal-backdrop" id="deletePermissionModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-{{ __('messages.app.page_') }}">{{ __('messages.app.page__4') }}</h4>
            <p class="nova-delete-modal-text">Are you sure you want to delete permission <strong id="deletePermName" class="nova-text-danger"></strong>?<br>This might affect roles assigned to it.</p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('deletePermissionModal').classList.remove('show')">{{ __('messages.app.page__11') }}</button>
                <form id="deletePermForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="nova-btn nova-btn-danger">{{ __('messages.app.page__12') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.confirm{{ __('messages.app.page__12') }} = function(id, name) {
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