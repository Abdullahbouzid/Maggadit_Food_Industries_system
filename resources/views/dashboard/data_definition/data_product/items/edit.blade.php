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
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-white border-0 pt-4 d-flex justify-content-between align-items-center">
                                    <h5 class="fw-bold mb-0">Packs (Units of Measure)</h5>
                                    <button type="button" class="btn btn-sm btn-primary" id="addPackBtn">+ Add Pack</button>
                                </div>
                                <div class="card-body">
                                    <div id="packsContainer">
                                        {{-- Display existing packs --}}
                                        @if($item->packs->count() > 0)
                                            @foreach($item->packs as $packIndex => $pack)
                                                <div class="pack-card border rounded-3 p-3 mb-3">
                                                    <div class="row g-3">
                                                        <div class="col-md-3">
                                                            <label class="form-label small">UOM</label>
                                                            <select name="packs[{{ $packIndex }}][uom_id]" class="form-select form-select-sm" required>
                                                                <option value="">Select UOM</option>
                                                                @foreach($uoms as $uom)
                                                                    <option value="{{ $uom->id }}" {{ old("packs.$packIndex.uom_id", $pack->uom_id) == $uom->id ? 'selected' : '' }}>{{ $uom->name_en }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label small">Quantity</label>
                                                            <input type="number" step="any" name="packs[{{ $packIndex }}][quantity]" class="form-control form-control-sm" value="{{ old("packs.$packIndex.quantity", $pack->quantity) }}" required>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label small">Barcode</label>
                                                            <input type="text" name="packs[{{ $packIndex }}][barcode]" class="form-control form-control-sm" value="{{ old("packs.$packIndex.barcode", $pack->barcode) }}">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label small">Serial Separator</label>
                                                            <input type="text" name="packs[{{ $packIndex }}][serial_separator]" class="form-control form-control-sm" value="{{ old("packs.$packIndex.serial_separator", $pack->serial_separator) }}">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label small">Equivalency Factor</label>
                                                            <input type="number" step="0.01" name="packs[{{ $packIndex }}][equivalency_factor]" class="form-control form-control-sm" value="{{ old("packs.$packIndex.equivalency_factor", $pack->equivalency_factor) }}">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label small">Height</label>
                                                            <input type="number" step="any" name="packs[{{ $packIndex }}][height]" class="form-control form-control-sm" value="{{ old("packs.$packIndex.height", $pack->height) }}">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label small">Width</label>
                                                            <input type="number" step="any" name="packs[{{ $packIndex }}][width]" class="form-control form-control-sm" value="{{ old("packs.$packIndex.width", $pack->width) }}">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label small">Depth</label>
                                                            <input type="number" step="any" name="packs[{{ $packIndex }}][depth]" class="form-control form-control-sm" value="{{ old("packs.$packIndex.depth", $pack->depth) }}">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label small">Weight</label>
                                                            <input type="number" step="any" name="packs[{{ $packIndex }}][weight]" class="form-control form-control-sm" value="{{ old("packs.$packIndex.weight", $pack->weight) }}">
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-check form-check-inline">
                                                                <input type="checkbox" name="packs[{{ $packIndex }}][is_weight_priced]" value="1" class="form-check-input" id="weightPriced{{ $packIndex }}" {{ old("packs.$packIndex.is_weight_priced", $pack->is_weight_priced) ? 'checked' : '' }}>
                                                                <label class="form-check-label small" for="weightPriced{{ $packIndex }}">Weight Priced</label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input type="checkbox" name="packs[{{ $packIndex }}][allow_fraction_in_qty]" value="1" class="form-check-input" id="allowFraction{{ $packIndex }}" {{ old("packs.$packIndex.allow_fraction_in_qty", $pack->allow_fraction_in_qty) ? 'checked' : '' }}>
                                                                <label class="form-check-label small" for="allowFraction{{ $packIndex }}">Allow Fraction Qty</label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input type="checkbox" name="packs[{{ $packIndex }}][is_default_pack]" value="1" class="form-check-input" id="defaultPack{{ $packIndex }}" {{ old("packs.$packIndex.is_default_pack", $pack->is_default_pack) ? 'checked' : '' }}>
                                                                <label class="form-check-label small" for="defaultPack{{ $packIndex }}">Default Pack</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label small">Pack Operations</label>
                                                            <select name="packs[{{ $packIndex }}][pack_operation_ids][]" class="form-select form-select-sm" multiple>
                                                                @foreach($packOperations as $op)
                                                                    <option value="{{ $op->id }}" {{ in_array($op->id, old("packs.$packIndex.pack_operation_ids", $pack->packOperations->pluck('id')->toArray())) ? 'selected' : '' }}>{{ $op->name_en }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label small">Pack Groups</label>
                                                            <select name="packs[{{ $packIndex }}][pack_group_ids][]" class="form-select form-select-sm" multiple>
                                                                @foreach($packGroups as $group)
                                                                    <option value="{{ $group->id }}" {{ in_array($group->id, old("packs.$packIndex.pack_group_ids", $pack->packGroups->pluck('id')->toArray())) ? 'selected' : '' }}>{{ $group->name_en }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="text-end mt-2">
                                                        <button type="button" class="btn btn-sm btn-outline-danger remove-pack">Remove Pack</button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="alert alert-info">No packs defined. Click "Add Pack" to add.</div>
                                        @endif
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
        // === Manage Packs (Add / Remove) ===
        let packIndex = {{ $item->packs->count() }};  // start with existing count
        const packsContainer = document.getElementById('packsContainer');
        const addPackBtn = document.getElementById('addPackBtn');

        function getPackTemplate(index) {
            return `
                <div class="pack-card border rounded-3 p-3 mb-3">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small">UOM</label>
                            <select name="packs[${index}][uom_id]" class="form-select form-select-sm" required>
                                <option value="">Select UOM</option>
                                @foreach($uoms as $uom)
                                    <option value="{{ $uom->id }}">{{ $uom->name_en }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Quantity</label>
                            <input type="number" step="any" name="packs[${index}][quantity]" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Barcode</label>
                            <input type="text" name="packs[${index}][barcode]" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Serial Separator</label>
                            <input type="text" name="packs[${index}][serial_separator]" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Equivalency Factor</label>
                            <input type="number" step="0.01" name="packs[${index}][equivalency_factor]" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Height</label>
                            <input type="number" step="any" name="packs[${index}][height]" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Width</label>
                            <input type="number" step="any" name="packs[${index}][width]" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Depth</label>
                            <input type="number" step="any" name="packs[${index}][depth]" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Weight</label>
                            <input type="number" step="any" name="packs[${index}][weight]" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-12">
                            <div class="form-check form-check-inline">
                                <input type="checkbox" name="packs[${index}][is_weight_priced]" value="1" class="form-check-input" id="weightPriced${index}">
                                <label class="form-check-label small" for="weightPriced${index}">Weight Priced</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="checkbox" name="packs[${index}][allow_fraction_in_qty]" value="1" class="form-check-input" id="allowFraction${index}">
                                <label class="form-check-label small" for="allowFraction${index}">Allow Fraction Qty</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="checkbox" name="packs[${index}][is_default_pack]" value="1" class="form-check-input" id="defaultPack${index}">
                                <label class="form-check-label small" for="defaultPack${index}">Default Pack</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Pack Operations</label>
                            <select name="packs[${index}][pack_operation_ids][]" class="form-select form-select-sm" multiple>
                                @foreach($packOperations as $op)
                                    <option value="{{ $op->id }}">{{ $op->name_en }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Pack Groups</label>
                            <select name="packs[${index}][pack_group_ids][]" class="form-select form-select-sm" multiple>
                                @foreach($packGroups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name_en }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="text-end mt-2">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-pack">Remove Pack</button>
                    </div>
                </div>
            `;
        }

        addPackBtn.addEventListener('click', function() {
            const newPackHtml = getPackTemplate(packIndex++);
            packsContainer.insertAdjacentHTML('beforeend', newPackHtml);
            attachRemovePackEvent(packsContainer.lastElementChild);
        });

        function attachRemovePackEvent(packDiv) {
            const removeBtn = packDiv.querySelector('.remove-pack');
            if (removeBtn) {
                removeBtn.addEventListener('click', function() {
                    packDiv.remove();
                });
            }
        }

        // Attach remove events to existing packs
        document.querySelectorAll('.pack-card').forEach(pack => {
            attachRemovePackEvent(pack);
        });

        // === Manage Additional Images: removal of existing images ===
        const existingImagesContainer = document.getElementById('existingImagesContainer');
        const newImagesContainer = document.getElementById('newImagesContainer');
        const addImageBtn = document.getElementById('addImageBtn');

        // When user clicks "×" on an existing image, disable it and send its id via deleted_images[]
        existingImagesContainer.querySelectorAll('.remove-existing-image').forEach(btn => {
            btn.addEventListener('click', function() {
                const imageDiv = this.closest('.existing-image-item');
                const hiddenInput = imageDiv.querySelector('input[name="deleted_images[]"]');
                if (hiddenInput) {
                    hiddenInput.disabled = false;  // enable to send value
                }
                imageDiv.style.opacity = '0.5';
                imageDiv.style.pointerEvents = 'none';
                // Optionally add a "removed" text
                const removedBadge = document.createElement('div');
                removedBadge.className = 'badge bg-danger position-absolute bottom-0 start-0 m-1';
                removedBadge.innerText = 'Removed';
                imageDiv.querySelector('.position-relative').appendChild(removedBadge);
            });
        });

        // Add new image input field (multiple files allowed, but we'll allow multiple inputs for simplicity)
        addImageBtn.addEventListener('click', function() {
            const inputId = 'new_image_' + Date.now();
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

        // Optionally: Remove any empty new image item on submit? Not needed.

        // Ensure that if no packs exist, we still have a container and the add button works.
        if (packsContainer.children.length === 0 || (packsContainer.children.length === 1 && packsContainer.children[0].classList.contains('alert'))) {
            // remove the alert if present
            const alertDiv = packsContainer.querySelector('.alert');
            if (alertDiv) alertDiv.remove();
        }

        // Also ensure that boolean checkboxes send proper value (Laravel expects 0/1). But the checkboxes with value="1" will send 1 only if checked; if unchecked they send nothing, which is fine. We'll add hidden inputs for all checkboxes to ensure 0 is sent.
        // This is optional, but for consistency we can add a small script before submit.
        const form = document.getElementById('itemForm');
        form.addEventListener('submit', function(e) {
            // Ensure all boolean fields (checkboxes) send a value even if not checked.
            // We'll find all checkboxes that are not inside a pack card? Actually all boolean fields are standalone.
            const booleanCheckboxes = form.querySelectorAll('input[type="checkbox"][value="1"]:not(.pack-card input)');
            booleanCheckboxes.forEach(cb => {
                if (!cb.checked) {
                    // Create hidden input with same name and value 0
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = cb.name;
                    hidden.value = '0';
                    cb.parentNode.appendChild(hidden);
                }
            });
            // For pack checkboxes, we also need to ensure they send 0 if not checked. But they are inside pack-card.
            const packCheckboxes = form.querySelectorAll('.pack-card input[type="checkbox"][value="1"]');
            packCheckboxes.forEach(cb => {
                if (!cb.checked) {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = cb.name;
                    hidden.value = '0';
                    cb.parentNode.appendChild(hidden);
                }
            });
        });
    });
</script>
@endpush
@endsection