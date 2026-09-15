@extends('dashboard.partials.layout')

@section('title', 'تعديل UOM: ' . $uom->name_en)

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-edit" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            تعديل UOM
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Update details for <strong>{{ $uom->name_en }} / {{ $uom->name_ar }}</strong></p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('uoms.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> رجوع
        </a>
    </div>
</div>

<div class="nova-card">
    <div class="nova-card-body">
        <form action="{{ route('uoms.update', $uom) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="nova-row nova-gap-3">
                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">Code <span class="nova-text-danger">*</span></label>
                        <input type="text" name="code" class="nova-input" value="{{ old('code', $uom->code) }}" required>
                        @error('code') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">Symbol (e.g., kg, m, pc)</label>
                        <input type="text" name="uom_symbol" class="nova-input" value="{{ old('uom_symbol', $uom->uom_symbol) }}">
                        @error('uom_symbol') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">الاسم بالإنجليزي <span class="nova-text-danger">*</span></label>
                        <input type="text" name="name_en" class="nova-input" value="{{ old('name_en', $uom->name_en) }}" required>
                        @error('name_en') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">الاسم بالعربي <span class="nova-text-danger">*</span></label>
                        <input type="text" name="name_ar" class="nova-input" value="{{ old('name_ar', $uom->name_ar) }}" required>
                        @error('name_ar') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="nova-col" style="grid-column: 1 / -1;">
                    <div class="nova-form-group">
                        <label class="nova-label">الوصف</label>
                        <textarea name="description" class="nova-input" rows="3">{{ old('description', $uom->description) }}</textarea>
                        @error('description') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">Current الصورة</label>
                        @if($uom->image)
                            <img src="{{ asset('uploads/' . $uom->image) }}" alt="{{ $uom->name_en }}" class="nova-rounded" style="width: 100px; height: 100px; object-fit: cover; margin-bottom: 0.5rem;">
                        @else
                            <p class="nova-text-muted">لا image</p>
                        @endif
                        <input type="file" name="image" class="nova-input nova-mt-2">
                        <small class="nova-help-text">Leave empty to keep current image. Max 2MB.</small>
                        @error('image') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">الحالة</label>
                        <select name="is_active" class="nova-select">
                            <option value="1" {{ old('is_active', $uom->is_active) ? 'selected' : '' }}>نشط</option>
                            <option value="0" {{ old('is_active', $uom->is_active) ? '' : 'selected' }}>غير نشط</option>
                        </select>
                    </div>
                </div>

                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">ترتيب الفرز</label>
                        <input type="number" name="sort_order" class="nova-input" value="{{ old('sort_order', $uom->sort_order) }}">
                        @error('sort_order') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="nova-col" style="grid-column: 1 / -1;">
                    <div class="nova-flex nova-items-center nova-gap-2">
                        <button type="submit" class="nova-btn nova-btn-primary">
                            <i class="fas fa-save"></i> Update UOM
                        </button>
                        <a href="{{ route('uoms.index') }}" class="nova-btn nova-btn-outline">إلغاء</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
