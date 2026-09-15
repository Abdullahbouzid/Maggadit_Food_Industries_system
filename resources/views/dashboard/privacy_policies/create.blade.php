@extends('dashboard.partials.layout')

@section('title', 'إضافة سياسة')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-{{ __('messages.app.page_') }}">
            <i class="fas fa-plus-circle" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            إضافة سياسة
        </h2>
        <p class="nova-page-sub{{ __('messages.app.page_') }}">أضف سياسة خصوصية أو شروط خدمة جديدة</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('privacy-policies.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> {{ __('messages.app.page__2') }}
        </a>
    </div>
</div>

@if($errors->any())
    <div class="nova-alert danger">
        <i class="fas fa-exclamation-triangle"></i>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h5 class="nova-fw-bold mb-0"><i class="fas fa-file-alt me-1"></i> {{ __('messages.app.page__1') }}</h5>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('privacy-policies.store') }}" method="POST">
            @csrf
            <div class="nova-row g-4">
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">المنظمة</label>
                        <select name="organization_id" class="nova-select">
                            <option value="">عامة (لجميع المنظمات)</option>
                            @foreach($organizations as $id => $name)
                                <option value="{{ $id }}" {{ old('organization_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        <div class="nova-help-text">اتركه فارغاً ليكون هذا المحتوى عاماً</div>
                    </div>
                </div>

                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">النوع <span class="nova-text-danger">*</span></label>
                        <select name="type" class="nova-select" required>
                            <option value="privacy_policy" {{ old('type') == 'privacy_policy' ? 'selected' : '' }}>سياسة خصوصية</option>
                            <option value="terms_of_service" {{ old('type') == 'terms_of_service' ? 'selected' : '' }}>شروط خدمة</option>
                        </select>
                    </div>
                </div>

                <div class="nova-col nova-col-12">
                    <div class="nova-form-group">
                        <label class="nova-label">العنوان</label>
                        <input type="text" name="{{ __('messages.app.page_') }}" class="nova-input" value="{{ old('{{ __('messages.app.page_') }}') }}" placeholder="مثال: سياسة الخصوصية الرسمية">
                    </div>
                </div>

                <div class="nova-col nova-col-12">
                    <div class="nova-form-group">
                        <label class="nova-label">المحتوى <span class="nova-text-danger">*</span></label>
                        <textarea name="content" rows="12" class="nova-input @error('content') nova-invalid @enderror" placeholder="أدخل نص السياسة أو الشروط هنا...">{{ old('content') }}</textarea>
                        @error('content')
                            <div class="nova-form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="nova-col nova-col-12">
                    <div class="nova-form-check">
                        <input type="checkbox" name="is_active" class="nova-form-check-input" id="isActive" value="1" {{ old('is_active') ? 'checked' : '' }}>
                        <label class="nova-form-check-label" for="isActive">نشط</label>
                        <div class="nova-help-text">سيظهر هذا المحتوى للعملاء في تطبيقهم</div>
                    </div>
                </div>

                <div class="nova-col nova-col-12">
                    <div class="nova-form-actions">
                        <a href="{{ route('privacy-policies.index') }}" class="nova-btn nova-btn-outline">
                            <i class="fas fa-x me-1"></i> إلغاء
                        </a>
                        <button type="submit" class="nova-btn nova-btn-primary">
                            <i class="fas fa-save me-1"></i> حفظ السياسة
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
