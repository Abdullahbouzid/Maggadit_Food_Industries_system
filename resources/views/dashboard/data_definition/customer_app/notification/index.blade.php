@extends('dashboard.partials.layout')

@section('title', 'إدارة الإشعارات')

@push('styles')
<style>
    /* ============ Nova Pagination ============ */
    .nova-pagination-nav { display: flex; justify-content: center; }
    .nova-pagination-list {
        display: flex;
        align-items: center;
        gap: .25rem;
        list-style: none;
        padding: 0;
        margin: 0;
        flex-wrap: wrap;
    }
    .nova-page-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2rem;
        height: 2rem;
        padding: 0 .5rem;
        font-size: .85rem;
        font-weight: 600;
        color: #374151;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: .375rem;
        text-decoration: none;
        transition: all .15s ease;
        line-height: 1;
    }
    .nova-page-btn:hover {
        background: #f3f4f6;
        border-color: #d1d5db;
        color: #111827;
    }
    .nova-page-active {
        background: var(--nova-primary);
        border-color: var(--nova-primary);
        color: #fff;
    }
    .nova-pagination-list .disabled .nova-page-btn {
        color: #9ca3af;
        cursor: not-allowed;
        background: #f9fafb;
        pointer-events: none;
    }
</style>
@endpush

@section('content')
@php
    /* نسخة من الـ paginator مع الحفاظ على query strings */
    $paginated = $notificationsPaginated->appends(request()->query());
@endphp

<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-bell" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            Send Notification
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Manage and send notifications to customers</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('notifications.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus"></i> Create New
        </a>
        <a href="{{ route('notifications.export', request()->query()) }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-download"></i> تصدير
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
        <div class="nova-stat-icon primary"><i class="fas fa-bell"></i></div>
        <p class="nova-stat-label">إجمالي الإشعارات</p>
        <p class="nova-stat-value">{{ $total }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon primary"><i class="fas fa-check-circle"></i></div>
        <p class="nova-stat-label">Sent</p>
        <p class="nova-stat-value">{{ $sent }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon accent"><i class="fas fa-clock"></i></div>
        <p class="nova-stat-label">معلق</p>
        <p class="nova-stat-value">{{ $pending }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon secondary"><i class="fas fa-layers"></i></div>
        <p class="nova-stat-label">Channels</p>
        <p class="nova-stat-value">{{ $channelsCount }}</p>
    </div>
</div>

<div class="nova-card nova-mb-4">
    <div class="nova-card-body">
        <form id="filterForm" method="GET" action="{{ route('notifications.index') }}" class="nova-flex nova-flex-wrap nova-gap-3" style="align-items: end;">
            <div class="nova-form-group" style="flex: 1 1 16rem; margin-bottom: 0;">
                <label class="nova-label">{{ __('messages.filter.search') }}</label>
                <div class="nova-input-group">
                    <span class="nova-input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="nova-input" placeholder="Search by subject or body..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="nova-form-group" style="flex: 1 1 11rem; margin-bottom: 0;">
                <label class="nova-label">Channel</label>
                <select name="channel" class="nova-select">
                    <option value="">All Channels</option>
                    <option value="all" {{ request('channel') == 'all' ? 'selected' : '' }}>{{ __('messages.filter.all') }}</option>
                    <option value="email" {{ request('channel') == 'email' ? 'selected' : '' }}>Email</option>
                    <option value="push" {{ request('channel') == 'push' ? 'selected' : '' }}>Push</option>
                    <option value="sms" {{ request('channel') == 'sms' ? 'selected' : '' }}>SMS</option>
                </select>
            </div>
            <div class="nova-form-group" style="flex: 1 1 10rem; margin-bottom: 0;">
                <label class="nova-label">{{ __('messages.filter.status') }}</label>
                <select name="status" class="nova-select">
                    <option value="">{{ __('messages.filter.all') }}</option>
                    <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>معلق</option>
                </select>
            </div>
            <div class="nova-form-group" style="flex: 1 1 11rem; margin-bottom: 0;">
                <label class="nova-label">{{ __('messages.filter.per_page') }}</label>
                <select name="per_page" class="nova-select">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>{{ __('messages.filter.all') }}</option>
                </select>
            </div>
            <div class="nova-flex nova-gap-2" style="flex: 1 1 12rem; align-items: end;">
                <button type="submit" class="nova-btn nova-btn-primary">فلتر</button>
                @if(request('search') || request('channel') || request('status') || request('per_page'))
                    <a href="{{ route('notifications.index') }}" class="nova-btn nova-btn-outline">إعادة تعيين</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="nova-table-wrapper">
    <div class="nova-toolbar">
        <div class="nova-toolbar-filters">
            <h3 class="nova-card-title" style="margin: 0;">
                <i class="fas fa-list"></i> قائمة الإشعارات
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
                    <th>الحالة</th>
                    <th style="text-align: right;">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paginated as $notification)
                <tr>
                    <td>{{ $loop->iteration + ($paginated->currentPage() - 1) * $paginated->perPage() }}</td>
                    <td>{{ $notification->subject_en }}</td>
                    <td>{{ $notification->subject_ar }}</td>
                    <td><span class="nova-badge primary">{{ strtoupper($notification->channel) }}</span></td>
                    <td>{{ $notification->group ?? '—' }}</td>
                    <td>
                        @if($notification->is_sent)
                            <span class="nova-badge success">Sent</span>
                        @else
                            <span class="nova-badge accent">معلق</span>
                        @endif
                    </td>
                    <td class="nova-text-end">
                        <x-table-actions
                            :view-url="route('notifications.show', $notification)"
                            :edit-url="route('notifications.edit', $notification)"
                            :delete-url="route('notifications.destroy', $notification)"
                            :delete-name="$notification->subject_en"
                            delete-modal="deleteNotificationModal" />
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="nova-empty">
                            <div class="nova-empty-icon"><i class="fas fa-bell"></i></div>
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
                Showing {{ $paginated->firstItem() ?? 0 }} to {{ $paginated->lastItem() ?? 0 }} of {{ $paginated->total() }} results
            </div>

            {{-- ==================== Inline Pagination (لا يحتاج أي ملف خارجي) ==================== --}}
            @if ($paginated->hasPages())
            <nav class="nova-pagination-nav" role="navigation" aria-label="Pagination">
                <ul class="nova-pagination-list">

                    {{-- Previous --}}
                    @if ($paginated->onFirstPage())
                        <li class="disabled"><span class="nova-page-btn" aria-disabled="true">‹</span></li>
                    @else
                        <li><a href="{{ $paginated->previousPageUrl() }}" class="nova-page-btn" rel="prev" aria-label="Previous">‹</a></li>
                    @endif

                    {{-- Page numbers (نافذة 5 صفحات حول الحالية) --}}
                    @php
                        $currentPage = $paginated->currentPage();
                        $lastPage    = $paginated->lastPage();
                        $startPage   = max(1, $currentPage - 2);
                        $endPage     = min($lastPage, $currentPage + 2);
                    @endphp

                    @if ($startPage > 1)
                        <li><a href="{{ $paginated->url(1) }}" class="nova-page-btn">1</a></li>
                        @if ($startPage > 2)
                            <li class="disabled"><span class="nova-page-btn">…</span></li>
                        @endif
                    @endif

                    @for ($page = $startPage; $page <= $endPage; $page++)
                        @if ($page == $currentPage)
                            <li class="active"><span class="nova-page-btn nova-page-active">{{ $page }}</span></li>
                        @else
                            <li><a href="{{ $paginated->url($page) }}" class="nova-page-btn">{{ $page }}</a></li>
                        @endif
                    @endfor

                    @if ($endPage < $lastPage)
                        @if ($endPage < $lastPage - 1)
                            <li class="disabled"><span class="nova-page-btn">…</span></li>
                        @endif
                        <li><a href="{{ $paginated->url($lastPage) }}" class="nova-page-btn">{{ $lastPage }}</a></li>
                    @endif

                    {{-- Next --}}
                    @if ($paginated->hasMorePages())
                        <li><a href="{{ $paginated->nextPageUrl() }}" class="nova-page-btn" rel="next" aria-label="Next">›</a></li>
                    @else
                        <li class="disabled"><span class="nova-page-btn" aria-disabled="true">›</span></li>
                    @endif

                </ul>
            </nav>
            @endif
            {{-- ==================== /Inline Pagination ==================== --}}

        </div>
    </div>
</div>

<x-delete-modal modal-id="deleteNotificationModal" />

 {{-- Resend Confirmation Modal --}}
<div class="nova-modal-backdrop" id="resendNotificationModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon" style="color: var(--nova-primary);"><i class="fas fa-redo"></i></div>
            <h4 class="nova-delete-modal-title">تأكيد Resend</h4>
            <p class="nova-delete-modal-text">
                Are you sure you want to resend the notification
                <strong id="resendNotificationName" class="nova-text-primary"></strong>?
                <br>This will send a new push notification to the targeted devices.
            </p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('resendNotificationModal').classList.remove('show')">إلغاء</button>
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
    // Auto-submit per_page
    const perPageSelect = document.querySelector('select[name="per_page"]');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    }

    // Resend confirmation
    window.confirmResend = function(id, name) {
        document.getElementById('resendNotificationName').innerText = name;
        document.getElementById('resendForm').action = '/notifications/' + id + '/resend';
        document.getElementById('resendNotificationModal').classList.add('show');
    };

    // Auto-dismiss alerts after 5s
    setTimeout(function() {
        document.querySelectorAll('.nova-alert').forEach(function(alert) {
            alert.style.transition = 'opacity 0.3s, transform 0.3s';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        });
    }, 5000);

    // Close modal on backdrop click
    document.querySelectorAll('.nova-modal-backdrop').forEach(function(backdrop) {
        backdrop.addEventListener('click', function(e) {
            if (e.target === backdrop) {
                backdrop.classList.remove('show');
            }
        });
    });

    // Close modal on ESC
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