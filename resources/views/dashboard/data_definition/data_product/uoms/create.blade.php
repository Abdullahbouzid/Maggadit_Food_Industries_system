@extends('dashboard.partials.layout')

@section('title', 'Add New UOM')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-plus-circle" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            Add New Unit of Measure
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Create a new UOM with bilingual names and optional symbol/image</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('uoms.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="nova-card">
    <div class="nova-card-body">
        <form action="{{ route('uoms.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="nova-row nova-gap-3">
                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">Code <span class="nova-text-danger">*</span></label>
                        <input type="text" name="code" class="nova-input" value="{{ old('code') }}" required>
                        @error('code') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">Symbol (e.g., kg, m, pc)</label>
                        <input type="text" name="uom_symbol" class="nova-input" value="{{ old('uom_symbol') }}">
                        @error('uom_symbol') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">Name (English) <span class="nova-text-danger">*</span></label>
                        <input type="text" name="name_en" class="nova-input" value="{{ old('name_en') }}" required>
                        @error('name_en') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">Name (Arabic) <span class="nova-text-danger">*</span></label>
                        <input type="text" name="name_ar" class="nova-input" value="{{ old('name_ar') }}" required>
                        @error('name_ar') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="nova-col" style="grid-column: 1 / -1;">
                    <div class="nova-form-group">
                        <label class="nova-label">Description</label>
                        <textarea name="description" class="nova-input" rows="3">{{ old('description') }}</textarea>
                        @error('description') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">Image</label>
                        <input type="file" name="image" class="nova-input" accept="image/*">
                        <small class="nova-help-text">Allowed: jpeg, png, jpg, gif (max 2MB)</small>
                        @error('image') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">Status</label>
                        <select name="is_active" class="nova-select">
                            <option value="1" {{ old('is_active', true) ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">Sort Order</label>
                        <input type="number" name="sort_order" class="nova-input" value="{{ old('sort_order') }}">
                        @error('sort_order') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="nova-col" style="grid-column: 1 / -1;">
                    <div class="nova-flex nova-items-center nova-gap-2">
                        <button type="submit" class="nova-btn nova-btn-primary">
                            <i class="fas fa-save"></i> Save UOM
                        </button>
                        <a href="{{ route('uoms.index') }}" class="nova-btn nova-btn-outline">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
