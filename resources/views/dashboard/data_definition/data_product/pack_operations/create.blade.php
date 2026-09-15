@extends('dashboard.partials.layout')

@section('title', 'إضافة جديد Pack Operation')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-plus-circle" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            إضافة جديد Pack Operation
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Examples: "Sales Order Return +10", "Transfer +5", "Pack +20"</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('pack-operations.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> رجوع
        </a>
    </div>
</div>

@if($errors->any())
    <div class="nova-alert danger">
        <i class="fas fa-exclamation-triangle"></i>
        <ul class="nova-mb-0 nova-mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h5 class="nova-fw-bold mb-0"><i class="fas fa-file-alt me-1"></i> Pack Operation Information</h5>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('pack-operations.store') }}" method="POST">
            @csrf
            <div class="nova-row g-4">
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">Code <span class="nova-text-danger">*</span></label>
                        <input type="text" name="code" class="nova-input @error('code') nova-invalid @enderror" value="{{ old('code') }}" required>
                        @error('code')
                            <div class="nova-form-error">{{ $message }}</div>
                        @enderror
                        <div class="nova-help-text">Unique identifier (e.g., SOR10, TRF5)</div>
                    </div>
                </div>
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">الاسم بالإنجليزي <span class="nova-text-danger">*</span></label>
                        <input type="text" name="name_en" class="nova-input @error('name_en') nova-invalid @enderror" value="{{ old('name_en') }}" required>
                        @error('name_en')
                            <div class="nova-form-error">{{ $message }}</div>
                        @enderror
                        <div class="nova-help-text">Example: "Sales Order Return +10"</div>
                    </div>
                </div>
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">الاسم بالعربي <span class="nova-text-danger">*</span></label>
                        <input type="text" name="name_ar" class="nova-input @error('name_ar') nova-invalid @enderror" value="{{ old('name_ar') }}" required>
                        @error('name_ar')
                            <div class="nova-form-error">{{ $message }}</div>
                        @enderror
                        <div class="nova-help-text">مثال: "مرتجعات أمر البيع +10"</div>
                    </div>
                </div>
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">الحالة</label>
                        <select name="is_active" class="nova-select @error('is_active') nova-invalid @enderror">
                            <option value="1" {{ old('is_active', true) ? 'selected' : '' }}>نشط</option>
                            <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>غير نشط</option>
                        </select>
                        @error('is_active')
                            <div class="nova-form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">ترتيب الفرز</label>
                        <input type="number" name="sort_order" class="nova-input @error('sort_order') nova-invalid @enderror" value="{{ old('sort_order') }}">
                        @error('sort_order')
                            <div class="nova-form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-12">
                    <div class="nova-form-actions">
                        <a href="{{ route('pack-operations.index') }}" class="nova-btn nova-btn-outline">
                            <i class="fas fa-x me-1"></i> إلغاء
                        </a>
                        <button type="submit" class="nova-btn nova-btn-primary">
                            <i class="fas fa-save me-1"></i> حفظ Operation
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
