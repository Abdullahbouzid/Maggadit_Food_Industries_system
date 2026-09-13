<template id="itemTemplate">
    <div class="nova-card nova-mb-3 item-row" style="background: var(--nova-surface-2);">
        <div class="nova-card-body">
            <div class="nova-row nova-gap-3">
                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label nova-fs-sm nova-fw-bold">المنتج (الصنف)</label>
                        <input type="hidden" name="items[__INDEX__][item_id]" class="item-id-input" value="">
                        <div class="nova-flex nova-gap-2">
                            <select name="items[__INDEX__][item_id_select]" class="nova-select item-select" required
                                    onchange="fetchWarehouses(this, __INDEX__); syncItemId(this)" style="flex: 1;">
                                <option value="">اختر المنتج</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}" data-name="{{ $item->name_ar }}">
                                        {{ $item->name_ar }} ({{ $item->category->name_ar ?? '' }})
                                    </option>
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
            </div>
        </div>
    </div>
</template>

<script>
function syncItemId(selectEl) {
    const row = selectEl.closest('.item-row');
    const hiddenInput = row.querySelector('.item-id-input');
    hiddenInput.value = selectEl.value;

    const priceInput = row.querySelector('.price-input');
    if (selectEl.value) {
        const option = selectEl.options[selectEl.selectedIndex];
        const price = option.dataset.price || 0;
        priceInput.value = price;
        priceInput.readOnly = false;
        updateSubtotal(row);
    } else {
        priceInput.value = '';
    }
}

function updateSubtotal(row) {
    const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
    const price = parseFloat(row.querySelector('.price-input').value) || 0;
    const subtotal = (qty * price).toFixed(2);
    const subtotalCell = row.querySelector('.subtotal-display');
    if (subtotalCell) subtotalCell.textContent = subtotal + ' ر.س';
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
</script>
