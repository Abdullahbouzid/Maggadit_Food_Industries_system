<template id="itemTemplate">
    <div class="row mb-3 item-row p-3 border rounded bg-light">
        <div class="col-md-4">
            <label class="form-label small fw-bold">المنتج (الصنف)</label>
            <input type="hidden" name="items[__INDEX__][item_id]" class="item-id-input" value="">
            <div class="input-group">
                <select name="items[__INDEX__][item_id_select]" class="form-select item-select" required
                        onchange="fetchWarehouses(this, __INDEX__); syncItemId(this)">
                    <option value="">اختر المنتج</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}" data-name="{{ $item->name_ar }}">
                            {{ $item->name_ar }} ({{ $item->category->name_ar ?? '' }})
                        </option>
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
        <div class="col-md-1 d-flex align-items-end">
            <span class="text-muted small">subtotal</span>
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
