@extends('dashboard.partials.layout')

@section('title', __('messages.contact.title'))

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-message-square" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            {{ __('messages.contact.title') }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">{{ __('messages.contact.subtitle') }}</p>
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
        <p class="nova-stat-label">{{ __('messages.contact.stat_total') }}</p>
        <p class="nova-stat-value">{{ $totalMessages ?? $messages->total() }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon warning">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <p class="nova-stat-label">{{ __('messages.contact.stat_new') }}</p>
        <p class="nova-stat-value">{{ $newMessages ?? 0 }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon success">
            <i class="fas fa-check-circle"></i>
        </div>
        <p class="nova-stat-label">{{ __('messages.contact.stat_replied') }}</p>
        <p class="nova-stat-value">{{ $repliedMessages ?? 0 }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon accent">
            <i class="fas fa-layer-group"></i>
        </div>
        <p class="nova-stat-label">{{ __('messages.contact.stat_closed') }}</p>
        <p class="nova-stat-value">{{ $closedMessages ?? 0 }}</p>
    </div>
</div>

<div class="nova-table-wrapper">
    <div class="nova-toolbar">
        <div class="nova-toolbar-filters">
            <h3 class="nova-card-title" style="margin: 0;">
                <i class="fas fa-list"></i>
                {{ __('messages.contact.list') }}
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
                    <th>{{ __('messages.contact.col_name') }}</th>
                    <th>{{ __('messages.contact.col_email') }}</th>
                    <th>{{ __('messages.contact.col_subject') }}</th>
                    <th>{{ __('messages.contact.col_status') }}</th>
                    <th>{{ __('messages.contact.col_importance') }}</th>
                    <th>{{ __('messages.contact.col_date') }}</th>
                    <th style="text-align: right;">{{ __('messages.contact.col_actions') }}</th>
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
                            <span class="nova-badge warning">{{ __('messages.contact.badge_new') }}</span>
                        @elseif($msg->status == 'read')
                            <span class="nova-badge secondary">{{ __('messages.contact.badge_read') }}</span>
                        @elseif($msg->status == 'replied')
                            <span class="nova-badge success">{{ __('messages.contact.stat_replied') }}</span>
                        @else
                            <span class="nova-badge secondary">{{ __('messages.contact.stat_closed') }}</span>
                        @endif
                    </td>
                    <td>
                        @if($msg->importance == 'urgent')
                            <span class="nova-badge danger">{{ __('messages.contact.badge_urgent') }}</span>
                        @elseif($msg->importance == 'high')
                            <span class="nova-badge accent">{{ __('messages.contact.badge_high') }}</span>
                        @elseif($msg->importance == 'medium')
                            <span class="nova-badge secondary">{{ __('messages.contact.badge_medium') }}</span>
                        @else
                            <span class="nova-badge secondary">{{ __('messages.contact.badge_low') }}</span>
                        @endif
                    </td>
                    <td>{{ $msg->created_at->format('Y-m-d H:i') }}</td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                            <a href="{{ route('contact-messages.show', $msg) }}" class="nova-icon-btn nova-btn-sm" title="{{ __('messages.contact.view') }}">
                                <i class="fas fa-eye"></i>
                            </a>
                            <form action="{{ route('contact-messages.forward', $msg) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="nova-icon-btn nova-btn-sm nova-text-success" title="{{ __('messages.contact.forward') }}" {{ $msg->{{ __('messages.contact.btn_forward') }}
                                    <i class="fas fa-send"></i>
                                </button>
                            </form>
                            <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger" title="{{ __('messages.contact.delete') }}" onclick="confirmDelete({{ $msg->id }}, '{{ addslashes($msg->name ?? 'Message') }}')">
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
                            <h3 class="nova-empty-title">{{ __('messages.contact.no_messages') }}</h3>
                            <p class="nova-empty-text">{{ __('messages.contact.empty_text') }}</p>
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
            <h4 class="nova-delete-modal-title">{{ __('messages.contact.confirm_delete') }}</h4>
            <p class="nova-delete-modal-text">Are you sure you want to delete the message from <strong id="deleteMessage{{ __('messages.contact.col_name') }}" class="nova-text-danger"></strong>?<br>This action cannot be undone.</p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('deleteMessageModal').classList.remove('show')">{{ __('messages.contact.btn_cancel') }}</button>
                <form id="deleteMessageForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="nova-btn nova-btn-danger">{{ __('messages.contact.btn_delete') }}</button>
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
        document.getElementById('deleteMessage{{ __('messages.contact.col_name') }}').innerText = name;
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
