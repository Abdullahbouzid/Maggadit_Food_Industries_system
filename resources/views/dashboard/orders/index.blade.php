@extends('dashboard.partials.layout')

@section('title', __('messages.orders.page_title'))

@push('styles')
<style>
    .nova-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--nova-shadow-xl);
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
    .nova-status-tabs {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }
    .nova-status-tab {
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
    }
    .nova-status-tab:hover {
        border-color: var(--nova-primary);
        color: var(--nova-primary);
        background: var(--nova-surface-2);
    }
    .nova-status-tab.active {
        background: var(--nova-primary);
        color: white;
        border-color: var(--nova-primary);
    }
    .nova-status-tab .nova-badge {
        font-size: 0.7rem;
        padding: 0.125rem 0.5rem;
    }
    .nova-status-tab.active .nova-badge {
        background: rgba(255,255,255,0.2);
        color: white;
        border-color: transparent;
    }
    .nova-filter-bar {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        align-items: flex-end;
        margin-bottom: 1rem;
    }
    .nova-filter-bar .nova-form-group {
        margin-bottom: 0;
        flex: 1;
        min-width: 180px;
    }
    .nova-filter-bar .nova-input,
    .nova-filter-bar .nova-select {
        height: 38px;
        font-size: 0.85rem;
    }
    @media (max-width: 767.98px) {
        .nova-filter-bar .nova-form-group {
            flex: 1 0 100%;
        }
    }
</style>
@endpush

@php
    $orders = $orders ?? collect();
    $filters = $filters ?? [
        'status' => request('status'),
        'customer_id' => request('customer_id'),
        'date_from' => request('date_from'),
        'date_to' => request('date_to'),
        'search' => request('search'),
    ];
    $statusCounts = $statusCounts ?? [];
@endphp

@section('content')

{{-- ===== Hero Header ===== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-shopping-cart" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            {{ __('messages.orders.title') }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">
            {{ now()->translatedFormat('l، Y M d') }}
        </p>
    </div>
    <div>
        <a href="{{ route('orders.create') }}" class="nova-btn nova-btn-primary">
            <i class="fas fa-plus"></i> {{ __('messages.orders.new_order') }}
        </a>
    </div>
</div>

{{-- ===== Filter Bar ===== --}}
<div class="nova-card">
    <div class="nova-card-body" style="padding: 1rem;">
        <form method="GET" action="{{ route('orders.index') }}" class="nova-filter-bar">
            <div class="nova-form-group">
                <input type="text" name="search" class="nova-input" placeholder="{{ __('messages.orders.search_placeholder') }}" value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="nova-form-group">
                <select name="status" class="nova-select">
                    <option value="">{{ __('messages.orders.all_statuses') }}</option>
                    @foreach(\App\Enums\OrderStatus::cases() as $status)
                        <option value="{{ $status->value }}" {{ ($filters['status'] ?? '') === $status->value ? 'selected' : '' }}>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="nova-form-group">
                <select name="customer_id" class="nova-select">
                    <option value="">{{ __('messages.orders.all_customers') }}</option>
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
                    <i class="fas fa-filter"></i> {{ __('messages.orders.apply_filters') }}
                </button>
                <a href="{{ route('orders.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
                    <i class="fas fa-undo"></i> {{ __('messages.orders.reset_filters') }}
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ===== Status Tabs ===== --}}
<div class="nova-status-tabs">
    <a href="{{ route('orders.index') }}" class="nova-status-tab {{ empty($filters['status']) ? 'active' : '' }}">
        {{ __('messages.orders.all') }}
        <span class="nova-badge">{{ array_sum($statusCounts) }}</span>
    </a>
    @foreach(\App\Enums\OrderStatus::cases() as $status)
        <a href="{{ route('orders.index', ['status' => $status->value]) }}" class="nova-status-tab {{ ($filters['status'] ?? '') === $status->value ? 'active' : '' }}">
            {{ $status->label() }}
            <span class="nova-badge">{{ $statusCounts[$status->value] ?? 0 }}</span>
        </a>
    @endforeach
</div>

{{-- ===== Orders Table ===== --}}
<div class="nova-card">
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('messages.orders.col_order_number') }}</th>
                    <th>{{ __('messages.orders.col_customer') }}</th>
                    <th>{{ __('messages.orders.col_warehouse') }}</th>
                    <th>{{ __('messages.orders.col_date') }}</th>
                    <th>{{ __('messages.orders.col_items_count') }}</th>
                    <th>{{ __('messages.orders.col_total_qty') }}</th>
                    <th>{{ __('messages.orders.col_currency') }}</th>
                    <th>{{ __('messages.orders.col_total') }}</th>
                    <th>{{ __('messages.orders.col_status') }}</th>
                    <th style="text-align: center;">{{ __('messages.orders.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                @php
                    $totalQty = $order->items->sum('quantity');
                    $itemsCount = $order->items->count();
                @endphp
                <tr>
                    <td><span class="nova-fw-semibold">{{ $order->id }}</span></td>
                    <td>
                        <span class="nova-fw-semibold">{{ $order->order_number }}</span>
                    </td>
                    <td>
                        <div>
                            <span class="nova-fw-semibold">{{ $order->customer->name ?? __('messages.orders.guest') }}</span>
                            @if($order->customer?->phone)
                                <br><small class="nova-text-muted"><i class="fas fa-phone" style="margin-inline-end: 0.25rem;"></i>{{ $order->customer->phone }}</small>
                            @endif
                            @if($order->customer?->email)
                                <br><small class="nova-text-muted"><i class="fas fa-envelope" style="margin-inline-end: 0.25rem;"></i>{{ $order->customer->email }}</small>
                            @endif
                        </div>
                    </td>
                    <td>
                        {{ $order->warehouse->name_en ?? $order->warehouse->name_ar ?? $order->warehouse->code ?? '-' }}
                    </td>
                    <td>{{ $order->created_at ? \Carbon\Carbon::parse($order->created_at)->translatedFormat('Y-m-d') : '-' }}</td>
                    <td class="nova-text-center">{{ $itemsCount }}</td>
                    <td class="nova-text-center">{{ number_format($totalQty, 3) }}</td>
                    <td>
                        {{ $order->currency->code ?? '-' }}
                    </td>
                    <td class="nova-text-end">
                        {{ number_format($order->total ?? 0, 2) }}
                        {{ $order->currency->symbol ?? $order->currency->code ?? '' }}
                    </td>
                    <td>
                        <span class="nova-badge {{ $order->status->badgeColor() }}">{{ $order->status->label() }}</span>
                    </td>
                    <td style="text-align: center;">
                        <div class="nova-table-actions">
                            <a href="{{ route('orders.show', $order->id) }}" class="nova-icon-btn nova-btn-sm" title="{{ __('messages.orders.view') }}">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('orders.edit', $order->id) }}" class="nova-icon-btn nova-btn-sm" title="{{ __('messages.orders.edit') }}">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="confirmDelete('{{ route('orders.destroy', $order->id) }}')" class="nova-icon-btn nova-btn-sm" title="{{ __('messages.orders.cancel') }}">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11">
                        <div class="nova-empty">
                            <div class="nova-empty-icon">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                            <h3 class="nova-empty-title">{{ __('messages.orders.no_orders') }}</h3>
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
                {{ __('messages.orders.showing_count', ['count' => $orders->count()]) }}
            </div>
            {{ $orders->links() }}
        </div>
    </div>
</div>

@endsection