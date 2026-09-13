@extends('dashboard.partials.layout')

@section('title', 'Create Item')

@section('content')
{{-- ===== Action Bar ===== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-plus-circle" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            Add New Item
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">
            Create a product with multiple packs and images
        </p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('items.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

{{-- ===== Validation Errors ===== --}}
@if($errors->any())
    <div class="nova-alert danger">
        <h6 class="nova-fw-bold">
            <i class="fas fa-exclamation-circle nova-me-1"></i>
            يرجى تصحيح الأخطاء التالية:
        </h6>
        <ul class="nova-mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('items.store') }}" method="POST" enctype="multipart/form-data" id="itemForm">
    @csrf

    <div class="form-layout">
        {{-- ===== MAIN COLUMN ===== --}}
        <div class="form-main">

            {{-- ===== Basic Information ===== --}}
            <div class="nova-card nova-mb-4">
                <div class="nova-card-header">
                    <h5 class="nova-card-title">
                        <i class="fas fa-info-circle me-2"></i> Basic Information
                    </h5>
                </div>
                <div class="nova-card-body">
                    <div class="form-grid form-grid-2">
                        <div class="nova-form-group">
                            <label class="nova-label">Code <span class="nova-text-danger">*</span></label>
                            <input type="text" name="code"
                                   class="nova-input @error('code') nova-invalid @enderror"
                                   value="{{ old('code') }}" required>
                            @error('code') <div class="nova-form-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">Sequence</label>
                            <input type="text" name="sequence" class="nova-input"
                                   value="{{ old('sequence') }}">
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">Name (English) <span class="nova-text-danger">*</span></label>
                            <input type="text" name="name_en"
                                   class="nova-input @error('name_en') nova-invalid @enderror"
                                   value="{{ old('name_en') }}" required>
                            @error('name_en') <div class="nova-form-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">Name (Arabic) <span class="nova-text-danger">*</span></label>
                            <input type="text" name="name_ar"
                                   class="nova-input @error('name_ar') nova-invalid @enderror"
                                   value="{{ old('name_ar') }}" required dir="rtl">
                            @error('name_ar') <div class="nova-form-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="nova-form-group form-grid-full">
                            <label class="nova-label">Definition</label>
                            <textarea name="definition" class="nova-input" rows="3"
                                      placeholder="وصف مختصر للمنتج...">{{ old('definition') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== Classification ===== --}}
            <div class="nova-card nova-mb-4">
                <div class="nova-card-header">
                    <h5 class="nova-card-title">
                        <i class="fas fa-sitemap me-2"></i> Classification
                    </h5>
                </div>
                <div class="nova-card-body">
                    <div class="form-grid form-grid-2">
                        <div class="nova-form-group">
                            <label class="nova-label">Item Type</label>
                            <select name="item_type_id" class="nova-select">
                                <option value="">-- None --</option>
                                @foreach($itemTypes as $type)
                                    <option value="{{ $type->id }}"
                                            {{ old('item_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name_en ?? $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">Category</label>
                            <select name="category_id" class="nova-select">
                                <option value="">-- None --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}"
                                            {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name_en ?? $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">Brand</label>
                            <select name="brand_id" class="nova-select">
                                <option value="">-- None --</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}"
                                            {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                                        {{ $brand->name_en ?? $brand->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">Model</label>
                            <select name="model_id" class="nova-select">
                                <option value="">-- None --</option>
                                @foreach($models as $model)
                                    <option value="{{ $model->id }}"
                                            {{ old('model_id') == $model->id ? 'selected' : '' }}>
                                        {{ $model->name_en ?? $model->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">Supplier</label>
                            <select name="supplier_id" class="nova-select">
                                <option value="">-- None --</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}"
                                            {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->vendor_name ?? $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== Tolerances & Stock ===== --}}
            <div class="nova-card nova-mb-4">
                <div class="nova-card-header">
                    <h5 class="nova-card-title">
                        <i class="fas fa-sliders-h me-2"></i> Tolerances & Stock
                    </h5>
                </div>
                <div class="nova-card-body">
                    <div class="form-grid form-grid-3">
                        <div class="nova-form-group">
                            <label class="nova-label">Shelf Life (days)</label>
                            <input type="number" name="shelf_life_time" class="nova-input"
                                   value="{{ old('shelf_life_time') }}" min="0">
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">Van Stock Tolerance</label>
                            <input type="number" step="any" name="van_stock_tolerance" class="nova-input"
                                   value="{{ old('van_stock_tolerance') }}">
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">Storage Tolerance</label>
                            <input type="number" step="any" name="storage_tolerance" class="nova-input"
                                   value="{{ old('storage_tolerance') }}">
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">Weight Tolerance</label>
                            <input type="number" step="any" name="weight_tolerance" class="nova-input"
                                   value="{{ old('weight_tolerance') }}">
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">Low Stock Qty</label>
                            <input type="number" step="any" name="low_stock_qty" class="nova-input"
                                   value="{{ old('low_stock_qty') }}" min="0">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== Packs ===== --}}
            <div class="nova-card nova-mb-4">
                <div class="nova-card-header">
                    <div class="nova-flex nova-items-center nova-justify-between">
                        <h5 class="nova-card-title">
                            <i class="fas fa-boxes me-2"></i> Packs (Pack Definitions)
                        </h5>
                        <a href="{{ route('pack-definitions.create') }}"
                           class="nova-btn nova-btn-primary nova-btn-sm" target="_blank">
                            <i class="fas fa-plus-circle"></i> New Pack Definition
                        </a>
                    </div>
                </div>
                <div class="nova-card-body">
                    <div class="nova-alert info">
                        <i class="fas fa-info-circle"></i>
                        Select pack definitions for this product from the table below.
                    </div>

                    @if($packDefinitions->count() > 0)
                        <div class="nova-table-scroll">
                            <table class="nova-table">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;">
                                            <input type="checkbox" id="selectAllPacks"
                                                   class="nova-form-check-input">
                                        </th>
                                        <th>Name</th>
                                        <th>Pack Type</th>
                                        <th>Capacity</th>
                                        <th>Qty/Pack</th>
                                        <th>Dimensions</th>
                                        <th>Weight</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($packDefinitions as $packDef)
                                        <tr class="pack-definition-row">
                                            <td>
                                                <input type="checkbox"
                                                       class="nova-form-check-input pack-definition-checkbox"
                                                       value="{{ $packDef->id }}"
                                                       {{ in_array($packDef->id, old('pack_definition_ids', [])) ? 'checked' : '' }}>
                                            </td>
                                            <td>
                                                <span class="nova-fw-bold">
                                                    {{ $packDef->name_ar ?? $packDef->name_en ?? $packDef->name ?? '#' . $packDef->id }}
                                                </span>
                                                @if($packDef->code)
                                                    <br><small class="nova-text-muted">{{ $packDef->code }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="nova-badge info">
                                                    {{ $packDef->packType?->name_ar ?? $packDef->packType?->name_en ?? '-' }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ $packDef->packCapacity?->value ?? '-' }}
                                                {{ $packDef->packCapacity?->uom?->code ?? '' }}
                                            </td>
                                            <td>
                                                <span class="nova-badge primary">
                                                    {{ $packDef->quantity_in_pack }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($packDef->height || $packDef->width || $packDef->depth)
                                                    {{ $packDef->height }}×{{ $packDef->width }}×{{ $packDef->depth }}
                                                @else
                                                    <span class="nova-text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>{{ $packDef->weight ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div id="selectedSummary" class="selected-summary">
                            <h6 class="nova-fw-bold nova-mb-2">
                                Selected: <span id="selectedCount">0</span> pack(s)
                            </h6>
                            <div id="selectedList" class="nova-flex nova-flex-wrap nova-gap-2"></div>
                        </div>
                    @else
                        <div class="nova-alert warning">
                            <i class="fas fa-triangle-exclamation"></i>
                            No pack definitions available.
                            <a href="{{ route('pack-definitions.create') }}"
                               class="nova-alert-link" target="_blank">
                                Create a pack definition first
                            </a>.
                        </div>
                    @endif
                </div>
            </div>

            {{-- ===== Additional Images ===== --}}
            <div class="nova-card nova-mb-4">
                <div class="nova-card-header">
                    <div class="nova-flex nova-items-center nova-justify-between">
                        <h5 class="nova-card-title">
                            <i class="fas fa-images me-2"></i> Additional Images
                        </h5>
                        <button type="button" class="nova-btn nova-btn-primary nova-btn-sm" id="addImageBtn">
                            <i class="fas fa-plus-circle"></i> Add Image
                        </button>
                    </div>
                </div>
                <div class="nova-card-body">
                    <div id="newImagesContainer"></div>
                    <p class="nova-text-muted nova-fs-sm nova-mt-2">
                        <i class="fas fa-info-circle"></i>
                        You can upload new images (JPEG, PNG, max 2MB each). Click the button to add more image fields.
                    </p>
                </div>
            </div>
        </div>

        {{-- ===== SIDEBAR COLUMN ===== --}}
        <div class="form-side">

            {{-- ===== Main Image ===== --}}
            <div class="nova-card nova-mb-4">
                <div class="nova-card-header">
                    <h5 class="nova-card-title"><i class="fas fa-image me-2"></i> Main Image</h5>
                </div>
                <div class="nova-card-body nova-text-center">
                    <div class="image-preview-wrapper">
                        <div id="imagePreview" class="image-preview image-preview-empty">
                            <i class="fas fa-image"></i>
                            <span>No image selected</span>
                        </div>
                    </div>
                    <input type="file" name="image" id="imageInput"
                           class="nova-input nova-mt-3" accept="image/*">
                    <p class="nova-text-muted nova-fs-sm nova-mt-2">
                        Upload main product image (optional).
                    </p>
                    @error('image') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- ===== Status & Flags ===== --}}
            <div class="nova-card nova-mb-4">
                <div class="nova-card-header">
                    <h5 class="nova-card-title"><i class="fas fa-flag me-2"></i> Status & Flags</h5>
                </div>
                <div class="nova-card-body">
                    @php
                        $flags = [
                            ['name' => 'is_inactive',       'label' => 'Inactive',           'icon' => 'eye-slash'],
                            ['name' => 'is_new',            'label' => 'New Product',        'icon' => 'star'],
                            ['name' => 'is_price_protected','label' => 'Price Protected',    'icon' => 'lock'],
                            ['name' => 'is_power_sku',      'label' => 'Power SKU',          'icon' => 'bolt'],
                            ['name' => 'is_default_batch',  'label' => 'Default Batch',      'icon' => 'layer-group'],
                            ['name' => 'is_temp_credit',    'label' => 'Temp Credit',        'icon' => 'credit-card'],
                            ['name' => 'is_batch_priced',   'label' => 'Batch Priced',       'icon' => 'tags'],
                            ['name' => 'is_initiative',     'label' => 'Initiative',         'icon' => 'rocket'],
                            ['name' => 'force_default_pack','label' => 'Force Default Pack', 'icon' => 'box'],
                            ['name' => 'has_serial_number', 'label' => 'Has Serial Number',  'icon' => 'barcode'],
                            ['name' => 'required_by_liter', 'label' => 'Required by Liter',  'icon' => 'flask'],
                            ['name' => 'required_by_weight','label' => 'Required by Weight', 'icon' => 'weight'],
                        ];
                    @endphp

                    <div class="flags-list">
                        @foreach($flags as $flag)
                            <div class="nova-form-check">
                                <input type="hidden" name="{{ $flag['name'] }}" value="0">
                                <input type="checkbox"
                                       name="{{ $flag['name'] }}"
                                       class="nova-form-check-input"
                                       value="1"
                                       id="{{ $flag['name'] }}"
                                       {{ old($flag['name']) ? 'checked' : '' }}>
                                <label class="nova-form-check-label" for="{{ $flag['name'] }}">
                                    <i class="fas fa-{{ $flag['icon'] }} nova-me-1"></i>
                                    {{ $flag['label'] }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ===== Submit ===== --}}
            <div class="nova-card sticky-actions">
                <div class="nova-card-body">
                    <div class="nova-flex nova-flex-column nova-gap-2">
                        <button type="submit" class="nova-btn nova-btn-primary" id="submitBtn">
                            <i class="fas fa-save"></i> Create Item
                        </button>
                        <a href="{{ route('items.index') }}" class="nova-btn nova-btn-outline">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('styles')
<style>
    /* ===== Form Layout ===== */
    .form-layout {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 1.25rem;
        align-items: start;
    }

    .form-main {
        min-width: 0;
    }

    .form-side {
        position: sticky;
        top: 1rem;
    }

    /* ===== Form Grids ===== */
    .form-grid {
        display: grid;
        gap: 1rem;
    }

    .form-grid-2 {
        grid-template-columns: repeat(2, 1fr);
    }

    .form-grid-3 {
        grid-template-columns: repeat(3, 1fr);
    }

    .form-grid-full {
        grid-column: 1 / -1;
    }

    /* ===== Selected Summary ===== */
    .selected-summary {
        margin-top: 1rem;
        padding: 0.75rem;
        background: #f8f9fc;
        border: 1px solid #e3e6f0;
        border-radius: 0.5rem;
    }

    /* ===== Pack Definition Row ===== */
    .pack-definition-row {
        cursor: pointer;
        transition: background 0.15s ease;
    }

    .pack-definition-row:hover {
        background: #f8f9fc;
    }

    .pack-definition-row.selected {
        background: rgba(28, 200, 138, 0.08);
    }

    /* ===== Image Preview ===== */
    .image-preview-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .image-preview {
        max-width: 100%;
        max-height: 220px;
        border-radius: 0.5rem;
        border: 1px solid #e3e6f0;
        object-fit: contain;
        transition: all 0.2s ease;
    }

    .image-preview-empty {
        width: 100%;
        height: 180px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        background: #f8f9fc;
        color: #858796;
        font-size: 0.85rem;
    }

    .image-preview-empty i {
        font-size: 2rem;
    }

    /* ===== Flags List ===== */
    .flags-list {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .flags-list .nova-form-check {
        padding: 0.4rem 0;
        border-bottom: 1px dashed #e3e6f0;
    }

    .flags-list .nova-form-check:last-child {
        border-bottom: none;
    }

    .flags-list .nova-form-check-label {
        cursor: pointer;
    }

    /* ===== Sticky Actions ===== */
    .sticky-actions {
        position: sticky;
        bottom: 1rem;
    }

    /* ===== Invalid State ===== */
    .nova-input.nova-invalid,
    .nova-select.nova-invalid {
        border-color: #e74a3b;
    }

    /* ===== New Image Item ===== */
    .new-image-item {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .new-image-item .nova-input {
        flex: 1;
    }

    /* ===== Responsive ===== */
    @media (max-width: 1200px) {
        .form-layout {
            grid-template-columns: 1fr 320px;
        }
    }

    @media (max-width: 992px) {
        .form-layout {
            grid-template-columns: 1fr;
        }
        .form-side {
            position: static;
        }
        .form-grid-3 {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .form-grid-2,
        .form-grid-3 {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ===== Pack Definitions Selection =====
    const packDefinitions = @json($packDefinitions);
    const selectedPackIds = new Set(@json(old('pack_definition_ids', [])));

    const selectedCountEl = document.getElementById('selectedCount');
    const selectedListEl = document.getElementById('selectedList');
    const selectAllCb = document.getElementById('selectAllPacks');
    const packCheckboxes = document.querySelectorAll('.pack-definition-checkbox');

    function updateSelectedPacks() {
        if (selectedCountEl) selectedCountEl.textContent = selectedPackIds.size;
        if (!selectedListEl) return;

        selectedListEl.innerHTML = '';
        selectedPackIds.forEach(id => {
            const packDef = packDefinitions.find(p => p.id === id);
            if (!packDef) return;

            const badge = document.createElement('span');
            badge.className = 'nova-badge success';
            const packTypeName = packDef.packType?.name_ar ?? packDef.packType?.name_en ?? '-';
            const packCapacityValue = packDef.packCapacity?.value ?? '-';
            const packCapacityUom = packDef.packCapacity?.uom?.code ?? '';
            badge.textContent = `${packDef.name_ar ?? packDef.name_en ?? '#' + packDef.id} (${packTypeName} - ${packCapacityValue}${packCapacityUom})`;
            selectedListEl.appendChild(badge);
        });
    }

    function syncRowState() {
        packCheckboxes.forEach(cb => {
            const row = cb.closest('.pack-definition-row');
            if (row) {
                row.classList.toggle('selected', cb.checked);
            }
        });
    }

    packCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            const packDefId = parseInt(this.value);
            if (this.checked) {
                selectedPackIds.add(packDefId);
            } else {
                selectedPackIds.delete(packDefId);
            }
            updateSelectedPacks();
            syncRowState();
        });
    });

    if (selectAllCb) {
        selectAllCb.addEventListener('change', function() {
            packCheckboxes.forEach(cb => {
                cb.checked = this.checked;
                const packDefId = parseInt(cb.value);
                if (this.checked) {
                    selectedPackIds.add(packDefId);
                } else {
                    selectedPackIds.delete(packDefId);
                }
            });
            updateSelectedPacks();
            syncRowState();
        });
    }

    // Click on row to toggle checkbox
    document.querySelectorAll('.pack-definition-row').forEach(row => {
        row.addEventListener('click', function(e) {
            if (e.target.tagName === 'INPUT' || e.target.closest('a')) return;
            const cb = this.querySelector('.pack-definition-checkbox');
            if (cb) {
                cb.checked = !cb.checked;
                cb.dispatchEvent(new Event('change'));
            }
        });
    });

    // Initialize
    updateSelectedPacks();
    syncRowState();

    // ===== Image Preview =====
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');

    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', function() {
            const file = this.files && this.files[0];
            if (!file) return;

            if (!file.type.startsWith('image/')) {
                alert('الرجاء اختيار ملف صورة صالح');
                this.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const wrapper = imagePreview.parentElement;
                const img = document.createElement('img');
                img.id = 'imagePreview';
                img.src = e.target.result;
                img.className = 'image-preview';
                img.alt = 'Preview';
                wrapper.innerHTML = '';
                wrapper.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    }

    // ===== Additional Images =====
    const newImagesContainer = document.getElementById('newImagesContainer');
    const addImageBtn = document.getElementById('addImageBtn');

    function createImageInput() {
        const div = document.createElement('div');
        div.className = 'nova-form-group nova-mb-2 new-image-item';
        div.innerHTML = `
            <input type="file" name="additional_images[]" class="nova-input" accept="image/*">
            <button type="button" class="nova-btn nova-btn-outline nova-btn-danger nova-btn-sm remove-new-image">
                <i class="fas fa-times"></i>
            </button>
        `;
        div.querySelector('.remove-new-image').addEventListener('click', function() {
            div.remove();
        });
        return div;
    }

    if (addImageBtn) {
        addImageBtn.addEventListener('click', function() {
            newImagesContainer.appendChild(createImageInput());
        });
    }

    // Add first image input by default
    if (newImagesContainer && newImagesContainer.children.length === 0) {
        newImagesContainer.appendChild(createImageInput());
    }

    // ===== Submit Button Loading State =====
    const form = document.getElementById('itemForm');
    const submitBtn = document.getElementById('submitBtn');

    if (form && submitBtn) {
        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جارٍ الإنشاء...';
        });
    }

    // ===== Unsaved Changes Warning =====
    let formChanged = false;
    const formInputs = form ? form.querySelectorAll('input:not([type="hidden"]), select, textarea') : [];

    formInputs.forEach(input => {
        input.addEventListener('change', () => { formChanged = true; });
        input.addEventListener('input', () => { formChanged = true; });
    });

    if (form) {
        form.addEventListener('submit', () => { formChanged = false; });
    }

    window.addEventListener('beforeunload', function(e) {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    // ===== Auto-dismiss alerts =====
    setTimeout(function() {
        document.querySelectorAll('.nova-alert').forEach(function(alert) {
            alert.style.transition = 'opacity 0.3s, transform 0.3s';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        });
    }, 5000);
});
</script>
@endpush