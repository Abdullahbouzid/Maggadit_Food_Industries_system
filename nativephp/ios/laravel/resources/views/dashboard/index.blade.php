@extends('dashboard.partials.layout')

@section('title', __('messages.dashboard.page_title'))

@push('styles')
<style>
    .nova-kpi-card {
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .nova-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--nova-shadow-xl);
    }
    .nova-kpi-trend.up {
        color: var(--nova-success);
    }
    .nova-kpi-trend.down {
        color: var(--nova-danger);
    }
    .nova-timeline-item {
        position: relative;
        padding-inline-start: 1.5rem;
    }
    .nova-timeline-item:not(:last-child)::before {
        content: '';
        position: absolute;
        inline-start: 0.75rem;
        top: 1.5rem;
        bottom: -0.5rem;
        width: 2px;
        background: var(--nova-border-color);
    }
    .nova-timeline-dot {
        position: absolute;
        inline-start: 0.5rem;
        top: 0.25rem;
        width: 0.75rem;
        height: 0.75rem;
        border-radius: 9999px;
        border: 2px solid var(--nova-primary);
        background: var(--nova-primary);
    }
    .nova-timeline-dot.completed {
        background: var(--nova-success);
        border-color: var(--nova-success);
    }
    .nova-timeline-dot.warning {
        background: var(--nova-warning);
        border-color: var(--nova-warning);
    }
    .nova-timeline-dot.danger {
        background: var(--nova-danger);
        border-color: var(--nova-danger);
    }
    .nova-progress-bar {
        height: 0.5rem;
        border-radius: 9999px;
        overflow: hidden;
        background: var(--nova-surface-3);
    }
    .nova-progress-fill {
        height: 100%;
        border-radius: 9999px;
        transition: width 0.3s ease;
    }
    .nova-progress-success { background-color: var(--nova-success); }
    .nova-progress-warning { background-color: var(--nova-warning); }
    .nova-progress-danger { background-color: var(--nova-danger); }
    .nova-table td { vertical-align: middle; }
    .nova-stat-value { font-size: 1.75rem; font-weight: 700; }
    .nova-stat-label { font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.025em; }
</style>
@endpush

@php
    $stats = $stats ?? [
        'orders_today' => 0,
        'orders_today_trend' => 0,
        'pending_invoices' => 0,
        'near_deadline' => 0,
        'near_deadline_trend' => 0,
        'pending_returns' => 0,
        'recent_orders' => collect(),
        'low_stock_items' => collect(),
        'near_deadline_invoices' => collect(),
    ];
@endphp

@section('content')

<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-tachometer-alt" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            {{ __('messages.dashboard.title') }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">
            {{ now()->translatedFormat('l، Y M d') }}
        </p>
    </div>
</div>

{{-- === END PART 1 === --}}

{{-- ===== KPI Cards ===== --}}
<div class="nova-row cols-4" style="margin-bottom: 1.5rem; gap: 1rem;">
    {{-- Orders Today --}}
    <div class="nova-card nova-kpi-card">
        <div class="nova-card-body" style="padding: 1.25rem;">
            <div class="nova-flex nova-items-center nova-justify-between nova-mb-2">
                <div class="nova-stat-icon primary" style="width: 40px; height: 40px;">
                    <i class="fas fa-truck-loading"></i>
                </div>
                @if(($stats['orders_today_trend'] ?? 0) >= 0)
                    <span class="nova-kpi-trend up">
                        <i class="fas fa-arrow-up"></i> {{ $stats['orders_today_trend'] ?? 0 }}%
                    </span>
                @else
                    <span class="nova-kpi-trend down">
                        <i class="fas fa-arrow-down"></i> {{ abs($stats['orders_today_trend'] ?? 0) }}%
                    </span>
                @endif
            </div>
            <p class="nova-stat-label">{{ __('messages.dashboard.kpi_orders_today') }}</p>
            <p class="nova-stat-value">{{ number_format($stats['orders_today'] ?? 0) }}</p>
        </div>
    </div>

    {{-- Pending Cash Invoices --}}
    <div class="nova-card nova-kpi-card">
        <div class="nova-card-body" style="padding: 1.25rem;">
            <div class="nova-flex nova-items-center nova-justify-between nova-mb-2">
                <div class="nova-stat-icon warning" style="width: 40px; height: 40px;">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <span class="nova-badge warning">{{ __('messages.dashboard.cash') }}</span>
            </div>
            <p class="nova-stat-label">{{ __('messages.dashboard.kpi_pending_invoices') }}</p>
            <p class="nova-stat-value">{{ number_format($stats['pending_invoices'] ?? 0) }}</p>
        </div>
    </div>

    {{-- Near Deadline Invoices --}}
    <div class="nova-card nova-kpi-card">
        <div class="nova-card-body" style="padding: 1.25rem;">
            <div class="nova-flex nova-items-center nova-justify-between nova-mb-2">
                <div class="nova-stat-icon danger" style="width: 40px; height: 40px;">
                    <i class="fas fa-clock"></i>
                </div>
                @if(($stats['near_deadline_trend'] ?? 0) >= 0)
                    <span class="nova-kpi-trend up">
                        <i class="fas fa-arrow-up"></i> {{ $stats['near_deadline_trend'] ?? 0 }}
                    </span>
                @else
                    <span class="nova-kpi-trend down">
                        <i class="fas fa-arrow-down"></i> {{ abs($stats['near_deadline_trend'] ?? 0) }}
                    </span>
                @endif
            </div>
            <p class="nova-stat-label">{{ __('messages.dashboard.kpi_near_deadline') }}</p>
            <p class="nova-stat-value nova-text-danger">{{ number_format($stats['near_deadline'] ?? 0) }}</p>
        </div>
    </div>

    {{-- Pending Returns --}}
    <div class="nova-card nova-kpi-card">
        <div class="nova-card-body" style="padding: 1.25rem;">
            <div class="nova-flex nova-items-center nova-justify-between nova-mb-2">
                <div class="nova-stat-icon accent" style="width: 40px; height: 40px;">
                    <i class="fas fa-undo"></i>
                </div>
                <span class="nova-badge accent">{{ __('messages.dashboard.pending') }}</span>
            </div>
            <p class="nova-stat-label">{{ __('messages.dashboard.kpi_pending_returns') }}</p>
            <p class="nova-stat-value">{{ number_format($stats['pending_returns'] ?? 0) }}</p>
        </div>
    </div>
</div>

{{-- ===== Recent Orders Section ===== --}}
<div class="nova-card">
    <div class="nova-card-header">
        <h2 class="nova-card-title">
            <i class="fas fa-history" style="margin-inline-end: 0.5rem;"></i>
            {{ __('messages.dashboard.recent_orders') }}
        </h2>
        <div class="nova-card-actions nova-flex nova-items-center nova-gap-2">
            {{-- MED-4: State filter dropdown --}}
            <select class="nova-select nova-select-sm" onchange="window.location = this.value" style="width: 180px;">
                <option value="{{ route('orders.index', [], true) }}">{{ __('messages.dashboard.all_statuses') }}</option>
                <option value="{{ route('orders.index', ['status' => 'pending_review']) }}">{{ __('messages.dashboard.status_pending_review') }}</option>
                <option value="{{ route('orders.index', ['status' => 'quotation_sent']) }}">{{ __('messages.dashboard.status_quotation_sent') }}</option>
                <option value="{{ route('orders.index', ['status' => 'customer_approved']) }}">{{ __('messages.dashboard.status_customer_approved') }}</option>
                <option value="{{ route('orders.index', ['status' => 'in_delivery']) }}">{{ __('messages.dashboard.status_in_delivery') }}</option>
                <option value="{{ route('orders.index', ['status' => 'delivered']) }}">{{ __('messages.dashboard.status_delivered') }}</option>
                <option value="{{ route('orders.index', ['status' => 'cancelled']) }}">{{ __('messages.dashboard.status_cancelled') }}</option>
                <option value="{{ route('orders.index', ['status' => 'expired']) }}">{{ __('messages.dashboard.status_expired') }}</option>
            </select>
            <a href="{{ route('orders.index', [], true) }}" class="nova-btn nova-btn-outline nova-btn-sm">
                <i class="fas fa-list"></i> {{ __('messages.dashboard.view_all') }}
            </a>
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th>{{ __('messages.dashboard.col_order_number') }}</th>
                    <th>{{ __('messages.dashboard.col_customer') }}</th>
                    <th>{{ __('messages.dashboard.col_status') }}</th>
                    <th>{{ __('messages.dashboard.col_total') }}</th>
                    <th>{{ __('messages.dashboard.col_date') }}</th>
                    <th style="text-align: center;">{{ __('messages.dashboard.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stats['recent_orders'] as $order)
                @php
                    $statusEnum = $order->status instanceof \App\Enums\OrderStatus ? $order->status : \App\Enums\OrderStatus::tryFrom($order->status);
                    $badgeClass = $statusEnum ? $statusEnum->badgeColor() : 'secondary';
                    $statusLabel = $statusEnum ? $statusEnum->label() : ucfirst((string) $order->status);
                @endphp
                <tr>
                    <td>
                        <span class="nova-fw-semibold">{{ $order->order_number ?? $order->id }}</span>
                    </td>
                    <td>{{ $order->customer->name ?? __('messages.dashboard.guest') }}</td>
                    <td>
                        <span class="nova-badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                    </td>
                    <td>
                        {{ number_format($order->total ?? 0, 2) }}
                        {{ $order->currency->symbol ?? $order->currency->code ?? '' }}
                    </td>
                    <td>{{ \Carbon\Carbon::parse($order->created_at)->format('Y-m-d') ?? '-' }}</td>
                    <td style="text-align: center;">
                        <div class="nova-table-actions">
                            {{-- TODO: Replace with route('orders.show', $order->id) once route is registered --}}
                            <a href="{{ route('orders.show', $order->id) }}" class="nova-icon-btn nova-btn-sm" title="{{ __('messages.dashboard.view') }}">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="nova-empty">
                            <div class="nova-empty-icon">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                            <h3 class="nova-empty-title">{{ __('messages.dashboard.no_recent_orders') }}</h3>
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
                {{ __('messages.dashboard.showing_count', ['count' => $stats['recent_orders']->count() ?? 0]) }}
</div>
</div>

{{-- ===== Low Stock Alerts & Near Deadline Alerts ===== --}}
<div class="nova-row cols-2" style="gap: 1.5rem;">
    {{-- Low Stock Alert --}}
    <div class="nova-card">
        <div class="nova-card-header">
            <h2 class="nova-card-title nova-text-danger">
                <i class="fas fa-exclamation-triangle" style="margin-inline-end: 0.5rem;"></i>
                {{ __('messages.dashboard.low_stock_alerts') }}
            </h2>
        </div>
        <div class="nova-card-body" style="padding: 0.75rem;">
            @forelse($stats['low_stock_items'] as $item)
            <div class="nova-flex nova-items-center nova-justify-between nova-py-2 nova-border-bottom nova-border-light">
                <div>
                    <span class="nova-fw-semibold">{{ $item->name ?? $item->sku }}</span>
                    <div class="nova-text-muted nova-fs-sm">{{ __('messages.dashboard.warehouse') }}: {{ $item->warehouse->name ?? '-' }}</div>
                </div>
                <div class="nova-text-end">
                    <span class="nova-text-danger nova-fw-semibold">{{ number_format($item->quantity_on_hand ?? 0, 0) }}</span>
                    <div>
                        <span class="nova-badge danger">{{ __('messages.dashboard.below_reorder') }}</span>
                    </div>
                </div>
            </div>
            @empty
            <div class="nova-empty nova-py-3">
                <div class="nova-empty-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 class="nova-empty-title">{{ __('messages.dashboard.no_low_stock') }}</h3>
                <p class="nova-empty-text">{{ __('messages.dashboard.all_stock_healthy') }}</p>
            </div>
            @endforelse
        </div>
        @if($stats['low_stock_items']->isNotEmpty())
        <div class="nova-card-footer">
            {{-- TODO: Replace with route('inventory.index') once route is registered --}}
            <a href="{{ route('inventory.index') }}" class="nova-btn nova-btn-outline nova-btn-sm nova-btn-block">
                <i class="fas fa-boxes"></i> {{ __('messages.dashboard.manage_inventory') }}
            </a>
        </div>
        @endif
    </div>

    {{-- Near Deadline Invoices --}}
    <div class="nova-card">
        <div class="nova-card-header">
            <h2 class="nova-card-title nova-text-warning">
                <i class="fas fa-hourglass-half" style="margin-inline-end: 0.5rem;"></i>
                {{ __('messages.dashboard.near_deadline') }}
            </h2>
        </div>
        <div class="nova-card-body" style="padding: 0.75rem;">
            @forelse($stats['near_deadline_invoices'] as $invoice)
            @php
                $deadline = $invoice->payment_deadline_at;
                $daysLeft = $deadline ? now()->diffInDays($deadline, false) : null;
            @endphp
            <div class="nova-flex nova-items-center nova-justify-between nova-py-2 nova-border-bottom nova-border-light">
                <div>
                    <span class="nova-fw-semibold">{{ $invoice->invoice_number ?? $invoice->id }}</span>
                    <div class="nova-text-muted nova-fs-sm">{{ $invoice->customer->name ?? __('messages.dashboard.guest') }}</div>
                </div>
                <div class="nova-text-end">
                    <span>
                        {{ number_format($invoice->total ?? 0, 2) }}
                        {{ $invoice->currency->symbol ?? $invoice->currency->code ?? '' }}
                    </span>
                    @if($daysLeft === null)
                        <span class="nova-badge secondary">{{ __('messages.dashboard.no_deadline') }}</span>
                    @elseif($daysLeft < 0)
                        <span class="nova-badge danger">{{ __('messages.dashboard.overdue') }}</span>
                    @elseif($daysLeft <= 1)
                        <span class="nova-badge danger">{{ $daysLeft }} {{ __('messages.dashboard.day') }}</span>
                    @elseif($daysLeft <= 3)
                        <span class="nova-badge warning">{{ $daysLeft }} {{ __('messages.dashboard.days_left') }}</span>
                    @else
                        <span class="nova-badge accent">{{ $daysLeft }} {{ __('messages.dashboard.days_left') }}</span>
                    @endif
                </div>
            </div>
            @empty
            <div class="nova-empty nova-py-3">
                <div class="nova-empty-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 class="nova-empty-title">{{ __('messages.dashboard.no_deadline_invoices') }}</h3>
                <p class="nova-empty-text">{{ __('messages.dashboard.all_payments_current') }}</p>
            </div>
            @endforelse
        </div>
        @if($stats['near_deadline_invoices']->isNotEmpty())
        <div class="nova-card-footer">
            {{-- TODO: Replace with route('invoices.index') once route is registered --}}
            <a href="{{ route('invoices.index') }}" class="nova-btn nova-btn-outline nova-btn-sm nova-btn-block">
                <i class="fas fa-file-invoice"></i> {{ __('messages.dashboard.view_all_invoices') }}
            </a>
        </div>
        @endif
    </div>
</div>

@endsection
    </div>
</div>