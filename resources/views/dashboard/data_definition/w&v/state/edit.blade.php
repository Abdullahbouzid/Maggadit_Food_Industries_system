@extends('dashboard.partials.layout')

@section('title', 'تعديل State')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-edit" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            تعديل State: {{ $state->code }}
        </h2>
        <p class="nova-page-subtitle">Modify state classification</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('states.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> رجوع
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
        <h5 class="nova-fw-bold mb-0"><i class="fas fa-file-alt me-1"></i> Basic Information</h5>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('states.update', $state) }}" method="POST">
            @csrf @method('PUT')
            <div class="nova-row nova-gap-4">
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">Country <span class="nova-text-danger">*</span></label>
                        <select name="country_id" class="nova-select @error('country_id') nova-invalid @enderror" required>
                            <option value="">Select Country</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}" {{ old('country_id', $state->country_id) == $country->id ? 'selected' : '' }}>{{ $country->name }}</option>
                            @endforeach
                        </select>
                        @error('country_id') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-md-6"></div>
                <div class="nova-col nova-col-md-4">
                    <div class="nova-form-group">
                        <label class="nova-label">Code <span class="nova-text-danger">*</span></label>
                        <input type="text" name="code" class="nova-input @error('code') nova-invalid @enderror" value="{{ old('code', $state->code) }}" required>
                        @error('code') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-md-4">
                    <div class="nova-form-group">
                        <label class="nova-label">الاسم بالإنجليزي <span class="nova-text-danger">*</span></label>
                        <input type="text" name="name_en" class="nova-input @error('name_en') nova-invalid @enderror" value="{{ old('name_en', $state->name_en) }}" required>
                        @error('name_en') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-md-4">
                    <div class="nova-form-group">
                        <label class="nova-label">الاسم بالعربي <span class="nova-text-danger">*</span></label>
                        <input type="text" name="name_ar" class="nova-input @error('name_ar') nova-invalid @enderror" value="{{ old('name_ar', $state->name_ar) }}" required>
                        @error('name_ar') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-12">
                    <div class="nova-form-group">
                        <label class="nova-label">الوصف</label>
                        <textarea name="description" class="nova-input" rows="3">{{ old('description', $state->description) }}</textarea>
                    </div>
                </div>
                <div class="nova-col nova-col-md-4">
                    <div class="nova-form-group">
                        <label class="nova-label">ترتيب الفرز</label>
                        <input type="number" name="sort_order" class="nova-input" value="{{ old('sort_order', $state->sort_order) }}">
                    </div>
                </div>
                <div class="nova-col nova-col-md-4">
                    <div class="nova-form-check nova-mt-4">
                        <input type="checkbox" name="is_active" value="1" class="nova-form-check-input" id="is_active" {{ old('is_active', $state->is_active) ? 'checked' : '' }}>
                        <label class="nova-form-check-label" for="is_active">نشط</label>
                    </div>
                </div>
                <div class="nova-col nova-col-12">
                    <div class="nova-form-actions">
                        <a href="{{ route('states.index') }}" class="nova-btn nova-btn-outline">
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
