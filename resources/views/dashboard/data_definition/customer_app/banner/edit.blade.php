@extends('dashboard.partials.layout')

@section('title', 'تعديل Banner')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-edit" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            تعديل Banner
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Update banner details, image, and visibility settings</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('banners.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> رجوع to Banners
        </a>
    </div>
</div>

@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

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
            <i class="fas fa-cog"></i> Banner التفاصيل
        </h5>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('banners.update', $banner) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="nova-row g-4">
                {{-- Title --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-heading me-1"></i> Title <small class="nova-text-muted">(optional)</small>
                        </label>
                        <input type="text" name="title" class="nova-input" value="{{ old('title', $banner->title) }}">
                        @error('title') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Type --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-tag me-1"></i> Type
                        </label>
                        <select name="type" class="nova-select">
                            <option value="home" {{ old('type', $banner->type) == 'home' ? 'selected' : '' }}>Home</option>
                            <option value="category" {{ old('type', $banner->type) == 'category' ? 'selected' : '' }}>الفئة</option>
                            <option value="product" {{ old('type', $banner->type) == 'product' ? 'selected' : '' }}>Product</option>
                            <option value="promo" {{ old('type', $banner->type) == 'promo' ? 'selected' : '' }}>Promo</option>
                            <option value="custom" {{ old('type', $banner->type) == 'custom' ? 'selected' : '' }}>Custom</option>
                        </select>
                        @error('type') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Current الصورة --}}
                <div class="nova-col nova-col-md-12">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-image me-1"></i> Current الصورة
                        </label>
                        <div>
                            @if($banner->image)
                                <img src="{{ asset('uploads/' . $banner->image) }}" alt="{{ $banner->title }}" width="120" height="120" class="nova-object-fit-cover" style="border-radius: 10px; box-shadow: var(--nova-shadow-sm);">
                                <div class="nova-form-help mt-2">
                                    <span class="nova-text-muted small">{{ basename($banner->image) }}</span>
                                </div>
                            @else
                                <p class="nova-text-muted fst-italic">لا image uploaded</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- جديد الصورة --}}
                <div class="nova-col nova-col-md-12">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-upload me-1"></i> Replace الصورة <small class="nova-text-muted">(optional, leave empty to keep current)</small>
                        </label>
                        <input type="file" name="image" class="nova-input" accept="image/*">
                        @error('image') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- URL --}}
                <div class="nova-col nova-col-md-12">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-link me-1"></i> URL <small class="nova-text-muted">(optional)</small>
                        </label>
                        <input type="url" name="url" class="nova-input" value="{{ old('url', $banner->url) }}" placeholder="https://example.com">
                        @error('url') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Order --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-sort-numeric-down me-1"></i> Order
                        </label>
                        <input type="number" name="order" class="nova-input" value="{{ old('order', $banner->order) }}" min="0">
                        @error('order') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Show in App --}}
                <div class="nova-col nova-col-md-6 d-flex align-items-end">
                    <div class="nova-form-check">
                        <input type="hidden" name="show_in_customer_app" value="0">
                        <input type="checkbox" name="show_in_customer_app" class="nova-form-check-input" id="showInApp" value="1" {{ old('show_in_customer_app', $banner->show_in_customer_app) ? 'checked' : '' }}>
                        <label class="nova-form-check-label" for="showInApp">Show in Customer App</label>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="nova-col nova-col-12">
                    <div class="nova-form-actions">
                        <a href="{{ route('banners.index') }}" class="nova-btn nova-btn-outline">
                            <i class="fas fa-times"></i> إلغاء
                        </a>
                        <button type="submit" class="nova-btn nova-btn-primary">
                            <i class="fas fa-save"></i> Update Banner
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
