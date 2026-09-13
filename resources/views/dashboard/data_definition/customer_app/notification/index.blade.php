@extends('dashboard.partials.layout')

@section('title', 'Notifications Management')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-bell" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            Send Notification
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Manage and send notifications to customers</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('notifications.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus"></i> Create New
        </a>
        <a href="{{ route('notifications.export', request()->query()) }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-download"></i> Export
        </a>
    </div>
</div>

@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

<div class="nova-row cols-2 cols-lg-4">
    <div class="nova-stat">
        <div class="nova-stat-icon primary">
            <i class="fas fa-bell"></i>
        </div>
        <p class="nova-stat-label">Total Notifications</p>
        <p class="nova-stat-value">{{ $total }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon primary">
            <i class="fas fa-check-circle"></i>
        </div>
        <p class="nova-stat-label">Sent</p>
        <p class="nova-stat-value">{{ $sent }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon accent">
            <i class="fas fa-clock"></i>
        </div>
        <p class="nova-stat-label">Pending</p>
        <p class="nova-stat-value">{{ $pending }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon secondary">
            <i class="fas fa-layers"></i>
        </div>
        <p class="nova-stat-label">Channels</p>
        <p class="nova-stat-value">{{ $channelsCount }}</p>
    </div>
</div>

<div class="nova-card nova-mb-4">
    <div class="nova-card-body">
        <form id="filterForm" method="GET" action="{{ route('notifications.index') }}" class="nova-flex nova-flex-wrap nova-gap-3" style="align-items: end;">
            <div class="nova-form-group" style="flex: 1 1 16rem; margin-bottom: 0;">
                <label class="nova-label">Search</label>
                <div class="nova-input-group">
                    <span class="nova-input-group-text">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" class="nova-input" placeholder="Search by subject or body..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="nova-form-group" style="flex: 1 1 11rem; margin-bottom: 0;">
                <label class="nova-label">Channel</label>
                <select name="channel" class="nova-select">
                    <option value="">All Channels</option>
                    <option value="all" {{ request('channel') == 'all' ? 'selected' : '' }}>All</option>
                    <option value="email" {{ request('channel') == 'email' ? 'selected' : '' }}>Email</option>
                    <option value="push" {{ request('channel') == 'push' ? 'selected' : '' }}>Push</option>
                    <option value="sms" {{ request('channel') == 'sms' ? 'selected' : '' }}>SMS</option>
                </select>
            </div>
            <div class="nova-form-group" style="flex: 1 1 10rem; margin-bottom: 0;">
                <label class="nova-label">Status</label>
                <select name="status" class="nova-select">
                    <option value="">All</option>
                    <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>
            <div class="nova-form-group" style="flex: 1 1 11rem; margin-bottom: 0;">
                <label class="nova-label">Per Page</label>
                <select name="per_page" class="nova-select">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All</option>
                </select>
            </div>
            <div class="nova-flex nova-gap-2" style="flex: 1 1 12rem; align-items: end;">
                <button type="submit" class="nova-btn nova-btn-primary">Filter</button>
                @if(request('search') || request('channel') || request('status') || request('per_page'))
                    <a href="{{ route('notifications.index') }}" class="nova-btn nova-btn-outline">Reset</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="nova-table-wrapper">
    <div class="nova-toolbar">
        <div class="nova-toolbar-filters">
            <h3 class="nova-card-title" style="margin: 0;">
                <i class="fas fa-list"></i>
                Notifications List
            </h3>
        </div>
        <div class="nova-toolbar-actions">
            <a href="{{ route('notifications.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
                <i class="fas fa-plus"></i> Add Notification
            </a>
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Subject (EN)</th>
                    <th>Subject (AR)</th>
                    <th>Channel</th>
                    <th>Group</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notificationsPaginated as $notification)
                <tr>
                    <td>{{ $loop->iteration + ($notificationsPaginated->currentPage() - 1) * $notificationsPaginated->perPage() }}</td>
                    <td>{{ $notification->subject_en }}</td>
                    <td>{{ $notification->subject_ar }}</td>
                    <td><span class="nova-badge primary">{{ strtoupper($notification->channel) }}</span></td>
                    <td>{{ $notification->group ?? '—' }}</td>
                    <td>
                        @if($notification->is_sent)
                            <span class="nova-badge success">Sent</span>
                        @else
                            <span class="nova-badge accent">Pending</span>
                        @endif
                    </td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                            <a href="{{ route('notifications.show', $notification) }}" class="nova-icon-btn nova-btn-sm" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('notifications.edit', $notification) }}" class="nova-icon-btn nova-btn-sm" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if(in_array($notification->channel, ['all', 'push']))
                            <button type="button" class="nova-icon-btn nova-btn-sm nova-text-info" title="Resend" onclick="confirmResend({{ $notification->id }}, '{{ addslashes($notification->subject_en) }}')">
                                <i class="fas fa-redo"></i>
                            </button>
                            @endif
                            <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger" title="Delete" onclick="confirmDelete({{ $notification->id }}, '{{ addslashes($notification->subject_en) }}')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="nova-empty">
                            <div class="nova-empty-icon">
                                <i class="fas fa-bell"></i>
                            </div>
                            <h3 class="nova-empty-title">No notifications found</h3>
                            <p class="nova-empty-text">Create your first notification now.</p>
                            <a href="{{ route('notifications.create') }}" class="nova-btn nova-btn-primary">
                                <i class="fas fa-plus"></i> Create Notification
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
                Showing {{ $notificationsPaginated->firstItem() ?? 0 }} to {{ $notificationsPaginated->lastItem() ?? 0 }} of {{ $notificationsPaginated->total() }} entries
            </div>
            <div class="nova-pagination">
                {{ $notificationsPaginated->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal (Nova) --}}
<div class="nova-modal-backdrop" id="deleteNotificationModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">Confirm Deletion</h4>
            <p class="nova-delete-modal-text">Are you sure you want to delete the notification <strong id="deleteNotificationName" class="nova-text-danger"></strong>?<br>This action cannot be undone.</p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('deleteNotificationModal').classList.remove('show')">Cancel</button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="nova-btn nova-btn-danger">Delete Forever</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Resend Confirmation Modal (Nova) --}}
<div class="nova-modal-backdrop" id="resendNotificationModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon" style="color: var(--nova-primary);">
                <i class="fas fa-redo"></i>
            </div>
            <h4 class="nova-delete-modal-title">Confirm Resend</h4>
            <p class="nova-delete-modal-text">Are you sure you want to resend the notification <strong id="resendNotificationName" class="nova-text-primary"></strong>?<br>This will send a new push notification to the targeted devices.</p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('resendNotificationModal').classList.remove('show')">Cancel</button>
                <form id="resendForm" method="POST" action="">
                    @csrf
                    <button type="submit" class="nova-btn nova-btn-primary">Resend Now</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const perPageSelect = document.querySelector('select[name="per_page"]');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    }

    window.confirmDelete = function(id, name) {
        document.getElementById('deleteNotificationName').innerText = name;
        document.getElementById('deleteForm').action = '/notifications/' + id;
        document.getElementById('deleteNotificationModal').classList.add('show');
    };

    window.confirmResend = function(id, name) {
        document.getElementById('resendNotificationName').innerText = name;
        document.getElementById('resendForm').action = '/notifications/' + id + '/resend';
        document.getElementById('resendNotificationModal').classList.add('show');
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
