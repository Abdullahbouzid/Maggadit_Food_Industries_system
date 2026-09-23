@extends('dashboard.partials.layout')

@section('title', __('messages.orders.edit_order'))

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
    .nova-order-info-card {
        background: var(--nova-surface-1);
        border-radius: var(--nova-radius-lg);
        padding: 1.5rem;
    }
    .nova-order-info-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }
    .nova-order-info-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    .nova-order-info-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.025em;
        color: var(--nova-text-3);
    }
    .nova-order-info-value {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--nova-text-1);
    }
    .nova-items-table-container {
        border: 1px solid var(--nova-border-color);
        border-radius: var(--nova-radius-lg);
        overflow: hidden;
    }
    .nova-items-table {
        width: 100%;
        border-collapse: collapse;
    }
    .nova-items-table th {
        background: var(--nova-surface-2);
        padding: 0.75rem;
        text-align: left;
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--nova-text-1);
        border-bottom: 1px solid var(--nova-border-color);
    }
    .nova-items-table td {
        padding: 1rem;
        border-bottom: 1px solid var(--nova-border-color);
        vertical-align: middle;
    }
    .nova-item-select {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid var(--nova-border-color);
        border-radius: var(--nova-radius-md);
        background: var(--nova-surface-1);
    }
    .nova-qty-input {
        width: 80px;
        padding: 0.5rem;
        border: 1px solid var(--nova-border-color);
        border-radius: var(--nova-radius-md);
        text-align: center;
    }
    .nova-price-input {
        width: 100px;
        padding: 0.5rem;
        border: 1px solid var(--nova-border-color);
        border-radius: var(--nova-radius-md);
        text-align: right;
    }
    .nova-discount-input {
        width: 100px;
        padding: 0.5rem;
        border: 1px solid var(--nova-border-color);
        border-radius: var(--nova-radius-md);
        text-align: right;
    }
    .nova-total-readonly {
        font-weight: 600;
        color: var(--nova-primary);
        text-align: right;
        padding-right: 1rem;
    }
    .nova-remove-btn {
        background: var(--nova-danger);
        color: white;
        border: none;
        border-radius: var(--nova-radius-md);
        padding: 0.5rem;
        cursor: pointer;
        transition: background 0.2s;
    }
    .nova-remove-btn:hover {
        background: var(--nova-danger-dark, #dc2626);
    }
    .nova-add-item-btn {
        background: var(--nova-primary);
        color: white;
        border: none;
        border-radius: var(--nova-radius-md);
        padding: 0.75rem 1.5rem;
        cursor: pointer;
        font-weight: 500;
        transition: background 0.2s;
    }
    .nova-add-item-btn:hover {
        background: var(--nova-primary-dark, #2563eb);
    }
    .nova-summary-sticky {
        position: sticky;
        bottom: 0;
        background: var(--nova-surface-1);
        border-top: 2px solid var(--nova-border-color);
        padding: 1rem;
        margin: 1rem -1.5rem -1.5rem;
        border-radius: 0 0 var(--nova-radius-lg) var(--nova-radius-lg);
    }
    .nova-summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
    .nova-summary-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    .nova-summary-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.025em;
        color: var(--nova-text-3);
    }
    .nova-summary-value {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--nova-text-1);
    }
    .nova-currency-symbol {
        font-weight: 600;
        color: var(--nova-primary);
    }
    .nova-status-warning {
        background: var(--nova-warning-light, #fef3c7);
        border: 1px solid var(--nova-warning, #f59e0b);
        color: var(--nova-warning-dark, #92400e);
        padding: 0.75rem 1rem;
        border-radius: var(--nova-radius-md);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
</style>
@endpush

@php
$order = $order ?? null;
$customers = $customers ?? collect();
$warehouses = $warehouses ?? collect();
$items = $items ?? collect();
$currencies = $currencies ?? collect();

$statusEnum = $order && $order->status ? \App\Enums\OrderStatus::tryFrom($order->status) : null;
$isTerminalStatus = $statusEnum && in_array($statusEnum->value, ['delivered', 'cancelled', 'returned', 'expired']);
@endphp

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
        </div>

        <div class="nova-flex nova-items-center nova-gap-2">
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

{{-- ==================== Form ==================== --}}
<form id="orderEditForm" action="{{ route('orders.update', $order->id) }}" method="POST">
    @csrf
    @method('PUT')

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
                            {{ $order->created_at ? $order->created_at->format('Y-m-d') : '-' }}
                        </div>
                    </div>
                    <div class="nova-order-info-item">
                        <label class="nova-order-info-label">{{ __('messages.orders.warehouse') }}</label>
                        <select name="warehouse_id" class="nova-input" {{ $isTerminalStatus ? 'disabled' : '' }}>
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
                        <select name="currency_id" class="nova-input" {{ $isTerminalStatus ? 'disabled' : '' }}>
                            <option value="">{{ __('messages.orders.select_currency') }}</option>
                            @foreach($currencies as $currency)
                                <option value="{{ $currency->id }}" {{ $order->currency_id == $currency->id ? 'selected' : '' }}>
                                    {{ $currency->code }} - {{ $currency->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="nova-order-info-row">
                    <div class="nova-order-info-item" style="grid-column: 1 / -1;">
                        <label class="nova-order-info-label">{{ __('messages.orders.notes') }}</label>
                        <textarea name="notes" class="nova-input" rows="3" {{ $isTerminalStatus ? 'disabled' : '' }}>{{ $order->notes }}</textarea>
                    </div>
                    <div class="nova-order-info-item">
                        <label class="nova-order-info-label">{{ __('messages.orders.scheduled_delivery_date') }}</label>
                        <input type="date" name="scheduled_delivery_date" class="nova-input" value="{{ $order->scheduled_delivery_date ? $order->scheduled_delivery_date : '' }}" {{ $isTerminalStatus ? 'disabled' : '' }}>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                            <th style="width: 80px;">#</th>
                            <th>{{ __('messages.orders.col_product') }}</th>
                            <th>{{ __('messages.orders.col_pack') }}</th>
                            <th style="width: 120px;">{{ __('messages.orders.col_quantity') }}</th>
                            <th style="width: 120px;">{{ __('messages.orders.col_price') }}</th>
                            <th style="width: 120px;">{{ __('messages.orders.col_discount') }}</th>
                            <th style="width: 120px;">{{ __('messages.orders.col_total') }}</th>
                            <th style="width: 80px;">{{ __('messages.orders.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="itemsTbody">
                        @if($order->items && $order->items->count() > 0)
                            @foreach($order->items as $index => $item)
                            <tr class="item-row" data-id="{{ $item->id }}">
                                <td class="nova-fw-semibold">{{ $index + 1 }}</td>
                                <td>
                                    <select name="items[{{ $index }}][item_id]" class="nova-item-select item-select" {{ $isTerminalStatus ? 'disabled' : '' }}>
                                        <option value="">{{ __('messages.orders.select_product') }}</option>
                                        @foreach($items as $itemOption)
                                            <option value="{{ $itemOption->id }}" {{ $item->item_id == $itemOption->id ? 'selected' : '' }}>
                                                {{ $itemOption->name_en ?? $itemOption->name_ar ?? $itemOption->code }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="items[{{ $index }}][item_pack_id]" class="nova-item-select pack-select" {{ $isTerminalStatus ? 'disabled' : '' }}>
                                        <option value="">{{ __('messages.orders.select_pack') }}</option>
                                        @foreach($item->item->packs as $pack)
                                            <option value="{{ $pack->id }}" {{ $item->item_pack_id == $pack->id ? 'selected' : '' }}>
                                                {{ $pack->name ?? $pack->packDefinition->name ?? 'Pack #' . $pack->id }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $index }}][quantity]" class="nova-qty-input quantity-input" value="{{ $item->quantity }}" step="any" {{ $isTerminalStatus ? 'disabled' : '' }}>
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $index }}][unit_price]" class="nova-price-input price-input" value="{{ $item->unit_price }}" step="0.01" {{ $isTerminalStatus ? 'disabled' : '' }}>
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $index }}][discount_percent]" class="nova-discount-input discount-input" value="{{ $item->discount_percent }}" step="0.01" {{ $isTerminalStatus ? 'disabled' : '' }}>
                                </td>
                                <td class="nova-total-readonly">
                                    {{ number_format($item->line_total ?? (($item->quantity * $item->unit_price) - (($item->quantity * $item->unit_price) * ($item->discount_percent / 100))), 2) }}
                                </td>
                                <td>
                                    @if(!$isTerminalStatus)
                                    <button type="button" class="nova-remove-btn" onclick="removeItemRow(this)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr id="emptyRow">
                                <td colspan="8">
                                    <div class="nova-empty nova-py-3">
                                        <div class="nova-empty-icon">
                                            <i class="fas fa-clipboard-list"></i>
                                        </div>
                                        <h3 class="nova-empty-title">{{ __('messages.orders.no_items') }}</h3>
                                    </div>
                                </td>
                            </tr>
                        @endif
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
                    <span class="nova-currency-symbol"> {{ $order->currency ? ($order->currency->symbol ?? $order->currency->code ?? '') : '' }}</span>
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

@endsection

@push('scripts')
<script>
    const allItems = @json($items->map(function($item) {
        return [
            'id' => $item->id,
            'name_en' => $item->name_en,
            'name_ar' => $item->name_ar,
            'code' => $item->code,
            'packs' => $item->packs->map(function($pack) {
                return [
                    'id' => $pack->id,
                    'name' => $pack->name ?? $pack->packDefinition->name,
                    'price' => $pack->price ?? 0,
                    'quantity_in_pack' => $pack->quantity_in_pack ?? 1
                ];
            })->toArray()
        ];
    }))->toArray();

    const currencySymbol = '{{ $order->currency ? ($order->currency->symbol ?? $order->currency->code ?? '') : '' }}';

    const lang = {
        selectProduct: '{{ __('messages.orders.select_product') }}',
        selectPack: '{{ __('messages.orders.select_pack') }}',
        noItems: '{{ __('messages.orders.no_items') }}',
        addItem: '{{ __('messages.orders.add_item') }}',
        totalItems: '{{ __('messages.orders.total_items') }}',
        totalQuantity: '{{ __('messages.orders.total_quantity') }}',
        grandTotal: '{{ __('messages.orders.grand_total') }}'
    };

    function calculateLineTotal(row) {
        const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const discount = parseFloat(row.querySelector('.discount-input').value) || 0;

        const subtotal = quantity * price;
        const discountAmount = subtotal * (discount / 100);
        const total = subtotal - discountAmount;

        const totalElement = row.querySelector('.nova-total-readonly');
        totalElement.textContent = total.toFixed(2);

        return total;
    }

    function updatePackOptions(selectElement, itemId) {
        const item = allItems.find(i => i.id === parseInt(itemId));
        const packSelect = selectElement.closest('tr').querySelector('.pack-select');

        if (item && item.packs.length > 0) {
            while (packSelect.options.length > 1) {
                packSelect.remove(1);
            }
            item.packs.forEach(pack => {
                const option = document.createElement('option');
                option.value = pack.id;
                option.textContent = pack.name;
                packSelect.appendChild(option);
            });
        } else {
            while (packSelect.options.length > 1) {
                packSelect.remove(1);
            }
        }
    }

    function addNewItemRow() {
        const tbody = document.getElementById('itemsTbody');
        const emptyRow = document.getElementById('emptyRow');
        if (emptyRow) emptyRow.remove();

        const rowCount = tbody.querySelectorAll('.item-row').length;
        const row = document.createElement('tr');
        row.className = 'item-row';
        row.innerHTML = `
            <td class="nova-fw-semibold">${rowCount + 1}</td>
            <td>
                <select name="items[${rowCount}][item_id]" class="nova-item-select item-select">
                    <option value="">${lang.selectProduct}</option>
                    ${allItems.map(item => `
                        <option value="${item.id}">${item.name_en || item.name_ar || item.code}</option>
                    `).join('')}
                </select>
            </td>
            <td>
                <select name="items[${rowCount}][item_pack_id]" class="nova-item-select pack-select">
                    <option value="">${lang.selectPack}</option>
                </select>
            </td>
            <td>
                <input type="number" name="items[${rowCount}][quantity]" class="nova-qty-input quantity-input" value="1" step="any">
            </td>
            <td>
                <input type="number" name="items[${rowCount}][unit_price]" class="nova-price-input price-input" value="0" step="0.01">
            </td>
            <td>
                <input type="number" name="items[${rowCount}][discount_percent]" class="nova-discount-input discount-input" value="0" step="0.01">
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
                    <div class="nova-empty nova-py-3">
                        <div class="nova-empty-icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <h3 class="nova-empty-title">${lang.noItems}</h3>
                    </div>
                </td>
            `;
            tbody.appendChild(noItemsRow);
        }

        updateSummary();
    }

    function attachEventListeners(row) {
        const itemSelect = row.querySelector('.item-select');
        const quantityInput = row.querySelector('.quantity-input');
        const priceInput = row.querySelector('.price-input');
        const discountInput = row.querySelector('.discount-input');

        itemSelect.addEventListener('change', function() {
            updatePackOptions(this, this.value);
            calculateLineTotal(row);
            updateSummary();
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

    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('.item-row');
        rows.forEach(row => {
            attachEventListeners(row);
        });

        updateSummary();
    });
</script>
@endpush
