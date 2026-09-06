@extends('dashboard.partials.layout')

@section('title', 'Edit Item Pack')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold"><i data-feather="edit-2"></i> Edit Item Pack</h1>
                        <p class="text-muted">Update packaging details for <strong>{{ $itemPack->barcode }}</strong>.</p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <form action="{{ route('item-packs.update', $itemPack) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row g-4">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Item <span class="text-danger">*</span></label>
                                    <select name="item_id" class="form-select @error('item_id') is-invalid @enderror" required>
                                        <option value="">Select item</option>
                                        @foreach($items as $item)
                                            <option value="{{ $item->id }}" {{ old('item_id', $itemPack->item_id) == $item->id ? 'selected' : '' }}>{{ $item->name_en ?? $item->name_ar }} ({{ $item->code }})</option>
                                        @endforeach
                                    </select>
                                    @error('item_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Pack Definition</label>
                                    <select name="pack_definition_id" class="form-select @error('pack_definition_id') is-invalid @enderror" id="packDefinitionSelect">
                                        <option value="">Select pack definition</option>
                                        @foreach($packDefinitions as $def)
                                            <option value="{{ $def->id }}" {{ old('pack_definition_id', $itemPack->pack_definition_id) == $def->id ? 'selected' : '' }}>{{ $def->name_en ?? $def->name_ar }} ({{ $def->code }})</option>
                                        @endforeach
                                    </select>
                                    @error('pack_definition_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Pack Type <span class="text-danger">*</span></label>
                                    <select name="pack_type_id" class="form-select @error('pack_type_id') is-invalid @enderror" required>
                                        <option value="">Select pack type</option>
                                        @foreach($packTypes as $packType)
                                            <option value="{{ $packType->id }}" {{ old('pack_type_id', $itemPack->pack_type_id) == $packType->id ? 'selected' : '' }}>{{ $packType->name_en ?? $packType->name_ar }}</option>
                                        @endforeach
                                    </select>
                                    @error('pack_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Pack Capacity <span class="text-danger">*</span></label>
                                    <select name="pack_capacity_id" class="form-select @error('pack_capacity_id') is-invalid @enderror" required>
                                        <option value="">Select capacity</option>
                                        @foreach($packCapacities as $capacity)
                                            <option value="{{ $capacity->id }}" {{ old('pack_capacity_id', $itemPack->pack_capacity_id) == $capacity->id ? 'selected' : '' }}>{{ $capacity->name_en ?? $capacity->name_ar }} ({{ $capacity->uom->uom_symbol ?? '' }})</option>
                                        @endforeach
                                    </select>
                                    @error('pack_capacity_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Quantity in Pack <span class="text-danger">*</span></label>
                                    <input type="number" step="0.001" min="0.001" name="quantity_in_pack" class="form-control @error('quantity_in_pack') is-invalid @enderror" value="{{ old('quantity_in_pack', $itemPack->quantity_in_pack) }}" required>
                                    @error('quantity_in_pack') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Barcode <span class="text-danger">*</span></label>
                                    <input type="text" name="barcode" class="form-control @error('barcode') is-invalid @enderror" value="{{ old('barcode', $itemPack->barcode) }}" required>
                                    @error('barcode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Serial Separator</label>
                                    <input type="text" name="serial_separator" class="form-control @error('serial_separator') is-invalid @enderror" value="{{ old('serial_separator', $itemPack->serial_separator) }}">
                                    @error('serial_separator') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Equivalency Factor</label>
                                    <input type="number" step="0.0001" min="0" name="equivalency_factor" class="form-control @error('equivalency_factor') is-invalid @enderror" value="{{ old('equivalency_factor', $itemPack->equivalency_factor) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Height</label>
                                    <input type="number" step="0.01" min="0" name="height" class="form-control @error('height') is-invalid @enderror" value="{{ old('height', $itemPack->height) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Width</label>
                                    <input type="number" step="0.01" min="0" name="width" class="form-control @error('width') is-invalid @enderror" value="{{ old('width', $itemPack->width) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Depth</label>
                                    <input type="number" step="0.01" min="0" name="depth" class="form-control @error('depth') is-invalid @enderror" value="{{ old('depth', $itemPack->depth) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Weight</label>
                                    <input type="number" step="0.01" min="0" name="weight" class="form-control @error('weight') is-invalid @enderror" value="{{ old('weight', $itemPack->weight) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Sort Order</label>
                                    <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $itemPack->sort_order) }}">
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check mt-4">
                                        <input type="hidden" name="is_default_pack" value="0">
                                        <input type="checkbox" name="is_default_pack" class="form-check-input" value="1" {{ old('is_default_pack', $itemPack->is_default_pack) ? 'checked' : '' }}>
                                        <label class="form-check-label">Default Pack</label>
                                    </div>
                                    <div class="form-check mt-2">
                                        <input type="hidden" name="is_weight_priced" value="0">
                                        <input type="checkbox" name="is_weight_priced" class="form-check-input" value="1" {{ old('is_weight_priced', $itemPack->is_weight_priced) ? 'checked' : '' }}>
                                        <label class="form-check-label">Weight Priced</label>
                                    </div>
                                    <div class="form-check mt-2">
                                        <input type="hidden" name="allow_fraction_in_qty" value="0">
                                        <input type="checkbox" name="allow_fraction_in_qty" class="form-check-input" value="1" {{ old('allow_fraction_in_qty', $itemPack->allow_fraction_in_qty) ? 'checked' : '' }}>
                                        <label class="form-check-label">Allow Fraction Qty</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Pack Operations</label>
                                    <select name="pack_operation_ids[]" class="form-select @error('pack_operation_ids') is-invalid @enderror" multiple>
                                        @foreach($packOperations as $operation)
                                            <option value="{{ $operation->id }}" {{ in_array($operation->id, old('pack_operation_ids', $itemPack->packOperations->pluck('id')->toArray())) ? 'selected' : '' }}>{{ $operation->name_en }} ({{ $operation->code }})</option>
                                        @endforeach
                                    </select>
                                    @error('pack_operation_ids') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Pack Groups</label>
                                    <select name="pack_group_ids[]" class="form-select @error('pack_group_ids') is-invalid @enderror" multiple>
                                        @foreach($packGroups as $group)
                                            <option value="{{ $group->id }}" {{ in_array($group->id, old('pack_group_ids', $itemPack->packGroups->pluck('id')->toArray())) ? 'selected' : '' }}>{{ $group->name_en }} ({{ $group->code }})</option>
                                        @endforeach
                                    </select>
                                    @error('pack_group_ids') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <hr>
                                    <button type="submit" class="btn btn-primary px-5">Update Item Pack</button>
                                    <a href="{{ route('item-packs.index') }}" class="btn btn-secondary px-5">Cancel</a>
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
    document.addEventListener('DOMContentLoaded', function () {
        var select = document.getElementById('packDefinitionSelect');
        if (!select) return;

        select.addEventListener('change', function () {
            var packDefinitionId = this.value;
            if (!packDefinitionId) return;

            fetch('/pack-definitions/' + packDefinitionId + '/show-ajax')
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    var setVal = function (name, value) {
                        var el = document.querySelector('[name="' + name + '"]');
                        if (el) el.value = value;
                    };
                    var setChecked = function (name, checked) {
                        var el = document.querySelector('[name="' + name + '"]');
                        if (el) el.checked = !!checked;
                    };

                    setVal('pack_type_id', data.pack_type_id);
                    setVal('pack_capacity_id', data.pack_capacity_id);
                    setVal('quantity_in_pack', data.quantity_in_pack);
                    setVal('barcode', data.barcode);
                    setVal('serial_separator', data.serial_separator);
                    setVal('equivalency_factor', data.equivalency_factor);
                    setVal('height', data.height);
                    setVal('width', data.width);
                    setVal('depth', data.depth);
                    setVal('weight', data.weight);
                    setChecked('is_weight_priced', data.is_weight_priced);
                    setChecked('allow_fraction_in_qty', data.allow_fraction_in_qty);
                    setChecked('is_default_pack', data.is_default_pack);
                })
                .catch(function (err) { console.error('Error fetching pack definition data', err); });
        });
    });
</script>
@endpush
