@php($item = null;
    foreach ($items as $i) {
        if ($i->id == $itemId) {
            $item = $i;
            break;
        }
    }
    $price = $item ? ($item->price) : $unitPrice;
    $itemNameStr = $itemName ?? ($item->name_ar ?? "Product #{$itemId}");
    $itemCategory = $item ? ($item->category->name_ar ?? '') : '';)

<div class="nova-card nova-mb-3 item-row" style="background: var(--nova-surface-2);">
    <div class="nova-card-body">
        <div class="nova-row nova-gap-3">
            <div class="nova-col">
                <div class="nova-form-group">
                    <label class="nova-label nova-fs-sm nova-fw-bold">المنتج (الصنف)</label>
                    <input type="hidden" name="items[{{ $itemIndex }}][item_id]" class="item-id-input" value="{{ $itemId }}">
                    <div class="nova-flex nova-gap-2">
                        <select name="items[{{ $itemIndex }}][item_id_select]" class="nova-select item-select" required
                                onchange="fetchWarehouses(this, {{ $itemIndex }}); syncItemId(this)" style="flex: 1;">
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
                        <button type="button" class="nova-btn nova-btn-danger nova-btn-sm" onclick="this.closest('.item-row').remove()">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="nova-col">
                <div class="nova-form-group">
                    <label class="nova-label nova-fs-sm nova-fw-bold">المخزن (مصدر الصرف)</label>
                    <select name="items[{{ $itemIndex }}][warehouse_id]" class="nova-select warehouse-select" required>
                        <option value="">اختر المنتج أولاً</option>
                        @if($selectedWarehouseId)
                            @php($selectedWarehouse = \App\Models\Warehouse::find($selectedWarehouseId);)
                            @if($selectedWarehouse)
                                <option value="{{ $selectedWarehouse->id }}" selected>
                                    {{ $selectedWarehouse->name }}
                                </option>
                            @endif
                        @endif
                    </select>
                    <small class="nova-help-text available-stock">جارٍ التحميل من المخازن...</small>
                </div>
            </div>

            <div class="nova-col">
                <div class="nova-form-group">
                    <label class="nova-label nova-fs-sm nova-fw-bold">السعر</label>
                    <input type="number" name="items[{{ $itemIndex }}][unit_price]" class="nova-input price-input"
                           step="0.01" min="0" value="{{ $unitPrice ?? 0 }}" readonly>
                </div>
            </div>

            <div class="nova-col">
                <div class="nova-form-group">
                    <label class="nova-label nova-fs-sm nova-fw-bold">الكمية</label>
                    <input type="number" name="items[{{ $itemIndex }}][quantity]" class="nova-input qty-input"
                           min="1" step="0.01" value="{{ $quantity }}" required>
                </div>
            </div>

            <div class="nova-col nova-flex nova-items-end">
                <span class="nova-text-muted nova-fs-sm subtotal-display">0 ر.س</span>
            </div>
        </div>
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
