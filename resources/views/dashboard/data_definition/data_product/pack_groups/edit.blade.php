@extends('dashboard.partials.layout')

@section('title', 'Edit Pack Group')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-edit" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            Edit Pack Group: {{ $packGroup->code }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Modify pack group details</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('pack-groups.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> Back
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
        <h5 class="nova-fw-bold mb-0"><i class="fas fa-file-alt me-1"></i> Pack Group Information</h5>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('pack-groups.update', $packGroup) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="nova-row g-4">
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">Code <span class="nova-text-danger">*</span></label>
                        <input type="text" name="code" class="nova-input @error('code') nova-invalid @enderror" value="{{ old('code', $packGroup->code) }}" required>
                        @error('code')
                            <div class="nova-form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">Sort Order</label>
                        <input type="number" name="sort_order" class="nova-input @error('sort_order') nova-invalid @enderror" value="{{ old('sort_order', $packGroup->sort_order) }}">
                        @error('sort_order')
                            <div class="nova-form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">Name (English) <span class="nova-text-danger">*</span></label>
                        <input type="text" name="name_en" class="nova-input @error('name_en') nova-invalid @enderror" value="{{ old('name_en', $packGroup->name_en) }}" required>
                        @error('name_en')
                            <div class="nova-form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">Name (Arabic) <span class="nova-text-danger">*</span></label>
                        <input type="text" name="name_ar" class="nova-input @error('name_ar') nova-invalid @enderror" value="{{ old('name_ar', $packGroup->name_ar) }}" required>
                        @error('name_ar')
                            <div class="nova-form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-check nova-mt-4">
                        <input type="checkbox" name="is_active" value="1" class="nova-form-check-input" id="is_active" {{ old('is_active', $packGroup->is_active) ? 'checked' : '' }}>
                        <label class="nova-form-check-label" for="is_active">Active</label>
                    </div>
                </div>
                <div class="nova-col nova-col-12">
                    <div class="nova-form-actions">
                        <a href="{{ route('pack-groups.index') }}" class="nova-btn nova-btn-outline">
                            <i class="fas fa-x me-1"></i> Cancel
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
