@extends('dashboard.partials.layout')

@section('title', 'إنشاء جهاز جديد')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-plus-circle" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            إنشاء جهاز جديد
        </h2>
        <p class="nova-page-subtitle">أدخل بيانات الجهاز الجديد</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('devices.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
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
            <i class="fas fa-file-alt"></i> بيانات الجهاز
        </h5>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('devices.store') }}" method="POST">
            @csrf

            <div class="nova-row g-4">
                {{-- Name (EN) --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-tag me-1"></i> الاسم (إنجليزي) <span class="nova-text-danger">*</span>
                        </label>
                        <input type="text"
                               name="name_en"
                               class="nova-input @error('name_en') nova-invalid @enderror"
                               value="{{ old('name_en') }}"
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
                               value="{{ old('name_ar') }}"
                               placeholder="أدخل الاسم بالعربية..."
                               required>
                    </div>
                    @error('name_ar') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                {{-- Serial Number --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-qrcode me-1"></i> الشيفرة الخاصة <span class="nova-text-danger">*</span>
                        </label>
                        <input type="text"
                               name="serial"
                               class="nova-input @error('serial') nova-invalid @enderror"
                               value="{{ old('serial') }}"
                               placeholder="أدخل الشيفرة الخاصة..."
                               required>
                    </div>
                    @error('serial') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                {{-- Organization --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-building me-1"></i> المنظمة <span class="nova-text-danger">*</span>
                        </label>
                        <select name="organization_id"
                                class="nova-select @error('organization_id') nova-invalid @enderror"
                                required>
                            <option value="">اختر المنظمة</option>
                            @foreach($organizations as $org)
                                <option value="{{ $org->id }}" {{ old('organization_id') == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('organization_id') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                {{-- Model لا --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-cube me-1"></i> رقم الموديل
                        </label>
                        <input type="text"
                               name="model_no"
                               class="nova-input"
                               value="{{ old('model_no') }}"
                               placeholder="أدخل رقم الموديل...">
                    </div>
                </div>

                {{-- Brand --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-tag me-1"></i> الماركة (صانع الجهاز)
                        </label>
                        <input type="text"
                               name="brand"
                               class="nova-input"
                               value="{{ old('brand') }}"
                               placeholder="مثال: أبل، سامسونج، ديل...">
                    </div>
                </div>

                {{-- Purchase Date --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-calendar me-1"></i> تاريخ الشراء
                        </label>
                        <input type="date"
                               name="purchase_date"
                               class="nova-input"
                               value="{{ old('purchase_date') }}">
                    </div>
                </div>

                {{-- Warranty Expiry --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-calendar-check me-1"></i> انتهاء الضمان
                        </label>
                        <input type="date"
                               name="warranty_expiry"
                               class="nova-input"
                               value="{{ old('warranty_expiry') }}">
                    </div>
                </div>

                {{-- غير نشط --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-check">
                        <input type="hidden" name="is_inactive" value="0">
                        <input class="nova-form-check-input" type="checkbox" name="is_inactive" value="1" id="is_inactive" {{ old('is_inactive') ? 'checked' : '' }}>
                        <label class="nova-form-check-label fw-bold" for="is_inactive">غير نشط</label>
                        <div class="nova-help-text">تعطيل هذا الجهاز</div>
                    </div>
                </div>

                {{-- Remark --}}
                <div class="nova-col nova-col-12">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-comment me-1"></i> الملاحظات
                        </label>
                        <textarea name="remark"
                                  class="nova-textarea"
                                  rows="3"
                                  placeholder="أدخل ملاحظات إضافية...">{{ old('remark') }}</textarea>
                    </div>
                </div>

                {{-- Form إجراءات --}}
                <div class="nova-col nova-col-12">
                    <div class="nova-form-actions">
                        <a href="{{ route('devices.index') }}" class="nova-btn nova-btn-outline">
                            <i class="fas fa-x me-1"></i> إلغاء
                        </a>
                        <button type="submit" class="nova-btn nova-btn-primary">
                            <i class="fas fa-save me-1"></i> حفظ
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
