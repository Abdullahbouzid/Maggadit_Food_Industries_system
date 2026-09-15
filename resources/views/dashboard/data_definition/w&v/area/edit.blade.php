@extends('dashboard.partials.layout')

@section('title', 'تعديل منطقة')

@section('content')
{{-- ===== Action Bar ===== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-edit" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            تعديل منطقة: {{ $area->code }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">تحديث معلومات المنطقة</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('areas.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> عودة
        </a>
    </div>
</div>

{{-- ===== Validation خطأs ===== --}}
@if($errors->any())
    <div class="nova-alert danger">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <strong>يرجى تصحيح الأخطاء التالية:</strong>
            <ul class="nova-mb-0 nova-mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<form action="{{ route('areas.update', $area) }}" method="POST" id="areaForm">
    @csrf
    @method('PUT')

    {{-- Basic Information --}}
    <div class="nova-card nova-mb-4">
        <div class="nova-card-header">
            <h5 class="nova-fw-bold mb-0">
                <i class="fas fa-file-alt me-1"></i> المعلومات الأساسية
            </h5>
        </div>
        <div class="nova-card-body">
            <div class="form-grid form-grid-3">
                <div class="nova-form-group">
                    <label class="nova-label">الكود <span class="nova-text-danger">*</span></label>
                    <input type="text" name="code"
                           class="nova-input @error('code') nova-invalid @enderror"
                           value="{{ old('code', $area->code) }}" required>
                    @error('code') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                <div class="nova-form-group">
                    <label class="nova-label">الاسم (إنجليزي) <span class="nova-text-danger">*</span></label>
                    <input type="text" name="name_en"
                           class="nova-input @error('name_en') nova-invalid @enderror"
                           value="{{ old('name_en', $area->name_en) }}" required>
                    @error('name_en') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                <div class="nova-form-group">
                    <label class="nova-label">الاسم (عربي) <span class="nova-text-danger">*</span></label>
                    <input type="text" name="name_ar"
                           class="nova-input @error('name_ar') nova-invalid @enderror"
                           value="{{ old('name_ar', $area->name_ar) }}" required>
                    @error('name_ar') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                <div class="nova-form-group form-grid-span-1">
                    <label class="nova-label">الولاية</label>
                    <select name="state_id" class="nova-select @error('state_id') nova-invalid @enderror">
                        <option value="">-- لا شيء --</option>
                        @foreach($states as $state)
                            <option value="{{ $state->id }}" {{ old('state_id', $area->state_id) == $state->id ? 'selected' : '' }}>
                                {{ $state->name_en }}
                            </option>
                        @endforeach
                    </select>
                    @error('state_id') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                <div class="nova-form-group">
                    <label class="nova-label">ترتيب الفرز</label>
                    <input type="number" name="sort_order" class="nova-input" value="{{ old('sort_order', $area->sort_order) }}">
                </div>

                <div class="nova-form-group form-grid-span-3">
                    <label class="nova-label">الوصف</label>
                    <textarea name="description" class="nova-input @error('description') nova-invalid @enderror" rows="3">{{ old('description', $area->description) }}</textarea>
                    @error('description') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                <div class="nova-form-group">
                    <label class="nova-form-check">
                        <input type="checkbox" name="is_active" value="1" class="nova-form-check-input" id="is_active" {{ old('is_active', $area->is_active) ? 'checked' : '' }}>
                        <span class="nova-form-check-label" for="is_active">نشط</span>
                    </label>
                </div>
            </div>
        </div>
        <div class="nova-card-footer">
            <button type="submit" class="nova-btn nova-btn-primary">تحديث</button>
            <a href="{{ route('areas.index') }}" class="nova-btn nova-btn-secondary">إلغاء</a>
        </div>
    </div>
</form>
@endsection
