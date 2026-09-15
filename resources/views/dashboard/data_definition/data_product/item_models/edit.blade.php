@extends('dashboard.partials.layout')

@section('title', 'تعديل نموذج منتج')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-edit" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            تعديل نموذج المنتج: {{ $itemModel->name_en }}
        </h2>
        <p class="nova-page-subtitle">تحرير بيانات نموذج المنتج</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('item-models.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> العودة
        </a>
    </div>
</div>

@if($errors->any())
    <div class="nova-alert danger">
        <i class="fas fa-exclamation-triangle"></i>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="nova-card">
    <div class="nova-card-header">
        <h5 class="mb-0 fw-bold">
            <i class="fas fa-file-alt"></i> بيانات نموذج المنتج
        </h5>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('item-models.update', $itemModel) }}" method="POST">
            @csrf @method('PUT')

            <div class="nova-row g-4">
                {{-- Code --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-qrcode me-1"></i> الشيفرة <span class="nova-text-danger">*</span>
                        </label>
                        <input type="text"
                               name="code"
                               class="nova-input @error('code') nova-invalid @enderror"
                               value="{{ old('code', $itemModel->code) }}"
                               placeholder="أدخل الشيفرة..."
                               required>
                    </div>
                    @error('code') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                {{-- ترتيب الفرز --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-arrows-up-down me-1"></i> ترتيب الفرز
                        </label>
                        <input type="number"
                               name="sort_order"
                               class="nova-input @error('sort_order') nova-invalid @enderror"
                               value="{{ old('sort_order', $itemModel->sort_order) }}"
                               placeholder="أدخل رقم الترتيب...">
                    </div>
                    @error('sort_order') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                {{-- Name (EN) --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-tag me-1"></i> الاسم (إنجليزي) <span class="nova-text-danger">*</span>
                        </label>
                        <input type="text"
                               name="name_en"
                               class="nova-input @error('name_en') nova-invalid @enderror"
                               value="{{ old('name_en', $itemModel->name_en) }}"
                               placeholder="أدخل الاسم بالإنجليزية..."
                               required>
                    </div>
                    @error('name_en') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                {{-- Name (AR) --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-tag me-1"></i> الاسم (عربي) <span class="nova-text-danger">*</span>
                        </label>
                        <input type="text"
                               name="name_ar"
                               class="nova-input @error('name_ar') nova-invalid @enderror"
                               value="{{ old('name_ar', $itemModel->name_ar) }}"
                               placeholder="أدخل الاسم بالعربية..."
                               required>
                    </div>
                    @error('name_ar') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                {{-- نشط --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-check">
                        <input type="hidden" name="is_active" value="0">
                        <input class="nova-form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $itemModel->is_active) ? 'checked' : '' }}>
                        <label class="nova-form-check-label fw-bold" for="is_active">نشط</label>
                        <div class="nova-help-text">تفعيل هذا النموذج</div>
                    </div>
                </div>

                {{-- Form إجراءات --}}
                <div class="nova-col nova-col-12">
                    <div class="nova-form-actions">
                        <a href="{{ route('item-models.index') }}" class="nova-btn nova-btn-outline">
                            <i class="fas fa-x me-1"></i> إلغاء
                        </a>
                        <button type="submit" class="nova-btn nova-btn-primary">
                            <i class="fas fa-save me-1"></i> تحديث
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
