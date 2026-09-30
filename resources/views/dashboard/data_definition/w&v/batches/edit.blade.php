@extends('dashboard.partials.layout')

@section('title', 'تعديل الدفعة: ' . $batch->batch_number)

@section('content')
{{-- ===== Action Bar ===== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-edit" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            تعديل الدفعة: {{ $batch->batch_number }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">تعديل بيانات الدفعة والمنتجات المرتبطة بها</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('production-batches.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> عودة
        </a>
    </div>
</div>

{{-- ===== Validation Errors ===== --}}
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

<form action="{{ route('production-batches.update', $batch) }}" method="POST" id="batchForm">
    @csrf
    @method('PUT')

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
                           value="{{ old('batch_number', $batch->batch_number) }}" required>
                    @error('batch_number') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                <div class="nova-form-group">
                    <label class="nova-label">تاريخ الإنتاج</label>
                    <input type="date" name="production_date"
                           class="nova-input @error('production_date') nova-invalid @enderror"
                           value="{{ old('production_date', $batch->production_date?->format('Y-m-d')) }}">
                    @error('production_date') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                <div class="nova-form-group">
                    <label class="nova-label">تاريخ الانتهاء</label>
                    <input type="date" name="expiry_date"
                           class="nova-input @error('expiry_date') nova-invalid @enderror"
                           value="{{ old('expiry_date', $batch->expiry_date?->format('Y-m-d')) }}">
                    @error('expiry_date') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                <div class="nova-form-group">
                    <label class="nova-label">الحالة <span class="nova-text-danger">*</span></label>
                    <select name="status" class="nova-select @error('status') nova-invalid @enderror" required>
                        <option value="active" {{ old('status', $batch->status) == 'active' ? 'selected' : '' }}>نشطة</option>
                        <option value="expired" {{ old('status', $batch->status) == 'expired' ? 'selected' : '' }}>منتهية</option>
                        <option value="consumed" {{ old('status', $batch->status) == 'consumed' ? 'selected' : '' }}>مستهلكة</option>
                    </select>
                    @error('status') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                <div class="nova-form-group form-grid-span-2">
                    <label class="nova-label">ملاحظات</label>
                    <textarea name="notes" class="nova-input @error('notes') nova-invalid @enderror"
                              rows="2">{{ old('notes', $batch->notes) }}</textarea>
                    @error('notes') <div class="nova-form-error">{{ $message }}</div> @enderror
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
            <div id="itemsContainer" class="items-container">
                @foreach($batch->batchItems as $index => $item)
                    @php
                        $iteration = $loop->iteration;
                        $isDispatched = $item->dispatched_quantity > 0;
                    @endphp
                    <div class="item-row {{ $isDispatched ? 'item-row-dispatched' : '' }}"
                         id="item-row-{{ $iteration }}"
                         data-batch-item-id="{{ $item->id }}"
                         data-dispatched="{{ $item->dispatched_quantity ?? 0 }}">

                        {{-- Product --}}
                        <div class="nova-form-group">
                            <label class="nova-label">
                                <i class="fas fa-box item-label-icon"></i> المنتج
                            </label>
                            <select name="items[{{ $iteration }}][item_id]"
                                    class="nova-select item-select" required>
                                <option value="">اختر منتج</option>
                                @foreach($items as $product)
                                    <option value="{{ $product->id }}"
                                        {{ old("items.$iteration.item_id", $item->item_id) == $product->id ? 'selected' : '' }}>
                                        {{ $product->name_en }} ({{ $product->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Pack --}}
                        <div class="nova-form-group">
                            <label class="nova-label">
                                <i class="fas fa-cube item-label-icon"></i> العبوة
                            </label>
                            <select name="items[{{ $iteration }}][item_pack_id]" class="nova-select pack-select">
                                <option value="">اختر عبوة</option>
                                @if($item->item)
                                    @foreach($item->item->packs as $pack)
                                        <option value="{{ $pack->id }}"
                                            {{ old("items.$iteration.item_pack_id", $item->item_pack_id) == $pack->id ? 'selected' : '' }}>
                                            {{ $pack->barcode }} (كمية: {{ $pack->quantity_in_pack }})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <input type="hidden" name="items[{{ $iteration }}][id]" value="{{ $item->id }}">
                        </div>

                        {{-- Quantity --}}
                        <div class="nova-form-group">
                            <label class="nova-label">
                                <i class="fas fa-hashtag item-label-icon"></i> الكمية
                                <span class="nova-text-danger">*</span>
                            </label>
                            <input type="number" step="0.001" min="0.001"
                                   name="items[{{ $iteration }}][quantity]"
                                   class="nova-input quantity-input"
                                   value="{{ old("items.$iteration.quantity", $item->quantity) }}" required>

                            @if($isDispatched)
                                <div class="dispatch-warning">
                                    <i class="fas fa-info-circle"></i>
                                    تم توزيع {{ number_format($item->dispatched_quantity, 3) }} من هذه الكمية
                                </div>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="nova-form-group nova-form-col-auto">
                            <button type="button"
                                    class="nova-btn nova-btn-outline nova-btn-sm nova-text-danger remove-item-row"
                                    {{ $isDispatched ? 'disabled title="لا يمكن حذف منتج تم توزيعه"' : '' }}>
                                <i class="fas fa-{{ $isDispatched ? 'lock' : 'trash-alt' }}"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div id="itemsError" class="nova-alert danger nova-mt-3" style="display: none;">
                <i class="fas fa-exclamation-triangle"></i>
                يجب إضافة منتج واحد على الأقل.
            </div>
        </div>
    </div>

    {{-- ============================================================
         Form Actions
         ============================================================ --}}
    <div class="form-actions">
        <button type="submit" class="nova-btn nova-btn-success nova-btn-lg">
            <i class="fas fa-save"></i> تحديث الدفعة
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
        grid-template-columns: 2fr 2fr 1.5fr auto;
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

    /* Dispatched row style */
    .item-row-dispatched {
        background: #fffbeb;
        border-color: #fde68a;
    }

    .item-row-dispatched:hover {
        background: #fef3c7;
        border-color: #fcd34d;
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

    .item-row .remove-item-row:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* ============================================================
       Item Label Icons
       ============================================================ */
    .item-label-icon {
        color: #b7b9cc;
        font-size: 0.75rem;
        margin-right: 0.25rem;
    }

    /* ============================================================
       Dispatch Warning
       ============================================================ */
    .dispatch-warning {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        margin-top: 0.4rem;
        padding: 0.35rem 0.6rem;
        background: #fef3c7;
        border: 1px solid #fde68a;
        border-radius: 0.35rem;
        font-size: 0.75rem;
        color: #92400e;
        font-weight: 500;
    }

    .dispatch-warning i {
        color: #d97706;
    }

    /* ============================================================
       Items Error
       ============================================================ */
    #itemsError {
        display: none;
        align-items: center;
        gap: 0.5rem;
    }

    /* ============================================================
       Form Actions
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
        const itemsContainer = document.getElementById('itemsContainer');
        const itemsError = document.getElementById('itemsError');

        let rowCounter = {{ $batch->batchItems->count() }};

        /* ============================================================
           Create Item Row HTML
           ============================================================ */
        function createItemRow(itemId = '', packId = '', quantity = '') {
            rowCounter++;
            const uid = Date.now() + Math.floor(Math.random() * 1000);
            const rowId = 'item-row-' + uid;

            let itemOptions = '<option value="">اختر منتج</option>';
            items.forEach(item => {
                const selected = item.id == itemId ? 'selected' : '';
                itemOptions += `<option value="${item.id}" ${selected}>${item.name_en} (${item.code})</option>`;
            });

            let packOptions = '<option value="">اختر عبوة</option>';
            if (itemId) {
                const selectedItem = items.find(i => i.id == itemId);
                if (selectedItem && selectedItem.packs) {
                    selectedItem.packs.forEach(pack => {
                        const selectedPack = pack.id == packId ? 'selected' : '';
                        packOptions += `<option value="${pack.id}" ${selectedPack}>${pack.barcode} (كمية: ${pack.quantity_in_pack})</option>`;
                    });
                }
            }

            return `
                <div class="item-row" id="${rowId}">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-box item-label-icon"></i> المنتج
                        </label>
                        <select name="items[${uid}][item_id]" class="nova-select item-select" required>
                            ${itemOptions}
                        </select>
                    </div>

                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-cube item-label-icon"></i> العبوة
                        </label>
                        <select name="items[${uid}][item_pack_id]" class="nova-select pack-select">
                            ${packOptions}
                        </select>
                    </div>

                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-hashtag item-label-icon"></i> الكمية
                            <span class="nova-text-danger">*</span>
                        </label>
                        <input type="number" step="0.001" min="0.001"
                               name="items[${uid}][quantity]"
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
           Add Item Row
           ============================================================ */
        document.getElementById('addItemRow').addEventListener('click', function() {
            itemsContainer.insertAdjacentHTML('beforeend', createItemRow());
            itemsError.style.display = 'none';
        });

        /* ============================================================
           Remove Item Row (Event Delegation)
           ============================================================ */
        document.addEventListener('click', function(e) {
            const removeBtn = e.target.closest('.remove-item-row');
            if (!removeBtn || removeBtn.disabled) return;

            const row = removeBtn.closest('.item-row');
            if (!row) return;

            // Prevent removing dispatched rows (safety check)
            const dispatched = parseFloat(row.getAttribute('data-dispatched') || '0');
            if (dispatched > 0) {
                alert('لا يمكن حذف منتج تم توزيعه.');
                return;
            }

            row.remove();

            if (document.querySelectorAll('.item-row').length === 0) {
                itemsContainer.insertAdjacentHTML('beforeend', createItemRow());
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
                                option.textContent = pack.display_text;
                                packSelect.appendChild(option);
                            });
                            packSelect.disabled = false;
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
           Form Submit Validation
           ============================================================ */
        document.getElementById('batchForm').addEventListener('submit', function(e) {
            const rows = document.querySelectorAll('.item-row');
            if (rows.length === 0) {
                e.preventDefault();
                itemsError.style.display = 'flex';
                itemsError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });
</script>
@endpush