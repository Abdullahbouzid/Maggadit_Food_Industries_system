@extends('dashboard.partials.layout')

@section('title', 'إنشاء دفعة جديدة')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="plus-circle" class="align-middle me-2"></i>
                            إنشاء دفعة جديدة
                        </h1>
                        <p class="text-muted">أدخل بيانات الدفعة والمنتجات المرتبطة بها</p>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i data-feather="alert-circle" class="me-2"></i>
                        <strong>يوجد أخطاء في الإدخال:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('production-batches.store') }}" method="POST" id="batchForm">
                    @csrf

                    <div class="row">
                        <!-- بيانات الدفعة -->
                        <div class="col-lg-6">
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-light border-0 py-3">
                                    <h6 class="fw-bold mb-0"><i data-feather="info"></i> معلومات الدفعة</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="batch_number" class="form-label fw-semibold">رقم الدفعة <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('batch_number') is-invalid @enderror" id="batch_number" name="batch_number" value="{{ old('batch_number', $batchNumber) }}" required>
                                        @error('batch_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="production_date" class="form-label fw-semibold">تاريخ الإنتاج</label>
                                        <input type="date" class="form-control @error('production_date') is-invalid @enderror" id="production_date" name="production_date" value="{{ old('production_date') }}">
                                        @error('production_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="expiry_date" class="form-label fw-semibold">تاريخ الانتهاء</label>
                                        <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" id="expiry_date" name="expiry_date" value="{{ old('expiry_date') }}">
                                        @error('expiry_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="status" class="form-label fw-semibold">الحالة <span class="text-danger">*</span></label>
                                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>نشطة</option>
                                            <option value="expired" {{ old('status') == 'expired' ? 'selected' : '' }}>منتهية</option>
                                            <option value="consumed" {{ old('status') == 'consumed' ? 'selected' : '' }}>مستهلكة</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="notes" class="form-label fw-semibold">ملاحظات</label>
                                        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- سلة المنتجات -->
                        <div class="col-lg-6">
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-light border-0 py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold mb-0"><i data-feather="shopping-bag"></i> المنتجات في الدفعة</h6>
                                    <button type="button" class="btn btn-sm btn-primary" id="addItemRow">
                                        <i data-feather="plus"></i> إضافة منتج
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div id="itemsContainer">
                                        <!-- سيتم إضافة الصفوف ديناميكياً -->
                                    </div>
                                    <div id="itemsError" class="text-danger mt-2" style="display:none;">يجب إضافة منتج واحد على الأقل.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-success btn-lg shadow-sm">
                                <i data-feather="save"></i> حفظ الدفعة
                            </button>
                            <a href="{{ route('production-batches.index') }}" class="btn btn-secondary btn-lg shadow-sm ms-2">إلغاء</a>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        feather.replace();

        // بيانات المنتجات والعبوات (من الـ Controller)
         const items = @json($items);
        const warehouses = @json($warehouses ?? \App\Models\Warehouse::where('is_active', true)->get());

        let rowCounter = 0;

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

            const html = `
                <div class="row g-3 align-items-end mb-3 item-row" id="${rowId}">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">المنتج</label>
                        <select name="items[${rowCounter}][item_id]" class="form-select item-select" required>
                            ${itemOptions}
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">العبوة</label>
                        <select name="items[${rowCounter}][item_pack_id]" class="form-select pack-select">
                            ${packOptions}
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">المخزن <span class="text-info">(يتم تعبئته تلقائياً)</span></label>
                        <select name="items[${rowCounter}][warehouse_id]" class="form-select warehouse-select">
                            ${warehouseOptions}
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">الكمية <span class="text-danger">*</span></label>
                        <input type="number" step="0.001" min="0.001" name="items[${rowCounter}][quantity]" class="form-control quantity-input" value="${quantity}" required>
                    </div>
                    <div class="col-md-1 d-flex justify-content-end">
                        <button type="button" class="btn btn-outline-danger remove-item-row" style="margin-top: 24px;">
                            <i data-feather="trash-2"></i>
                        </button>
                    </div>
                </div>
            `;
            return html;
        }

        function addDefaultRow() {
            const container = document.getElementById('itemsContainer');
            const rowHtml = createItemRow();
            container.insertAdjacentHTML('beforeend', rowHtml);
            if (window.feather) feather.replace();
        }

        document.getElementById('addItemRow').addEventListener('click', function() {
            const container = document.getElementById('itemsContainer');
            const rowHtml = createItemRow();
            container.insertAdjacentHTML('beforeend', rowHtml);
            if (window.feather) feather.replace();
            document.getElementById('itemsError').style.display = 'none';
        });

        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-item-row')) {
                const row = e.target.closest('.item-row');
                if (row) {
                    row.remove();
                    if (document.querySelectorAll('.item-row').length === 0) {
                        const rowHtml = createItemRow();
                        document.getElementById('itemsContainer').insertAdjacentHTML('beforeend', rowHtml);
                        if (window.feather) feather.replace();
                    }
                }
            }
        });

        // Fetch packs via AJAX when item changes
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('item-select')) {
                const itemId = e.target.value;
                const packSelect = e.target.closest('.item-row').querySelector('.pack-select');
                const warehouseSelect = e.target.closest('.item-row').querySelector('.warehouse-select');
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

        // Auto-fill warehouse when pack changes
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

        document.getElementById('batchForm').addEventListener('submit', function(e) {
            const rows = document.querySelectorAll('.item-row');
            if (rows.length === 0) {
                e.preventDefault();
                document.getElementById('itemsError').style.display = 'block';
            }
        });

        addDefaultRow();

        document.addEventListener('DOMNodeInserted', function(e) {
            if (e.target.closest && e.target.closest('.item-row')) {
                if (window.feather) feather.replace();
            }
        });
    });
</script>
@endpush