@extends('dashboard.partials.layout')

@section('title', 'إضافة قائمة أسعار')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-plus-circle" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            إضافة قائمة أسعار
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Create a new price list</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('price-lists.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="nova-card">
    <div class="nova-card-body">
        <form method="POST" action="{{ route('price-lists.store') }}">
            @csrf
            <div class="nova-form-group">
                <label class="nova-label">الاسم <span class="nova-text-danger">*</span></label>
                <input type="text" name="name" class="nova-input" value="{{ old('name') }}" required>
                @error('name') <div class="nova-form-error">{{ $message }}</div> @enderror
            </div>
            <div class="nova-form-group">
                <label class="nova-label">الوصف</label>
                <textarea name="description" class="nova-input" rows="3">{{ old('description') }}</textarea>
                @error('description') <div class="nova-form-error">{{ $message }}</div> @enderror
            </div>
            <div class="nova-form-group">
                <label class="nova-label">الحالة</label>
                <select name="status" class="nova-select">
                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>نشطة</option>
                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>غير نشطة</option>
                </select>
            </div>
            <div class="nova-flex nova-items-center nova-gap-2">
                <button type="submit" class="nova-btn nova-btn-primary">
                    <i class="fas fa-save"></i> حفظ
                </button>
                <a href="{{ route('price-lists.index') }}" class="nova-btn nova-btn-outline">
                    <i class="fas fa-times"></i> إلغاء
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

