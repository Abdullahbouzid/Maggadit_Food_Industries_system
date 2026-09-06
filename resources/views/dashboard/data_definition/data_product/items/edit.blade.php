{{-- resources/views/dashboard/data_definition/data_product/items/edit.blade.php --}}
@extends('dashboard.partials.layout')

@section('title', 'Edit Item')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold">
                            <i data-feather="edit"></i> Edit Item: {{ $item->code }}
                        </h1>
                    </div>
                </div>

                <form action="{{ route('items.update', $item) }}" method="POST" enctype="multipart/form-data" id="itemForm">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-8">
                            {{-- Basic Information Card --}}
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-white border-0 pt-4">
                                    <h5 class="fw-bold mb-0">Basic Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Code <span class="text-danger">*</span></label>
                                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $item->code) }}" required>
                                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Sequence (optional)</label>
                                            <input type="text" name="sequence" class="form-control" value="{{ old('sequence', $item->sequence) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Name (English) <span class="text-danger">*</span></label>
                                            <input type="text" name="name_en" class="form-control @error('name_en') is-invalid @enderror" value="{{ old('name_en', $item->name_en) }}" required>
                                            @error('name_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Name (Arabic) <span class="text-danger">*</span></label>
                                            <input type="text" name="name_ar" class="form-control @error('name_ar') is-invalid @enderror" value="{{ old('name_ar', $item->name_ar) }}" required>
                                            @error('name_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Definition</label>
                                            <textarea name="definition" class="form-control" rows="3">{{ old('definition', $item->definition) }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Classification Card --}}
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-white border-0 pt-4">
                                    <h5 class="fw-bold mb-0">Classification</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Item Type</label>
                                            <select name="item_type_id" class="form-select">
                                                <option value="">-- None --</option>
                                                @foreach($itemTypes as $type)
                                                    <option value="{{ $type->id }}" {{ old('item_type_id', $item->item_type_id) == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Category</label>
                                            <select name="category_id" class="form-select">
                                                <option value="">-- None --</option>
                                                @foreach($categories as $cat)
                                                    <option value="{{ $cat->id }}" {{ old('category_id', $item->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Brand</label>
                                            <select name="brand_id" class="form-select">
                                                <option value="">-- None --</option>
                                                @foreach($brands as $brand)
                                                    <option value="{{ $brand->id }}" {{ old('brand_id', $item->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Model</label>
                                            <select name="model_id" class="form-select">
                                                <option value="">-- None --</option>
                                                @foreach($models as $model)
                                                    <option value="{{ $model->id }}" {{ old('model_id', $item->model_id) == $model->id ? 'selected' : '' }}>{{ $model->name_en }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Supplier</label>
                                            <select name="supplier_id" class="form-select">
                                                <option value="">-- None --</option>
                                                @foreach($suppliers as $supplier)
                                                    <option value="{{ $supplier->id }}" {{ old('supplier_id', $item->supplier_id) == $supplier->id ? 'selected' : '' }}>{{ $supplier->vendor_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Tolerances & Stock Card --}}
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-white border-0 pt-4">
                                    <h5 class="fw-bold mb-0">Tolerances & Stock</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label">Shelf Life (days)</label>
                                            <input type="number" name="shelf_life_time" class="form-control" value="{{ old('shelf_life_time', $item->shelf_life_time) }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Van Stock Tolerance</label>
                                            <input type="number" step="any" name="van_stock_tolerance" class="form-control" value="{{ old('van_stock_tolerance', $item->van_stock_tolerance) }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Storage Tolerance</label>
                                            <input type="number" step="any" name="storage_tolerance" class="form-control" value="{{ old('storage_tolerance', $item->storage_tolerance) }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Weight Tolerance</label>
                                            <input type="number" step="any" name="weight_tolerance" class="form-control" value="{{ old('weight_tolerance', $item->weight_tolerance) }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Low Stock Qty</label>
                                            <input type="number" step="any" name="low_stock_qty" class="form-control" value="{{ old('low_stock_qty', $item->low_stock_qty) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Packs Card --}}
                            {{-- Packs Card - Using Pack Definitions --}}
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-white border-0 pt-4 d-flex justify-content-between align-items-center">
                                    <h5 class="fw-bold mb-0">Packs (Pack Definitions)</h5>
                                    <a href="{{ route('pack-definitions.create') }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                        <i data-feather="plus-circle"></i> إضافة تعريف باكج جديد
                                    </a>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info mb-3">
                                        <i data-feather="info"></i> اختر تعريفات الباكجات المطلوبة لهذا المنتج من الجدول أدناه.
                                    </div>

                                    <div class="table-responsive mb-3">
                                        <table class="table table-hover align-middle" id="packDefinitionsTable">
                                            <thead>
                                                <tr>
                                                    <th style="width: 40px;"><input type="checkbox" id="selectAllPacks" class="form-check-input"></th>
                                                    <th>الاسم</th>
                                                    <th>نوع الباكج</th>
                                                    <th>السعة</th>
                                                    <th>الكمية/عبوة</th>
                                                    <th>الأبعاد</th>
                                                    <th>الوزن</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $currentPackDefIds = $item->packs->pluck('pack_type_id')->toArray();
                                                @endphp
                                                @foreach($packDefinitions as $packDef)
                                                    @php
                                                        $packTypeName = $packDef->packType?->name_ar ?? $packDef->packType?->name_en ?? '-';
                                                        $packCapacityValue = $packDef->packCapacity?->value ?? '-';
                                                        $packCapacityUom = $packDef->packCapacity?->uom?->code ?? '';
                                                        $isSelected = $item->packs->contains('pack_type_id', $packDef->pack_type_id);
                                                    @endphp
                                                    <tr class="pack-definition-row" data-pack-definition-id="{{ $packDef->id }}" style="cursor: pointer;">
                                                        <td>
                                                            <input type="checkbox" class="form-check-input pack-definition-checkbox" value="{{ $packDef->id }}" {{ $isSelected ? 'checked' : '' }}>
                                                        </td>
                                                        <td>
                                                            <strong>{{ $packDef->name_ar ?? $packDef->name_en ?? $packDef->name ?? '#' . $packDef->id }}</strong>
                                                            @if($packDef->description)
                                                                <br><small class="text-muted">{{ Str::limit($packDef->description, 50) }}</small>
                                                            @endif
                                                        </td>
                                                        <td><span class="badge bg-info">{{ $packTypeName }}</span></td>
                                                        <td>{{ $packCapacityValue }} {{ $packCapacityUom }}</td>
                                                        <td><span class="badge bg-primary">{{ $packDef->quantity_in_pack }}</span></td>
                                                        <td>
                                                            @if($packDef->height || $packDef->width || $packDef->depth)
                                                                {{ $packDef->height }}×{{ $packDef->width }}×{{ $packDef->depth }}
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td>{{ $packDef->weight ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div id="noPackDefinitionsMessage" class="text-muted text-center py-3" style="display: {{ $packDefinitions->count() > 0 ? 'none' : 'block' }};">
                                        <i data-feather="info"></i> لا توجد تعريفات باكجات. قم بإنشاء تعريف باكج أولاً.
                                    </div>

                                    {{-- Selected packs summary --}}
                                    <div id="selectedPacksSummary" class="mt-3" style="display: none;">
                                        <h6 class="fw-bold mb-2">الباكجات المحددة للمنتج:</h6>
                                        <div id="selectedPacksList"></div>
                                    </div>

                                    {{-- Add Selected Button --}}
                                    <button type="button" id="addSelectedPacksBtn" class="btn btn-primary btn-sm mb-2 w-100">
                                        <i data-feather="plus-circle"></i> أضف الباكجات المحددة
                                    </button>
                                </div>
                            </div>

                            {{-- Additional Images Card --}}
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-white border-0 pt-4 d-flex justify-content-between align-items-center">
                                    <h5 class="fw-bold mb-0">Additional Images</h5>
                                    <button type="button" class="btn btn-sm btn-primary" id="addImageBtn">+ Add Image</button>
                                </div>
                                <div class="card-body">
                                    <div id="existingImagesContainer" class="row g-3 mb-3">
                                        @foreach($item->images as $image)
                                            <div class="col-md-3 existing-image-item" data-image-id="{{ $image->id }}">
                                                <div class="position-relative">
                                                    <img src="{{ asset('uploads/'.$image->image_path) }}" class="img-thumbnail w-100" style="height: 150px; object-fit: cover;">
                                                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 remove-existing-image">×</button>
                                                    <input type="hidden" name="deleted_images[]" value="{{ $image->id }}" disabled>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div id="newImagesContainer"></div>
                                    <div class="mt-2 text-muted small">You can upload new images (JPEG, PNG, max 2MB each). Existing images can be removed by clicking the × button.</div>
                                </div>
                            </div>
                        </div>

                        {{-- Right Sidebar: Main Image & Flags --}}
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-white border-0 pt-4">
                                    <h5 class="fw-bold mb-0">Main Image</h5>
                                </div>
                                <div class="card-body text-center">
                                    @if($item->image)
                                        <img src="{{ asset('uploads/'.$item->image) }}" class="img-fluid rounded mb-3" style="max-height: 200px;">
                                    @else
                                        <div class="bg-light rounded p-3 mb-3">No image</div>
                                    @endif
                                    <input type="file" name="image" class="form-control" accept="image/*">
                                    <div class="form-text">Leave empty to keep current image.</div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-white border-0 pt-4">
                                    <h5 class="fw-bold mb-0">Status & Flags</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" name="is_inactive" value="1" class="form-check-input" id="is_inactive" {{ old('is_inactive', $item->is_inactive) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_inactive">Inactive</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" name="is_new" value="1" class="form-check-input" id="is_new" {{ old('is_new', $item->is_new) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_new">New Product</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" name="is_price_protected" value="1" class="form-check-input" id="is_price_protected" {{ old('is_price_protected', $item->is_price_protected) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_price_protected">Price Protected</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" name="is_power_sku" value="1" class="form-check-input" id="is_power_sku" {{ old('is_power_sku', $item->is_power_sku) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_power_sku">Power SKU</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" name="is_default_batch" value="1" class="form-check-input" id="is_default_batch" {{ old('is_default_batch', $item->is_default_batch) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_default_batch">Default Batch</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" name="is_temp_credit" value="1" class="form-check-input" id="is_temp_credit" {{ old('is_temp_credit', $item->is_temp_credit) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_temp_credit">Temp Credit</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" name="is_batch_priced" value="1" class="form-check-input" id="is_batch_priced" {{ old('is_batch_priced', $item->is_batch_priced) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_batch_priced">Batch Priced</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" name="is_initiative" value="1" class="form-check-input" id="is_initiative" {{ old('is_initiative', $item->is_initiative) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_initiative">Initiative</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" name="force_default_pack" value="1" class="form-check-input" id="force_default_pack" {{ old('force_default_pack', $item->force_default_pack) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="force_default_pack">Force Default Pack</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" name="has_serial_number" value="1" class="form-check-input" id="has_serial_number" {{ old('has_serial_number', $item->has_serial_number) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="has_serial_number">Has Serial Number</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" name="required_by_liter" value="1" class="form-check-input" id="required_by_liter" {{ old('required_by_liter', $item->required_by_liter) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="required_by_liter">Required by Liter</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" name="required_by_weight" value="1" class="form-check-input" id="required_by_weight" {{ old('required_by_weight', $item->required_by_weight) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="required_by_weight">Required by Weight</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3 mb-5">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-lg">Update Item</button>
                            <a href="{{ route('items.index') }}" class="btn btn-secondary btn-lg">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let packIndex = {{ $item->packs->count() }};
        const packsContainer = document.getElementById('packsContainer');
        const addPackBtn = document.getElementById('addPackBtn');

        // === Select All Pack Definitions ===
        const selectAllCheckbox = document.getElementById('selectAllPacks');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                document.querySelectorAll('.pack-definition-checkbox').forEach(cb => {
                    cb.checked = this.checked;
                    cb.closest('tr').classList.toggle('table-active', this.checked);
                });
                updateSelectedSummary();
            });
        }

        // === Toggle row selection on checkbox or row click ===
        document.querySelectorAll('.pack-definition-checkbox').forEach(cb => {
            cb.addEventListener('change', function() {
                this.closest('tr').classList.toggle('table-active', this.checked);
                updateSelectedSummary();
            });
        });

        document.querySelectorAll('.pack-definition-row').forEach(row => {
            row.addEventListener('click', function(e) {
                if (e.target.type === 'checkbox') return;
                const cb = this.querySelector('.pack-definition-checkbox');
                if (cb) {
                    cb.checked = !cb.checked;
                    this.classList.toggle('table-active', cb.checked);
                    updateSelectedSummary();
                }
            });
        });

        // === Add Selected Pack Definitions as Cards ===
        function updateSelectedSummary() {
            const selectedSummary = document.getElementById('selectedPacksSummary');
            const selectedList = document.getElementById('selectedPacksList');
            const checked = document.querySelectorAll('.pack-definition-checkbox:checked');

            if (checked.length === 0) {
                selectedSummary.style.display = 'none';
                return;
            }

            selectedSummary.style.display = 'block';
            let html = '<div class="row g-2">';
            checked.forEach(cb => {
                const row = cb.closest('tr');
                const name = row.querySelector('strong')?.innerText || '';
                const type = row.querySelector('.badge.bg-info')?.innerText || '';
                const qty = row.querySelector('.badge.bg-primary')?.innerText || '';
                html += `<div class="col-auto"><span class="badge bg-success">${name} ${type ? '- ' + type : ''} ${qty ? '- ' + qty + ' units' : ''}</span></div>`;
            });
            html += '</div>';
            selectedList.innerHTML = html;
        }

        // === Load Pack Definition Data via AJAX and Add to Form ===
        function addPackDefinitionCard(packDefId) {
            fetch('/pack-definitions/' + packDefId + '/show-ajax')
                .then(r => r.json())
                .then(data => {
                    const newCard = getPackTemplate(packIndex++, data);
                    packsContainer.insertAdjacentHTML('beforeend', newCard);
                    const addedCard = packsContainer.lastElementChild;
                    attachRemovePackEvent(addedCard);
                })
                .catch(err => console.error('Error loading pack definition:', err));
        }

        function getPackTemplate(index, data) {
            const pt = {{ Js::from($packTypes->pluck('name_en', 'id')) }};
            const pc = {{ Js::from($packCapacities->mapWithKeys(fn($c) => [$c->id => $c->name_en . ' (' . ($c->uom->uom_symbol ?? '') . ')'])->toArray()) }};
            const po = {{ Js::from($packOperations->pluck('name_en', 'id')) }};
            const pg = {{ Js::from($packGroups->pluck('name_en', 'id')) }};

            let packTypeOptions = '<option value="">Select Type</option>';
            Object.entries(pt).forEach(([id, name]) => {
                packTypeOptions += `<option value="${id}" ${data.pack_type_id == id ? 'selected' : ''}>${name}</option>`;
            });

            let packCapOptions = '<option value="">Select Capacity</option>';
            Object.entries(pc).forEach(([id, name]) => {
                packCapOptions += `<option value="${id}" ${data.pack_capacity_id == id ? 'selected' : ''}>${name}</option>`;
            });

            let packOpOptions = '';
            if (data.pack_operations && data.pack_operations.length > 0) {
                data.pack_operations.forEach(id => {
                    if (po[id]) packOpOptions += `<option value="${id}" selected>${po[id]}</option>`;
                });
            }

            let packGrpOptions = '';
            if (data.pack_groups && data.pack_groups.length > 0) {
                data.pack_groups.forEach(id => {
                    if (pg[id]) packGrpOptions += `<option value="${id}" selected>${pg[id]}</option>`;
                });
            }

            return `
                <div class="pack-card border rounded-3 p-3 mb-3 position-relative" data-pack-index="${index}">
                    <span class="badge bg-secondary position-absolute top-0 start-0 m-2">Pack ${index + 1}</span>
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-pack" aria-label="Remove"></button>
                    <div class="row g-3 mt-2">
                        <div class="col-md-2">
                            <label class="form-label small">Pack Type</label>
                            <select name="packs[${index}][pack_type_id]" class="form-select form-select-sm" required>${packTypeOptions}</select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Pack Capacity</label>
                            <select name="packs[${index}][pack_capacity_id]" class="form-select form-select-sm" required>${packCapOptions}</select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Quantity in Pack</label>
                            <input type="number" step="any" name="packs[${index}][quantity_in_pack]" class="form-control form-select-sm" value="${data.quantity_in_pack || ''}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Barcode</label>
                            <input type="text" name="packs[${index}][barcode]" class="form-control form-control-sm" value="${data.barcode || ''}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Serial Separator</label>
                            <input type="text" name="packs[${index}][serial_separator]" class="form-control form-control-sm" value="${data.serial_separator || ''}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Equivalency Factor</label>
                            <input type="number" step="0.01" name="packs[${index}][equivalency_factor]" class="form-control form-control-sm" value="${data.equivalency_factor || ''}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Height</label>
                            <input type="number" step="any" name="packs[${index}][height]" class="form-control form-control-sm" value="${data.height || ''}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Width</label>
                            <input type="number" step="any" name="packs[${index}][width]" class="form-control form-control-sm" value="${data.width || ''}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Depth</label>
                            <input type="number" step="any" name="packs[${index}][depth]" class="form-control form-control-sm" value="${data.depth || ''}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Weight</label>
                            <input type="number" step="any" name="packs[${index}][weight]" class="form-control form-control-sm" value="${data.weight || ''}">
                        </div>
                        <div class="col-md-12">
                            <div class="form-check form-check-inline">
                                <input type="checkbox" name="packs[${index}][is_weight_priced]" value="1" class="form-check-input" id="weightPriced${index}" ${data.is_weight_priced ? 'checked' : ''}>
                                <label class="form-check-label small" for="weightPriced${index}">Weight Priced</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="checkbox" name="packs[${index}][allow_fraction_in_qty]" value="1" class="form-check-input" id="allowFraction${index}" ${data.allow_fraction_in_qty ? 'checked' : ''}>
                                <label class="form-check-label small" for="allowFraction${index}">Allow Fraction Qty</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="checkbox" name="packs[${index}][is_default_pack]" value="1" class="form-check-input" id="defaultPack${index}" ${data.is_default_pack ? 'checked' : ''}>
                                <label class="form-check-label small" for="defaultPack${index}">Default Pack</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Pack Operations</label>
                            <select name="packs[${index}][pack_operation_ids][]" class="form-select form-select-sm" multiple>${packOpOptions}</select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Pack Groups</label>
                            <select name="packs[${index}][pack_group_ids][]" class="form-select form-select-sm" multiple>${packGrpOptions}</select>
                        </div>
                    </div>
                </div>
            `;
        }

        function attachRemovePackEvent(packDiv) {
            const removeBtn = packDiv.querySelector('.remove-pack');
            if (removeBtn) {
                removeBtn.addEventListener('click', function() {
                    packDiv.remove();
                });
            }
        }

        // === Add "Add Selected" Button Handler ===
        const addSelectedBtn = document.getElementById('addSelectedPacksBtn');
        if (addSelectedBtn) {
            addSelectedBtn.addEventListener('click', function() {
                const checked = document.querySelectorAll('.pack-definition-checkbox:checked');
                if (checked.length === 0) {
                    alert('Please select at least one pack definition first.');
                    return;
                }
                checked.forEach(cb => {
                    addPackDefinitionCard(cb.value);
                });
            });
        }

        addPackBtn.addEventListener('click', function() {
            const newPackHtml = getPackTemplate(packIndex++, {});
            packsContainer.insertAdjacentHTML('beforeend', newPackHtml);
            attachRemovePackEvent(packsContainer.lastElementChild);
        });

        document.querySelectorAll('.pack-card').forEach(pack => {
            attachRemovePackEvent(pack);
        });

        // === Manage Additional Images ===
        const existingImagesContainer = document.getElementById('existingImagesContainer');
        const newImagesContainer = document.getElementById('newImagesContainer');
        const addImageBtn = document.getElementById('addImageBtn');

        existingImagesContainer.querySelectorAll('.remove-existing-image').forEach(btn => {
            btn.addEventListener('click', function() {
                const imageDiv = this.closest('.existing-image-item');
                const hiddenInput = imageDiv.querySelector('input[name="deleted_images[]"]');
                if (hiddenInput) hiddenInput.disabled = false;
                imageDiv.style.opacity = '0.5';
                imageDiv.style.pointerEvents = 'none';
                const removedBadge = document.createElement('div');
                removedBadge.className = 'badge bg-danger position-absolute bottom-0 start-0 m-1';
                removedBadge.innerText = 'Removed';
                imageDiv.querySelector('.position-relative').appendChild(removedBadge);
            });
        });

        addImageBtn.addEventListener('click', function() {
            const newInputHtml = `
                <div class="mb-2 new-image-item">
                    <input type="file" name="additional_images[]" class="form-control" accept="image/*">
                    <button type="button" class="btn btn-sm btn-outline-danger mt-1 remove-new-image">Remove</button>
                </div>
            `;
            newImagesContainer.insertAdjacentHTML('beforeend', newInputHtml);
            const lastItem = newImagesContainer.lastElementChild;
            lastItem.querySelector('.remove-new-image').addEventListener('click', function() {
                lastItem.remove();
            });
        });

        // === Form Submit: Ensure Checkboxes Send 0 if Unchecked ===
        const form = document.getElementById('itemForm');
        form.addEventListener('submit', function() {
            document.querySelectorAll('input[type="checkbox"][value="1"]').forEach(cb => {
                if (!cb.checked) {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = cb.name;
                    hidden.value = '0';
                    cb.parentNode.appendChild(hidden);
                }
            });
        });

        updateSelectedSummary();
    });
</script>
@endpush
@endsection