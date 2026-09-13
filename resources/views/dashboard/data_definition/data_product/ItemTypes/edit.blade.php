@extends('dashboard.partials.layout')

@section('title', 'Edit Item Type: ' . $itemType->name)

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-edit" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            Edit Item Type
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Update details for <strong>{{ $itemType->name }}</strong></p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('item-types.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="nova-card">
    <div class="nova-card-body">
        <form action="{{ route('item-types.update', $itemType) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="nova-row nova-gap-3">
                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">Code <span class="nova-text-danger">*</span></label>
                        <input type="text" name="code" class="nova-input @error('code') nova-invalid @enderror" value="{{ old('code', $itemType->code) }}" required>
                        @error('code') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">Name <span class="nova-text-danger">*</span></label>
                        <input type="text" name="name" class="nova-input @error('name') nova-invalid @enderror" value="{{ old('name', $itemType->name) }}" required>
                        @error('name') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">Description</label>
                        <textarea name="description" class="nova-input" rows="3">{{ old('description', $itemType->description) }}</textarea>
                        @error('description') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">Current Image</label>
                        @if($itemType->image)
                            <img src="{{ asset('uploads/' . $itemType->image) }}" alt="{{ $itemType->name }}" class="nova-rounded" style="width: 100px; height: 100px; object-fit: cover; margin-bottom: 0.5rem;">
                        @else
                            <p class="nova-text-muted">No image</p>
                        @endif
                        <input type="file" name="image" class="nova-input nova-mt-2">
                        <small class="nova-help-text">Leave empty to keep current image. Max 2MB.</small>
                        @error('image') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">Status</label>
                        <select name="is_active" class="nova-select">
                            <option value="1" {{ old('is_active', $itemType->is_active) ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('is_active', $itemType->is_active) ? '' : 'selected' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">Sort Order</label>
                        <input type="number" name="sort_order" class="nova-input" value="{{ old('sort_order', $itemType->sort_order) }}">
                        @error('sort_order') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="nova-col" style="grid-column: 1 / -1;">
                    <div class="nova-flex nova-items-center nova-gap-2">
                        <button type="submit" class="nova-btn nova-btn-primary">
                            <i class="fas fa-save"></i> Update Item Type
                        </button>
                        <a href="{{ route('item-types.index') }}" class="nova-btn nova-btn-outline">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
