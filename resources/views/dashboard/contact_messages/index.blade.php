@extends('dashboard.partials.layout')

@section('title', 'Contact Messages')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-message-square" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            Contact Messages
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Manage incoming customer messages from "Contact Us" form</p>
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

<div class="nova-row cols-2 cols-lg-4 nova-mb-4">
    <div class="nova-stat">
        <div class="nova-stat-icon primary">
            <i class="fas fa-inbox"></i>
        </div>
        <p class="nova-stat-label">Total Messages</p>
        <p class="nova-stat-value">{{ $totalMessages ?? $messages->total() }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon warning">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <p class="nova-stat-label">New Messages</p>
        <p class="nova-stat-value">{{ $newMessages ?? 0 }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon success">
            <i class="fas fa-check-circle"></i>
        </div>
        <p class="nova-stat-label">Replied</p>
        <p class="nova-stat-value">{{ $repliedMessages ?? 0 }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon accent">
            <i class="fas fa-layer-group"></i>
        </div>
        <p class="nova-stat-label">Closed</p>
        <p class="nova-stat-value">{{ $closedMessages ?? 0 }}</p>
    </div>
</div>

<div class="nova-table-wrapper">
    <div class="nova-toolbar">
        <div class="nova-toolbar-filters">
            <h3 class="nova-card-title" style="margin: 0;">
                <i class="fas fa-list"></i>
                Messages List
            </h3>
        </div>
        <div class="nova-toolbar-search">
            <i class="fas fa-search"></i>
            <input type="search" class="nova-input" placeholder="Search messages...">
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Importance</th>
                    <th>Date</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($messages as $msg)
                <tr class="{{ $msg->status === 'new' ? 'nova-row-highlighted' : '' }}">
                    <td>{{ $msg->id }}</td>
                    <td>
                        <span class="nova-fw-semibold">{{ $msg->name }}</span>
                        @if($msg->phone)
                            <br><small class="nova-text-muted">{{ $msg->phone }}</small>
                        @endif
                    </td>
                    <td>{{ $msg->email }}</td>
                    <td>{{ $msg->subject ?? '—' }}</td>
                    <td>
                        @if($msg->status == 'new')
                            <span class="nova-badge warning">New</span>
                        @elseif($msg->status == 'read')
                            <span class="nova-badge secondary">Read</span>
                        @elseif($msg->status == 'replied')
                            <span class="nova-badge success">Replied</span>
                        @else
                            <span class="nova-badge secondary">Closed</span>
                        @endif
                    </td>
                    <td>
                        @if($msg->importance == 'urgent')
                            <span class="nova-badge danger">Urgent</span>
                        @elseif($msg->importance == 'high')
                            <span class="nova-badge accent">High</span>
                        @elseif($msg->importance == 'medium')
                            <span class="nova-badge secondary">Medium</span>
                        @else
                            <span class="nova-badge secondary">Low</span>
                        @endif
                    </td>
                    <td>{{ $msg?->created_at?->format('Y-m-d H:i') ?? '—' }}</td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                            <a href="{{ route('contact-messages.show', $msg) }}" class="nova-icon-btn nova-btn-sm" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <form action="{{ route('contact-messages.forward', $msg) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="nova-icon-btn nova-btn-sm nova-text-success" title="Forward" {{ $msg->forwarded_to_email ? 'disabled' : '' }}>
                                    <i class="fas fa-send"></i>
                                </button>
                            </form>
                            <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger" title="Delete" onclick="confirmDelete({{ $msg->id }}, '{{ addslashes($msg->name ?? 'Message') }}')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="nova-empty">
                            <div class="nova-empty-icon">
                                <i class="fas fa-inbox"></i>
                            </div>
                            <h3 class="nova-empty-title">No messages found</h3>
                            <p class="nova-empty-text">No customer messages to display.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="nova-card-footer">
        <div class="nova-pagination">
            {{ $messages->links() }}
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal (Nova) --}}
<div class="nova-modal-backdrop" id="deleteMessageModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">Confirm Deletion</h4>
            <p class="nova-delete-modal-text">Are you sure you want to delete the message from <strong id="deleteMessageName" class="nova-text-danger"></strong>?<br>This action cannot be undone.</p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('deleteMessageModal').classList.remove('show')">Cancel</button>
                <form id="deleteMessageForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="nova-btn nova-btn-danger">Delete Forever</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.confirmDelete = function(id, name) {
        document.getElementById('deleteMessageName').innerText = name;
        document.getElementById('deleteMessageForm').action = '/contact-messages/' + id;
        document.getElementById('deleteMessageModal').classList.add('show');
    };
});
</script>
@endpush

<style>
.nova-row-highlighted {
    background: rgba(251, 191, 36, 0.1);
}
</style>
