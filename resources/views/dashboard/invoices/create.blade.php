@extends('dashboard.partials.layout')

@section('title', 'Create Invoice')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold mb-2">Create Invoice</h1>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <form action="{{ route('invoices.store') }}" method="POST" id="invoiceForm">
                            @csrf
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Customer</label>
                                    <select name="customer_id" class="form-select" required>
                                        <option value="">Select Customer</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Invoice Date</label>
                                    <input type="date" name="invoice_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                            
                            <h5 class="fw-bold mb-3 border-bottom pb-2">Invoice Items (Products & Warehouses)</h5>
                            
                            <div id="itemsContainer">
                                <!-- Items will be added here -->
                            </div>
                            
                            <button type="button" class="btn btn-outline-primary mb-4" id="addItemBtn">
                                <i data-feather="plus"></i> إضافة صنف
                            </button>

                            <div class="row">
                                <div class="col-md-12 text-end">
                                    <table class="table table-sm table-borderless justify-content-end mb-3">
                                        <tbody>
                                            <tr>
                                                <td class="text-muted">الإجمالي:</td>
                                                <td class="fw-bold fs-5 text-primary" id="totalAmount">0 ر.س</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <a href="{{ route('invoices.index') }}" class="btn btn-secondary">إلغاء</a>
                                    <button type="submit" class="btn btn-success">حفظ الفاتورة</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Template for new item row -->
<template id="itemTemplate">
    <div class="row mb-3 item-row p-3 border rounded bg-light">
        <input type="hidden" name="items[__INDEX__][item_id]" class="item-id-input" value="">
        <div class="col-md-3">
            <label class="form-label small fw-bold">المنتج (الصنف)</label>
            <select name="items[__INDEX__][item_id_select]" class="form-select item-select" required
                    onchange="fetchWarehouses(this, __INDEX__); syncItemId(this)">
                <option value="">اختر المنتج</option>
                @foreach($items as $item)
                    <option value="{{ $item->id }}" data-price="{{ $item->price ?? 0 }}">
                        {{ $item->name_ar }} ({{ $item->category->name_ar ?? '' }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold">المخزن (مصدر الصرف)</label>
            <select name="items[__INDEX__][warehouse_id]" class="form-select warehouse-select" required>
                <option value="">اختر المنتج أولاً</option>
            </select>
            <small class="text-muted available-stock d-block mt-1">المتوفر: -</small>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold">السعر</label>
            <input type="number" name="items[__INDEX__][unit_price]" class="form-control price-input"
                   step="0.01" min="0" placeholder="السعر" readonly>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold">الكمية</label>
            <input type="number" name="items[__INDEX__][quantity]" class="form-control qty-input"
                   min="1" step="0.01" required>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <span class="text-muted small subtotal-display">0 ر.س</span>
        </div>
        <div class="col-md-1 d-flex align-items-end">
            <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.item-row').remove()">
                <i data-feather="trash-2"></i>
            </button>
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
        if (typeof feather !== 'undefined') feather.replace();
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

    if (typeof feather !== 'undefined') feather.replace();
</script>
@endpush
