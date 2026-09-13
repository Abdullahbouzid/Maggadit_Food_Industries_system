@extends('dashboard.partials.layout')

@section('title', 'Create Invoice')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-file-invoice" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            Create Invoice
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Create a new invoice and assign warehouse stock</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('invoices.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="nova-card">
    <div class="nova-card-body">
        <form action="{{ route('invoices.store') }}" method="POST" id="invoiceForm">
            @csrf
            <div class="nova-row nova-gap-4 nova-mb-4">
                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">Customer</label>
                        <select name="customer_id" class="nova-select" required>
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">Invoice Date</label>
                        <input type="date" name="invoice_date" class="nova-input" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
            </div>

            <div class="nova-flex nova-items-center nova-gap-2 nova-mb-3" style="border-bottom: 1px solid var(--nova-border); padding-bottom: 0.75rem;">
                <i class="fas fa-shopping-bag"></i>
                <h5 class="nova-fw-bold" style="margin: 0;">Invoice Items (Products & Warehouses)</h5>
            </div>

            <div id="itemsContainer">
                <!-- Items will be added here -->
            </div>

            <button type="button" class="nova-btn nova-btn-outline nova-mb-4" id="addItemBtn">
                <i class="fas fa-plus"></i> إضافة صنف
            </button>

            <div class="nova-row">
                <div class="nova-col nova-text-end">
                    <table class="nova-table nova-table-borderless nova-mb-3" style="max-width: 300px; margin-left: auto;">
                        <tbody>
                            <tr>
                                <td class="nova-text-muted">الإجمالي:</td>
                                <td class="nova-fw-bold" style="font-size: 1.25rem; color: var(--nova-primary);" id="totalAmount">0 ر.س</td>
                            </tr>
                        </tbody>
                    </table>
                    <a href="{{ route('invoices.index') }}" class="nova-btn nova-btn-outline">إلغاء</a>
                    <button type="submit" class="nova-btn nova-btn-primary">حفظ الفاتورة</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Template for new item row -->
<template id="itemTemplate">
    <div class="nova-card nova-mb-3 item-row" style="background: var(--nova-surface-2);">
        <div class="nova-card-body">
            <input type="hidden" name="items[__INDEX__][item_id]" class="item-id-input" value="">
            <div class="nova-row nova-gap-3">
                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label nova-fs-sm nova-fw-bold">المنتج (الصنف)</label>
                        <select name="items[__INDEX__][item_id_select]" class="nova-select item-select" required
                                onchange="fetchWarehouses(this, __INDEX__); syncItemId(this)">
                            <option value="">اختر المنتج</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" data-price="{{ $item->price ?? 0 }}">
                                    {{ $item->name_ar }} ({{ $item->category->name_ar ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label nova-fs-sm nova-fw-bold">المخزن (مصدر الصرف)</label>
                        <select name="items[__INDEX__][warehouse_id]" class="nova-select warehouse-select" required>
                            <option value="">اختر المنتج أولاً</option>
                        </select>
                        <small class="nova-help-text available-stock">المتوفر: -</small>
                    </div>
                </div>
                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label nova-fs-sm nova-fw-bold">السعر</label>
                        <input type="number" name="items[__INDEX__][unit_price]" class="nova-input price-input"
                               step="0.01" min="0" placeholder="السعر" readonly>
                    </div>
                </div>
                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label nova-fs-sm nova-fw-bold">الكمية</label>
                        <input type="number" name="items[__INDEX__][quantity]" class="nova-input qty-input"
                               min="1" step="0.01" required>
                    </div>
                </div>
                <div class="nova-col nova-flex nova-items-end">
                    <span class="nova-text-muted nova-fs-sm subtotal-display">0 ر.س</span>
                </div>
                <div class="nova-col nova-flex nova-items-end">
                    <button type="button" class="nova-btn nova-btn-danger nova-btn-sm" onclick="this.closest('.item-row').remove()">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

@endsection

@push('scripts')
<script>
    let itemIndex = 0;

    document.getElementById('addItemBtn').addEventListener('click', function() {
        const template = document.getElementById('itemTemplate').innerHTML;
        const html = template.replace(/__INDEX__/g, itemIndex++);
        document.getElementById('itemsContainer').insertAdjacentHTML('beforeend', html);
    });

    function syncItemId(selectEl) {
        const row = selectEl.closest('.item-row');
        const hiddenInput = row.querySelector('.item-id-input');
        hiddenInput.value = selectEl.value;

        const priceInput = row.querySelector('.price-input');
        if (selectEl.value) {
            const option = selectEl.options[selectEl.selectedIndex];
            priceInput.value = option.dataset.price || 0;
            updateSubtotal(row);
        } else {
            priceInput.value = '';
        }
    }

    function fetchWarehouses(selectElement, index) {
        const itemId = selectElement.value;
        const row = selectElement.closest('.item-row');
        const warehouseSelect = row.querySelector('.warehouse-select');
        const stockDisplay = row.querySelector('.available-stock');

        warehouseSelect.innerHTML = '<option value="">جارٍ التحميل...</option>';
        stockDisplay.innerText = 'المتوفر: -';

        if (!itemId) return;

        fetch(`/invoices/get-warehouses/${itemId}`)
            .then(res => res.json())
            .then(data => {
                warehouseSelect.innerHTML = '<option value="">اختر المخزن</option>';
                if (data.length === 0) {
                    warehouseSelect.innerHTML = '<option value="">لا يوجد مخزون متاح</option>';
                } else {
                    data.forEach(wh => {
                        const opt = document.createElement('option');
                        opt.value = wh.id;
                        opt.textContent = `${wh.name || wh.name_en} (المتوفر: ${parseFloat(wh.quantity).toFixed(2)})`;
                        opt.dataset.stock = wh.quantity;
                        warehouseSelect.appendChild(opt);
                    });
                }
            })
            .catch(err => {
                warehouseSelect.innerHTML = '<option value="">خطأ في تحميل المواقع</option>';
            });
    }

    function updateSubtotal(row) {
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const subtotal = (qty * price).toFixed(2);
        row.querySelector('.subtotal-display').textContent = subtotal + ' ر.س';
        updateTotal();
    }

    function updateTotal() {
        let total = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            total += qty * price;
        });
        document.getElementById('totalAmount').textContent = total.toFixed(2) + ' ر.س';
    }

    document.getElementById('itemsContainer').addEventListener('input', function(e) {
        if (e.target.classList.contains('qty-input') || e.target.classList.contains('price-input')) {
            updateSubtotal(e.target.closest('.item-row'));
        }
    });

    document.getElementById('itemsContainer').addEventListener('change', function(e) {
        if (e.target.classList.contains('warehouse-select')) {
            const selectedOption = e.target.options[e.target.selectedIndex];
            const stockDisplay = e.target.closest('.item-row').querySelector('.available-stock');
            if (selectedOption.value) {
                stockDisplay.innerText = `المتوفر: ${selectedOption.dataset.stock}`;
                const qtyInput = e.target.closest('.item-row').querySelector('.qty-input');
                qtyInput.setAttribute('max', selectedOption.dataset.stock);
            } else {
                stockDisplay.innerText = 'المتوفر: -';
            }
        }
    });

    document.getElementById('addItemBtn').click();
</script>
@endpush
