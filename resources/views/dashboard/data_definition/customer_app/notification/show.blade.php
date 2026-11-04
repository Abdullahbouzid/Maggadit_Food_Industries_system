@extends('dashboard.partials.layout')

@section('title', 'Notification Details')

@push('styles')
<style>
    /* ============ Layout Grid (bulletproof) ============ */
    .nova-show-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.25rem;
    }
    @media (min-width: 992px) {
        .nova-show-grid { grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); }
    }
    .nova-show-grid > * { min-width: 0; }

    /* ============ Field Grid (2 per row) ============ */
    .nova-info-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem 1.25rem;
    }
    @media (min-width: 576px) {
        .nova-info-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    .nova-info-grid > * { min-width: 0; }

    /* ============ Info Item ============ */
    .nova-info-label {
        display: flex;
        align-items: center;
        gap: .35rem;
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #6b7280;
        margin-bottom: .35rem;
    }
    .nova-info-value {
        margin: 0;
        font-size: .95rem;
        font-weight: 600;
        color: #111827;
        word-break: break-word;
    }
    .nova-info-value.paragraph {
        font-weight: 500;
        line-height: 1.6;
        white-space: pre-wrap;
    }

    /* ============ Media placeholder ============ */
    .nova-media-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 2.5rem 1rem;
        border: 2px dashed #e5e7eb;
        border-radius: .75rem;
        color: #9ca3af;
        background: #f9fafb;
    }
    .nova-media-placeholder i { font-size: 2rem; margin-bottom: .5rem; }

    /* ============ Badge chips for customers ============ */
    .nova-chip-list {
        display: flex;
        flex-wrap: wrap;
        gap: .35rem;
    }
    .nova-chip {
        display: inline-flex;
        align-items: center;
        padding: .2rem .6rem;
        font-size: .78rem;
        font-weight: 600;
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #e5e7eb;
        border-radius: 999px;
    }
</style>
@endpush

@section('content')

@php
    // تجهيز بيانات العملاء مرة واحدة
    $rawCustomers = $notification->customers;
    if (is_array($rawCustomers)) {
        $customerList = $rawCustomers;
    } elseif (is_string($rawCustomers)) {
        $customerList = json_decode($rawCustomers, true) ?: [];
    } else {
        $customerList = [];
    }
    if (!is_array($customerList)) $customerList = [];

    // تجهيز بيانات مشتركة
    $statusLabel = $notification->is_sent ? 'Sent' : 'Pending';
    $statusClass = $notification->is_sent ? 'success' : 'warning';
@endphp

{{-- ==================== Action Bar ==================== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-eye" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            Notification Details
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">View notification details and content</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('notifications.edit', $notification) }}" class="nova-btn nova-btn-warning nova-btn-sm">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="{{ route('notifications.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
</div>

{{-- ==================== Main Grid: Details | Media ==================== --}}
<div class="nova-show-grid">

    {{-- ========== LEFT: Details ========== --}}
    <div class="nova-card">
        <div class="nova-card-header">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-bell"></i> Notification Details
            </h5>
        </div>
        <div class="nova-card-body">
            <div class="nova-info-grid">

                {{-- Subject EN --}}
                <div>
                    <div class="nova-info-label">
                        <i class="fas fa-heading"></i> Subject (EN)
                    </div>
                    <p class="nova-info-value">{{ $notification->subject_en ?: '—' }}</p>
                </div>

                {{-- Subject AR --}}
                <div>
                    <div class="nova-info-label">
                        <i class="fas fa-heading"></i> العنوان (AR)
                    </div>
                    <p class="nova-info-value">{{ $notification->subject_ar ?: '—' }}</p>
                </div>

                {{-- Body EN --}}
                <div>
                    <div class="nova-info-label">
                        <i class="fas fa-align-left"></i> Body (EN)
                    </div>
                    <p class="nova-info-value paragraph">{{ $notification->body_en ?: '—' }}</p>
                </div>

                {{-- Body AR --}}
                <div>
                    <div class="nova-info-label">
                        <i class="fas fa-align-left"></i> المحتوى (AR)
                    </div>
                    <p class="nova-info-value paragraph">{{ $notification->body_ar ?: '—' }}</p>
                </div>

                {{-- Channel --}}
                <div>
                    <div class="nova-info-label">
                        <i class="fas fa-bell"></i> Channel
                    </div>
                    <p><span class="nova-badge primary">{{ strtoupper($notification->channel) }}</span></p>
                </div>

                {{-- Icon Type --}}
                <div>
                    <div class="nova-info-label">
                        <i class="fas fa-tag"></i> Icon Type
                    </div>
                    <p><span class="nova-badge info">{{ strtoupper($notification->icon_type ?? '—') }}</span></p>
                </div>

                {{-- Group --}}
                <div>
                    <div class="nova-info-label">
                        <i class="fas fa-users-cog"></i> Group
                    </div>
                    <p class="nova-info-value">{{ $notification->group ?? '—' }}</p>
                </div>

                {{-- Status --}}
                <div>
                    <div class="nova-info-label">
                        <i class="fas fa-info-circle"></i> Status
                    </div>
                    <p><span class="nova-badge {{ $statusClass }}">{{ $statusLabel }}</span></p>
                </div>

                {{-- Customers --}}
                <div style="grid-column: 1 / -1;">
                    <div class="nova-info-label">
                        <i class="fas fa-users"></i> Customers
                    </div>
                    @if(!empty($customerList))
                        <div class="nova-chip-list">
                            @foreach($customerList as $c)
                                <span class="nova-chip">
                                    @if($c === 'all')
                                        <i class="fas fa-globe" style="margin-inline-end:.35rem;"></i> All Customers
                                    @else
                                        #{{ $c }}
                                    @endif
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="nova-info-value"><span class="nova-text-muted">— All Customers —</span></p>
                    @endif
                </div>

                {{-- Scheduled At --}}
                <div>
                    <div class="nova-info-label">
                        <i class="fas fa-calendar-alt"></i> Scheduled At
                    </div>
                    <p class="nova-info-value">
                        {{ $notification->scheduled_at ? $notification->scheduled_at->format('F d, Y \a\t h:i A') : '—' }}
                    </p>
                </div>

                {{-- Created At --}}
                <div>
                    <div class="nova-info-label">
                        <i class="fas fa-clock"></i> Created At
                    </div>
                    <p class="nova-info-value">
                        {{ optional($notification->created_at)->format('F d, Y \a\t h:i A') ?? '—' }}
                    </p>
                </div>

                {{-- Updated At --}}
                <div>
                    <div class="nova-info-label">
                        <i class="fas fa-history"></i> Last Updated
                    </div>
                    <p class="nova-info-value">
                        {{ optional($notification->updated_at)->format('F d, Y \a\t h:i A') ?? '—' }}
                    </p>
                </div>

            </div>
        </div>
    </div>

    {{-- ========== RIGHT: Media ========== --}}
    <div class="nova-card">
        <div class="nova-card-header">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-image"></i> Media
            </h5>
        </div>
        <div class="nova-card-body" style="display: flex; flex-direction: column; gap: 1rem;">

            @if($notification->image)
                <div>
                    <div class="nova-info-label">
                        <i class="fas fa-image"></i> Image
                    </div>
                    <img src="{{ asset('uploads/' . $notification->image) }}"
                         alt="Notification image"
                         style="width:100%; max-height:220px; object-fit:cover; border-radius:.5rem; border:1px solid #e5e7eb;">
                </div>
            @endif

            @if($notification->video)
                <div>
                    <div class="nova-info-label">
                        <i class="fas fa-video"></i> Video
                    </div>
                    <video controls
                           style="width:100%; border-radius:.5rem; border:1px solid #e5e7eb;">
                        <source src="{{ asset('uploads/' . $notification->video) }}" type="video/mp4">
                    </video>
                </div>
            @endif

            @if(!$notification->image && !$notification->video)
                <div class="nova-media-placeholder">
                    <i class="fas fa-image"></i>
                    <p class="mb-0">No media attached</p>
                </div>
            @endif

        </div>
    </div>

</div>

{{-- ==================== Bottom Actions ==================== --}}
<div class="nova-card nova-mt-4">
    <div class="nova-card-body">
        <div class="nova-flex nova-gap-2" style="flex-wrap: wrap;">
            <a href="{{ route('notifications.index') }}" class="nova-btn nova-btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Notifications
            </a>
            <a href="{{ route('notifications.edit', $notification) }}" class="nova-btn nova-btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <button type="button"
                    class="nova-btn nova-btn-outline nova-text-danger"
                    onclick="confirmDelete(this)"
                    data-id="{{ $notification->id }}"
                    data-name="{{ $notification->subject_en ?? 'Notification' }}"
                    data-url="{{ route('notifications.destroy', $notification) }}">
                <i class="fas fa-trash-alt"></i> Delete
            </button>
        </div>
    </div>
</div>

{{-- ==================== Delete Confirmation Modal ==================== --}}
<div class="nova-modal-backdrop" id="deleteNotificationModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">Confirm Deletion</h4>
            <p class="nova-delete-modal-text">
                Are you sure you want to delete the notification
                <strong id="deleteNotificationName" class="nova-text-danger"></strong>?
                <br>This action cannot be undone.
            </p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline"
                        onclick="document.getElementById('deleteNotificationModal').classList.remove('show')">
                    Cancel
                </button>
                <form id="deleteForm" method="POST" action="">
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
    // Delete confirmation — يقرأ URL من data-url (آمن مع أي prefix)
    window.confirmDelete = function(btn) {
        const name = btn.dataset.name || 'this notification';
        const url  = btn.dataset.url;

        document.getElementById('deleteNotificationName').innerText = name;
        document.getElementById('deleteForm').action = url;
        document.getElementById('deleteNotificationModal').classList.add('show');
    };

    // Close on backdrop click
    document.querySelectorAll('.nova-modal-backdrop').forEach(function(backdrop) {
        backdrop.addEventListener('click', function(e) {
            if (e.target === backdrop) backdrop.classList.remove('show');
        });
    });

    // Close on ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.nova-modal-backdrop.show').forEach(function(m) {
                m.classList.remove('show');
            });
        }
    });
});
</script>
@endpush