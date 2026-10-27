@extends('dashboard.partials.layout')

@section('title', __('messages.invoice_page.title'))

@push('styles')
<style>
    .nova-invoice-tabs {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }
    .nova-invoice-tabs .nova-tab-btn {
        padding: 0.5rem 1rem;
        border-radius: 10px;
        border: 1px solid var(--nova-border);
        background: var(--nova-surface);
        color: var(--nova-text-3);
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.15s;
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        white-space: nowrap;
        text-decoration: none;
    }
    .nova-invoice-tabs .nova-tab-btn:hover {
        border-color: var(--nova-primary);
        color: var(--nova-primary);
        background: var(--nova-surface-2);
    }
    .nova-invoice-tabs .nova-tab-btn.active {
        background: var(--nova-primary);
        color: white;
        border-color: var(--nova-primary);
    }
    .nova-invoice-tabs .nova-tab-btn .nova-badge {
        font-size: 0.7rem;
        padding: 0.125rem 0.5rem;
    }
    .nova-invoice-tabs .nova-tab-btn.active .nova-badge {
        background: rgba(255,255,255,0.2);
        color: white;
        border-color: transparent;
    }
</style>
@endpush

@php
    $invoices = $invoices ?? collect();
    $filters = $filters ?? [
        'status' => request('status'),
        'payment_type' => request('payment_type'),
        'date_from' => request('date_from'),
        'date_to' => request('date_to'),
        'search' => request('search'),
    ];
    $statusCounts = $statusCounts ?? collect();
    @endphp

@section('content')

{{-- ===== Hero Header ===== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-file-invoice" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            {{ __('messages.invoice_page.title') }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">
            {{ __('messages.invoice_page.subtitle') }}
        </p>
    </div>
    <div>
        <a href="javascript:void(0)" class="nova-btn nova-btn-secondary" title="{{ __('messages.invoice_page.export') }}">
            <i class="fas fa-download"></i> {{ __('messages.invoice_page.export') }}
        </a>
    </div>
</div>

{{-- ===== Filter Bar ===== --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-body">
        <form method="GET" action="{{ route('invoices.index') }}" class="nova-filter-bar">
            <div class="nova-form-group">
                <input type="text" name="search" class="nova-input" placeholder="{{ __('messages.invoice_page.search_placeholder') }}" value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="nova-form-group">
                <select name="status" class="nova-select">
                    <option value="">{{ __('messages.invoice_page.status_all') }}</option>
                    @foreach(\App\Enums\InvoiceStatus::cases() as $status)
                        @if($status->value !== 'rejected' && $status->value !== 'cancelled' && $status->value !== 'expired')
                            <option value="{{ $status->value }}" {{ ($filters['status'] ?? '') === $status->value ? 'selected' : '' }}>
                                {{ $status->label() }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="nova-form-group">
                <select name="payment_type" class="nova-select">
                    <option value="">{{ __('messages.invoice_page.status_all') }}</option>
                    <option value="cash" {{ ($filters['payment_type'] ?? '') === 'cash' ? 'selected' : '' }}>{{ __('messages.invoice_page.payment_type_cash') }}</option>
                    <option value="credit" {{ ($filters['payment_type'] ?? '') === 'credit' ? 'selected' : '' }}>{{ __('messages.invoice_page.payment_type_credit') }}</option>
                </select>
            </div>
            <div class="nova-form-group">
                <input type="date" name="date_from" class="nova-input" value="{{ $filters['date_from'] ?? '' }}">
            </div>
            <div class="nova-form-group">
                <input type="date" name="date_to" class="nova-input" value="{{ $filters['date_to'] ?? '' }}">
            </div>
            <div class="nova-form-group" style="flex: 0 0 auto; display: flex; gap: 0.5rem;">
                <button type="submit" class="nova-btn nova-btn-primary nova-btn-sm">
                    <i class="fas fa-filter"></i> {{ __('messages.invoice_page.apply') }}
                </button>
                <a href="{{ route('invoices.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
                    <i class="fas fa-undo"></i> {{ __('messages.invoice_page.reset') }}
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ===== Status Tabs ===== --}}
<div class="nova-invoice-tabs">
    @php
        $currentStatus = request('status');
    @endphp
    <a href="{{ route('invoices.index', array_merge(request()->except(['status', 'page']), ['status' => null])) }}"
       class="nova-tab-btn {{ empty($currentStatus) ? 'active' : '' }}">
        {{ __('messages.invoice_page.status_all') }}
        <span class="nova-badge">{{ $invoices->total() }}</span>
    </a>
    <a href="{{ route('invoices.index', array_merge(request()->except(['status', 'page']), ['status' => 'pending_approval'])) }}"
       class="nova-tab-btn {{ $currentStatus === 'pending_approval' ? 'active' : '' }}">
        {{ __('messages.invoice_page.status_pending_approval') }}
        <span class="nova-badge nova-badge-warning">{{ $statusCounts['pending_approval'] ?? 0 }}</span>
    </a>
    <a href="{{ route('invoices.index', array_merge(request()->except(['status', 'page']), ['status' => 'approved'])) }}"
       class="nova-tab-btn {{ $currentStatus === 'approved' ? 'active' : '' }}">
        {{ __('messages.invoice_page.status_approved') }}
        <span class="nova-badge nova-badge-success">{{ $statusCounts['approved'] ?? 0 }}</span>
    </a>
    <a href="{{ route('invoices.index', array_merge(request()->except(['status', 'page']), ['status' => 'paid'])) }}"
       class="nova-tab-btn {{ $currentStatus === 'paid' ? 'active' : '' }}">
        {{ __('messages.invoice_page.status_paid') }}
        <span class="nova-badge nova-badge-success">{{ $statusCounts['paid'] ?? 0 }}</span>
    </a>
    <a href="{{ route('invoices.index', array_merge(request()->except(['status', 'page']), ['status' => 'overdue'])) }}"
       class="nova-tab-btn {{ $currentStatus === 'overdue' ? 'active' : '' }}">
        {{ __('messages.invoice_page.status_overdue') }}
        <span class="nova-badge nova-badge-danger">{{ $statusCounts['overdue'] ?? 0 }}</span>
    </a>
</div>

{{-- ===== Invoices Table ===== --}}
<div class="nova-card">
    <div class="nova-table-wrapper">
        <table class="nova-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('messages.invoice_page.col_branch') }}</th>
                    <th>{{ __('messages.invoice_page.col_customer') }}</th>
                    <th>{{ __('messages.invoice_page.col_date') }}</th>
                    <th>{{ __('messages.invoice_page.col_payment_type') }}</th>
                    <th>{{ __('messages.invoice_page.col_total') }}</th>
                    <th>{{ __('messages.invoice_page.col_paid') }}</th>
                    <th>{{ __('messages.invoice_page.col_remaining') }}</th>
                    <th>{{ __('messages.invoice_page.col_deadline') }}</th>
                    <th>{{ __('messages.invoice_page.col_status') }}</th>
                    <th class="nova-text-end">{{ __('messages.invoice_page.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                    @php
                        $invoiceStatus = \App\Enums\InvoiceStatus::tryFrom($invoice->status);
                        $deadline = $invoice->payment_deadline_at;
                        $daysLeft = $deadline ? now()->diffInDays($deadline, false) : null;
                        $remaining = ($invoice->total_amount ?? 0) - ($invoice->paid_amount ?? 0);
                        $currencySymbol = $invoice->currency->symbol ?? $invoice->currency->code ?? '';
                    @endphp
                    <tr>
                        <td><span class="nova-fw-semibold">{{ $invoice->invoice_number ?? $invoice->id }}</span></td>
                        <td>{{ $invoice->order ? ($invoice->order->branch ? $invoice->order->branch->name : ($invoice->order->branch_id ?? '—')) : '—' }}</td>
                        <td>{{ $invoice->customer->name ?? '—' }}</td>
                        <td>{{ $invoice->created_at ? \Carbon\Carbon::parse($invoice->created_at)->translatedFormat('Y-m-d') : '—' }}</td>
                        <td>
                            @if($invoice->payment_type === 'cash')
                                <span class="nova-badge nova-badge-success">{{ __('messages.invoice_page.payment_type_cash') }}</span>
                            @elseif($invoice->payment_type === 'credit')
                                <span class="nova-badge nova-badge-warning">{{ __('messages.invoice_page.payment_type_credit') }}</span>
                            @else
                                {{ $invoice->payment_type ?? '—' }}
                            @endif
                        </td>
                        <td>{{ $currencySymbol }} {{ number_format($invoice->total_amount ?? 0, 2) }}</td>
                        <td>{{ $currencySymbol }} {{ number_format($invoice->paid_amount ?? 0, 2) }}</td>
                        <td class="nova-fw-bold">{{ $currencySymbol }} {{ number_format($remaining, 2) }}</td>
                        <td>
                            @if($daysLeft === null)
                                <span class="nova-text-muted">{{ __('messages.invoice_page.no_deadline') }}</span>
                            @elseif($daysLeft < 0)
                                <span class="nova-badge nova-badge-danger">{{ __('messages.invoice_page.overdue') }}</span>
                            @elseif($daysLeft <= 1)
                                <span class="nova-badge nova-badge-danger">{{ $daysLeft }} {{ __('messages.invoice_page.days_left') }}</span>
                            @elseif($daysLeft <= 3)
                                <span class="nova-badge nova-badge-warning">{{ $daysLeft }} {{ __('messages.invoice_page.days_left') }}</span>
                            @else
                                <span class="nova-badge nova-badge-accent">{{ $daysLeft }} {{ __('messages.invoice_page.days_left') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($invoiceStatus)
                                <span class="nova-badge {{ $invoiceStatus->badgeColor() }}">{{ $invoiceStatus->label() }}</span>
                            @endif
                        </td>
                        <td class="nova-text-end">
                            <div class="nova-table-actions">
                                <a href="{{ route('invoices.show', $invoice) }}" class="nova-icon-btn nova-btn-sm" title="{{ __('messages.invoice_page.view') }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($invoiceStatus && in_array($invoiceStatus->value, ['pending_approval']))
                                    <a href="{{ route('invoices.show', $invoice) }}" class="nova-icon-btn nova-btn-sm" title="{{ __('messages.invoice_page.approve') }}">
                                        <i class="fas fa-check"></i>
                                    </a>
                                @endif
                                <a href="javascript:void(0)" class="nova-icon-btn nova-btn-sm" title="{{ __('messages.invoice_page.record_payment') }}">
                                    <i class="fas fa-money-bill-wave"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12">
                            <div class="nova-empty">
                                <div class="nova-empty-icon">
                                    <i class="fas fa-file-invoice"></i>
                                </div>
                                <h3 class="nova-empty-title">{{ __('messages.invoice_page.empty') }}</h3>
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
                {{ __('messages.invoice_page.showing_count', ['count' => $invoices->count()]) }}
            </div>
            {{ $invoices->links() }}
        </div>
    </div>
</div>

@endsection