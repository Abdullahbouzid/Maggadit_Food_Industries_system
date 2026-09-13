@extends('dashboard.partials.layout')

@section('title', 'Create Banner')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-plus-circle" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            Add New Banner
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Create a new banner for the customer app</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('banners.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Banners
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
    <div class="nova-card-body">
        <form action="{{ route('banners.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="nova-row g-4">
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-heading me-1"></i> Title <small class="nova-text-muted">(optional)</small>
                        </label>
                        <input type="text" name="title" class="nova-input" value="{{ old('title') }}">
                        @error('title') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-tag me-1"></i> Type
                        </label>
                        <select name="type" class="nova-select">
                            <option value="home" {{ old('type') == 'home' ? 'selected' : '' }}>Home</option>
                            <option value="category" {{ old('type') == 'category' ? 'selected' : '' }}>Category</option>
                            <option value="product" {{ old('type') == 'product' ? 'selected' : '' }}>Product</option>
                            <option value="promo" {{ old('type') == 'promo' ? 'selected' : '' }}>Promo</option>
                            <option value="custom" {{ old('type') == 'custom' ? 'selected' : '' }}>Custom</option>
                        </select>
                        @error('type') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="nova-col nova-col-md-12">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-image me-1"></i> Image <span class="nova-text-danger">*</span>
                        </label>
                        <input type="file" name="image" class="nova-input" accept="image/*">
                        @error('image') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="nova-col nova-col-md-12">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-link me-1"></i> URL <small class="nova-text-muted">(optional)</small>
                        </label>
                        <input type="url" name="url" class="nova-input" value="{{ old('url') }}" placeholder="https://example.com">
                        @error('url') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-sort-numeric-down me-1"></i> Order
                        </label>
                        <input type="number" name="order" class="nova-input" value="{{ old('order', 0) }}" min="0">
                        @error('order') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="nova-col nova-col-md-6 d-flex align-items-end">
                    <div class="nova-form-check">
                        <input type="hidden" name="show_in_customer_app" value="0">
                        <input type="checkbox" name="show_in_customer_app" class="nova-form-check-input" id="showInApp" value="1" {{ old('show_in_customer_app', true) ? 'checked' : '' }}>
                        <label class="nova-form-check-label" for="showInApp">Show in Customer App</label>
                    </div>
                </div>

                <div class="nova-col nova-col-12">
                    <div class="nova-form-actions">
                        <a href="{{ route('banners.index') }}" class="nova-btn nova-btn-outline">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="nova-btn nova-btn-primary">
                            <i class="fas fa-save"></i> Save Banner
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
