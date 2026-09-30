@extends('dashboard.partials.layout')

@section('title', 'تعديل الفاتورة')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-edit" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            تعديل الفاتورة #{{ $invoice->invoice_number }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Update invoice details and items</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('invoices.show', $invoice) }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-eye"></i> View
        </a>
        <a href="{{ route('invoices.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

<form action="{{ route('invoices.update', $invoice) }}" method="POST" id="invoiceForm">
    @csrf
    @method('PUT')
    <div class="nova-card nova-mb-4">
        <div class="nova-card-body">
            <div class="nova-row nova-gap-3">
                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">العميل</label>
                        <select name="customer_id" class="nova-select" required>
                            <option value="">اختر العميل</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ $invoice->customer_id == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">تاريخ الفاتورة</label>
                        <input type="date" name="invoice_date" class="nova-input"
                               value="{{ $invoice->invoice_date->format('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">الحالة</label>
                        <select name="status" class="nova-select">
                            <option value="pending" {{ $invoice->status == 'pending' ? 'selected' : '' }}>معلقة</option>
                            <option value="completed" {{ $invoice->status == 'completed' ? 'selected' : '' }}>مكتملة</option>
                            <option value="cancelled" {{ $invoice->status == 'cancelled' ? 'selected' : '' }}>ملغاة</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="nova-row nova-mt-3">
                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">ملاحظات</label>
                        <textarea name="notes" class="nova-input" rows="2"
                                  placeholder="إضافة ملاحظة...">{{$invoice->notes}}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="nova-flex nova-items-center nova-gap-2 nova-mb-3" style="border-bottom: 1px solid var(--nova-border); padding-bottom: 0.75rem;">
        <i class="fas fa-shopping-bag"></i>
        <h5 class="nova-fw-bold" style="margin: 0;">الأصناف (المنتجات والمواقع والكميات)</h5>
    </div>

    <div id="itemsContainer">
        @if($invoice->items->isNotEmpty())
            @foreach($invoice->items as $index => $invItem)
                @include('dashboard.invoices.partials.item-row', [
                    'itemIndex' => $index,
                    'itemId' => $invItem->item_id,
                    'itemName' => $invItem->item->name_ar ?? "Item #{$invItem->item_id}",
                    'selectedWarehouseId' => $invItem->warehouse_id,
                    'quantity' => $invItem->quantity,
                    'unitPrice' => $invItem->unit_price,
                ])
            @endforeach
        @else
            <div class="nova-alert info">
                <i class="fas fa-info-circle"></i>
                لا توجد أصناف. أضف أول صنف باستخدام الزر أدناه.
            </div>
        @endif
    </div>

    <button type="button" class="nova-btn nova-btn-outline nova-mb-4" id="addItemBtn">
        <i class="fas fa-plus"></i> إضافة صنف
    </button>
</form>

<div class="nova-row">
    <div class="nova-col nova-text-end">
        <a href="{{ route('invoices.show', $invoice) }}" class="nova-btn nova-btn-outline">إلغاء</a>
        <button type="submit" form="invoiceForm" class="nova-btn nova-btn-primary">حفظ التعديلات</button>
    </div>
</div>

@include('dashboard.invoices.partials.item-template')

@endsection

@push('scripts')
<script>
let itemIndex = {{ $invoice->items->count() }};

document.getElementById('addItemBtn').addEventListener('click', function() {
    const template = document.getElementById('itemTemplate').innerHTML;
    const html = template.replace(/__INDEX__/g, itemIndex++);
    document.getElementById('itemsContainer').insertAdjacentHTML('beforeend', html);
});

function fetchWarehouses(selectElement, index) {
    const itemId = selectElement.value;
    const row = selectElement.closest('.item-row');
    const warehouseSelect = row.querySelector('.warehouse-select');
    const stockDisplay = row.querySelector('.available-stock');

    warehouseSelect.innerHTML = '<option value="">جارٍ التحميل...</option>';

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
            e.target.closest('.item-row').querySelector('.qty-input').removeAttribute('max');
        }
    }
});

document.getElementById('itemsContainer').addEventListener('change', function(e) {
    if (e.target.classList.contains('item-select')) {
        const itemId = e.target.value;
        const row = e.target.closest('.item-row');
        const nameDisplay = row.querySelector('.product-name-display');
        if (nameDisplay && itemId) {
            const selected = e.target.options[e.target.selectedIndex];
            nameDisplay.value = selected.textContent;
        }
    }
});
</script>
@endpush
