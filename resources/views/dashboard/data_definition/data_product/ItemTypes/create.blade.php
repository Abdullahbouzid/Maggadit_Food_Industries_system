@extends('dashboard.partials.layout')

@section('title', 'إضافة جديد نوع العنصر')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-plus-circle" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            إضافة جديد نوع العنصر
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Create a new item type with code, name, and optional image</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('item-types.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> رجوع
        </a>
    </div>
</div>

<div class="nova-card">
    <div class="nova-card-body">
        <form action="{{ route('item-types.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="nova-row nova-gap-3">
                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">Code <span class="nova-text-danger">*</span></label>
                        <input type="text" name="code" class="nova-input @error('code') nova-invalid @enderror" value="{{ old('code') }}" required>
                        @error('code') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">Name <span class="nova-text-danger">*</span></label>
                        <input type="text" name="name" class="nova-input @error('name') nova-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">الوصف</label>
                        <textarea name="description" class="nova-input" rows="3">{{ old('description') }}</textarea>
                        @error('description') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">الصورة</label>
                        <input type="file" name="image" class="nova-input" accept="image/*">
                        <small class="nova-help-text">الكلowed formats: jpeg, png, jpg, gif (max 2MB)</small>
                        @error('image') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">الحالة</label>
                        <select name="is_active" class="nova-select">
                            <option value="1" {{ old('is_active', true) ? 'selected' : '' }}>{{ __('messages.filter.status_active') }}</option>
                            <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>{{ __('messages.filter.status_inactive') }}</option>
                        </select>
                    </div>
                </div>

                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">ترتيب الفرز</label>
                        <input type="number" name="sort_order" class="nova-input" value="{{ old('sort_order') }}">
                        @error('sort_order') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="nova-col" style="grid-column: 1 / -1;">
                    <div class="nova-flex nova-items-center nova-gap-2">
                        <button type="submit" class="nova-btn nova-btn-primary">
                            <i class="fas fa-save"></i> حفظ نوع العنصر
                        </button>
                        <a href="{{ route('item-types.index') }}" class="nova-btn nova-btn-outline">إلغاء</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
