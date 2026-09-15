@extends('dashboard.partials.layout')

@section('title', 'إضافة عنصر الصورة')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-plus-circle" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            إضافة عنصر الصورة
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Upload an image for an item.</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('item-images.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
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
        <h5 class="nova-fw-bold mb-0"><i class="fas fa-file-alt me-1"></i> الصورة Information</h5>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('item-images.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="nova-row g-4">
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">Item <span class="nova-text-danger">*</span></label>
                        <select name="item_id" class="nova-select @error('item_id') nova-invalid @enderror" id="item_id" required>
                            <option value="">Select item</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" {{ old('item_id') == $item->id ? 'selected' : '' }}>{{ $item->name_en ?? $item->name_ar }} ({{ $item->code }})</option>
                            @endforeach
                        </select>
                        @error('item_id')
                            <div class="nova-form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">ترتيب الفرز</label>
                        <input type="number" name="sort_order" class="nova-input @error('sort_order') nova-invalid @enderror" value="{{ old('sort_order', 0) }}">
                        @error('sort_order')
                            <div class="nova-form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">الصورة <span class="nova-text-danger">*</span></label>
                        <input type="file" name="image" class="nova-input @error('image') nova-invalid @enderror" accept="image/*" required>
                        @error('image')
                            <div class="nova-form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">Alt Text</label>
                        <input type="text" name="alt_text" class="nova-input @error('alt_text') nova-invalid @enderror" value="{{ old('alt_text') }}">
                        @error('alt_text')
                            <div class="nova-form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-12">
                    <div class="nova-form-actions">
                        <a href="{{ route('item-images.index') }}" class="nova-btn nova-btn-outline">
                            <i class="fas fa-x me-1"></i> إلغاء
                        </a>
                        <button type="submit" class="nova-btn nova-btn-primary">
                            <i class="fas fa-save me-1"></i> Upload الصورة
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
