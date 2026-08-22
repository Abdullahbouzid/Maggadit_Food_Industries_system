@extends('dashboard.partials.layout')

@section('title', 'تعديل الفاتورة')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="edit-2" class="me-2"></i>
                            تعديل الفاتورة #{{ $invoice->invoice_number }}
                        </h1>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('invoices.update', $invoice) }}" method="POST" id="invoiceForm">
                    @csrf
                    @method('PUT')
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">العميل</label>
                                    <select name="customer_id" class="form-select" required>
                                        <option value="">اختر العميل</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ $invoice->customer_id == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">تاريخ الفاتورة</label>
                                    <input type="date" name="invoice_date" class="form-control"
                                           value="{{ $invoice->invoice_date->format('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">الحالة</label>
                                    <select name="status" class="form-select">
                                        <option value="pending" {{ $invoice->status == 'pending' ? 'selected' : '' }}>معلقة</option>
                                        <option value="completed" {{ $invoice->status == 'completed' ? 'selected' : '' }}>مكتملة</option>
                                        <option value="cancelled" {{ $invoice->status == 'cancelled' ? 'selected' : '' }}>ملغاة</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold">ملاحظات</label>
                                    <textarea name="notes" class="form-control" rows="2"
                                              placeholder="إضافة ملاحظة...">{{$invoice->notes}}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3 border-bottom pb-2">
                        <i data-feather="shopping-bag" class="me-2"></i>
                        الأصناف (المنتجات والمواقع والكميات)
                    </h5>

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
                            <div class="alert alert-info">
                                <i data-feather="info"></i>
                                لا توجد أصناف. أضف أول صنف باستخدام الزر أدناه.
                            </div>
                        @endif
                    </div>

                    <button type="button" class="btn btn-outline-primary mb-4" id="addItemBtn">
                        <i data-feather="plus"></i> إضافة صنف
                    </button>
                </form>

                <div class="row">
                    <div class="col-md-12 text-end">
                        <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-secondary">إلغاء</a>
                        <button type="submit" form="invoiceForm" class="btn btn-success">حفظ التعديلات</button>
                    </div>
                </div>
            </div>
        </main>
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
    if (typeof feather !== 'undefined') feather.replace();
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

if (typeof feather !== 'undefined') feather.replace();
</script>
@endpush
