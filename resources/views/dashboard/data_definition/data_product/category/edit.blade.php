@extends('dashboard.partials.layout')

@section('title', 'تعديل الفئة: ' . $category->name)

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-edit" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            تعديل الفئة: {{ $category->name }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Modify category details</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('categories.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
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
        <h5 class="nova-fw-bold mb-0"><i class="fas fa-file-alt me-1"></i> الفئة Information</h5>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('categories.update', $category) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="nova-row g-4">
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">الفئة Code <span class="nova-text-danger">*</span></label>
                        <input type="text" name="code" class="nova-input @error('code') nova-invalid @enderror" value="{{ old('code', $category->code) }}" required>
                        @error('code')
                            <div class="nova-form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">الفئة Name <span class="nova-text-danger">*</span></label>
                        <input type="text" name="name" class="nova-input @error('name') nova-invalid @enderror" value="{{ old('name', $category->name) }}" required>
                        @error('name')
                            <div class="nova-form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-12">
                    <div class="nova-form-group">
                        <label class="nova-label">Division <span class="nova-text-danger">*</span></label>
                        <input type="text" name="division" class="nova-input @error('division') nova-invalid @enderror" value="{{ old('division', $category->division) }}" required>
                        @error('division')
                            <div class="nova-form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-12">
                    <div class="nova-form-group">
                        <label class="nova-label">Current الصورة</label>
                        @if($category->image)
                            <div class="nova-mb-2">
                                <img src="{{ asset('uploads/' . $category->image) }}" alt="{{ $category->name }}" width="100" style="object-fit: cover; border: 1px solid var(--nova-border); border-radius: 10px; box-shadow: var(--nova-shadow-sm);">
                            </div>
                        @else
                            <p class="nova-text-muted">لا image uploaded yet.</p>
                        @endif
                        <label class="nova-label">Change الصورة (اختياري)</label>
                        <input type="file" name="image" class="nova-input @error('image') nova-invalid @enderror" accept="image/*">
                        @error('image')
                            <div class="nova-form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-12">
                    <div class="nova-form-check">
                        <input type="checkbox" name="show_in_customer_app" value="1" class="nova-form-check-input" id="show_in_customer_app" {{ old('show_in_customer_app', $category->show_in_customer_app) ? 'checked' : '' }}>
                        <label class="nova-form-check-label" for="show_in_customer_app">Show in Customer App</label>
                    </div>
                </div>
                <div class="nova-col nova-col-12">
                    <div class="nova-form-actions">
                        <a href="{{ route('categories.index') }}" class="nova-btn nova-btn-outline">
                            <i class="fas fa-x me-1"></i> إلغاء
                        </a>
                        <button type="submit" class="nova-btn nova-btn-primary">
                            <i class="fas fa-save me-1"></i> Update
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
