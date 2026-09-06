{{-- resources/views/dashboard/data_definition/data_product/items/create.blade.php --}}
@extends('dashboard.partials.layout')

@section('title', 'Create Item')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold">
                            <i data-feather="plus-circle"></i> Add New Item
                        </h1>
                        <p class="text-muted">Create a product with multiple packs and images</p>
                    </div>
                </div>

                <form action="{{ route('items.store') }}" method="POST" enctype="multipart/form-data" id="itemForm">
                    @csrf

                    <div class="row">
                        {{-- Left Column (8) --}}
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
                                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" required>
                                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Sequence (optional)</label>
                                            <input type="text" name="sequence" class="form-control" value="{{ old('sequence') }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Name (English) <span class="text-danger">*</span></label>
                                            <input type="text" name="name_en" class="form-control @error('name_en') is-invalid @enderror" value="{{ old('name_en') }}" required>
                                            @error('name_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Name (Arabic) <span class="text-danger">*</span></label>
                                            <input type="text" name="name_ar" class="form-control @error('name_ar') is-invalid @enderror" value="{{ old('name_ar') }}" required>
                                            @error('name_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Definition</label>
                                            <textarea name="definition" class="form-control" rows="3">{{ old('definition') }}</textarea>
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
                                                    <option value="{{ $type->id }}" {{ old('item_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Category</label>
                                            <select name="category_id" class="form-select">
                                                <option value="">-- None --</option>
                                                @foreach($categories as $cat)
                                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Brand</label>
                                            <select name="brand_id" class="form-select">
                                                <option value="">-- None --</option>
                                                @foreach($brands as $brand)
                                                    <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Model</label>
                                            <select name="model_id" class="form-select">
                                                <option value="">-- None --</option>
                                                @foreach($models as $model)
                                                    <option value="{{ $model->id }}" {{ old('model_id') == $model->id ? 'selected' : '' }}>{{ $model->name_en }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Supplier</label>
                                            <select name="supplier_id" class="form-select">
                                                <option value="">-- None --</option>
                                                @foreach($suppliers as $supplier)
                                                    <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->vendor_name }}</option>
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
                                            <input type="number" name="shelf_life_time" class="form-control" value="{{ old('shelf_life_time') }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Van Stock Tolerance</label>
                                            <input type="number" step="any" name="van_stock_tolerance" class="form-control" value="{{ old('van_stock_tolerance') }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Storage Tolerance</label>
                                            <input type="number" step="any" name="storage_tolerance" class="form-control" value="{{ old('storage_tolerance') }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Weight Tolerance</label>
                                            <input type="number" step="any" name="weight_tolerance" class="form-control" value="{{ old('weight_tolerance') }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Low Stock Qty</label>
                                            <input type="number" step="any" name="low_stock_qty" class="form-control" value="{{ old('low_stock_qty') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

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
                                                @foreach($packDefinitions as $packDef)
                                                    @php
                                                        $packTypeName = $packDef->packType?->name_ar ?? $packDef->packType?->name_en ?? '-';
                                                        $packCapacityValue = $packDef->packCapacity?->value ?? '-';
                                                        $packCapacityUom = $packDef->packCapacity?->uom?->code ?? '';
                                                        $isSelected = old('packs') ? in_array($packDef->id, array_column(old('packs'), 'pack_definition_id')) : false;
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
                                </div>
                            </div>

                            {{-- Additional Images Card --}}
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-white border-0 pt-4 d-flex justify-content-between align-items-center">
                                    <h5 class="fw-bold mb-0">Additional Images</h5>
                                    <button type="button" class="btn btn-sm btn-primary" id="addImageBtn">+ Add Image</button>
                                </div>
                                <div class="card-body">
                                    <div id="newImagesContainer"></div>
                                    <div class="mt-2 text-muted small">You can upload new images (JPEG, PNG, max 2MB each). Click the button to add more image fields.</div>
                                </div>
                            </div>
                        </div>

                        {{-- Right Column (4) - Main Image & Flags --}}
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-white border-0 pt-4">
                                    <h5 class="fw-bold mb-0">Main Image</h5>
                                </div>
                                <div class="card-body text-center">
                                    <div class="bg-light rounded p-3 mb-3">No image selected</div>
                                    <input type="file" name="image" class="form-control" accept="image/*">
                                    <div class="form-text">Upload main product image (optional).</div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-white border-0 pt-4">
                                    <h5 class="fw-bold mb-0">Status & Flags</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" name="is_inactive" value="1" class="form-check-input" id="is_inactive" {{ old('is_inactive') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_inactive">Inactive</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" name="is_new" value="1" class="form-check-input" id="is_new" {{ old('is_new') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_new">New Product</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" name="is_price_protected" value="1" class="form-check-input" id="is_price_protected" {{ old('is_price_protected') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_price_protected">Price Protected</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" name="is_power_sku" value="1" class="form-check-input" id="is_power_sku" {{ old('is_power_sku') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_power_sku">Power SKU</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" name="is_default_batch" value="1" class="form-check-input" id="is_default_batch" {{ old('is_default_batch') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_default_batch">Default Batch</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" name="is_temp_credit" value="1" class="form-check-input" id="is_temp_credit" {{ old('is_temp_credit') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_temp_credit">Temp Credit</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" name="is_batch_priced" value="1" class="form-check-input" id="is_batch_priced" {{ old('is_batch_priced') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_batch_priced">Batch Priced</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" name="is_initiative" value="1" class="form-check-input" id="is_initiative" {{ old('is_initiative') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_initiative">Initiative</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" name="force_default_pack" value="1" class="form-check-input" id="force_default_pack" {{ old('force_default_pack') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="force_default_pack">Force Default Pack</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" name="has_serial_number" value="1" class="form-check-input" id="has_serial_number" {{ old('has_serial_number') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="has_serial_number">Has Serial Number</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" name="required_by_liter" value="1" class="form-check-input" id="required_by_liter" {{ old('required_by_liter') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="required_by_liter">Required by Liter</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" name="required_by_weight" value="1" class="form-check-input" id="required_by_weight" {{ old('required_by_weight') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="required_by_weight">Required by Weight</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3 mb-5">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-lg">Create Item</button>
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
        const packDefinitions = @json($packDefinitions);
        const selectedPackIds = new Set();

        // Initialize from old input
        const oldPacks = @json(old('packs', []));
        if (oldPacks.length > 0) {
            oldPacks.forEach(pack => {
                if (pack.pack_definition_id) {
                    selectedPackIds.add(pack.pack_definition_id);
                }
            });
        }

        // Update selected packs display
        function updateSelectedPacks() {
            const summary = document.getElementById('selectedPacksSummary');
            const list = document.getElementById('selectedPacksList');
            list.innerHTML = '';

            if (selectedPackIds.size === 0) {
                summary.style.display = 'none';
                return;
            }

            summary.style.display = 'block';
            selectedPackIds.forEach(id => {
                const packDef = packDefinitions.find(p => p.id === id);
                if (!packDef) return;

                const packTypeName = packDef.packType?.name_ar ?? packDef.packType?.name_en ?? '-';
                const packCapacityValue = packDef.packCapacity?.value ?? '-';
                const packCapacityUom = packDef.packCapacity?.uom?.code ?? '';

                const badge = document.createElement('span');
                badge.className = 'badge bg-success me-1 mb-1';
                badge.textContent = `${packDef.name_ar ?? packDef.name_en ?? '#' + packDef.id} (${packTypeName} - ${packCapacityValue}${packCapacityUom})`;
                list.appendChild(badge);
            });
        }

        // Handle checkbox changes
        document.querySelectorAll('.pack-definition-checkbox').forEach(cb => {
            cb.addEventListener('change', function() {
                const packDefId = parseInt(this.value);
                if (this.checked) {
                    selectedPackIds.add(packDefId);
                } else {
                    selectedPackIds.delete(packDefId);
                }
                updateSelectedPacks();
            });
        });

        // Select all checkbox
        const selectAllCb = document.getElementById('selectAllPacks');
        if (selectAllCb) {
            selectAllCb.addEventListener('change', function() {
                document.querySelectorAll('.pack-definition-checkbox').forEach(cb => {
                    cb.checked = this.checked;
                    const packDefId = parseInt(cb.value);
                    if (this.checked) {
                        selectedPackIds.add(packDefId);
                    } else {
                        selectedPackIds.delete(packDefId);
                    }
                });
                updateSelectedPacks();
            });
        }

        // Handle form submit - add hidden inputs for selected packs
        const form = document.getElementById('itemForm');
        form.addEventListener('submit', function(e) {
            // Remove any existing pack definition inputs
            document.querySelectorAll('input[name^="packs["][name$="[pack_definition_id]"]').forEach(el => el.remove());

            // Add hidden inputs for selected packs
            selectedPackIds.forEach((packDefId, index) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `packs[${index}][pack_definition_id]`;
                input.value = packDefId;
                form.appendChild(input);
            });

            // Remove old inline pack inputs
            document.querySelectorAll('.pack-card').forEach(el => el.remove());
        });

        // Initialize display
        updateSelectedPacks();

        // === Manage Additional Images: add new file input fields ===
        const newImagesContainer = document.getElementById('newImagesContainer');
        const addImageBtn = document.getElementById('addImageBtn');

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

        // Ensure at least one image input field is present (optional)
        if (newImagesContainer.children.length === 0) {
            addImageBtn.click();
        }

        // === Handle checkboxes to send 0 when unchecked ===
        const booleanCheckboxes = form.querySelectorAll('input[type="checkbox"][value="1"]');
        booleanCheckboxes.forEach(cb => {
            if (!cb.checked) {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = cb.name;
                hidden.value = '0';
                cb.parentNode.appendChild(hidden);
            }
        });
    });
</script>
@endpush
@endsection
