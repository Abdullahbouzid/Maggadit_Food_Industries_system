@extends('dashboard.partials.layout')

@section('title', 'إضافة جديد UOM')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-plus-circle" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            إضافة جديد Unit من Measure
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">إنشاء وحدة قياس بأسماء ثنائية وسمات اختيارية</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('uoms.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> رجوع
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
                        <label class="nova-label">الاسم بالإنجليزي <span class="nova-text-danger">*</span></label>
                        <input type="text" name="name_en" class="nova-input" value="{{ old('name_en') }}" required>
                        @error('name_en') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">الاسم بالعربي <span class="nova-text-danger">*</span></label>
                        <input type="text" name="name_ar" class="nova-input" value="{{ old('name_ar') }}" required>
                        @error('name_ar') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="nova-col" style="grid-column: 1 / -1;">
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
                        <small class="nova-help-text">مسموح: jpeg, png, jpg, gif (الحد الأقصى 2MB)</small>
                        @error('image') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">الحالة</label>
                        <select name="is_active" class="nova-select">
                            <option value="1" {{ old('is_active', true) ? 'selected' : '' }}>نشط</option>
                            <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>غير نشط</option>
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
                            <i class="fas fa-save"></i> حفظ UOM
                        </button>
                        <a href="{{ route('uoms.index') }}" class="nova-btn nova-btn-outline">إلغاء</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
