@extends('dashboard.partials.layout')

@section('title', 'Create State')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-plus-circle" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            Create State
        </h2>
        <p class="nova-page-subtitle">Add a new state, province, or region</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('states.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> Back
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
        <form action="{{ route('states.store') }}" method="POST">
            @csrf
            <div class="nova-row nova-gap-4">
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">Country <span class="nova-text-danger">*</span></label>
                        <select name="country_id" class="nova-select @error('country_id') nova-invalid @enderror" required>
                            <option value="">Select Country</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>{{ $country->name }}</option>
                            @endforeach
                        </select>
                        @error('country_id') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-md-6"></div>
                <div class="nova-col nova-col-md-4">
                    <div class="nova-form-group">
                        <label class="nova-label">Code <span class="nova-text-danger">*</span></label>
                        <input type="text" name="code" class="nova-input @error('code') nova-invalid @enderror" value="{{ old('code') }}" required>
                        @error('code') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-md-4">
                    <div class="nova-form-group">
                        <label class="nova-label">Name (English) <span class="nova-text-danger">*</span></label>
                        <input type="text" name="name_en" class="nova-input @error('name_en') nova-invalid @enderror" value="{{ old('name_en') }}" required>
                        @error('name_en') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-md-4">
                    <div class="nova-form-group">
                        <label class="nova-label">Name (Arabic) <span class="nova-text-danger">*</span></label>
                        <input type="text" name="name_ar" class="nova-input @error('name_ar') nova-invalid @enderror" value="{{ old('name_ar') }}" required>
                        @error('name_ar') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-12">
                    <div class="nova-form-group">
                        <label class="nova-label">Description</label>
                        <textarea name="description" class="nova-input" rows="3">{{ old('description') }}</textarea>
                    </div>
                </div>
                <div class="nova-col nova-col-md-4">
                    <div class="nova-form-group">
                        <label class="nova-label">Sort Order</label>
                        <input type="number" name="sort_order" class="nova-input" value="{{ old('sort_order', 0) }}">
                    </div>
                </div>
                <div class="nova-col nova-col-md-4">
                    <div class="nova-form-check nova-mt-4">
                        <input type="checkbox" name="is_active" value="1" class="nova-form-check-input" id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="nova-form-check-label" for="is_active">Active</label>
                    </div>
                </div>
                <div class="nova-col nova-col-12">
                    <div class="nova-form-actions">
                        <a href="{{ route('states.index') }}" class="nova-btn nova-btn-outline">
                            <i class="fas fa-x me-1"></i> Cancel
                        </a>
                        <button type="submit" class="nova-btn nova-btn-primary">
                            <i class="fas fa-save me-1"></i> Save
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
