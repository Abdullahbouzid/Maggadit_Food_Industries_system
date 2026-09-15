@extends('dashboard.partials.layout')

@section('title', 'إضافة جديد Brand')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-plus-circle" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            إضافة جديد Brand
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Enter the new brand details</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('brands.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
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
        <h5 class="nova-fw-bold mb-0"><i class="fas fa-file-alt me-1"></i> Brand Information</h5>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('brands.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="nova-row g-4">
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">Brand Name <span class="nova-text-danger">*</span></label>
                        <input type="text" name="name" class="nova-input @error('name') nova-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="nova-form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">Logo (اختياري)</label>
                        <input type="file" name="logo" class="nova-input @error('logo') nova-invalid @enderror" accept="image/*">
                        <div class="nova-help-text">Preferred size 200×200, max 2MB.</div>
                        @error('logo')
                            <div class="nova-form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-12">
                    <div class="nova-form-check">
                        <input type="checkbox" name="show_in_customer_app" value="1" class="nova-form-check-input" id="show_in_customer_app" {{ old('show_in_customer_app') ? 'checked' : '' }}>
                        <label class="nova-form-check-label" for="show_in_customer_app">Show in Customer App</label>
                        <div class="nova-help-text">If enabled, this brand will appear in the customer app.</div>
                    </div>
                </div>
                <div class="nova-col nova-col-12">
                    <div class="nova-form-actions">
                        <a href="{{ route('brands.index') }}" class="nova-btn nova-btn-outline">
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
