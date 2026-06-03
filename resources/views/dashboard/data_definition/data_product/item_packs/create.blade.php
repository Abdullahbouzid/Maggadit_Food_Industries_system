@extends('dashboard.partials.layout')

@section('title', 'Add Item Pack')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold"><i data-feather="package"></i> Add Item Pack</h1>
                        <p class="text-muted">Define a new packaging variation for an item.</p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <form action="{{ route('item-packs.store') }}" method="POST">
                            @csrf
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Item <span class="text-danger">*</span></label>
                                    <select name="item_id" class="form-select @error('item_id') is-invalid @enderror" required>
                                        <option value="">Select item</option>
                                        @foreach($items as $item)
                                            <option value="{{ $item->id }}" {{ old('item_id') == $item->id ? 'selected' : '' }}>{{ $item->name_en ?? $item->name_ar }} ({{ $item->code }})</option>
                                        @endforeach
                                    </select>
                                    @error('item_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">UOM <span class="text-danger">*</span></label>
                                    <select name="uom_id" class="form-select @error('uom_id') is-invalid @enderror" required>
                                        <option value="">Select unit</option>
                                        @foreach($uoms as $uom)
                                            <option value="{{ $uom->id }}" {{ old('uom_id') == $uom->id ? 'selected' : '' }}>{{ $uom->name_en }} ({{ $uom->uom_symbol }})</option>
                                        @endforeach
                                    </select>
                                    @error('uom_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" step="0.001" min="0.001" name="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity') }}" required>
                                    @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Barcode <span class="text-danger">*</span></label>
                                    <input type="text" name="barcode" class="form-control @error('barcode') is-invalid @enderror" value="{{ old('barcode') }}" required>
                                    @error('barcode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Serial Separator</label>
                                    <input type="text" name="serial_separator" class="form-control @error('serial_separator') is-invalid @enderror" value="{{ old('serial_separator') }}">
                                    @error('serial_separator') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Equivalency Factor</label>
                                    <input type="number" step="0.0001" min="0" name="equivalency_factor" class="form-control @error('equivalency_factor') is-invalid @enderror" value="{{ old('equivalency_factor') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Height</label>
                                    <input type="number" step="0.01" min="0" name="height" class="form-control @error('height') is-invalid @enderror" value="{{ old('height') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Width</label>
                                    <input type="number" step="0.01" min="0" name="width" class="form-control @error('width') is-invalid @enderror" value="{{ old('width') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Depth</label>
                                    <input type="number" step="0.01" min="0" name="depth" class="form-control @error('depth') is-invalid @enderror" value="{{ old('depth') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Weight</label>
                                    <input type="number" step="0.01" min="0" name="weight" class="form-control @error('weight') is-invalid @enderror" value="{{ old('weight') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Sort Order</label>
                                    <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order') }}">
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check mt-4">
                                        <input type="hidden" name="is_default_pack" value="0">
                                        <input type="checkbox" name="is_default_pack" class="form-check-input" value="1" {{ old('is_default_pack') ? 'checked' : '' }}>
                                        <label class="form-check-label">Default Pack</label>
                                    </div>
                                    <div class="form-check mt-2">
                                        <input type="hidden" name="is_weight_priced" value="0">
                                        <input type="checkbox" name="is_weight_priced" class="form-check-input" value="1" {{ old('is_weight_priced') ? 'checked' : '' }}>
                                        <label class="form-check-label">Weight Priced</label>
                                    </div>
                                    <div class="form-check mt-2">
                                        <input type="hidden" name="allow_fraction_in_qty" value="0">
                                        <input type="checkbox" name="allow_fraction_in_qty" class="form-check-input" value="1" {{ old('allow_fraction_in_qty') ? 'checked' : '' }}>
                                        <label class="form-check-label">Allow Fraction Qty</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Pack Operations</label>
                                    <select name="pack_operation_ids[]" class="form-select @error('pack_operation_ids') is-invalid @enderror" multiple>
                                        @foreach($packOperations as $operation)
                                            <option value="{{ $operation->id }}" {{ in_array($operation->id, old('pack_operation_ids', [])) ? 'selected' : '' }}>{{ $operation->name_en }} ({{ $operation->code }})</option>
                                        @endforeach
                                    </select>
                                    @error('pack_operation_ids') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Pack Groups</label>
                                    <select name="pack_group_ids[]" class="form-select @error('pack_group_ids') is-invalid @enderror" multiple>
                                        @foreach($packGroups as $group)
                                            <option value="{{ $group->id }}" {{ in_array($group->id, old('pack_group_ids', [])) ? 'selected' : '' }}>{{ $group->name_en }} ({{ $group->code }})</option>
                                        @endforeach
                                    </select>
                                    @error('pack_group_ids') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <hr>
                                    <button type="submit" class="btn btn-primary px-5">Save Item Pack</button>
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
