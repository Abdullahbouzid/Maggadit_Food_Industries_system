@extends('dashboard.partials.layout')

@section('title', isset($warehouseMovement) ? 'تعديل الحركة' : 'إضافة حركة جديدة')

@section('content')
{{-- ===== Action Bar ===== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-{{ isset($warehouseMovement) ? 'edit' : 'plus-circle' }}"
               style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            {{ isset($warehouseMovement) ? 'تعديل الحركة #' . $warehouseMovement->id : 'إضافة حركة جديدة' }}
        </h2>
        <p class="nova-page-subtitle">أدخل بيانات الحركة بين المخازن</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('warehouse-movements.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> عودة
        </a>
    </div>
</div>

@if($errors->any())
    <div class="nova-alert danger nova-mt-3">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <strong>يوجد أخطاء في الإدخال:</strong>
            <ul class="nova-mb-0 nova-mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<form method="POST" action="{{ isset($warehouseMovement) ? route('warehouse-movements.update', $warehouseMovement) : route('warehouse-movements.store') }}" id="movementForm">
    @csrf
    @if(isset($warehouseMovement)) @method('PUT') @endif

    {{-- ===== Hidden input for selected items (populated by JS on submit) ===== --}}
    <div id="selectedItemsContainer"></div>

    <div class="nova-split-form">

        {{-- ===== Main Content ===== --}}
        <div class="nova-split-form-main">

            {{-- Available Items --}}
            <div id="availableItemsSection" class="nova-card"
                 style="display: {{ ($sourceWarehouseId ?? '') ? 'block' : 'none' }};">
                <div class="nova-card-header nova-flex nova-justify-between nova-items-center">
                    <h5 class="nova-fw-bold mb-0">
                        <i class="fas fa-box-open me-1"></i>
                        المنتجات المتوفرة
                    </h5>
                    <span class="nova-badge primary" id="itemsCountBadge">0 منتج</span>
                </div>
                <div class="nova-card-body nova-p-0">
                    <div class="nova-alert info nova-m-3" id="selectRowAlert" style="display: none;">
                        <i class="fas fa-info-circle"></i> حدد المنتجات وأدخل الكميات المراد نقلها.
                    </div>

                    <div class="nova-table-wrapper">
                        <table class="nova-table nova-table-compact">
                            <thead>
                                <tr>
                                    <th style="width: 32px;"></th>
                                    <th>#</th>
                                    <th>المنتج</th>
                                    <th>العبوة</th>
                                    <th>الدفعة</th>
                                    <th class="nova-text-center">الكمية المتوفرة</th>
                                    <th style="width: 140px;">الكمية المراد نقلها</th>
                                </tr>
                            </thead>
                            <tbody id="availableItemsBody">
                                @if(isset($availableItems) && $availableItems->count() > 0)
                                    @foreach($availableItems as $stock)
                                        @php
                                            $pack = $stock->batchItem->pack;
                                            $packTypeName = $pack?->packType?->name_ar ?? $pack?->packType?->name_en ?? 'قطعة';
                                            $packTypeCode = $pack?->packType?->code;
                                            $packCapacityValue = $pack?->packCapacity?->value;
                                            $packCapacityUom = $pack?->packCapacity?->uom?->code;
                                            $packTypeBadge = match(true) {
                                                $packTypeCode === 'BOX_3X3' || str_contains((string) $packTypeName, 'صندوق 3') => 'warning',
                                                $packTypeCode === 'BOX_4X4' || str_contains((string) $packTypeName, 'صندوق 4') => 'info',
                                                $packTypeCode === 'PIECE' || str_contains((string) $packTypeName, 'قطعة') => 'secondary',
                                                default => 'dark',
                                            };
                                        @endphp
                                        <tr class="available-item-row"
                                            data-batch-item-id="{{ $stock->batch_item_id }}"
                                            data-available="{{ $stock->quantity }}">
                                            <td class="nova-text-center">
                                                <input type="checkbox" name="selected_items[]" value="{{ $stock->batch_item_id }}"
                                                       class="nova-form-check-input item-checkbox">
                                            </td>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <strong>{{ $stock->batchItem->item->name_en ?? '' }}</strong>
                                                @if($stock->batchItem->item->code)
                                                    <br><small class="nova-text-muted">{{ $stock->batchItem->item->code }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="nova-badge {{ $packTypeBadge }}">{{ $packTypeName }}</span>
                                                @if($pack && $packCapacityValue)
                                                    <br><small class="nova-text-muted">
                                                        <i class="fas fa-hashtag" style="font-size: 0.7rem;"></i>
                                                        {{ $pack->barcode }} | {{ $packCapacityValue }} {{ $packCapacityUom }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td><code>{{ $stock->batchItem->batch?->batch_number ?? '-' }}</code></td>
                                            <td class="nova-text-center">
                                                <span class="nova-qty-pill">{{ number_format($stock->quantity, 3) }}</span>
                                            </td>
                                            <td>
                                                <input type="number" step="0.001" min="0" max="{{ $stock->quantity }}"
                                                       name="quantities[{{ $stock->batch_item_id }}]"
                                                       class="nova-input item-quantity" value="0" disabled>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <div id="noItemsMessage" class="nova-text-muted nova-text-center nova-py-4"
                         style="display: {{ (isset($availableItems) && $availableItems->count() > 0) ? 'none' : 'block' }};">
                        <i class="fas fa-info-circle"></i> لا توجد منتجات متوفرة.
                    </div>
                </div>
            </div>

            {{-- Movement Fields --}}
            <div class="nova-card">
                <div class="nova-card-header">
                    <h5 class="nova-fw-bold mb-0">
                        <i class="fas fa-edit me-1"></i> بيانات الحركة
                    </h5>
                </div>
                <div class="nova-card-body">
                    <div class="nova-form-grid-2">

                        <div class="nova-field nova-field-full" id="fromWarehouseWrapper">
                            <label class="nova-label">المخزن المصدر</label>
                            <select name="from_warehouse_id" id="from_warehouse_id"
                                    class="nova-select @error('from_warehouse_id') nova-invalid @enderror">
                                <option value="">-- اختر --</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}"
                                        {{ old('from_warehouse_id', $warehouseMovement->from_warehouse_id ?? $sourceWarehouseId ?? '') == $wh->id ? 'selected' : '' }}>
                                        {{ $wh->name_en }}
                                    </option>
                                @endforeach
                            </select>
                            @error('from_warehouse_id') <div class="nova-form-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="nova-field nova-field-full">
                            <label class="nova-label">
                                المخزن الهدف <span class="nova-text-danger">*</span>
                            </label>
                            <select name="to_warehouse_id" id="to_warehouse_id"
                                    class="nova-select @error('to_warehouse_id') nova-invalid @enderror" required>
                                <option value="">-- اختر --</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}"
                                        {{ old('to_warehouse_id', $warehouseMovement->to_warehouse_id ?? '') == $wh->id ? 'selected' : '' }}>
                                        {{ $wh->name_en }}
                                    </option>
                                @endforeach
                            </select>
                            @error('to_warehouse_id') <div class="nova-form-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="nova-field">
                            <label class="nova-label">تاريخ الحركة</label>
                            <input type="date" name="movement_date"
                                   class="nova-input @error('movement_date') nova-invalid @enderror"
                                   value="{{ old('movement_date', isset($warehouseMovement) && $warehouseMovement->movement_date ? $warehouseMovement->movement_date->format('Y-m-d') : request('movement_date', now()->toDateString())) }}">
                            @error('movement_date') <div class="nova-form-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="nova-field">
                            <label class="nova-text-transparent">&nbsp;</label>
                            <div class="nova-text-muted nova-fs-sm" id="selectedSummary">
                                <i class="fas fa-info-circle"></i> اختر منتجات من الجدول أعلاه.
                            </div>
                        </div>

                        <div class="nova-field nova-field-full">
                            <label class="nova-label">ملاحظات</label>
                            <textarea name="notes" class="nova-input @error('notes') nova-invalid @enderror"
                                      rows="3">{{ old('notes', $warehouseMovement->notes ?? '') }}</textarea>
                            @error('notes') <div class="nova-form-error">{{ $message }}</div> @enderror
                        </div>

                    </div>
                </div>
            </div>

        </div>

        {{-- ===== Sidebar ===== --}}
        <div class="nova-split-form-side">

            {{-- Movement Type Sidebar --}}
            <div class="nova-card">
                <div class="nova-card-header">
                    <h5 class="nova-fw-bold mb-0">
                        <i class="fas fa-exchange-alt me-1"></i> نوع الحركة
                    </h5>
                </div>
                <div class="nova-card-body">
                    <div class="nova-type-list">
                        @foreach($movementTypes as $key => $label)
                            <label class="nova-type-list-item">
                                <input type="radio" name="movement_type" value="{{ $key }}"
                                       {{ old('movement_type', $warehouseMovement->movement_type ?? '') == $key ? 'checked' : '' }}
                                       required>
                                <div class="nova-type-list-inner">
                                    <i class="fas fa-{{ $key === 'in' ? 'sign-in-alt' : ($key === 'transfer' ? 'exchange-alt' : 'undo') }}"></i>
                                    <span>{{ $label }}</span>
                                    <i class="fas fa-check-circle nova-type-check"></i>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('movement_type') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- Tips Card --}}
            <div class="nova-card nova-tips-card">
                <div class="nova-card-header">
                    <h5 class="nova-fw-bold mb-0">
                        <i class="fas fa-lightbulb me-1"></i> نصائح سريعة
                    </h5>
                </div>
                <div class="nova-card-body">
                    <ul class="nova-tips-list">
                        <li>
                            <i class="fas fa-check"></i>
                            اختر المخزن المصدر أولاً لتحميل المنتجات المتوفرة.
                        </li>
                        <li>
                            <i class="fas fa-check"></i>
                            حدد الصناديق بجوار المنتجات التي تريد نقلها.
                        </li>
                        <li>
                            <i class="fas fa-check"></i>
                            أدخل الكمية لكل منتج محدد — لا يمكن تجاوز الكمية المتوفرة.
                        </li>
                        <li>
                            <i class="fas fa-check"></i>
                            حدد المخزن الهدف قبل الحفظ.
                        </li>
                    </ul>
                </div>
            </div>

            {{-- إجراءات --}}
            <div class="nova-card nova-actions-sticky">
                <div class="nova-card-header">
                    <h5 class="nova-fw-bold mb-0">
                        <i class="fas fa-cog me-1"></i> الإجراءات
                    </h5>
                </div>
                <div class="nova-card-body">
                    <div class="nova-action-stack">
                        <button type="submit" class="nova-btn nova-btn-success">
                            <i class="fas fa-save"></i>
                            {{ isset($warehouseMovement) ? 'تحديث' : 'حفظ' }}
                        </button>
                        <a href="{{ route('warehouse-movements.index') }}" class="nova-btn nova-btn-secondary">إلغاء</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('styles')
<style>
    /* ===== Split Layout ===== */
    .nova-split-form {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 1.25rem;
        align-items: start;
    }

    @media (max-width: 992px) {
        .nova-split-form {
            grid-template-columns: 1fr;
        }
    }

    .nova-split-form-main {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .nova-split-form-side {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        position: sticky;
        top: 1rem;
    }

    /* ===== Type List ===== */
    .nova-type-list {
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
    }

    .nova-type-list-item {
        cursor: pointer;
        position: relative;
        display: block;
    }

    .nova-type-list-item input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .nova-type-list-inner {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        padding: 0.85rem 1rem;
        background: var(--nova-surface-1, #fff);
        border: 2px solid var(--nova-border, #e5e7eb);
        border-radius: 10px;
        transition: all .2s;
        font-weight: 600;
        font-size: 0.88rem;
        position: relative;
    }

    .nova-type-list-inner i:first-child {
        color: #6b7280;
        font-size: 1.05rem;
    }

    .nova-type-check {
        position: absolute;
        left: 12px;
        color: #10b981;
        opacity: 0;
        transition: opacity .2s;
    }

    .nova-type-list-item:hover .nova-type-list-inner {
        border-color: rgba(59,130,246,.4);
    }

    .nova-type-list-item input:checked ~ .nova-type-list-inner {
        border-color: #3b82f6;
        background: linear-gradient(135deg, rgba(59,130,246,.08), rgba(139,92,246,.08));
        color: #3b82f6;
    }

    .nova-type-list-item input:checked ~ .nova-type-list-inner i:first-child {
        color: #3b82f6;
    }

    .nova-type-list-item input:checked ~ .nova-type-list-inner .nova-type-check {
        opacity: 1;
    }

    /* ===== Form Grid ===== */
    .nova-form-grid-2 {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.25rem;
    }

    .nova-field {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    .nova-field-full {
        grid-column: 1 / -1;
    }

    /* ===== Tips ===== */
    .nova-tips-card {
        background: linear-gradient(135deg, rgba(245,158,11,.04), rgba(139,92,246,.04));
    }

    .nova-tips-list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .nova-tips-list li {
        display: flex;
        gap: 0.6rem;
        font-size: 0.82rem;
        color: #6b7280;
        line-height: 1.6;
    }

    .nova-tips-list li i {
        color: #10b981;
        flex-shrink: 0;
        margin-top: 3px;
    }

    /* ===== إجراءات ===== */
    .nova-action-stack {
        display: flex;
        flex-direction: column;
        gap: 0.55rem;
    }

    .nova-action-stack .nova-btn {
        width: 100%;
        justify-content: center;
    }

    /* ===== Compact Table ===== */
    .nova-p-0 { padding: 0 !important; }
    .nova-m-3 { margin: 0.75rem !important; }

    .nova-table-compact th,
    .nova-table-compact td {
        padding: 0.6rem 0.8rem !important;
        font-size: 0.85rem;
    }

    .nova-qty-pill {
        display: inline-block;
        padding: 3px 12px;
        background: rgba(34,197,94,.1);
        color: #22c55e;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.82rem;
    }

    .selected-row {
        background: linear-gradient(90deg, rgba(59,130,246,.08), transparent) !important;
        border-end: 3px solid #3b82f6;
    }

    @media (max-width: 768px) {
        .nova-form-grid-2 {
            grid-template-columns: 1fr;
        }
        .nova-split-form-side {
            position: static;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form                 = document.getElementById('movementForm');
        const fromWarehouseSelect  = document.getElementById('from_warehouse_id');
        const availableItemsSection = document.getElementById('availableItemsSection');
        const availableItemsBody   = document.getElementById('availableItemsBody');
        const noItemsMessage       = document.getElementById('noItemsMessage');
        const itemsCountBadge      = document.getElementById('itemsCountBadge');
        const selectRowAlert       = document.getElementById('selectRowAlert');
        const selectedSummary      = document.getElementById('selectedSummary');
        const selectedItemsContainer = document.getElementById('selectedItemsContainer');

        fromWarehouseSelect.addEventListener('change', function () {
            const warehouseId = this.value;
            if (!warehouseId) {
                availableItemsSection.style.display = 'none';
                selectRowAlert.style.display = 'none';
                return;
            }
            availableItemsSection.style.display = 'block';
            selectRowAlert.style.display = 'block';
            availableItemsBody.innerHTML = '<tr><td colspan="7" class="nova-text-center nova-py-4"><span class="nova-text-muted">جاري التحميل...</span></td></tr>';
            noItemsMessage.style.display = 'none';

            fetch(`/warehouse-movements/available-items/${warehouseId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length === 0) {
                        availableItemsBody.innerHTML = '';
                        noItemsMessage.style.display = 'block';
                        itemsCountBadge.textContent = '0 منتج';
                        return;
                    }

                    itemsCountBadge.textContent = `${data.length} منتج`;
                    availableItemsBody.innerHTML = '';
                    data.forEach((item, index) => {
                        const packTypeBadge = getPackTypeBadge(item.pack_type_code, item.pack_type_name);
                        const row = document.createElement('tr');
                        row.className = 'available-item-row';
                        row.setAttribute('data-batch-item-id', item.batch_item_id);
                        row.setAttribute('data-available', item.available_quantity);

                        row.innerHTML = `
                            <td class="nova-text-center">
                                <input type="checkbox" name="selected_items[]" value="${item.batch_item_id}"
                                       class="nova-form-check-input item-checkbox" disabled>
                            </td>
                            <td>${index + 1}</td>
                            <td>
                                <strong>${escapeHtml(item.item_name)}</strong>
                                ${item.item_code ? '<br><small class="nova-text-muted">' + escapeHtml(item.item_code) + '</small>' : ''}
                            </td>
                            <td>
                                <span class="nova-badge ${packTypeBadge}">${escapeHtml(item.pack_type_name)}</span>
                                ${item.pack_barcode ? '<br><small class="nova-text-muted"><i class="fas fa-hashtag"></i> ' + escapeHtml(item.pack_barcode) + (item.pack_capacity_value ? ` | ${item.pack_capacity_value} ${item.pack_capacity_uom || ''}` : '') + '</small>' : ''}
                            </td>
                            <td><code>${escapeHtml(item.batch_number || '-')}</code></td>
                            <td class="nova-text-center"><span class="nova-qty-pill">${parseFloat(item.available_quantity).toFixed(3)}</span></td>
                            <td>
                                <input type="number" step="0.001" min="0" max="${parseFloat(item.available_quantity).toFixed(3)}"
                                       name="quantities[${item.batch_item_id}]"
                                       class="nova-input item-quantity" value="0" disabled>
                            </td>
                        `;
                        availableItemsBody.appendChild(row);
                    });

                    enableCheckboxInteractivity();
                })
                .catch(error => {
                    console.error('خطأ:', error);
                    availableItemsBody.innerHTML = '<tr><td colspan="7" class="nova-text-center nova-text-danger">خطأ في تحميل البيانات</td></tr>';
                });
        });

        function enableCheckboxInteractivity() {
            // Enable checkboxes and quantity inputs
            document.querySelectorالكل('.item-checkbox').forEach(cb => cb.disabled = false);
            document.querySelectorالكل('.item-quantity').forEach(qi => qi.disabled = false);

            // Row click toggles checkbox (but not when clicking checkbox or quantity input directly)
            document.querySelectorالكل('.available-item-row').forEach(row => {
                row.addEventListener('click', function (e) {
                    const cb = this.querySelector('.item-checkbox');
                    const qty = this.querySelector('.item-quantity');
                    if (e.target.closest('.item-checkbox') || e.target.closest('.item-quantity')) {
                        toggleQuantity(qty, cb);
                        return;
                    }
                    cb.checked = !cb.checked;
                    toggleQuantity(qty, cb);
                });
            });

            // Checkbox change
            document.querySelectorالكل('.item-checkbox').forEach(cb => {
                cb.addEventListener('change', function () {
                    const qty = this.closest('tr').querySelector('.item-quantity');
                    toggleQuantity(qty, this);
                    updateSelectedCount();
                });
            });
        }

        function toggleQuantity(qtyInput, checkbox) {
            const available = parseFloat(checkbox.closest('tr').getAttribute('data-available'));
            if (checkbox.checked) {
                qtyInput.value = available;
            } else {
                qtyInput.value = '0';
            }
        }

        function updateSelectedCount() {
            const checked = document.querySelectorالكل('.item-checkbox:checked');
            const count = checked.length;
            if (count === 0) {
                itemsCountBadge.textContent = '0 منتج';
                itemsCountBadge.className = 'nova-badge primary';
                selectedSummary.innerHTML = '<i class="fas fa-info-circle"></i> اختر منتجات من الجدول أعلاه.';
            } else {
                itemsCountBadge.textContent = `${count} محدد`;
                itemsCountBadge.className = 'nova-badge success';
                selectedSummary.innerHTML = `<i class="fas fa-check-circle nova-text-success"></i> تم اختيار ${count} منتج.`;
            }
        }

        function getPackTypeBadge(code, name) {
            if (code === 'BOX_3X3' || (name && name.includes('صندوق 3'))) return 'warning';
            if (code === 'BOX_4X4' || (name && name.includes('صندوق 4'))) return 'info';
            if (code === 'BOX_6X6' || (name && name.includes('صندوق 6'))) return 'primary';
            if (code === 'PIECE' || (name && (name.includes('قطعة') || name.includes('PIECE')))) return 'secondary';
            return 'dark';
        }

        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        // Update quantity max when quantity input changes
        document.addEventListener('input', function (e) {
            if (e.target.classList.contains('item-quantity')) {
                const max = parseFloat(e.target.getAttribute('max')) || Infinity;
                const val = parseFloat(e.target.value);
                if (!isNaN(val) && val > max) {
                    e.target.value = max;
                }
                if (val < 0) {
                    e.target.value = 0;
                }
            }
        });

        // Form submit: collect selected items and build hidden inputs
        form.addEventListener('submit', function (e) {
            const checked = document.querySelectorالكل('.item-checkbox:checked');
            if (checked.length === 0) {
                e.preventافتراضي();
                const alert = document.createElement('div');
                alert.className = 'nova-alert danger nova-mt-3';
                alert.innerHTML = '<i class="fas fa-exclamation-triangle"></i><strong>يرجى اختيار منتج واحد على الأقل.</strong>';
                form.insertBefore(alert, form.firstChild);
                return;
            }

            // Clear previous hidden inputs
            selectedItemsContainer.innerHTML = '';

            // Create hidden inputs for each selected item
            let itemIndex = 0;
            checked.forEach(cb => {
                const batchItemId = cb.value;
                const row = cb.closest('tr');
                const qtyInput = row.querySelector('.item-quantity');
                const quantity = qtyInput ? qtyInput.value : '0';

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = `items[${itemIndex}][batch_item_id]`;
                idInput.value = batchItemId;
                selectedItemsContainer.appendChild(idInput);

                const qtyHidden = document.createElement('input');
                qtyHidden.type = 'hidden';
                qtyHidden.name = `items[${itemIndex}][quantity]`;
                qtyHidden.value = quantity;
                selectedItemsContainer.appendChild(qtyHidden);

                itemIndex++;
            });
        });

        // Initialize checkbox interactivity if items were pre-loaded
        if (fromWarehouseSelect.value) {
            if (document.querySelectorالكل('.item-checkbox').length > 0) {
                enableCheckboxInteractivity();
            }
        }
    });
</script>
@endpush
