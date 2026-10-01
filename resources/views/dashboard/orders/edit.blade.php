@extends('dashboard.partials.layout')

@section('title', __('messages.orders.edit_order'))

@php
$statusEnum = $order && $order->status
    ? ($order->status instanceof \App\Enums\OrderStatus ? $order->status : \App\Enums\OrderStatus::tryFrom($order->status))
    : null;
$isTerminalStatus = $statusEnum && in_array($statusEnum->value, ['delivered', 'cancelled', 'returned', 'expired']);
$customerObj = $order->customer;
$priceListId = $customerObj?->priceList?->id;
$priceListName = $customerObj?->priceList?->name ?? '—';
$walletBalance = $customerObj ? $customerObj->wallets->where('is_active', 1)->sum('balance') : 0;
@endphp

@push('styles')
<style>
    /* ==================== Hero & Info ==================== */
    .nova-order-hero { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; }
    .nova-order-hero-title { display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; }
    .nova-order-hero-title i { color: var(--nova-primary); }
    .nova-order-info-card { background: var(--nova-surface-1); border-radius: var(--nova-radius-lg); padding: 1.5rem; }
    .nova-order-info-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 1rem; }
    .nova-order-info-item { display: flex; flex-direction: column; gap: 0.25rem; }
    .nova-order-info-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.025em; color: var(--nova-text-3); }
    .nova-order-info-value { font-size: 0.875rem; font-weight: 500; color: var(--nova-text-1); }

    /* ==================== Items Table ==================== */
    .nova-items-table-container { border: 1px solid var(--nova-border-color); border-radius: var(--nova-radius-lg); overflow: hidden; }
    .nova-items-table { width: 100%; border-collapse: collapse; }
    .nova-items-table th { background: var(--nova-surface-2); padding: 0.75rem; text-align: left; font-weight: 600; font-size: 0.875rem; color: var(--nova-text-1); border-bottom: 1px solid var(--nova-border-color); }
    .nova-items-table td { padding: 1rem; border-bottom: 1px solid var(--nova-border-color); vertical-align: middle; }
    .nova-item-select { width: 100%; padding: 0.5rem; border: 1px solid var(--nova-border-color); border-radius: var(--nova-radius-md); background: var(--nova-surface-1); }
    .nova-qty-input { width: 80px; padding: 0.5rem; border: 1px solid var(--nova-border-color); border-radius: var(--nova-radius-md); text-align: center; }
    .nova-price-input { width: 100px; padding: 0.5rem; border: 1px solid var(--nova-border-color); border-radius: var(--nova-radius-md); text-align: right; }
    .nova-discount-input { width: 100px; padding: 0.5rem; border: 1px solid var(--nova-border-color); border-radius: var(--nova-radius-md); text-align: right; }
    .nova-total-readonly { font-weight: 600; color: var(--nova-primary); text-align: right; padding-right: 1rem; }
    .nova-remove-btn { background: var(--nova-danger); color: #dc2626; border: none; border-radius: var(--nova-radius-md); padding: 0.5rem; cursor: pointer; transition: background 0.2s; }
    .nova-remove-btn:hover { background: var(--nova-danger-dark, #ffffff); }
    .nova-add-item-btn { background: var(--nova-primary); color: white; border: none; border-radius: var(--nova-radius-md); padding: 0.75rem 1.5rem; cursor: pointer; font-weight: 500; transition: background 0.2s; }
    .nova-add-item-btn:hover { background: var(--nova-primary-dark, #2563eb); }

    /* ==================== Summary ==================== */
    .nova-summary-sticky { position: sticky; bottom: 0; background: var(--nova-surface-1); border-top: 2px solid var(--nova-border-color); padding: 1rem; margin: 1rem -1.5rem -1.5rem; border-radius: 0 0 var(--nova-radius-lg) var(--nova-radius-lg); }
    .nova-summary-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
    .nova-summary-item { display: flex; flex-direction: column; gap: 0.25rem; }
    .nova-summary-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.025em; color: var(--nova-text-3); }
    .nova-summary-value { font-size: 1.25rem; font-weight: 600; color: var(--nova-text-1); }
    .nova-currency-symbol { font-weight: 600; color: var(--nova-primary); }

    /* ==================== Customer Info Display ==================== */
    .customer-info-display { background: var(--nova-surface-2); border-radius: var(--nova-radius-md); padding: 1rem; margin-top: 0.75rem; }
    .customer-info-row { display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--nova-border-color); }
    .customer-info-row:last-child { border-bottom: none; }
    .customer-info-label { color: var(--nova-text-3); font-size: 0.875rem; }
    .customer-info-value { font-weight: 500; color: var(--nova-text-1); }

    /* ==================== Status badge & warning ==================== */
    .nova-status-warning { background: #fef3c7; border: 1px solid #f59e0b; color: #92400e; padding: 0.75rem 1rem; border-radius: var(--nova-radius-md); margin-top: 1rem; display: flex; align-items: center; gap: 0.5rem; }
    .terminal-lock { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.25rem 0.6rem; background: rgba(100,116,139,0.1); color: var(--nova-text-3); border-radius: var(--nova-radius-sm); font-size: 0.75rem; font-weight: 500; }

    /* ==================== Toast Notifications ==================== */
    #novaToastContainer { position: fixed; top: 1rem; right: 1rem; z-index: 9999; display: flex; flex-direction: column; gap: 0.75rem; max-width: 420px; width: calc(100vw - 2rem); pointer-events: none; }
    .nova-toast { pointer-events: auto; position: relative; display: flex; align-items: flex-start; gap: 0.75rem; padding: 1rem 1.25rem 1.15rem; border-radius: var(--nova-radius-lg); background: var(--nova-surface-1); border: 1px solid var(--nova-border-color); border-inline-start: 4px solid var(--nova-primary); box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12); font-size: 0.875rem; color: var(--nova-text-1); overflow: hidden; animation: novaToastIn 0.35s cubic-bezier(0.16, 1, 0.3, 1); }
    .nova-toast.hiding { animation: novaToastOut 0.28s ease forwards; }
    .nova-toast-icon { font-size: 1.15rem; line-height: 1; flex-shrink: 0; margin-top: 0.125rem; }
    .nova-toast-body { flex: 1; min-width: 0; }
    .nova-toast-title { font-weight: 600; margin-bottom: 0.2rem; font-size: 0.9rem; }
    .nova-toast-message { color: var(--nova-text-2); line-height: 1.5; word-wrap: break-word; }
    .nova-toast-close { background: transparent; border: none; color: var(--nova-text-3); cursor: pointer; padding: 0.15rem 0.25rem; line-height: 1; border-radius: var(--nova-radius-sm); transition: all 0.15s; }
    .nova-toast-close:hover { color: var(--nova-text-1); background: var(--nova-surface-2); }
    .nova-toast-progress { position: absolute; bottom: 0; inset-inline-start: 0; height: 3px; background: currentColor; opacity: 0.35; animation: novaToastProgress linear forwards; }
    .nova-toast.warning { border-inline-start-color: #f59e0b; }
    .nova-toast.warning .nova-toast-icon { color: #f59e0b; }
    .nova-toast.warning .nova-toast-progress { color: #f59e0b; }
    .nova-toast.error { border-inline-start-color: #dc2626; }
    .nova-toast.error .nova-toast-icon { color: #dc2626; }
    .nova-toast.error .nova-toast-progress { color: #dc2626; }
    .nova-toast.success { border-inline-start-color: #16a34a; }
    .nova-toast.success .nova-toast-icon { color: #16a34a; }
    .nova-toast.success .nova-toast-progress { color: #16a34a; }
    .nova-toast.info .nova-toast-icon { color: var(--nova-primary); }
    .nova-toast.info .nova-toast-progress { color: var(--nova-primary); }
    @keyframes novaToastIn { from { transform: translateX(110%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    @keyframes novaToastOut { to { transform: translateX(110%); opacity: 0; } }
    @keyframes novaToastProgress { from { width: 100%; } to { width: 0%; } }

    /* ==================== Warehouse Products ==================== */
    .warehouse-products-card { background: var(--nova-surface-1); border: 1px solid var(--nova-border-color); border-radius: var(--nova-radius-lg); margin-top: 1rem; display: none; overflow: hidden; }
    .warehouse-products-card.visible { display: block; }

    .warehouse-toolbar { display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center; padding: 1rem 1.25rem; background: var(--nova-surface-2); border-bottom: 1px solid var(--nova-border-color); }
    .warehouse-search { position: relative; flex: 1 1 240px; min-width: 200px; }
    .warehouse-search i { position: absolute; inset-inline-start: 0.85rem; top: 50%; transform: translateY(-50%); color: var(--nova-text-3); pointer-events: none; font-size: 0.85rem; }
    .warehouse-search input { width: 100%; padding-block: 0.55rem; padding-inline: 2.25rem 0.85rem; border: 1px solid var(--nova-border-color); border-radius: var(--nova-radius-md); background: var(--nova-surface-1); font-size: 0.875rem; transition: border-color 0.15s, box-shadow 0.15s; }
    .warehouse-search input:focus { outline: none; border-color: var(--nova-primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12); }
    .warehouse-filter-select { padding: 0.55rem 0.75rem; border: 1px solid var(--nova-border-color); border-radius: var(--nova-radius-md); background: var(--nova-surface-1); font-size: 0.85rem; min-width: 150px; cursor: pointer; }
    .warehouse-count-badge { display: inline-flex; align-items: center; justify-content: center; min-width: 26px; height: 22px; padding: 0 0.5rem; background: var(--nova-primary); color: #fff; border-radius: 999px; font-size: 0.75rem; font-weight: 600; margin-inline-start: 0.5rem; vertical-align: middle; }

    .warehouse-products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem; max-height: 620px; overflow-y: auto; padding: 1rem 1.25rem 1.25rem; background: var(--nova-surface-2); }
    .warehouse-product-item { position: relative; background: var(--nova-surface-1); border: 1px solid var(--nova-border-color); border-radius: var(--nova-radius-lg); cursor: pointer; transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease; overflow: hidden; display: flex; flex-direction: column; }
    .warehouse-product-item:hover { border-color: var(--nova-primary); box-shadow: 0 8px 24px rgba(37, 99, 235, 0.12); transform: translateY(-3px); }
    .warehouse-product-item.selected { border-color: var(--nova-primary); background: rgba(37, 99, 235, 0.04); box-shadow: 0 0 0 2px var(--nova-primary); }
    .warehouse-product-item.selected::after { content: "\f00c"; font-family: "Font Awesome 6 Free"; font-weight: 900; position: absolute; top: 0.5rem; inset-inline-end: 0.5rem; width: 24px; height: 24px; background: var(--nova-primary); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; z-index: 2; }
    .warehouse-product-item.out { opacity: 0.65; cursor: not-allowed; }
    .warehouse-product-item.out:hover { transform: none; box-shadow: none; border-color: var(--nova-border-color); }

    .nova-product-image { position: relative; height: 130px; background: var(--nova-surface-2); background-size: cover; background-position: center; display: flex; align-items: center; justify-content: center; font-size: 3rem; }
    .stock-badge { position: absolute; top: 0.5rem; inset-inline-start: 0.5rem; padding: 0.2rem 0.55rem; border-radius: 999px; font-size: 0.7rem; font-weight: 600; color: #fff; z-index: 2; }
    .stock-badge.available { background: #16a34a; }
    .stock-badge.low { background: #f59e0b; }
    .stock-badge.out { background: #dc2626; }

    .nova-product-body { padding: 0.85rem; display: flex; flex-direction: column; gap: 0.5rem; flex: 1; }
    .nova-product-title { font-size: 0.95rem; font-weight: 600; color: var(--nova-text-1); margin: 0; line-height: 1.35; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
    .nova-product-subtitle { font-size: 0.78rem; color: var(--nova-text-3); margin: 0; }
    .nova-product-meta { display: flex; flex-wrap: wrap; gap: 0.4rem; align-items: center; }
    .nova-badge.info { background: rgba(37, 99, 235, 0.1); color: var(--nova-primary); padding: 0.15rem 0.5rem; border-radius: var(--nova-radius-sm); font-size: 0.7rem; font-weight: 500; }
    .product-stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.35rem; padding: 0.55rem; background: var(--nova-surface-2); border-radius: var(--nova-radius-md); margin-top: auto; }
    .nova-product-stat { text-align: center; }
    .nova-product-stat-label { font-size: 0.62rem; text-transform: uppercase; color: var(--nova-text-3); margin-bottom: 0.1rem; }
    .nova-product-stat-value { font-size: 0.85rem; font-weight: 700; color: var(--nova-text-1); }
    .nova-product-stat-value.primary { color: var(--nova-primary); }
    .nova-product-stat-value.success { color: #16a34a; }

    .batch-toggle { width: 100%; padding: 0.45rem 0.75rem; background: transparent; border: 1px dashed var(--nova-border-color); border-radius: var(--nova-radius-md); color: var(--nova-text-2); font-size: 0.78rem; cursor: pointer; transition: all 0.15s; display: flex; align-items: center; justify-content: center; gap: 0.4rem; }
    .batch-toggle:hover { border-color: var(--nova-primary); color: var(--nova-primary); background: rgba(37, 99, 235, 0.04); }
    .batch-toggle i.chevron { transition: transform 0.2s; }
    .batch-toggle.open i.chevron { transform: rotate(180deg); }
    .batch-list { display: none; list-style: none; padding: 0.5rem 0.75rem; margin: 0; background: var(--nova-surface-2); border-radius: var(--nova-radius-md); font-size: 0.78rem; max-height: 140px; overflow-y: auto; }
    .batch-list.open { display: block; }
    .batch-list li { padding: 0.3rem 0; border-bottom: 1px solid var(--nova-border-color); display: flex; justify-content: space-between; gap: 0.5rem; }
    .batch-list li:last-child { border-bottom: none; }

    .product-actions { display: flex; flex-direction: column; gap: 0.4rem; }
    .add-to-order-btn { width: 100%; padding: 0.55rem; background: var(--nova-primary); color: #fff; border: none; border-radius: var(--nova-radius-md); font-size: 0.82rem; font-weight: 600; cursor: pointer; transition: background 0.15s; display: flex; align-items: center; justify-content: center; gap: 0.4rem; }
    .add-to-order-btn:hover:not(:disabled) { background: var(--nova-primary-dark, #1d4ed8); }
    .add-to-order-btn:disabled { opacity: 0.55; cursor: not-allowed; }
    .remove-from-order-btn { width: 100%; padding: 0.55rem; background: var(--nova-danger); color: #fff; border: none; border-radius: var(--nova-radius-md); font-size: 0.82rem; font-weight: 600; cursor: pointer; transition: background 0.15s; display: none; align-items: center; justify-content: center; gap: 0.4rem; }
    .remove-from-order-btn:hover { background: var(--nova-danger-dark, #dc2626); }
    .warehouse-product-item.selected .add-to-order-btn { display: none; }
    .warehouse-product-item.selected .remove-from-order-btn { display: inline-flex; }

    .warehouse-empty { grid-column: 1 / -1; padding: 3rem 1rem; text-align: center; color: var(--nova-text-3); }
    .warehouse-empty i { font-size: 3rem; opacity: 0.35; margin-bottom: 0.75rem; display: block; }
    .warehouse-empty p { margin: 0 0 0.25rem; font-weight: 600; color: var(--nova-text-2); }
    .warehouse-empty span { font-size: 0.82rem; }

    .warehouse-skeleton { background: var(--nova-surface-1); border: 1px solid var(--nova-border-color); border-radius: var(--nova-radius-lg); padding: 1rem; height: 320px; position: relative; overflow: hidden; }
    .warehouse-skeleton::after { content: ""; position: absolute; inset: 0; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.5), transparent); animation: shimmer 1.4s infinite; }
    @keyframes shimmer { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }
</style>
@endpush

@section('content')

{{-- ==================== Hero Header ==================== --}}
<div class="nova-actionbar nova-mb-4">
    <div class="nova-order-hero">
        <div class="nova-order-hero-title">
            <h2 class="nova-actionbar-title m-0">
                <i class="fas fa-edit"></i>
                {{ __('messages.orders.edit_order') }} #{{ $order->order_number ?? $order->id }}
            </h2>
            @if($statusEnum)
                <span class="nova-badge {{ $statusEnum->badgeColor() }}">{{ $statusEnum->label() }}</span>
            @endif
            @if($isTerminalStatus)
                <span class="terminal-lock">
                    <i class="fas fa-lock"></i> {{ __('messages.orders.locked') ?? 'مقفل' }}
                </span>
            @endif
        </div>

        <div class="nova-flex nova-items-center nova-gap-2">
            <a href="{{ route('orders.show', $order->id) }}" class="nova-btn nova-btn-outline nova-btn-sm">
                <i class="fas fa-eye"></i> {{ __('messages.orders.view') ?? 'عرض' }}
            </a>
            <a href="{{ route('orders.pdf', $order->id) }}" target="_blank" class="nova-btn nova-btn-outline nova-btn-sm">
                <i class="fas fa-print"></i> {{ __('messages.orders.print') ?? 'طباعة' }}
            </a>
            <a href="{{ route('orders.quotation.pdf', $order->id) }}" target="_blank" class="nova-btn nova-btn-secondary nova-btn-sm">
                <i class="fas fa-file-contract"></i> {{ __('messages.orders.print_quotation') ?? 'طباعة العرض' }}
            </a>
            <a href="{{ route('orders.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
                <i class="fas fa-arrow-right"></i> {{ __('messages.orders.back') }}
            </a>
        </div>
    </div>

@if($isTerminalStatus)
    <div class="nova-status-warning">
        <i class="fas fa-exclamation-triangle"></i>
        <span>{{ __('messages.orders.cannot_edit_terminal_status') }}</span>
    </div>
    @endif
</div>

@include('dashboard.orders.partials.status-transition', ['order' => $order])

{{-- ==================== Error / Success alerts ==================== --}}
@if(session('error'))
    <div class="nova-alert nova-alert-error nova-mb-4">
        <i class="fas fa-circle-xmark"></i> {{ session('error') }}
    </div>
@endif
@if($errors->any())
    <div class="nova-alert nova-alert-error nova-mb-4">
        <i class="fas fa-circle-xmark"></i>
        <ul class="nova-mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- ==================== Form ==================== --}}
<form id="orderEditForm" action="{{ route('orders.update', $order->id) }}" method="POST">
    @csrf
    @method('PUT')

    {{-- Hidden customer_id (customer is not editable on existing order) --}}
    <input type="hidden" name="customer_id" value="{{ $order->customer_id }}">

    {{-- ==================== Customer & Order Info Card ==================== --}}
    <div class="nova-card nova-mb-4">
        <div class="nova-card-header">
            <h3 class="nova-card-title">
                <i class="fas fa-info-circle" style="margin-inline-end: 0.5rem;"></i>
                {{ __('messages.orders.customer_order_info') }}
            </h3>
        </div>
        <div class="nova-card-body">
            <div class="nova-order-info-card">
                <div class="nova-order-info-row">
                    <div class="nova-order-info-item">
                        <label class="nova-order-info-label">{{ __('messages.orders.customer') }}</label>
                        <div class="nova-order-info-value">
                            {{ $order->customer ? $order->customer->name : __('messages.orders.guest') }}
                        </div>
                    </div>
                    <div class="nova-order-info-item">
                        <label class="nova-order-info-label">{{ __('messages.orders.order_date') }}</label>
                        <div class="nova-order-info-value">
                            {{ $order->created_at ? $order?->created_at?->format('Y-m-d H:i') ?? '—' : '-' }}
                        </div>
                    </div>
                    <div class="nova-order-info-item">
                        <label class="nova-order-info-label">{{ __('messages.orders.warehouse') }}</label>
                        <select name="warehouse_id" id="warehouseSelect" class="nova-input" {{ $isTerminalStatus ? 'disabled' : '' }} required>
                            <option value="">{{ __('messages.orders.select_warehouse') }}</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ $order->warehouse_id == $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->name_en ?? $warehouse->name_ar ?? $warehouse->code }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="nova-order-info-item">
                        <label class="nova-order-info-label">{{ __('messages.orders.currency') }}</label>
                        <select name="currency_id" id="currencySelect" class="nova-input" {{ $isTerminalStatus ? 'disabled' : '' }} required>
                            <option value="">{{ __('messages.orders.select_currency') }}</option>
                            @foreach($currencies as $currency)
                                <option value="{{ $currency->id }}" {{ $order->currency_id == $currency->id ? 'selected' : '' }}>
                                    {{ $currency->code }} - {{ $currency->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Customer info display --}}
                <div class="customer-info-display">
                    <div class="customer-info-row">
                        <span class="customer-info-label">{{ __('messages.orders.price_list') }}</span>
                        <span class="customer-info-value">{{ $priceListName }}</span>
                    </div>
                    <div class="customer-info-row">
                        <span class="customer-info-label">{{ __('messages.orders.wallet_balance') }}</span>
                        <span class="customer-info-value">
                            {{ number_format($walletBalance, 2) }}
                            {{ $order->currency ? ($order->currency->symbol ?? $order->currency->code ?? '') : '' }}
                        </span>
                    </div>
                </div>

                <div class="nova-order-info-row" style="margin-top: 1rem;">
                    <div class="nova-order-info-item" style="grid-column: 1 / -1;">
                        <label class="nova-order-info-label">{{ __('messages.orders.delivery_address') }}</label>
                        <textarea name="delivery_address" id="deliveryAddress" class="nova-input" rows="2"
                                  placeholder="{{ __('messages.orders.address_placeholder') }}"
                                  {{ $isTerminalStatus ? 'disabled' : '' }}>{{ $order->delivery_address ?? '' }}</textarea>
                    </div>
                    <div class="nova-order-info-item">
                        <label class="nova-order-info-label">{{ __('messages.orders.delivery_phone') }}</label>
                        <input type="text" name="delivery_phone" id="deliveryPhone" class="nova-input"
                               value="{{ $order->delivery_phone ?? '' }}"
                               placeholder="{{ __('messages.orders.phone_placeholder') }}"
                               {{ $isTerminalStatus ? 'disabled' : '' }}>
                    </div>
                </div>

                <div class="nova-order-info-row">
                    <div class="nova-order-info-item" style="grid-column: 1 / -1;">
                        <label class="nova-order-info-label">{{ __('messages.orders.notes') }}</label>
                        <textarea name="notes" class="nova-input" rows="3" {{ $isTerminalStatus ? 'disabled' : '' }}>{{ $order->notes }}</textarea>
                    </div>
                    <div class="nova-order-info-item">
                        <label class="nova-order-info-label">{{ __('messages.orders.scheduled_delivery_date') }}</label>
                        <input type="date" name="scheduled_delivery_date" class="nova-input"
                               value="{{ $order->scheduled_delivery_date ? \Carbon\Carbon::parse($order->scheduled_delivery_date)->format('Y-m-d') : '' }}"
                               {{ $isTerminalStatus ? 'disabled' : '' }}>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== Warehouse Products Card ==================== --}}
    @if(!$isTerminalStatus)
    <div id="warehouseProductsCard" class="nova-card nova-mb-4 warehouse-products-card">
        <div class="nova-card-header">
            <h3 class="nova-card-title">
                <i class="fas fa-boxes" style="margin-inline-end: 0.5rem;"></i>
                {{ __('messages.orders.warehouse_products') }}
                <span class="warehouse-count-badge" id="warehouseCountBadge">0</span>
            </h3>
        </div>

        <div class="warehouse-toolbar">
            <div class="warehouse-search">
                <i class="fas fa-search"></i>
                <input type="search" id="warehouseSearchInput" placeholder="ابحث بالاسم أو الباركود...">
            </div>
            <select id="warehouseAvailabilityFilter" class="warehouse-filter-select">
                <option value="all">كل الحالات</option>
                <option value="available">متوفر</option>
                <option value="low">مخزون منخفض</option>
                <option value="out">نفذ المخزون</option>
            </select>
            <select id="warehouseSortSelect" class="warehouse-filter-select">
                <option value="name">الاسم (أ - ي)</option>
                <option value="stock_desc">الأكثر مخزوناً</option>
                <option value="stock_asc">الأقل مخزوناً</option>
                <option value="batches_desc">الأكثر دفعات</option>
            </select>
        </div>

        <div class="nova-card-body p-0">
            <div id="warehouseProductsGrid" class="warehouse-products-grid"></div>
        </div>
    </div>
    @endif

    {{-- ==================== Items Card ==================== --}}
    <div class="nova-card nova-mb-4">
        <div class="nova-card-header">
            <h3 class="nova-card-title">
                <i class="fas fa-list" style="margin-inline-end: 0.5rem;"></i>
                {{ __('messages.orders.items') }}
            </h3>
        </div>
        <div class="nova-card-body p-0">
            <div class="nova-items-table-container">
                <table class="nova-items-table" id="itemsTable">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>{{ __('messages.orders.col_product') }}</th>
                            <th>{{ __('messages.orders.col_pack') }}</th>
                            <th style="width: 100px;">{{ __('messages.orders.col_quantity') }}</th>
                            <th style="width: 120px;">{{ __('messages.orders.col_price') }}</th>
                            <th style="width: 100px;">{{ __('messages.orders.col_discount') }}</th>
                            <th style="width: 120px;">{{ __('messages.orders.col_total') }}</th>
                            <th style="width: 80px;">{{ __('messages.orders.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="itemsTbody">
                        @forelse($order->items as $index => $item)
                        <tr class="item-row" data-id="{{ $item->id }}">
                            <td class="nova-fw-semibold">{{ $index + 1 }}</td>
                            <td>
                                <select name="items[{{ $index }}][item_id]" class="nova-item-select item-select" {{ $isTerminalStatus ? 'disabled' : '' }} required>
                                    <option value="">{{ __('messages.orders.select_product') }}</option>
                                    @foreach($itemsData as $itemOption)
                                        <option value="{{ $itemOption['id'] }}" {{ $item->item_id == $itemOption['id'] ? 'selected' : '' }}>
                                            {{ $itemOption['name_ar'] ?? $itemOption['name_en'] ?? $itemOption['code'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select name="items[{{ $index }}][item_pack_id]" class="nova-item-select pack-select" {{ $isTerminalStatus ? 'disabled' : '' }} required>
                                    <option value="">{{ __('messages.orders.select_pack') }}</option>
                                    @if($item->item)
                                        @foreach($item->item->packs as $pack)
                                            <option value="{{ $pack->id }}" {{ $item->item_pack_id == $pack->id ? 'selected' : '' }}>
                                                {{ $pack->getDisplayNameAttribute() ?? $pack->packDefinition->name ?? 'Pack #' . $pack->id }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </td>
                            <td>
                                <input type="number" name="items[{{ $index }}][quantity]" class="nova-qty-input quantity-input"
                                       value="{{ $item->quantity }}" step="any" min="0.001"
                                       {{ $isTerminalStatus ? 'disabled' : '' }} required>
                            </td>
                            <td>
                                <input type="number" name="items[{{ $index }}][unit_price]" class="nova-price-input price-input"
                                       value="{{ number_format($item->unit_price, 2, '.', '') }}" step="0.01"
                                       {{ $isTerminalStatus ? 'disabled' : '' }} readonly>
                            </td>
                            <td>
                                <input type="number" name="items[{{ $index }}][discount_percent]" class="nova-discount-input discount-input"
                                       value="{{ $item->discount_percent ?? 0 }}" step="0.01" min="0" max="100"
                                       {{ $isTerminalStatus ? 'disabled' : '' }}>
                            </td>
                            <td class="nova-total-readonly">
                                {{ number_format($item->line_total ?? (($item->quantity * $item->unit_price) - (($item->quantity * $item->unit_price) * ($item->discount_percent / 100))), 2, '.', '') }}
                            </td>
                            <td>
                                @if(!$isTerminalStatus)
                                <button type="button" class="nova-remove-btn" onclick="removeItemRow(this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr id="emptyRow">
                            <td colspan="8">
                                <div class="nova-empty nova-py-3" style="text-align: center; padding: 2rem;">
                                    <div class="nova-empty-icon" style="font-size: 2.5rem; color: var(--nova-text-3); margin-bottom: 0.5rem;">
                                        <i class="fas fa-clipboard-list"></i>
                                    </div>
                                    <h3 class="nova-empty-title" style="font-weight: 600;">{{ __('messages.orders.no_items') }}</h3>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(!$isTerminalStatus)
            <div class="nova-card-footer" style="border-top: 1px solid var(--nova-border-color); padding: 1rem; margin-top: 1rem;">
                <button type="button" class="nova-add-item-btn" onclick="addNewItemRow()">
                    <i class="fas fa-plus" style="margin-inline-end: 0.5rem;"></i>
                    {{ __('messages.orders.add_item') }}
                </button>
            </div>
            @endif
        </div>
    </div>

    {{-- ==================== Summary Card (Sticky Bottom) ==================== --}}
    <div class="nova-summary-sticky">
        <div class="nova-summary-row">
            <div class="nova-summary-item">
                <span class="nova-summary-label">{{ __('messages.orders.total_items') }}</span>
                <span class="nova-summary-value" id="summaryItemsCount">{{ $order->items ? $order->items->count() : 0 }}</span>
            </div>
            <div class="nova-summary-item">
                <span class="nova-summary-label">{{ __('messages.orders.total_quantity') }}</span>
                <span class="nova-summary-value" id="summaryTotalQty">0</span>
            </div>
            <div class="nova-summary-item">
                <span class="nova-summary-label">{{ __('messages.orders.grand_total') }}</span>
                <span class="nova-summary-value">
                    <span id="summaryGrandTotal">0</span>
                    <span class="nova-currency-symbol">
                        {{ $order->currency ? ($order->currency->symbol ?? $order->currency->code ?? '') : '' }}
                    </span>
                </span>
            </div>
        </div>

        <div class="nova-flex nova-gap-2 nova-justify-center">
            @if(!$isTerminalStatus)
                <button type="button" class="nova-btn nova-btn-secondary" onclick="sendNewPriceQuote()">
                    <i class="fas fa-paper-plane" style="margin-inline-end: 0.5rem;"></i>
                    {{ __('messages.orders.send_new_price_quote') }}
                </button>
                <button type="submit" class="nova-btn nova-btn-primary">
                    <i class="fas fa-save" style="margin-inline-end: 0.5rem;"></i>
                    {{ __('messages.orders.save_changes') }}
                </button>
                <button type="button" class="nova-btn nova-btn-outline" onclick="window.location.href='{{ route('orders.show', $order->id) }}'">
                    <i class="fas fa-times" style="margin-inline-end: 0.5rem;"></i>
                    {{ __('messages.orders.cancel') }}
                </button>
            @else
                <div class="nova-text-muted nova-fs-sm">
                    <i class="fas fa-info-circle" style="margin-inline-end: 0.5rem;"></i>
                    {{ __('messages.orders.cannot_edit_terminal_status_hint') }}
                </div>
            @endif
        </div>
    </div>

</form>

{{-- Confirmation Modal for sending price quote (Nova style) --}}
<div class="nova-modal-backdrop" id="sendQuoteModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-paper-plane"></i>
            </div>
            <h4 class="nova-delete-modal-title">ارسال عرض السعر</h4>
            <p class="nova-delete-modal-text">
                هل أنت م确保 من إرسال عرض سعر جديد للعميل؟<br>
                بعد الإرسال يتم تغيير 상태 الطلبية إلى "تم إرسال العرض" ويظهر العرض في تطبيق العميل.
            </p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('sendQuoteModal').classList.remove('show')">يلغ</button>
                <form id="sendQuoteForm" method="POST" action="">
                    @csrf
                    <button type="submit" class="nova-btn nova-btn-warning">ارسال العرض</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
/* ============================================================
   Global state
   ============================================================ */
const allItems = @json($itemsData);
const warehouseProductsData = @json($warehouseProducts ?? []);
const isTerminal = {{ $isTerminalStatus ? 'true' : 'false' }};
const orderId = {{ $order->id }};
const customerPriceListId = {{ $priceListId ?? 'null' }};
let warehouseProducts = [];

/* ============================================================
   Toast Notification System
   ============================================================ */
function showToast(message, type = 'info', options = {}) {
    const { title = '', duration = 4000 } = options;

    let container = document.getElementById('novaToastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'novaToastContainer';
        document.body.appendChild(container);
    }

    const icons = {
        info: 'fa-circle-info',
        success: 'fa-circle-check',
        warning: 'fa-triangle-exclamation',
        error: 'fa-circle-xmark',
    };
    const defaultTitles = {
        info: 'معلومة',
        success: 'تم بنجاح',
        warning: 'تنبيه',
        error: 'خطأ',
    };

    const toast = document.createElement('div');
    toast.className = `nova-toast ${type}`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <i class="fas ${icons[type] || icons.info} nova-toast-icon"></i>
        <div class="nova-toast-body">
            <div class="nova-toast-title">${title || defaultTitles[type] || ''}</div>
            <div class="nova-toast-message">${message}</div>
        </div>
        <button type="button" class="nova-toast-close" aria-label="إغلاق">
            <i class="fas fa-xmark"></i>
        </button>
        ${duration > 0 ? `<div class="nova-toast-progress" style="animation-duration: ${duration}ms;"></div>` : ''}
    `;

    const dismiss = () => {
        if (!toast.isConnected) return;
        toast.classList.add('hiding');
        setTimeout(() => toast.remove(), 300);
    };

    toast.querySelector('.nova-toast-close').addEventListener('click', dismiss);
    container.appendChild(toast);

    if (duration > 0) setTimeout(dismiss, duration);
    return toast;
}

/* ============================================================
   Item selects — filter by warehouse
   ============================================================ */
function updateItemSelectsForWarehouse(products) {
    warehouseProducts = products || [];
    const availableItemIds = warehouseProducts.length > 0
        ? [...new Set(warehouseProducts.map(p => p.item_id))]
        : null;

    document.querySelectorAll('.item-select').forEach(select => {
        const currentValue = select.value;
        const firstOption = select.querySelector('option[value=""]');
        select.innerHTML = '';
        if (firstOption) select.appendChild(firstOption.cloneNode(true));

        // المنتجات المسجلة في هذه الطلبية يجب أن تبقى موجودة في القائمة حتى لو
        // لم يكن لها رصيد في المخزن المحدد، وإلا اختفى المنتج المسجل من
        // الـ select عند فتح صفحة التعديل وأصبح السطر بلا منتج.
        const inStockIds = availableItemIds || [];
        const itemsToShow = availableItemIds
            ? allItems.filter(item =>
                inStockIds.includes(item.id)
                || (currentValue && parseInt(currentValue, 10) === parseInt(item.id, 10)))
            : allItems;

        itemsToShow.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.name_ar || item.name_en || item.code;

            // وسم المنتجات غير المتوفرة بدل حذفها
            if (availableItemIds && !inStockIds.includes(parseInt(item.id, 10))) {
                option.textContent += ' — غير متوفر في المخزن';
                option.dataset.noStock = '1';
            }

            select.appendChild(option);
        });

        if (currentValue) {
            const stillListed = Array.from(select.options).some(o => o.value == currentValue);
            if (stillListed) {
                select.value = currentValue;
            } else {
                select.value = '';
            }
        }
    });
}

/* ============================================================
   Warehouse Products — helpers
   ============================================================ */
function stockStatus(qty) {
    const n = Number(qty) || 0;
    if (n <= 0)  return { key: 'out',       label: 'نفذ المخزون',  cls: 'out' };
    if (n <= 10) return { key: 'low',       label: 'مخزون منخفض', cls: 'low' };
    return         { key: 'available', label: 'متوفر',        cls: 'available' };
}

function batchHtml(p) {
    if (!p.batches || p.batches.length === 0) {
        return `<div style="font-size:0.72rem;color:var(--nova-text-3);text-align:center;">لا توجد دفعات نشطة</div>`;
    }
    const id = `batch-${p.item_id}-${p.pack_id}`;
    return `
        <button type="button" class="batch-toggle" data-target="${id}">
            <i class="fas fa-layer-group"></i>
            <span>الدفعات النشطة (${p.batches_count})</span>
            <i class="fas fa-chevron-down chevron"></i>
        </button>
        <ul class="batch-list" id="${id}">
            ${p.batches.map(b => `
                <li>
                    <span style="color:var(--nova-primary);font-weight:500;">${b.batch_number}</span>
                    <span style="color:var(--nova-text-3);">
                        ${Number(b.pack_count).toFixed(3)} عبوة
                        ${b.expiry_date ? ` · ${new Date(b.expiry_date).toLocaleDateString('ar-EG')}` : ''}
                    </span>
                </li>
            `).join('')}
        </ul>
    `;
}

function renderProductCard(p) {
    const status  = stockStatus(p.available_qty);
    const pieces  = Number(p.available_qty) * Number(p.quantity_in_pack || 1);
    const disabled = status.key === 'out' ? 'disabled' : '';

    return `
        <article class="warehouse-product-item ${status.cls}"
                 data-item-id="${p.item_id}"
                 data-pack-id="${p.pack_id}"
                 data-price="${p.price}"
                 data-stock="${p.available_qty}"
                 data-quantity-in-pack="${p.quantity_in_pack}">
            <div class="nova-product-image" ${p.image_url ? `style="background-image:url('${p.image_url}')"` : ''}>
                ${p.image_url ? '' : '<i class="fas fa-image" style="color:var(--nova-text-3);"></i>'}
                <span class="stock-badge ${status.cls}">${status.label}</span>
            </div>
            <div class="nova-product-body">
                <h5 class="nova-product-title">${p.item_name || ''}</h5>
                ${p.item_name_ar ? `<p class="nova-product-subtitle">${p.item_name_ar}</p>` : ''}

                <div class="nova-product-meta">
                    ${p.barcode ? `<span class="nova-badge info">${p.barcode}</span>` : ''}
                    ${(p.pack_type || p.pack_capacity) ? `<span style="font-size:0.72rem;color:var(--nova-text-3);">${p.pack_type || ''}${p.pack_capacity ? ' · ' + p.pack_capacity : ''}</span>` : ''}
                </div>

                <div class="product-stats-grid">
                    <div class="nova-product-stat">
                        <div class="nova-product-stat-label">عبوات</div>
                        <div class="nova-product-stat-value primary">${Number(p.available_qty).toFixed(2)}</div>
                    </div>
                    <div class="nova-product-stat">
                        <div class="nova-product-stat-label">قطع</div>
                        <div class="nova-product-stat-value success">${pieces.toFixed(0)}</div>
                    </div>
                    <div class="nova-product-stat">
                        <div class="nova-product-stat-label">وحدة/عبوة</div>
                        <div class="nova-product-stat-value">${p.quantity_in_pack}</div>
                    </div>
                    <div class="nova-product-stat">
                        <div class="nova-product-stat-label">دفعات</div>
                        <div class="nova-product-stat-value">${p.batches_count || 0}</div>
                    </div>
                </div>

                ${batchHtml(p)}

                <div class="product-actions">
                    <button type="button" class="add-to-order-btn" ${disabled}>
                        <i class="fas fa-cart-plus"></i> إضافة للطلب
                    </button>
                    <button type="button" class="remove-from-order-btn" title="إزالة من الطلب">
                        <i class="fas fa-trash"></i> إزالة من الطلب
                    </button>
                </div>
            </div>
        </article>
    `;
}

function renderWarehouseProducts() {
    const grid        = document.getElementById('warehouseProductsGrid');
    if (!grid) return;
    const search      = (document.getElementById('warehouseSearchInput').value || '').trim().toLowerCase();
    const availability= document.getElementById('warehouseAvailabilityFilter').value;
    const sort        = document.getElementById('warehouseSortSelect').value;
    const badge       = document.getElementById('warehouseCountBadge');

    let list = warehouseProducts.slice();

    if (search) {
        list = list.filter(p => {
            const hay = [p.item_name, p.item_name_ar, p.barcode, p.code]
                .filter(Boolean).join(' ').toLowerCase();
            return hay.includes(search);
        });
    }
    if (availability !== 'all') {
        list = list.filter(p => stockStatus(p.available_qty).key === availability);
    }

    const sorters = {
        name:         (a, b) => (a.item_name || '').localeCompare(b.item_name || '', 'ar'),
        stock_desc:   (a, b) => Number(b.available_qty) - Number(a.available_qty),
        stock_asc:    (a, b) => Number(a.available_qty) - Number(b.available_qty),
        batches_desc: (a, b) => (b.batches_count || 0) - (a.batches_count || 0),
    };
    list.sort(sorters[sort] || sorters.name);

    badge.textContent = list.length;

    if (list.length === 0) {
        grid.innerHTML = `
            <div class="warehouse-empty">
                <i class="fas fa-box-open"></i>
                <p>لا توجد منتجات مطابقة</p>
                <span>جرّب تعديل الفلاتر أو كلمة البحث</span>
            </div>`;
        return;
    }

    grid.innerHTML = list.map(renderProductCard).join('');

    // Mark existing order rows as selected
    document.querySelectorAll('.item-row').forEach(row => {
        const i = row.querySelector('.item-select')?.value;
        const p = row.querySelector('.pack-select')?.value;
        if (i && p) {
            const card = grid.querySelector(`.warehouse-product-item[data-item-id="${i}"][data-pack-id="${p}"]`);
            if (card) card.classList.add('selected');
        }
    });
}

function showWarehouseSkeleton() {
    const grid = document.getElementById('warehouseProductsGrid');
    if (!grid) return;
    grid.innerHTML = Array.from({ length: 6 }).map(() => `<div class="warehouse-skeleton"></div>`).join('');
}

/* ============================================================
   Load warehouse products
   ============================================================ */
async function loadWarehouseProducts(warehouseId) {
    const card = document.getElementById('warehouseProductsCard');
    if (!card) return;

    if (!warehouseId) {
        card.classList.remove('visible');
        warehouseProducts = [];
        const badge = document.getElementById('warehouseCountBadge');
        if (badge) badge.textContent = '0';
        updateItemSelectsForWarehouse(null);
        return;
    }

    card.classList.add('visible');
    showWarehouseSkeleton();

    try {
        const response = await fetch(`/warehouse/${warehouseId}/products`, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const products = await response.json();

        warehouseProducts = products || [];
        updateItemSelectsForWarehouse(warehouseProducts);

        const availableItemIds = [...new Set(warehouseProducts.map(p => p.item_id))];
        document.querySelectorAll('.item-row').forEach(row => {
            const itemSelect = row.querySelector('.item-select');
            if (itemSelect.value && !availableItemIds.includes(parseInt(itemSelect.value))) {
                itemSelect.style.borderColor = 'var(--nova-danger)';
                itemSelect.title = 'هذا المنتج غير متاح في المخزن المحدد';
            } else {
                itemSelect.style.borderColor = '';
                itemSelect.title = '';
            }
        });

        // Set stock data for existing rows
        document.querySelectorAll('.item-row').forEach(row => {
            const itemId = row.querySelector('.item-select').value;
            const packId = row.querySelector('.pack-select').value;
            if (itemId && packId) {
                const product = warehouseProducts.find(p => p.item_id == itemId && p.pack_id == packId);
                if (product) {
                    row.querySelector('.price-input').dataset.stock = product.available_qty;
                }
            }
        });

        renderWarehouseProducts();
    } catch (e) {
        console.error('Failed to load warehouse products:', e);
        document.getElementById('warehouseProductsGrid').innerHTML = `
            <div class="warehouse-empty">
                <i class="fas fa-circle-exclamation" style="color:var(--nova-danger);"></i>
                <p>خطأ في تحميل المنتجات</p>
                <span>حاول مرة أخرى لاحقاً</span>
            </div>`;
        showToast('فشل تحميل منتجات المخزن.', 'error', { title: 'خطأ في الاتصال' });
    }
}

/* ============================================================
   Warehouse select — change handler
   ============================================================ */
const warehouseSelect = document.getElementById('warehouseSelect');
if (warehouseSelect && !isTerminal) {
    warehouseSelect.addEventListener('change', function () {
        loadWarehouseProducts(this.value);
    });
}

/* ============================================================
   Warehouse toolbar events
   ============================================================ */
const searchInput = document.getElementById('warehouseSearchInput');
const availFilter = document.getElementById('warehouseAvailabilityFilter');
const sortSelect  = document.getElementById('warehouseSortSelect');
const productsGrid = document.getElementById('warehouseProductsGrid');

if (searchInput) searchInput.addEventListener('input', renderWarehouseProducts);
if (availFilter) availFilter.addEventListener('change', renderWarehouseProducts);
if (sortSelect)  sortSelect.addEventListener('change', renderWarehouseProducts);

if (productsGrid) {
    productsGrid.addEventListener('click', (e) => {
        const toggle = e.target.closest('.batch-toggle');
        if (toggle) {
            e.stopPropagation();
            const list = document.getElementById(toggle.dataset.target);
            if (list) {
                list.classList.toggle('open');
                toggle.classList.toggle('open');
            }
            return;
        }
        if (e.target.closest('.batch-list')) return;

        const removeBtn = e.target.closest('.remove-from-order-btn');
        if (removeBtn) {
            e.stopPropagation();
            const card = removeBtn.closest('.warehouse-product-item');
            if (card) removeProductFromOrder(card.dataset.itemId, card.dataset.packId);
            return;
        }

        const card = e.target.closest('.warehouse-product-item');
        if (!card) return;
        if (card.classList.contains('out')) {
            showToast('هذا المنتج غير متوفر في المخزن حالياً.', 'warning', { title: 'المنتج غير متوفر' });
            return;
        }

        addProductToOrder(
            card.dataset.itemId,
            card.dataset.packId,
            parseFloat(card.dataset.price),
            parseFloat(card.dataset.stock)
        );
    });
}

/* ============================================================
   Add product to order
   ============================================================ */
function addProductToOrder(itemId, packId, price, stock) {
    const existingRow = Array.from(document.querySelectorAll('.item-row')).find(row => {
        const itemSelect = row.querySelector('.item-select');
        const packSelect = row.querySelector('.pack-select');
        return itemSelect.value == itemId && packSelect.value == packId;
    });

    if (existingRow) {
        const qtyInput = existingRow.querySelector('.quantity-input');
        qtyInput.value = (parseFloat(qtyInput.value) + 1).toFixed(3).replace(/\.?0+$/, '');
        calculateLineTotal(existingRow);
        updateSummary();
        return;
    }

    // Remove empty row
    const emptyRow = document.getElementById('emptyRow');
    if (emptyRow) emptyRow.remove();

    const rows = document.querySelectorAll('.item-row');
    let targetRow = null;

    for (const row of rows) {
        const itemSelect = row.querySelector('.item-select');
        if (!itemSelect.value) {
            targetRow = row;
            break;
        }
    }

    if (!targetRow) {
        addNewItemRow();
        targetRow = document.querySelector('#itemsTbody .item-row:last-child');
    }

    const itemSelect = targetRow.querySelector('.item-select');
    itemSelect.value = itemId;

    updatePackOptions(itemSelect, itemId, true);

    setTimeout(() => {
        const packSelect = targetRow.querySelector('.pack-select');
        packSelect.value = packId;
        const priceInput = targetRow.querySelector('.price-input');
        priceInput.value = price.toFixed(2);
        priceInput.dataset.stock = stock;

        const productItem = document.querySelector(`.warehouse-product-item[data-item-id="${itemId}"][data-pack-id="${packId}"]`);
        if (productItem) productItem.classList.add('selected');

        calculateLineTotal(targetRow);
        updateSummary();
    }, 50);
}

/* ============================================================
   Remove product from order
   ============================================================ */
function removeProductFromOrder(itemId, packId) {
    const rows = document.querySelectorAll('.item-row');
    let targetRow = null;

    for (const row of rows) {
        const itemSelect = row.querySelector('.item-select');
        const packSelect = row.querySelector('.pack-select');
        if (itemSelect.value == itemId && packSelect.value == packId) {
            targetRow = row;
            break;
        }
    }

    if (targetRow) {
        removeItemRow(targetRow.querySelector('.nova-remove-btn'));
    }

    const productItem = document.querySelector(`.warehouse-product-item[data-item-id="${itemId}"][data-pack-id="${packId}"]`);
    if (productItem) productItem.classList.remove('selected');

    updateSummary();
}

/* ============================================================
   Item rows
   ============================================================ */
function addNewItemRow() {
    const tbody = document.getElementById('itemsTbody');
    const emptyRow = document.getElementById('emptyRow');
    if (emptyRow) emptyRow.remove();

    const rows = tbody.querySelectorAll('.item-row');
    const rowCount = rows.length;
    const row = document.createElement('tr');
    row.className = 'item-row';

    const availableItemIds = warehouseProducts.length > 0
        ? [...new Set(warehouseProducts.map(p => p.item_id))]
        : null;
    const itemsToShow = availableItemIds
        ? allItems.filter(item => availableItemIds.includes(item.id))
        : allItems;

    row.innerHTML = `
        <td class="nova-fw-semibold">${rowCount + 1}</td>
        <td>
            <select name="items[${rowCount}][item_id]" class="nova-item-select item-select" required>
                <option value="">{{ __('messages.orders.select_product') }}</option>
                ${itemsToShow.map(item => `
                    <option value="${item.id}">${item.name_ar || item.name_en || item.code}</option>
                `).join('')}
            </select>
        </td>
        <td>
            <select name="items[${rowCount}][item_pack_id]" class="nova-item-select pack-select" required>
                <option value="">{{ __('messages.orders.select_pack') }}</option>
            </select>
        </td>
        <td>
            <input type="number" name="items[${rowCount}][quantity]" class="nova-qty-input quantity-input" value="1" step="any" min="0.001" required>
        </td>
        <td>
            <input type="number" name="items[${rowCount}][unit_price]" class="nova-price-input price-input" value="0" step="0.01" readonly>
        </td>
        <td>
            <input type="number" name="items[${rowCount}][discount_percent]" class="nova-discount-input discount-input" value="0" step="0.01" min="0" max="100">
        </td>
        <td class="nova-total-readonly">0.00</td>
        <td>
            <button type="button" class="nova-remove-btn" onclick="removeItemRow(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;

    tbody.appendChild(row);
    attachEventListeners(row);
    updateSummary();
}

function updatePackOptions(selectElement, itemId, skipPriceUpdate = false) {
    const item = allItems.find(i => i.id == itemId);
    const row = selectElement.closest('tr');
    const packSelect = row.querySelector('.pack-select');
    const currentPackValue = packSelect.value;

    packSelect.innerHTML = '<option value="">اختر العبوة</option>';

    if (item && item.packs.length > 0) {
        item.packs.forEach(pack => {
            const option = document.createElement('option');
            option.value = pack.id;
            option.dataset.price = pack.price;
            option.textContent = pack.name;
            packSelect.appendChild(option);
        });
    }

    // Restore previously selected pack if exists
    if (currentPackValue) {
        const exists = Array.from(packSelect.options).some(o => o.value == currentPackValue);
        if (exists) packSelect.value = currentPackValue;
    }

    // Auto-price from price list if customer has one
    if (!skipPriceUpdate && customerPriceListId) {
        updatePriceFromPriceList(row, customerPriceListId);
    }
}

async function updatePriceFromPriceList(row, priceListId) {
    const itemSelect = row.querySelector('.item-select');
    const packSelect = row.querySelector('.pack-select');
    const priceInput = row.querySelector('.price-input');

    if (!itemSelect.value || !packSelect.value) return;

    try {
        const response = await fetch(`/price-list/${priceListId}/price?item_id=${itemSelect.value}&pack_id=${packSelect.value}`, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await response.json();

        if (data.price !== undefined && data.price !== null && parseFloat(data.price) > 0) {
            priceInput.value = parseFloat(data.price).toFixed(2);
        } else {
            const item = allItems.find(i => i.id == itemSelect.value);
            const pack = item?.packs.find(p => p.id == packSelect.value);
            if (pack && pack.price !== undefined) {
                priceInput.value = parseFloat(pack.price).toFixed(2);
            }
        }
        calculateLineTotal(row);
        updateSummary();
    } catch (e) {
        console.error('Failed to fetch price:', e);
    }
}

function updatePriceFromPack(row) {
    const itemSelect = row.querySelector('.item-select');
    const packSelect = row.querySelector('.pack-select');
    const priceInput = row.querySelector('.price-input');

    if (customerPriceListId) {
        updatePriceFromPriceList(row, customerPriceListId);
        return;
    }

    const item = allItems.find(i => i.id == itemSelect.value);
    const pack = item?.packs.find(p => p.id == packSelect.value);
    if (pack && pack.price !== undefined) {
        priceInput.value = parseFloat(pack.price).toFixed(2);
    }

    calculateLineTotal(row);
    updateSummary();
}

/* ============================================================
   Line total + stock check
   ============================================================ */
function calculateLineTotal(row) {
    const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
    const price = parseFloat(row.querySelector('.price-input').value) || 0;
    const discount = parseFloat(row.querySelector('.discount-input').value) || 0;

    const subtotal = quantity * price;
    const discountAmount = subtotal * (discount / 100);
    const total = subtotal - discountAmount;

    const totalElement = row.querySelector('.nova-total-readonly');
    totalElement.textContent = total.toFixed(2);

    const stock = parseFloat(row.querySelector('.price-input').dataset.stock) || 0;
    const qtyInput = row.querySelector('.quantity-input');

    if (stock > 0 && quantity > stock) {
        showToast(
            `الكمية المطلوبة (${quantity}) تتجاوز المخزون المتاح (${stock}). تم ضبطها تلقائياً على الحد الأقصى.`,
            'warning',
            { title: 'تجاوز المخزون المتاح', duration: 5000 }
        );

        qtyInput.value = stock;

        const correctedSubtotal = stock * price;
        const correctedDiscountAmount = correctedSubtotal * (discount / 100);
        const correctedTotal = correctedSubtotal - correctedDiscountAmount;
        totalElement.textContent = correctedTotal.toFixed(2);

        qtyInput.style.borderColor = 'var(--nova-warning)';
        qtyInput.style.backgroundColor = 'rgba(245, 158, 11, 0.05)';
        qtyInput.title = `تم تعديل الكمية إلى الحد الأقصى المتاح: ${stock}`;
    } else {
        qtyInput.style.borderColor = '';
        qtyInput.style.backgroundColor = '';
        qtyInput.title = '';
    }

    return total;
}

function removeItemRow(button) {
    const row = button.closest('.item-row');
    row.remove();

    const tbody = document.getElementById('itemsTbody');
    const rows = tbody.querySelectorAll('.item-row');
    rows.forEach((row, index) => {
        row.querySelector('td:first-child').textContent = index + 1;
        const inputs = row.querySelectorAll('input, select');
        inputs.forEach(input => {
            const name = input.getAttribute('name');
            if (name) {
                input.setAttribute('name', name.replace(/items\[\d+\]/, `items[${index}]`));
            }
        });
    });

    if (rows.length === 0) {
        const noItemsRow = document.createElement('tr');
        noItemsRow.id = 'emptyRow';
        noItemsRow.innerHTML = `
            <td colspan="8">
                <div class="nova-empty nova-py-3" style="text-align: center; padding: 2rem;">
                    <div class="nova-empty-icon" style="font-size: 2.5rem; color: var(--nova-text-3); margin-bottom: 0.5rem;">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <h3 class="nova-empty-title" style="font-weight: 600;">{{ __('messages.orders.no_items') }}</h3>
                </div>
            </td>
        `;
        tbody.appendChild(noItemsRow);
    }

    updateSummary();
}

function updateSummary() {
    const rows = document.querySelectorAll('.item-row');
    let itemsCount = rows.length;
    let totalQty = 0;
    let grandTotal = 0;

    rows.forEach(row => {
        const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const total = parseFloat(row.querySelector('.nova-total-readonly').textContent) || 0;

        totalQty += quantity;
        grandTotal += total;
    });

    document.getElementById('summaryItemsCount').textContent = itemsCount;
    document.getElementById('summaryTotalQty').textContent = totalQty.toFixed(3);
    document.getElementById('summaryGrandTotal').textContent = grandTotal.toFixed(2);
}

/* ============================================================
   Attach row events
   ============================================================ */
function attachEventListeners(row) {
    const itemSelect = row.querySelector('.item-select');
    const quantityInput = row.querySelector('.quantity-input');
    const priceInput = row.querySelector('.price-input');
    const discountInput = row.querySelector('.discount-input');
    const packSelect = row.querySelector('.pack-select');

    if (!itemSelect || !quantityInput || !priceInput || !discountInput || !packSelect) return;

    itemSelect.addEventListener('change', function() {
        this.style.borderColor = '';
        this.title = '';
        updatePackOptions(this, this.value);
        calculateLineTotal(row);
        updateSummary();
    });

    packSelect.addEventListener('change', function() {
        updatePriceFromPack(row);
    });

    quantityInput.addEventListener('input', function() {
        calculateLineTotal(row);
        updateSummary();
    });

    priceInput.addEventListener('input', function() {
        calculateLineTotal(row);
        updateSummary();
    });

    discountInput.addEventListener('input', function() {
        calculateLineTotal(row);
        updateSummary();
    });
}

/* ============================================================
   Send new price quote
   ============================================================ */
function sendNewPriceQuote() {
    const modal = document.getElementById('sendQuoteModal');
    const form = document.getElementById('sendQuoteForm');
    form.action = `/orders/${orderId}/send-new-quote`;
    modal.classList.add('show');
}

/* ============================================================
   Init
   ============================================================ */
document.addEventListener('DOMContentLoaded', function() {
    // Attach listeners to existing rows
    document.querySelectorAll('.item-row').forEach(row => attachEventListeners(row));

    // Load warehouse products if warehouse is already selected
    if (!isTerminal && warehouseSelect && warehouseSelect.value) {
        loadWarehouseProducts(warehouseSelect.value);
    }

    updateSummary();
});
</script>
@endpush