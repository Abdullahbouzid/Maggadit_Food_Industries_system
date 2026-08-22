@php
    $item = null;
    foreach ($items as $i) {
        if ($i->id == $itemId) {
            $item = $i;
            break;
        }
    }
    $price = $item ? ($item->price) : $unitPrice;
    $itemNameStr = $itemName ?? ($item->name_ar ?? "Product #{$itemId}");
    $itemCategory = $item ? ($item->category->name_ar ?? '') : '';
@endphp

<div class="row mb-3 item-row p-3 border rounded bg-light">
    <div class="col-md-4">
        <label class="form-label small fw-bold">المنتج (الصنف)</label>
        <input type="hidden" name="items[{{ $itemIndex }}][item_id]" class="item-id-input" value="{{ $itemId }}">
        <div class="input-group">
            <select name="items[{{ $itemIndex }}][item_id_select]" class="form-select item-select" required
                    onchange="fetchWarehouses(this, {{ $itemIndex }}); syncItemId(this)">
                <option value="{{ $itemId }}" selected data-price="{{ $item ? $item->price : $unitPrice }}">
                    {{ $itemNameStr }} ({{ $itemCategory }})
                </option>
                @foreach($items as $availItem)
                    @if($availItem->id != $itemId)
                        <option value="{{ $availItem->id }}" data-price="{{ $availItem->price }}">
                            {{ $availItem->name_ar }} ({{ $availItem->category->name_ar ?? '' }})
                        </option>
                    @endif
                @endforeach
            </select>
            <button type="button" class="btn btn-outline-secondary"
                    onclick="this.closest('.item-row').remove()">
                <i data-feather="trash-2"></i>
            </button>
        </div>
    </div>

    <div class="col-md-3">
        <label class="form-label small fw-bold">المخزن (مصدر الصرف)</label>
        <select name="items[{{ $itemIndex }}][warehouse_id]" class="form-select warehouse-select" required>
            <option value="">اختر المنتج أولاً</option>
            @if($selectedWarehouseId)
                @php $selectedWarehouse = \App\Models\Warehouse::find($selectedWarehouseId); @endphp
                @if($selectedWarehouse)
                    <option value="{{ $selectedWarehouse->id }}" selected>
                        {{ $selectedWarehouse->name }}
                    </option>
                @endif
            @endif
        </select>
        <small class="text-muted available-stock d-block mt-1">جارٍ التحميل من المخازن...</small>
    </div>

    <div class="col-md-2">
        <label class="form-label small fw-bold">السعر</label>
        <input type="number" name="items[{{ $itemIndex }}][unit_price]" class="form-control price-input"
               step="0.01" min="0" value="{{ $unitPrice ?? 0 }}" readonly>
    </div>

    <div class="col-md-2">
        <label class="form-label small fw-bold">الكمية</label>
        <input type="number" name="items[{{ $itemIndex }}][quantity]" class="form-control qty-input"
               min="1" step="0.01" value="{{ $quantity }}" required>
    </div>

    <div class="col-md-1 d-flex align-items-end">
        <span class="text-muted small subtotal-display">0 ر.س</span>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const row = document.querySelector('[data-item-index="{{ $itemIndex }}"], .item-row');
    if (row) {
        const qtyInput = row.querySelector('.qty-input');
        const priceInput = row.querySelector('.price-input');
        const stockDisplay = row.querySelector('.available-stock');

        if (qtyInput && priceInput) {
            updateSubtotalLocal(qtyInput, priceInput, row);
        }

        syncItemIdAndWarehouses({{ $itemId }}, {{ $itemIndex }}, {{ $selectedWarehouseId ?? 'null' }});
    }
});

function syncItemIdAndWarehouses(itemId, index, selectedWarehouseId) {
    const selectEl = document.querySelector('select[name="items\\[' + index + '\\]\\[item_id_select\\]"]');
    if (selectEl) {
        const hiddenInput = selectEl.closest('.item-row').querySelector('.item-id-input');
        hiddenInput.value = itemId;

        fetch(`/invoices/get-warehouses/${itemId}`)
            .then(res => res.json())
            .then(data => {
                const whSelect = selectEl.closest('.item-row').querySelector('.warehouse-select');
                const stockDisplay = selectEl.closest('.item-row').querySelector('.available-stock');
                whSelect.innerHTML = '<option value="">اختر المخزن</option>';
                data.forEach(wh => {
                    const opt = document.createElement('option');
                    opt.value = wh.id;
                    opt.textContent = `${wh.name || wh.name_en} (المتوفر: ${parseFloat(wh.quantity).toFixed(2)})`;
                    opt.dataset.stock = wh.quantity;
                    if (selectedWarehouseId && wh.id == selectedWarehouseId) {
                        opt.selected = true;
                    }
                    whSelect.appendChild(opt);
                });
                const selectedOption = whSelect.options[whSelect.selectedIndex];
                if (selectedOption && selectedOption.dataset.stock) {
                    stockDisplay.innerText = `المتوفر: ${selectedOption.dataset.stock}`;
                }
            });
    }
}

function updateSubtotalLocal(qtyInput, priceInput, row) {
    const qty = parseFloat(qtyInput.value) || 0;
    const price = parseFloat(priceInput.value) || 0;
    const subtotal = (qty * price).toFixed(2);
    const cell = row.querySelector('.subtotal-display');
    if (cell) cell.textContent = subtotal + ' ر.س';
}
</script>
