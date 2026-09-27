@extends('dashboard.partials.layout')

@section('title', __('messages.orders.page_title'))

@push('styles')
<style>
    .nova-order-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .nova-order-hero-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .nova-order-hero-title i {
        color: var(--nova-primary);
    }
    .nova-summary-card {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .nova-summary-card .nova-stat-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.025em;
        color: var(--nova-text-3);
    }
    .nova-summary-card .nova-stat-value {
        font-size: 1.25rem;
        font-weight: 600;
    }
    .nova-order-item-name {
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 180px;
    }
    .nova-timeline-simple {
        position: relative;
        padding-inline-start: 2rem;
    }
    .nova-timeline-simple:not(:last-child)::before {
        content: '';
        position: absolute;
        inline-start: 0.75rem;
        top: 0.75rem;
        bottom: -1rem;
        width: 2px;
        background: var(--nova-border-color);
    }
    .nova-timeline-simple:last-child::before {
        content: none;
    }
    .nova-timeline-simple .nova-timeline-dot {
        position: absolute;
        inline-start: 0.25rem;
        top: 0.1rem;
        width: 1rem;
        height: 1rem;
        border-radius: 9999px;
        border: 2px solid var(--nova-primary);
        background: var(--nova-primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.5rem;
    }
    .nova-timeline-simple .nova-timeline-dot.completed {
        background: var(--nova-success);
        border-color: var(--nova-success);
    }
    .nova-timeline-simple .nova-timeline-dot.warning {
        background: var(--nova-warning);
        border-color: var(--nova-warning);
    }
    .nova-timeline-simple .nova-timeline-dot.danger {
        background: var(--nova-danger);
        border-color: var(--nova-danger);
    }
    .nova-timeline-simple .nova-timeline-label {
        font-size: 0.875rem;
        font-weight: 500;
    }
    .nova-timeline-simple .nova-text-muted {
        font-size: 0.75rem;
        color: var(--nova-text-3);
    }
</style>
@endpush

@php
    $statusEnum = $order->status instanceof \App\Enums\OrderStatus ? $order->status : \App\Enums\OrderStatus::tryFrom($order->status);
    $badgeClass = $statusEnum?->badgeColor() ?? 'secondary';
    $statusLabel = $statusEnum?->label() ?? $order->status;
@endphp

@section('content')

{{-- ==================== Hero ==================== --}}
<div class="nova-actionbar nova-mb-4">
    <div class="nova-order-hero">
        <div class="nova-order-hero-title">
            <h2 class="nova-actionbar-title m-0">
                <i class="fas fa-shopping-cart"></i>
                {{ __('messages.orders.order_details') }} #{{ $order->order_number ?? $order->id }}
            </h2>
            @if($statusEnum)
                <span class="nova-badge {{ $badgeClass }}">{{ $statusLabel }}</span>
            @endif
        </div>

        <div class="nova-flex nova-items-center nova-gap-2">
            <a href="{{ route('orders.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
                <i class="fas fa-arrow-right"></i> {{ __('messages.orders.back') }}
            </a>
            <a href="{{ route('orders.edit', $order->id) }}" class="nova-btn nova-btn-primary nova-btn-sm">
                <i class="fas fa-edit"></i> {{ __('messages.orders.edit') }}
            </a>
            <a href="javascript:void(0)"
               class="nova-btn nova-btn-danger nova-btn-sm"
               onclick="confirmDelete({{ $order->id }}, '{{ __('messages.orders.cancel') }}', '{{ __('messages.orders.cancel') }}')">
                <i class="fas fa-ban"></i> {{ __('messages.orders.cancel') }}
            </a>
        </div>
    </div>
</div>

{{-- ==================== Summary Row: 3 Cards ==================== --}}
<div class="nova-row cols-3" style="gap: 1rem; margin-bottom: 1.5rem;">
    {{-- العميل --}}
    <div class="nova-card nova-summary-card">
        <div class="nova-card-body">
            <p class="nova-stat-label">{{ __('messages.orders.customer') }}</p>
            <p class="nova-stat-value">{{ $order->customer->name ?? __('messages.orders.guest') }}</p>
            @if($order->customer?->email)
                <p class="nova-text-muted nova-fs-sm">{{ $order->customer->email }}</p>
            @endif
            @if($order->customer?->phone)
                <p class="nova-text-muted nova-fs-sm"><i class="fas fa-phone" style="margin-inline-end: 0.25rem;"></i>{{ $order->customer->phone }}</p>
            @endif
        </div>
    </div>

    {{-- التسليم --}}
    <div class="nova-card nova-summary-card">
        <div class="nova-card-body">
            <p class="nova-stat-label">{{ __('messages.orders.scheduled_delivery_date') }}</p>
            <p class="nova-stat-value">
                {{ $order->scheduled_delivery_date
                    ? \Carbon\Carbon::parse($order->scheduled_delivery_date)->format('Y-m-d')
                    : '-' }}
            </p>
            @if($order->delivery_address)
                <p class="nova-text-muted nova-fs-sm"><i class="fas fa-map-marker-alt" style="margin-inline-end: 0.25rem;"></i>{{ $order->delivery_address }}</p>
            @endif
            @if($order->delivery_phone)
                <p class="nova-text-muted nova-fs-sm"><i class="fas fa-phone" style="margin-inline-end: 0.25rem;"></i>{{ $order->delivery_phone }}</p>
            @endif
        </div>
    </div>

    {{-- الطلب --}}
    <div class="nova-card nova-summary-card">
        <div class="nova-card-body">
            <p class="nova-stat-label">{{ __('messages.orders.col_total') }}</p>
            <p class="nova-stat-value">
                {{ number_format($order->total ?? 0, 2) }}
                {{ $order->currency->symbol ?? $order->currency->code ?? '' }}
            </p>
            @if($order->created_by)
                <p class="nova-text-muted nova-fs-sm">{{ __('messages.orders.created_by') }}: {{ $order->createdBy?->name ?? '-' }}</p>
            @endif
        </div>
    </div>
</div>

{{-- ==================== Order Items Card ==================== --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h3 class="nova-card-title">
            <i class="fas fa-list" style="margin-inline-end: 0.5rem;"></i>
            {{ __('messages.orders.items') }}
        </h3>
    </div>
    <div class="nova-card-body p-0">
        <div class="nova-table-scroll">
            <table class="nova-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('messages.orders.col_product') }}</th>
                        <th>{{ __('messages.orders.col_pack') }}</th>
                        <th class="nova-text-center">{{ __('messages.orders.col_quantity') }}</th>
                        <th class="nova-text-end">{{ __('messages.orders.col_price') }}</th>
                        <th class="nova-text-end">{{ __('messages.orders.col_discount') }}</th>
                        <th class="nova-text-end">{{ __('messages.orders.col_total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($order->items as $index => $item)
                    @php
                        $total = $item->line_total ?? (($item->quantity * $item->unit_price) - (($item->quantity * $item->unit_price) * ($item->discount_percent / 100)));
                        $discount = $item->quantity * $item->unit_price * ($item->discount_percent / 100);
                    @endphp
                    <tr>
                        <td class="nova-fw-semibold">{{ $index + 1 }}</td>
                        <td>
                            <span class="nova-order-item-name d-block">
                                {{ $item->item->name_ar ?? $item->item->name_en ?? $item->item->code ?? '-' }}
                            </span>
                        </td>
                        <td>
                            <span class="nova-text-muted nova-fs-sm">
                                {{ $item->pack ? $item->pack->getDisplayNameAttribute() : '-' }}
                            </span>
                        </td>
                        <td class="nova-text-center">{{ $item->quantity }}</td>
                        <td class="nova-text-end">{{ number_format($item->unit_price ?? 0, 2) }}</td>
                        <td class="nova-text-end">{{ number_format($discount ?? 0, 2) }}</td>
                        <td class="nova-text-end nova-fw-semibold">{{ number_format($total ?? 0, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="nova-empty nova-py-3">
                                <div class="nova-empty-icon">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                                <h3 class="nova-empty-title">{{ __('messages.orders.no_items') }}</h3>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($order->items->isNotEmpty())
    <div class="nova-card-footer">
        <div class="nova-flex nova-justify-between nova-items-center">
            <div class="nova-text-muted nova-fs-sm">
                {{ __('messages.orders.showing_count', ['count' => $order->items->count()]) }}
            </div>
            <div class="nova-text-end">
                <span class="nova-fw-bold nova-text-primary">
                    {{ number_format($order->total ?? 0, 2) }}
                    {{ $order->currency->symbol ?? $order->currency->code ?? '' }}
                </span>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- ==================== Quotation Card ==================== --}}
@if($order->quotation)
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h3 class="nova-card-title">
            <i class="fas fa-file-contract" style="margin-inline-end: 0.5rem;"></i>
            {{ __('messages.orders.quotation') }}
        </h3>
    </div>
    <div class="nova-card-body">
        <div class="nova-row nova-gap-3">
            <div class="nova-col">
                <p><strong>{{ __('messages.orders.quotation_number') }}:</strong> {{ $order->quotation->quotation_number ?? '-' }}</p>
                <p><strong>{{ __('messages.orders.created_at') }}:</strong> {{ $order->quotation->created_at ? \Carbon\Carbon::parse($order->quotation->created_at)->format('Y-m-d') : '-' }}</p>
                <p><strong>{{ __('messages.orders.notes') }}:</strong> {{ $order->quotation->notes ?? '-' }}</p>
            </div>
            <div class="nova-col nova-text-end">
                <p><strong>{{ __('messages.orders.col_total') }}:</strong> {{ number_format($order->quotation->total_amount ?? 0, 2) }}</p>
                <p><strong>{{ __('messages.orders.status') }}:</strong> {{ $order->quotation->status }}</p>
            </div>
        </div>
    </div>
</div>
@else
<div class="nova-card nova-mb-4">
    <div class="nova-card-body nova-py-3 nova-text-center">
        <i class="fas fa-file-contract fa-2x nova-text-muted"></i>
        <p class="nova-text-muted">{{ __('messages.orders.no_quotation') }}</p>
    </div>
</div>
@endif

{{-- ==================== Invoice Card ==================== --}}
@if($order->invoice)
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h3 class="nova-card-title">
            <i class="fas fa-file-invoice" style="margin-inline-end: 0.5rem;"></i>
            {{ __('messages.orders.invoice') }}
        </h3>
    </div>
    <div class="nova-card-body">
        <div class="nova-row nova-gap-3">
            <div class="nova-col">
                <p><strong>{{ __('messages.orders.invoice_number') }}:</strong> {{ $order->invoice->invoice_number ?? '-' }}</p>
                <p><strong>{{ __('messages.orders.invoice_date') }}:</strong> {{ $order->invoice->invoice_date ? \Carbon\Carbon::parse($order->invoice->invoice_date)->format('Y-m-d') : '-' }}</p>
                <p><strong>{{ __('messages.orders.col_status') }}:</strong>
                    <span class="nova-badge {{ $order->invoice->status == 'paid' ? 'success' : ($order->invoice->status == 'cancelled' ? 'danger' : 'warning') }}">
                        {{ $order->invoice->status }}
                    </span>
                </p>
            </div>
            <div class="nova-col nova-text-end">
                <p><strong>{{ __('messages.orders.col_total') }}:</strong> {{ number_format($order->invoice->total_amount ?? 0, 2) }}</p>
                <p><strong>{{ __('messages.orders.payment_deadline') }}:</strong>
                    @php
                        $deadline = $order->invoice->payment_deadline_at;
                        $daysLeft = $deadline ? \Carbon\Carbon::parse($deadline)->diffInDays(now(), false) : null;
                    @endphp
                    @if($daysLeft === null)
                        <span class="nova-badge secondary">{{ __('messages.orders.no_deadline') }}</span>
                    @elseif($daysLeft < 0)
                        <span class="nova-badge danger">{{ __('messages.orders.overdue') }}</span>
                    @elseif($daysLeft <= 1)
                        <span class="nova-badge danger">{{ $daysLeft }} {{ __('messages.orders.day') }}</span>
                    @elseif($daysLeft <= 3)
                        <span class="nova-badge warning">{{ $daysLeft }} {{ __('messages.orders.days_left') }}</span>
                    @else
                        <span class="nova-badge accent">{{ $daysLeft }} {{ __('messages.orders.days_left') }}</span>
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>
@else
<div class="nova-card nova-mb-4">
    <div class="nova-card-body nova-py-3 nova-text-center">
        <i class="fas fa-file-invoice fa-2x nova-text-muted"></i>
        <p class="nova-text-muted">{{ __('messages.orders.no_invoice') }}</p>
    </div>
</div>
@endif

{{-- ==================== Delivery Timeline ==================== --}}
@if($order->delivery)
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h3 class="nova-card-title">
            <i class="fas fa-truck-loading" style="margin-inline-end: 0.5rem;"></i>
            {{ __('messages.orders.delivery') }}
        </h3>
    </div>
    <div class="nova-card-body">
        @php
            $deliveryStages = [
                ['key' => 'shipping', 'label' => __('messages.orders.stage_shipping')],
                ['key' => 'out_for_delivery', 'label' => __('messages.orders.stage_out_for_delivery')],
                ['key' => 'unloading', 'label' => __('messages.orders.stage_unloading')],
                ['key' => 'received', 'label' => __('messages.orders.stage_received')],
            ];
            $currentStage = $order->delivery->current_stage;
        @endphp

        <div class="nova-timeline-simple">
            @foreach($deliveryStages as $stage)
                @php
                    $isCompleted = $currentStage && array_search($stage['key'], array_column($deliveryStages, 'key')) < array_search($currentStage, array_column($deliveryStages, 'key'))
                        || ($currentStage && $stage['key'] === 'received' && $currentStage === 'received')
                        || ($currentStage && $stage['key'] === 'unloading' && in_array($currentStage, ['unloading', 'received']));
                    $isCurrent = $stage['key'] === $currentStage;
                @endphp
                <div class="nova-timeline-item {{ $isCompleted ? 'completed' : ($isCurrent ? 'warning' : '') }}">
                    <div class="nova-timeline-dot {{ $isCompleted ? 'completed' : ($isCurrent ? 'warning' : 'secondary') }}"></div>
                    <div>
                        <span class="nova-timeline-label">{{ $stage['label'] }}</span>
                        @if($order->delivery->started_at && $stage['key'] === $currentStage)
                            <p class="nova-text-muted nova-fs-sm">
                                {{ __('messages.orders.started_at') }}: {{ \Carbon\Carbon::parse($order->delivery->started_at)->format('Y-m-d H:i') }}
                            </p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if($order->delivery->vehicle)
            <p class="nova-text-muted nova-fs-sm mt-2">
                <i class="fas fa-truck" style="margin-inline-end: 0.5rem;"></i>
                {{ __('messages.orders.vehicle') }}: {{ $order->delivery->vehicle->name ?? '-' }}
            </p>
        @endif
    </div>
</div>
@else
<div class="nova-card nova-mb-4">
    <div class="nova-card-body nova-py-3 nova-text-center">
        <i class="fas fa-truck-loading fa-2x nova-text-muted"></i>
        <p class="nova-text-muted">{{ __('messages.orders.no_delivery') }}</p>
    </div>
</div>
@endif

{{-- ==================== Status History Timeline ==================== --}}
@if($order->statusHistory->isNotEmpty())
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h3 class="nova-card-title">
            <i class="fas fa-history" style="margin-inline-end: 0.5rem;"></i>
            {{ __('messages.orders.status_history') }}
        </h3>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table nova-table-borderless nova-fs-sm">
            <thead>
                <tr>
                    <th>{{ __('messages.orders.col_date') }}</th>
                    <th>{{ __('messages.orders.col_status') }}</th>
                    <th>{{ __('messages.orders.changed_by') }}</th>
                    <th>{{ __('messages.orders.notes') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->statusHistory as $history)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($history->created_at)->format('Y-m-d H:i') }}</td>
                    <td>
                        <span class="nova-badge {{ $history->toStatusBadgeColor }}">
                            {{ $history->toLabel }}
                        </span>
                    </td>
                    <td>{{ $history->changedBy?->name ?? $history->changed_by ?? '-' }}</td>
                    <td>{{ $history->notes ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@else
<div class="nova-card nova-mb-4">
    <div class="nova-card-body nova-py-3 nova-text-center">
        <i class="fas fa-history fa-2x nova-text-muted"></i>
        <p class="nova-text-muted">{{ __('messages.orders.no_status_history') }}</p>
    </div>
</div>
@endif

{{-- ==================== Notes ==================== --}}
@if($order->notes)
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h3 class="nova-card-title">
            <i class="fas fa-sticky-note" style="margin-inline-end: 0.5rem;"></i>
            {{ __('messages.orders.notes') }}
        </h3>
    </div>
    <div class="nova-card-body">
        <p class="nova-text-muted">{{ $order->notes }}</p>
    </div>
</div>
@endif

@endsection
