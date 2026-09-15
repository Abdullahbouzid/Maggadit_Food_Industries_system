@extends('dashboard.partials.layout')

@section('title', 'إنشاء دفعة جديدة')

@section('content')
{{-- ===== Action Bar ===== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-plus-circle" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            إنشاء دفعة جديدة
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">أدخل بيانات الدفعة والمنتجات المرتبطة بها</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('production-batches.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> عودة
        </a>
    </div>
</div>

{{-- ===== Validation خطأs ===== --}}
@if($errors->any())
    <div class="nova-alert danger">
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

<form action="{{ route('production-batches.store') }}" method="POST" id="batchForm">
    @csrf

    {{-- ============================================================
         Section 1: Batch Information
         ============================================================ --}}
    <div class="nova-card nova-mb-4">
        <div class="nova-card-header">
            <h5 class="nova-fw-bold mb-0">
                <i class="fas fa-file-alt me-1"></i> معلومات الدفعة
            </h5>
        </div>
        <div class="nova-card-body">
            <div class="form-grid form-grid-3">
                <div class="nova-form-group">
                    <label class="nova-label">رقم الدفعة <span class="nova-text-danger">*</span></label>
                    <input type="text" name="batch_number"
                           class="nova-input @error('batch_number') nova-invalid @enderror"
                           value="{{ old('batch_number', $batchNumber) }}" required>
                    @error('batch_number') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                <div class="nova-form-group">
                    <label class="nova-label">تاريخ الإنتاج</label>
                    <input type="date" name="production_date"
                           class="nova-input @error('production_date') nova-invalid @enderror"
                           value="{{ old('production_date') }}">
                    @error('production_date') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                <div class="nova-form-group">
                    <label class="nova-label">تاريخ الانتهاء</label>
                    <input type="date" name="expiry_date"
                           class="nova-input @error('expiry_date') nova-invalid @enderror"
                           value="{{ old('expiry_date') }}">
                    @error('expiry_date') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                <div class="nova-form-group">
                    <label class="nova-label">الحالة <span class="nova-text-danger">*</span></label>
                    <select name="status" class="nova-select @error('status') nova-invalid @enderror" required>
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>نشطة</option>
                        <option value="expired" {{ old('status') == 'expired' ? 'selected' : '' }}>منتهية</option>
                        <option value="consumed" {{ old('status') == 'consumed' ? 'selected' : '' }}>مستهلكة</option>
                    </select>
                    @error('status') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                <div class="nova-form-group form-grid-span-2">
                    <label class="nova-label">ملاحظات</label>
                    <textarea name="notes" class="nova-input @error('notes') nova-invalid @enderror"
                              rows="1">{{ old('notes') }}</textarea>
                    @error('notes') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                <div class="nova-form-group form-grid-span-3">
                    <div class="nova-form-check status-checkbox-wrapper">
                        <input type="checkbox" name="is_active" value="1"
                               class="nova-form-check-input" id="is_active"
                               {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="nova-form-check-label" for="is_active">
                            <i class="fas fa-toggle-on"></i> نشط
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         Section 2: Products in Batch
         ============================================================ --}}
    <div class="nova-card nova-mb-4">
        <div class="nova-card-header nova-flex nova-justify-between nova-items-center">
            <h5 class="nova-fw-bold mb-0">
                <i class="fas fa-shopping-bag me-1"></i> المنتجات في الدفعة
            </h5>
            <button type="button" class="nova-btn nova-btn-primary nova-btn-sm" id="addItemRow">
                <i class="fas fa-plus"></i> إضافة منتج
            </button>
        </div>
        <div class="nova-card-body">
            <div id="itemsContainer" class="items-container"></div>

            <div id="itemsخطأ" class="nova-alert danger nova-mt-3" style="display: none;">
                <i class="fas fa-exclamation-triangle"></i>
                يجب إضافة منتج واحد على الأقل.
            </div>
        </div>
    </div>

    {{-- ============================================================
         Form إجراءات
         ============================================================ --}}
    <div class="form-actions">
        <button type="submit" class="nova-btn nova-btn-success nova-btn-lg">
            <i class="fas fa-save"></i> حفظ الدفعة
        </button>
        <a href="{{ route('production-batches.index') }}" class="nova-btn nova-btn-secondary nova-btn-lg">
            <i class="fas fa-times"></i> إلغاء
        </a>
    </div>
</form>
@endsection

@push('styles')
<style>
    /* ============================================================
       Form Grid System
       ============================================================ */
    .form-grid {
        display: grid;
        gap: 1.25rem;
    }

    .form-grid-3 {
        grid-template-columns: repeat(3, 1fr);
    }

    .form-grid-span-1 {
        grid-column: span 1;
    }

    .form-grid-span-2 {
        grid-column: span 2;
    }

    .form-grid-span-3 {
        grid-column: span 3;
    }

    .form-grid .nova-form-group {
        margin-bottom: 0;
    }

    /* ============================================================
       الحالة Checkbox
       ============================================================ */
    .status-checkbox-wrapper {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.875rem;
        background: #f0fdf4;
        border: 1.5px solid #86efac;
        border-radius: 10px;
        width: fit-content;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .status-checkbox-wrapper:hover {
        background: #dcfce7;
        border-color: #22c55e;
    }

    .status-checkbox-wrapper .nova-form-check-label {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        color: #166534;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        margin: 0;
    }

    /* ============================================================
       Items Container
       ============================================================ */
    .items-container {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    /* ============================================================
       Item Row - Grid Layout
       ============================================================ */
    .item-row {
        display: grid;
        grid-template-columns: 2fr 2fr 2fr 1.2fr auto;
        gap: 0.75rem;
        align-items: end;
        padding: 1rem;
        background: #f8f9fc;
        border: 1px solid #e3e6f0;
        border-radius: 0.5rem;
        transition: all 0.2s ease;
    }

    .item-row:hover {
        background: #f1f3f9;
        border-color: #d1d3e2;
    }

    .item-row .nova-form-group {
        margin-bottom: 0;
        min-width: 0;
    }

    .item-row .nova-form-col-auto {
        display: flex;
        align-items: end;
        justify-content: flex-end;
    }

    .item-row .remove-item-row {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        flex-shrink: 0;
    }

    /* ============================================================
       Form إجراءات
       ============================================================ */
    .form-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        padding: 1rem 0;
        border-top: 1px solid var(--nova-border);
    }

    /* ============================================================
       Responsive
       ============================================================ */
    @media (max-width: 1200px) {
        .item-row {
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }

        .item-row .nova-form-col-auto {
            grid-column: span 2;
            justify-content: flex-start;
        }

        .item-row .remove-item-row {
            width: 100%;
        }
    }

    @media (max-width: 992px) {
        .form-grid-3 {
            grid-template-columns: repeat(2, 1fr);
        }

        .form-grid-span-3 {
            grid-column: span 2;
        }
    }

    @media (max-width: 768px) {
        .form-grid-3 {
            grid-template-columns: 1fr;
        }

        .form-grid-span-2,
        .form-grid-span-3 {
            grid-column: span 1;
        }

        .item-row {
            grid-template-columns: 1fr;
        }

        .item-row .nova-form-col-auto {
            grid-column: span 1;
        }

        .form-actions {
            flex-direction: column;
        }

        .form-actions .nova-btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const items = @json($items);
        const warehouses = @json($warehouses ?? []);
        const itemsContainer = document.getElementById('itemsContainer');
        const itemsخطأ = document.getElementById('itemsخطأ');

        let rowCounter = 0;

        /* ============================================================
           Create Item Row HTML
           ============================================================ */
        function createItemRow(itemId = '', packId = '', quantity = '', warehouseId = '') {
            rowCounter++;
            const rowId = 'item-row-' + rowCounter;

            let itemOptions = '<option value="">اختر منتج</option>';
            items.forEach(item => {
                itemOptions += `<option value="${item.id}" ${item.id == itemId ? 'selected' : ''}>${item.name_en} (${item.code})</option>`;
            });

            let packOptions = '<option value="">اختر عبوة</option>';
            if (itemId) {
                const selectedItem = items.find(i => i.id == itemId);
                if (selectedItem && selectedItem.packs) {
                    selectedItem.packs.forEach(pack => {
                        packOptions += `<option value="${pack.id}" data-warehouse-id="${pack.warehouse_id || ''}" ${pack.id == packId ? 'selected' : ''}>${pack.display_text || pack.barcode}</option>`;
                    });
                }
            }

            let warehouseOptions = '<option value="">اختر مخزن</option>';
            warehouses.forEach(wh => {
                warehouseOptions += `<option value="${wh.id}" ${wh.id == warehouseId ? 'selected' : ''}>${wh.name_en || wh.name_ar}</option>`;
            });

            return `
                <div class="item-row" id="${rowId}">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-box item-label-icon"></i> المنتج
                        </label>
                        <select name="items[${rowCounter}][item_id]" class="nova-select item-select" required>
                            ${itemOptions}
                        </select>
                    </div>

                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-cube item-label-icon"></i> العبوة
                        </label>
                        <select name="items[${rowCounter}][item_pack_id]" class="nova-select pack-select">
                            ${packOptions}
                        </select>
                    </div>

                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-warehouse item-label-icon"></i> المخزن
                            <small class="nova-text-muted">(تلقائي)</small>
                        </label>
                        <select name="items[${rowCounter}][warehouse_id]" class="nova-select warehouse-select">
                            ${warehouseOptions}
                        </select>
                    </div>

                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-hashtag item-label-icon"></i> الكمية
                            <span class="nova-text-danger">*</span>
                        </label>
                        <input type="number" step="0.001" min="0.001"
                               name="items[${rowCounter}][quantity]"
                               class="nova-input quantity-input"
                               value="${quantity}" required>
                    </div>

                    <div class="nova-form-group nova-form-col-auto">
                        <button type="button"
                                class="nova-btn nova-btn-outline nova-btn-sm nova-text-danger remove-item-row"
                                title="حذف الصف">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            `;
        }

        /* ============================================================
           إضافة عنصر Row
           ============================================================ */
        document.getElementById('addItemRow').addEventListener('click', function() {
            itemsContainer.insertAdjacentHTML('beforeend', createItemRow());
            itemsخطأ.style.display = 'none';
        });

        /* ============================================================
           Remove Item Row (Event Delegation)
           ============================================================ */
        document.addEventListener('click', function(e) {
            const removeBtn = e.target.closest('.remove-item-row');
            if (removeBtn) {
                const row = removeBtn.closest('.item-row');
                if (row) {
                    row.remove();
                    if (document.querySelectorالكل('.item-row').length === 0) {
                        itemsContainer.insertAdjacentHTML('beforeend', createItemRow());
                    }
                }
            }
        });

        /* ============================================================
           Fetch Packs via AJAX when Item changes
           ============================================================ */
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('item-select')) {
                const itemId = e.target.value;
                const row = e.target.closest('.item-row');
                const packSelect = row.querySelector('.pack-select');
                const warehouseSelect = row.querySelector('.warehouse-select');

                packSelect.innerHTML = '<option value="">جاري التحميل...</option>';
                packSelect.disabled = true;

                if (itemId) {
                    fetch(`/production-batches/get-packs/${itemId}`)
                        .then(response => response.json())
                        .then(data => {
                            packSelect.innerHTML = '<option value="">اختر عبوة</option>';
                            data.forEach(pack => {
                                const option = document.createElement('option');
                                option.value = pack.id;
                                option.textContent = pack.display_text || pack.barcode;
                                option.setAttribute('data-warehouse-id', pack.warehouse_id || '');
                                packSelect.appendChild(option);
                            });
                            packSelect.disabled = false;
                            packSelect.dispatchEvent(new Event('change'));
                        })
                        .catch(error => {
                            console.error('خطأ في تحميل العبوات:', error);
                            packSelect.innerHTML = '<option value="">خطأ في التحميل</option>';
                            packSelect.disabled = false;
                        });
                } else {
                    packSelect.innerHTML = '<option value="">اختر عبوة</option>';
                    packSelect.disabled = false;
                }
            }
        });

        /* ============================================================
           Auto-fill Warehouse when Pack changes
           ============================================================ */
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('pack-select')) {
                const selectedOption = e.target.options[e.target.selectedIndex];
                const warehouseId = selectedOption.getAttribute('data-warehouse-id');
                const warehouseSelect = e.target.closest('.item-row').querySelector('.warehouse-select');

                if (warehouseId && warehouseSelect) {
                    const options = warehouseSelect.options;
                    for (let i = 0; i < options.length; i++) {
                        if (options[i].value == warehouseId) {
                            options[i].selected = true;
                            break;
                        }
                    }
                }
            }
        });

        /* ============================================================
           Form إرسال Validation
           ============================================================ */
        document.getElementById('batchForm').addEventListener('submit', function(e) {
            const rows = document.querySelectorالكل('.item-row');
            if (rows.length === 0) {
                e.preventافتراضي();
                itemsخطأ.style.display = 'flex';
                itemsخطأ.scrollIntoعرض({ behavior: 'smooth', block: 'center' });
            }
        });

        /* ============================================================
           Add افتراضي Row
           ============================================================ */
        itemsContainer.insertAdjacentHTML('beforeend', createItemRow());
    });
</script>
@endpush

@push('styles')
<style>
    /* Item label icons */
    .item-label-icon {
        color: #b7b9cc;
        font-size: 0.75rem;
        margin-right: 0.25rem;
    }

    /* Items error alert display fix */
    #itemsخطأ {
        display: none;
        align-items: center;
        gap: 0.5rem;
    }
</style>
@endpush