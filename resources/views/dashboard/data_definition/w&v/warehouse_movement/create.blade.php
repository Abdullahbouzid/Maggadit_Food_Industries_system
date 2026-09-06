@extends('dashboard.partials.layout')

@section('title', isset($warehouseMovement) ? 'تعديل الحركة' : 'إضافة حركة جديدة')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold">
                            <i data-feather="{{ isset($warehouseMovement) ? 'edit' : 'plus' }}" class="align-middle me-2"></i>
                            {{ isset($warehouseMovement) ? 'تعديل الحركة #' . $warehouseMovement->id : 'إضافة حركة جديدة' }}
                        </h1>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <form method="POST" action="{{ isset($warehouseMovement) ? route('warehouse-movements.update', $warehouseMovement) : route('warehouse-movements.store') }}">
                            @csrf
                            @if(isset($warehouseMovement)) @method('PUT') @endif

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">نوع الحركة <span class="text-danger">*</span></label>
                                    <select name="movement_type" class="form-select @error('movement_type') is-invalid @enderror" required>
                                        <option value="">-- اختر --</option>
                                        @foreach($movementTypes as $key => $label)
                                            <option value="{{ $key }}" {{ old('movement_type', $warehouseMovement->movement_type ?? '') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('movement_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-12">
                                    <div id="availableItemsSection" style="display: {{ ($sourceWarehouseId ?? '') ? 'block' : 'none' }};">
                                        <div class="card border-0 shadow-sm rounded-4 mb-3">
                                            <div class="card-header bg-light border-0 py-3 d-flex justify-content-between align-items-center">
                                                <h6 class="fw-bold mb-0">
                                                    <i data-feather="package"></i>
                                                    المنتجات المتوفرة في المخزن المصدر (Item Packs)
                                                </h6>
                                                <span class="badge bg-primary" id="itemsCountBadge">0</span>
                                            </div>
                                            <div class="card-body">
                                                <div class="alert alert-info" id="selectRowAlert" style="display: none;">
                                                    <i data-feather="info"></i> اضغط على صف لتحديد المنتج والكمية المراد نقلها.
                                                </div>
                                                <div class="table-responsive">
                                                    <table class="table table-hover align-middle" id="availableItemsTable">
                                                        <thead>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>المنتج</th>
                                                                <th>الرمز</th>
                                                                <th>العبوة (Pack)</th>
                                                                <th>الدفعة</th>
                                                                <th>الكمية المتوفرة</th>
                                                                <th>اختيار</th>
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
                                                                    <tr class="available-item-row" data-batch-item-id="{{ $stock->batch_item_id }}" data-available="{{ $stock->quantity }}" style="cursor: pointer;">
                                                                        <td>{{ $loop->iteration }}</td>
                                                                        <td>
                                                                            <strong>{{ $stock->batchItem->item->name_en ?? '' }}</strong>
                                                                            @if($stock->batchItem->item->name_ar)
                                                                                <br><small class="text-muted">{{ $stock->batchItem->item->name_ar }}</small>
                                                                            @endif
                                                                        </td>
                                                                        <td><code>{{ $stock->batchItem->item->code ?? '' }}</code></td>
                                                                        <td>
                                                                            <span class="badge bg-{{ $packTypeBadge }}">{{ $packTypeName }}</span>
                                                                            @if($pack)
                                                                                <br><small class="text-muted">
                                                                                    <i data-feather="hash" style="width: 10px;"></i>
                                                                                    {{ $pack->barcode }}
                                                                                    @if($packCapacityValue)
                                                                                        | {{ $packCapacityValue }} {{ $packCapacityUom }}
                                                                                    @endif
                                                                                </small>
                                                                            @endif
                                                                        </td>
                                                                        <td><code>{{ $stock->batchItem->batch?->batch_number ?? '-' }}</code></td>
                                                                        <td><span class="badge bg-success">{{ number_format($stock->quantity, 3) }}</span></td>
                                                                        <td>
                                                                            <button type="button" class="btn btn-sm btn-primary select-item-btn">
                                                                                <i data-feather="check"></i> تحديد
                                                                            </button>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div id="noItemsMessage" class="text-muted text-center py-3" style="display: {{ (isset($availableItems) && $availableItems->count() > 0) ? 'none' : 'block' }};">
                                                    <i data-feather="info"></i> لا توجد منتجات متوفرة في هذا المخزن.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">المنتج (Batch Item) <span class="text-danger">*</span></label>
                                    <select name="batch_item_id" id="batch_item_id" class="form-select @error('batch_item_id') is-invalid @enderror" required>
                                        <option value="">-- اختر من الجدول أعلاه --</option>
                                        @if(isset($sourceWarehouseId) && $sourceWarehouseId && isset($availableItems))
                                            @foreach($availableItems as $stock)
                                                <option value="{{ $stock->batch_item_id }}" {{ old('batch_item_id', $warehouseMovement->batch_item_id ?? '') == $stock->batch_item_id ? 'selected' : '' }}>
                                                    {{ $stock->batchItem->item->name_en ?? '' }} - {{ $stock->batchItem->batch?->batch_number ?? '' }} ({{ number_format($stock->quantity, 3) }})
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('batch_item_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">المخزن المصدر</label>
                                    <select name="from_warehouse_id" id="from_warehouse_id" class="form-select @error('from_warehouse_id') is-invalid @enderror">
                                        <option value="">-- اختر --</option>
                                        @foreach($warehouses as $wh)
                                            <option value="{{ $wh->id }}" {{ old('from_warehouse_id', $warehouseMovement->from_warehouse_id ?? $sourceWarehouseId ?? '') == $wh->id ? 'selected' : '' }}>{{ $wh->name_en }}</option>
                                        @endforeach
                                    </select>
                                    @error('from_warehouse_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">المخزن الهدف <span class="text-danger">*</span></label>
                                    <select name="to_warehouse_id" class="form-select @error('to_warehouse_id') is-invalid @enderror" required>
                                        <option value="">-- اختر --</option>
                                        @foreach($warehouses as $wh)
                                            <option value="{{ $wh->id }}" {{ old('to_warehouse_id', $warehouseMovement->to_warehouse_id ?? '') == $wh->id ? 'selected' : '' }}>{{ $wh->name_en }}</option>
                                        @endforeach
                                    </select>
                                    @error('to_warehouse_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">الكمية <span class="text-danger">*</span></label>
                                    <input type="number" step="0.001" min="0.001" name="quantity" id="quantity_input" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity', $warehouseMovement->quantity ?? '') }}" required>
                                    <small class="text-muted" id="quantityHint">حدد منتجاً أولاً لمعرفة الكمية المتوفرة</small>
                                    @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">تاريخ الحركة</label>
                                    <input type="date" name="movement_date" class="form-control @error('movement_date') is-invalid @enderror" value="{{ old('movement_date', isset($warehouseMovement) && $warehouseMovement->movement_date ? $warehouseMovement->movement_date->format('Y-m-d') : now()->toDateString()) }}">
                                    @error('movement_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">ملاحظات</label>
                                    <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $warehouseMovement->notes ?? '') }}</textarea>
                                    @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                                        <i data-feather="save"></i> {{ isset($warehouseMovement) ? 'تحديث' : 'حفظ' }}
                                    </button>
                                    <a href="{{ route('warehouse-movements.index') }}" class="btn btn-secondary btn-lg shadow-sm ms-2">إلغاء</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof feather !== 'undefined') {
            feather.replace();
        }

        const fromWarehouseSelect = document.getElementById('from_warehouse_id');
        const availableItemsSection = document.getElementById('availableItemsSection');
        const availableItemsBody = document.getElementById('availableItemsBody');
        const noItemsMessage = document.getElementById('noItemsMessage');
        const itemsCountBadge = document.getElementById('itemsCountBadge');
        const batchItemSelect = document.getElementById('batch_item_id');
        const quantityInput = document.getElementById('quantity_input');
        const quantityHint = document.getElementById('quantityHint');
        const selectRowAlert = document.getElementById('selectRowAlert');

        // عند تغيير المخزن المصدر
        fromWarehouseSelect.addEventListener('change', function() {
            const warehouseId = this.value;
            if (!warehouseId) {
                availableItemsSection.style.display = 'none';
                selectRowAlert.style.display = 'none';
                return;
            }
            availableItemsSection.style.display = 'block';
            selectRowAlert.style.display = 'block';
            availableItemsBody.innerHTML = '<tr><td colspan="7" class="text-center py-3"><i class="spinner-border spinner-border-sm"></i> جاري التحميل...</td></tr>';
            noItemsMessage.style.display = 'none';

            fetch(`/warehouse-movements/available-items/${warehouseId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length === 0) {
                        availableItemsBody.innerHTML = '';
                        noItemsMessage.style.display = 'block';
                        itemsCountBadge.textContent = '0';
                        updateBatchItemSelect([]);
                        return;
                    }

                    itemsCountBadge.textContent = data.length;
                    availableItemsBody.innerHTML = '';
                    data.forEach((item, index) => {
                        const packTypeBadge = getPackTypeBadge(item.pack_type_code, item.pack_type_name);
                        const row = document.createElement('tr');
                        row.className = 'available-item-row';
                        row.setAttribute('data-batch-item-id', item.batch_item_id);
                        row.setAttribute('data-available', item.available_quantity);
                        row.style.cursor = 'pointer';

                        row.innerHTML = `
                            <td>${index + 1}</td>
                            <td>
                                <strong>${escapeHtml(item.item_name)}</strong>
                                ${item.item_name_ar ? '<br><small class="text-muted">' + escapeHtml(item.item_name_ar) + '</small>' : ''}
                            </td>
                            <td><code>${escapeHtml(item.item_code)}</code></td>
                            <td>
                                <span class="badge bg-${packTypeBadge}">${escapeHtml(item.pack_type_name)}</span>
                                <br><small class="text-muted">
                                    <i data-feather="hash" style="width: 10px;"></i>
                                    ${escapeHtml(item.pack_barcode)}
                                    ${item.pack_capacity_value ? '| ' + item.pack_capacity_value + ' ' + (item.pack_capacity_uom || '') : ''}
                                </small>
                            </td>
                            <td><code>${escapeHtml(item.batch_number || '-')}</code></td>
                            <td><span class="badge bg-success">${parseFloat(item.available_quantity).toFixed(3)}</span></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary select-item-btn">
                                    <i data-feather="check"></i> تحديد
                                </button>
                            </td>
                        `;
                        availableItemsBody.appendChild(row);
                    });

                    // إضافة مستمعي الأحداث للصفوف
                    attachRowListeners();

                    if (typeof feather !== 'undefined') {
                        feather.replace();
                    }

                    // تحديث قائمة المنتجات في الـ select
                    updateBatchItemSelect(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    availableItemsBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">خطأ في تحميل البيانات</td></tr>';
                });
        });

        // عند اختيار صف
        function attachRowListeners() {
            document.querySelectorAll('.available-item-row').forEach(row => {
                row.addEventListener('click', function() {
                    const batchItemId = this.getAttribute('data-batch-item-id');
                    const available = parseFloat(this.getAttribute('data-available'));

                    // تحديد العنصر في القائمة المنسدلة
                    batchItemSelect.value = batchItemId;

                    // تحديث الكمية المتاحة
                    quantityInput.setAttribute('max', available);
                    quantityInput.value = available;
                    quantityHint.innerHTML = `الكمية المتوفرة: <strong class="text-success">${available.toFixed(3)}</strong>`;

                    // إبراز الصف المحدد
                    document.querySelectorAll('.available-item-row').forEach(r => r.classList.remove('table-active'));
                    this.classList.add('table-active');

                    // الانتقال لحقل الكمية
                    quantityInput.focus();
                });
            });
        }

        // تشغيل المستمعين عند التحميل إذا كان هناك مخزن محدد مسبقاً
        if (fromWarehouseSelect.value) {
            attachRowListeners();
        }

        function getPackTypeBadge(code, name) {
            if (code === 'BOX_3X3' || (name && name.includes('صندوق 3'))) return 'warning';
            if (code === 'BOX_4X4' || (name && name.includes('صندوق 4'))) return 'info';
            if (code === 'BOX_6X6' || (name && name.includes('صندوق 6'))) return 'primary';
            if (code === 'PIECE' || (name && (name.includes('قطعة') || name.includes('PIECE')))) return 'secondary';
            return 'dark';
        }

        function updateBatchItemSelect(data) {
            // الاحتفاظ بالقيمة المحددة حالياً
            const currentVal = batchItemSelect.value;
            batchItemSelect.innerHTML = '<option value="">-- اختر من الجدول أعلاه --</option>';
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.batch_item_id;
                option.textContent = `${item.item_name} - ${item.batch_number || ''} (${parseFloat(item.available_quantity).toFixed(3)})`;
                batchItemSelect.appendChild(option);
            });
            if (currentVal) {
                batchItemSelect.value = currentVal;
            }
        }

        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        // التحقق من أن الكمية المدخلة لا تتجاوز المتاحة
        quantityInput.addEventListener('input', function() {
            const max = parseFloat(this.getAttribute('max')) || Infinity;
            const val = parseFloat(this.value);
            if (!isNaN(val) && val > max) {
                this.value = max;
            }
        });
    });
</script>
@endpush