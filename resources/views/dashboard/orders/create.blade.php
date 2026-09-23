@extends('dashboard.partials.layout')

@section('title', __('messages.orders.new_order'))

@php
$itemsData = \App\Models\Item::with(['packs', 'packs.packType', 'packs.packCapacity'])
    ->where('is_inactive', false)
    ->orderBy('name_ar')
    ->get()
    ->map(function ($item) {
        return [
            'id' => $item->id,
            'name_ar' => $item->name_ar,
            'name_en' => $item->name_en,
            'code' => $item->code,
            'packs' => $item->packs->map(function ($pack) {
                return [
                    'id' => $pack->id,
                    'name' => $pack->getDisplayNameAttribute(),
                    'pack_type' => $pack->packType->name_ar ?? '',
                    'capacity' => $pack->packCapacity->formatted_value ?? '',
                    'price' => $pack->price ?? 0,
                    'quantity_in_pack' => $pack->quantity_in_pack ?? 1,
                ];
            })->values()->toArray(),
        ];
    })->values()->toArray();
@endphp

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
</style>
@endpush

@section('content')

{{-- ==================== Hero Header ==================== --}}
<div class="nova-actionbar nova-mb-4">
    <div class="nova-order-hero">
        <div class="nova-order-hero-title">
            <h2 class="nova-actionbar-title m-0">
                <i class="fas fa-plus-circle"></i>
                {{ __('messages.orders.new_order') }}
            </h2>
        </div>

        <div class="nova-flex nova-items-center nova-gap-2">
            <a href="{{ route('orders.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
                <i class="fas fa-arrow-right"></i> {{ __('messages.orders.back') }}
            </a>
        </div>
    </div>
</div>

{{-- ==================== Form ==================== --}}
<form id="orderCreateForm" action="{{ route('orders.store') }}" method="POST">
    @csrf

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
                        <select name="customer_id" class="nova-input" required>
                            <option value="">{{ __('messages.orders.select_customer') }}</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="nova-order-info-item">
                        <label class="nova-order-info-label">{{ __('messages.orders.warehouse') }}</label>
                        <select name="warehouse_id" class="nova-input" required>
                            <option value="">{{ __('messages.orders.select_warehouse') }}</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name_en ?? $warehouse->name_ar ?? $warehouse->code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="nova-order-info-item">
                        <label class="nova-order-info-label">{{ __('messages.orders.currency') }}</label>
                        <select name="currency_id" class="nova-input" required>
                            <option value="">{{ __('messages.orders.select_currency') }}</option>
                            @foreach($currencies as $currency)
                                <option value="{{ $currency->id }}">{{ $currency->code }} - {{ $currency->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="nova-order-info-row">
                    <div class="nova-order-info-item" style="grid-column: 1 / -1;">
                        <label class="nova-order-info-label">{{ __('messages.orders.notes') }}</label>
                        <textarea name="notes" class="nova-input" rows="3">{{ old('notes') }}</textarea>
                    </div>
                    <div class="nova-order-info-item">
                        <label class="nova-order-info-label">{{ __('messages.orders.scheduled_delivery_date') }}</label>
                        <input type="date" name="scheduled_delivery_date" class="nova-input" value="{{ old('scheduled_delivery_date') }}">
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
                        <tr class="item-row" data-id="0">
                            <td class="nova-fw-semibold">1</td>
                            <td>
                                <select name="items[0][item_id]" class="nova-item-select item-select" required>
                                    <option value="">اختر المنتج</option>
                                    @foreach($itemsData as $itemOption)
                                        <option value="{{ $itemOption['id'] }}">{{ $itemOption['name_ar'] ?? $itemOption['name_en'] ?? $itemOption['code'] }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select name="items[0][item_pack_id]" class="nova-item-select pack-select">
                                    <option value="">{{ __('messages.orders.select_pack') }}</option>
                                </select>
                            </td>
                            <td>
                                <input type="number" name="items[0][quantity]" class="nova-qty-input quantity-input" value="1" step="any" required>
                            </td>
                            <td>
                                <input type="number" name="items[0][unit_price]" class="nova-price-input price-input" value="0" step="0.01" required>
                            </td>
                            <td>
                                <input type="number" name="items[0][discount_percent]" class="nova-discount-input discount-input" value="0" step="0.01">
                            </td>
                            <td class="nova-total-readonly">0.00</td>
                            <td>
                                <button type="button" class="nova-remove-btn" onclick="removeItemRow(this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="nova-card-footer" style="border-top: 1px solid var(--nova-border-color); padding: 1rem; margin-top: 1rem;">
                <button type="button" class="nova-add-item-btn" onclick="addNewItemRow()">
                    <i class="fas fa-plus" style="margin-inline-end: 0.5rem;"></i>
                    {{ __('messages.orders.add_item') }}
                </button>
            </div>
        </div>
    </div>

    {{-- ==================== Summary Card (Sticky Bottom) ==================== --}}
    <div class="nova-summary-sticky">
        <div class="nova-summary-row">
            <div class="nova-summary-item">
                <span class="nova-summary-label">{{ __('messages.orders.total_items') }}</span>
                <span class="nova-summary-value" id="summaryItemsCount">1</span>
            </div>
            <div class="nova-summary-item">
                <span class="nova-summary-label">{{ __('messages.orders.total_quantity') }}</span>
                <span class="nova-summary-value" id="summaryTotalQty">0</span>
            </div>
            <div class="nova-summary-item">
                <span class="nova-summary-label">{{ __('messages.orders.grand_total') }}</span>
                <span class="nova-summary-value">
                    <span id="summaryGrandTotal">0</span>
                    <span class="nova-currency-symbol"> {{ $currencies->first() ? ($currencies->first()->symbol ?? $currencies->first()->code ?? '') : '' }}</span>
                </span>
            </div>
        </div>

        <div class="nova-flex nova-gap-2 nova-justify-center">
            <button type="submit" class="nova-btn nova-btn-primary">
                <i class="fas fa-plus" style="margin-inline-end: 0.5rem;"></i>
                {{ __('messages.orders.new_order') }}
            </button>
            <button type="button" class="nova-btn nova-btn-outline" onclick="window.location.href='{{ route('orders.index') }}'">
                <i class="fas fa-times" style="margin-inline-end: 0.5rem;"></i>
                {{ __('messages.orders.cancel') }}
            </button>
        </div>
    </div>

</form>

@endsection

@push('scripts')
<script>
    const allItems = @json($itemsData);

    function addNewItemRow() {
        const tbody = document.getElementById('itemsTbody');
        const rows = tbody.querySelectorAll('.item-row');
        const rowCount = rows.length;
        const row = document.createElement('tr');
        row.className = 'item-row';
        row.innerHTML = `
            <td class="nova-fw-semibold">${rowCount + 1}</td>
            <td>
                <select name="items[${rowCount}][item_id]" class="nova-item-select item-select" required>
                    <option value="">اختر المنتج</option>
                    ${allItems.map(item => `
                        <option value="${item.id}">${item.name_ar || item.name_en || item.code}</option>
                    `).join('')}
                </select>
            </td>
            <td>
                <select name="items[${rowCount}][item_pack_id]" class="nova-item-select pack-select" required>
                    <option value="">اختر العبوة</option>
                </select>
            </td>
            <td>
                <input type="number" name="items[${rowCount}][quantity]" class="nova-qty-input quantity-input" value="1" step="any" required>
            </td>
            <td>
                <input type="number" name="items[${rowCount}][unit_price]" class="nova-price-input price-input" value="0" step="0.01" readonly>
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
    }

    function updatePackOptions(selectElement, itemId) {
        const item = allItems.find(i => i.id == itemId);
        const packSelect = selectElement.closest('tr').querySelector('.pack-select');

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
    }

    function updatePriceFromPack(row) {
        const packSelect = row.querySelector('.pack-select');
        const priceInput = row.querySelector('.price-input');
        const opt = packSelect.selectedOptions[0];

        if (opt && opt.dataset.price) {
            priceInput.value = parseFloat(opt.dataset.price).toFixed(2);
        } else {
            priceInput.value = '0';
        }

        calculateLineTotal(row);
        updateSummary();
    }

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

    function attachEventListeners(row) {
        const itemSelect = row.querySelector('.item-select');
        const quantityInput = row.querySelector('.quantity-input');
        const priceInput = row.querySelector('.price-input');
        const discountInput = row.querySelector('.discount-input');
        const packSelect = row.querySelector('.pack-select');

        itemSelect.addEventListener('change', function() {
            updatePackOptions(this, this.value);
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

    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('.item-row');
        rows.forEach(row => {
            attachEventListeners(row);
        });

        updateSummary();
    });
</script>
@endpush
