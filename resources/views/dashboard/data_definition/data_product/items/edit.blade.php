@extends('dashboard.partials.layout')

@section('title', 'تعديل Item')

@section('content')
{{-- ===== Action Bar ===== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-edit" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            تعديل Item
            @if($item->code)
                <span class="nova-badge info" style="margin-inline-start: 0.5rem;">{{ $item->code }}</span>
            @endif
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">
            Update product details and packaging for
            <strong>{{ $item->name_en }}</strong>
        </p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('items.show', $item) }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-eye"></i> عرض
        </a>
        <a href="{{ route('items.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> رجوع
        </a>
    </div>
</div>

{{-- ===== Validation خطأs ===== --}}
@if($errors->any())
    <div class="nova-alert danger">
        <h6 class="nova-fw-bold">
            <i class="fas fa-exclamation-circle nova-me-1"></i>
            يرجى تصحيح الأخطاء التالية:
        </h6>
        <ul class="nova-mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- ===== Meta Info ===== --}}
@if($item->updated_at)
    <div class="nova-alert info" style="display: flex; align-items: center; gap: 0.5rem;">
        <i class="fas fa-clock"></i>
        آخر تحديث: {{ $item->updated_at->diffForHumans() }}
        <span class="nova-text-muted">({{ $item->updated_at->format('Y-m-d H:i') }})</span>
    </div>
@endif

<form action="{{ route('items.update', $item) }}" method="POST" enctype="multipart/form-data" id="itemForm">
    @csrf
    @method('PUT')

    <div class="form-layout">
        {{-- ===== MAIN COLUMN ===== --}}
        <div class="form-main">

            {{-- ===== Basic Information ===== --}}
            <div class="nova-card nova-mb-4">
                <div class="nova-card-header">
                    <h5 class="nova-card-title">
                        <i class="fas fa-info-circle me-2"></i> Basic Information
                    </h5>
                </div>
                <div class="nova-card-body">
                    <div class="form-grid form-grid-2">
                        <div class="nova-form-group">
                            <label class="nova-label">Code <span class="nova-text-danger">*</span></label>
                            <input type="text" name="code"
                                   class="nova-input @error('code') nova-invalid @enderror"
                                   value="{{ old('code', $item->code) }}" required>
                            @error('code') <div class="nova-form-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">Sequence</label>
                            <input type="text" name="sequence" class="nova-input"
                                   value="{{ old('sequence', $item->sequence) }}">
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">Name (EN) <span class="nova-text-danger">*</span></label>
                            <input type="text" name="name_en"
                                   class="nova-input @error('name_en') nova-invalid @enderror"
                                   value="{{ old('name_en', $item->name_en) }}" required>
                            @error('name_en') <div class="nova-form-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">Name (AR) <span class="nova-text-danger">*</span></label>
                            <input type="text" name="name_ar"
                                   class="nova-input @error('name_ar') nova-invalid @enderror"
                                   value="{{ old('name_ar', $item->name_ar) }}" required dir="rtl">
                            @error('name_ar') <div class="nova-form-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="nova-form-group form-grid-full">
                            <label class="nova-label">Definition</label>
                            <textarea name="definition" class="nova-input" rows="3"
                                      placeholder="وصف مختصر للمنتج...">{{ old('definition', $item->definition) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== Classification ===== --}}
            <div class="nova-card nova-mb-4">
                <div class="nova-card-header">
                    <h5 class="nova-card-title">
                        <i class="fas fa-sitemap me-2"></i> Classification
                    </h5>
                </div>
                <div class="nova-card-body">
                    <div class="form-grid form-grid-2">
                        <div class="nova-form-group">
                            <label class="nova-label">نوع العنصر</label>
                            <select name="item_type_id" class="nova-select">
                                <option value="">-- لا شيء --</option>
                                @foreach($itemTypes as $type)
                                    <option value="{{ $type->id }}"
                                            {{ old('item_type_id', $item->item_type_id) == $type->id ? 'selected' : '' }}>
                                        {{ $type->name_en ?? $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">الفئة</label>
                            <select name="category_id" class="nova-select">
                                <option value="">-- لا شيء --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}"
                                            {{ old('category_id', $item->category_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name_en ?? $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">Brand</label>
                            <select name="brand_id" class="nova-select">
                                <option value="">-- لا شيء --</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}"
                                            {{ old('brand_id', $item->brand_id) == $brand->id ? 'selected' : '' }}>
                                        {{ $brand->name_en ?? $brand->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">Model</label>
                            <select name="model_id" class="nova-select">
                                <option value="">-- لا شيء --</option>
                                @foreach($models as $model)
                                    <option value="{{ $model->id }}"
                                            {{ old('model_id', $item->model_id) == $model->id ? 'selected' : '' }}>
                                        {{ $model->name_en ?? $model->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">Supplier</label>
                            <select name="supplier_id" class="nova-select">
                                <option value="">-- لا شيء --</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}"
                                            {{ old('supplier_id', $item->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->vendor_name ?? $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== Tolerances & Stock ===== --}}
            <div class="nova-card nova-mb-4">
                <div class="nova-card-header">
                    <h5 class="nova-card-title">
                        <i class="fas fa-sliders-h me-2"></i> Tolerances & Stock
                    </h5>
                </div>
                <div class="nova-card-body">
                    <div class="form-grid form-grid-3">
                        <div class="nova-form-group">
                            <label class="nova-label">Shelf Life (days)</label>
                            <input type="number" name="shelf_life_time" class="nova-input"
                                   value="{{ old('shelf_life_time', $item->shelf_life_time) }}" min="0">
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">Van Stock Tolerance</label>
                            <input type="number" step="any" name="van_stock_tolerance" class="nova-input"
                                   value="{{ old('van_stock_tolerance', $item->van_stock_tolerance) }}">
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">Storage Tolerance</label>
                            <input type="number" step="any" name="storage_tolerance" class="nova-input"
                                   value="{{ old('storage_tolerance', $item->storage_tolerance) }}">
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">Weight Tolerance</label>
                            <input type="number" step="any" name="weight_tolerance" class="nova-input"
                                   value="{{ old('weight_tolerance', $item->weight_tolerance) }}">
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">منخفض Stock Qty</label>
                            <input type="number" step="any" name="low_stock_qty" class="nova-input"
                                   value="{{ old('low_stock_qty', $item->low_stock_qty) }}" min="0">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== SIDEBAR COLUMN ===== --}}
        <div class="form-side">

            {{-- ===== Main الصورة ===== --}}
            <div class="nova-card nova-mb-4">
                <div class="nova-card-header">
                    <h5 class="nova-card-title"><i class="fas fa-image me-2"></i> Main الصورة</h5>
                </div>
                <div class="nova-card-body nova-text-center">
                    <div class="image-preview-wrapper">
                        @if($item->image)
                            <img id="imagePreview"
                                 src="{{ asset('uploads/' . $item->image) }}"
                                 alt="{{ $item->name_en }}"
                                 class="image-preview">
                        @else
                            <div id="imagePreview" class="image-preview image-preview-empty">
                                <i class="fas fa-image"></i>
                                <span>لا image</span>
                            </div>
                        @endif
                    </div>
                    <input type="file" name="image" id="imageInput" class="nova-input nova-mt-3"
                           accept="image/*">
                    <p class="nova-text-muted nova-fs-sm nova-mt-2">
                        <i class="fas fa-info-circle"></i>
                        اترك الحقل فارغاً للإبقاء على الصورة الحالية.
                    </p>
                    @error('image') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- ===== الحالة & Flags ===== --}}
            <div class="nova-card nova-mb-4">
                <div class="nova-card-header">
                    <h5 class="nova-card-title"><i class="fas fa-flag me-2"></i> الحالة & Flags</h5>
                </div>
                <div class="nova-card-body">
                    @php
                        $flags = [
                            ['name' => 'is_inactive',       'label' => 'غير نشط',           'icon' => 'eye-slash'],
                            ['name' => 'is_new',            'label' => 'جديد Product',        'icon' => 'star'],
                            ['name' => 'is_price_protected','label' => 'Price Protected',    'icon' => 'lock'],
                            ['name' => 'is_power_sku',      'label' => 'Power SKU',          'icon' => 'bolt'],
                            ['name' => 'is_default_batch',  'label' => 'افتراضي Batch',      'icon' => 'layer-group'],
                            ['name' => 'is_temp_credit',    'label' => 'Temp Credit',        'icon' => 'credit-card'],
                            ['name' => 'is_batch_priced',   'label' => 'Batch Priced',       'icon' => 'tags'],
                            ['name' => 'is_initiative',     'label' => 'Initiative',         'icon' => 'rocket'],
                            ['name' => 'force_default_pack','label' => 'Force افتراضي Pack', 'icon' => 'box'],
                            ['name' => 'has_serial_number', 'label' => 'Has Serial Number',  'icon' => 'barcode'],
                            ['name' => 'required_by_liter', 'label' => 'مطلوب by Liter',  'icon' => 'flask'],
                            ['name' => 'required_by_weight','label' => 'مطلوب by Weight', 'icon' => 'weight'],
                        ];
                    @endphp

                    <div class="flags-list">
                        @foreach($flags as $flag)
                            <div class="nova-form-check">
                                <input type="hidden" name="{{ $flag['name'] }}" value="0">
                                <input type="checkbox"
                                       name="{{ $flag['name'] }}"
                                       class="nova-form-check-input"
                                       value="1"
                                       id="{{ $flag['name'] }}"
                                       {{ old($flag['name'], $item->{$flag['name']}) ? 'checked' : '' }}>
                                <label class="nova-form-check-label" for="{{ $flag['name'] }}">
                                    <i class="fas fa-{{ $flag['icon'] }} nova-me-1"></i>
                                    {{ $flag['label'] }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ===== إرسال ===== --}}
            <div class="nova-card sticky-actions">
                <div class="nova-card-body">
                    <div class="nova-flex nova-flex-column nova-gap-2">
                        <button type="submit" class="nova-btn nova-btn-primary" id="submitBtn">
                            <i class="fas fa-save"></i> Update Item
                        </button>
                        <a href="{{ route('items.index') }}" class="nova-btn nova-btn-outline">
                            <i class="fas fa-times"></i> إلغاء
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('styles')
<style>
    /* ===== Form Layout ===== */
    .form-layout {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 1.25rem;
        align-items: start;
    }

    .form-main {
        min-width: 0;
    }

    .form-side {
        position: sticky;
        top: 1rem;
    }

    /* ===== Form Grids ===== */
    .form-grid {
        display: grid;
        gap: 1rem;
    }

    .form-grid-2 {
        grid-template-columns: repeat(2, 1fr);
    }

    .form-grid-3 {
        grid-template-columns: repeat(3, 1fr);
    }

    .form-grid-full {
        grid-column: 1 / -1;
    }

    /* ===== الصورة Preview ===== -- */
    .image-preview-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .image-preview {
        max-width: 100%;
        max-height: 220px;
        border-radius: 0.5rem;
        border: 1px solid #e3e6f0;
        object-fit: contain;
        transition: all 0.2s ease;
    }

    .image-preview-empty {
        width: 100%;
        height: 180px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        background: #f8f9fc;
        color: #858796;
        font-size: 0.85rem;
    }

    .image-preview-empty i {
        font-size: 2rem;
    }

    /* ===== Flags List ===== -- */
    .flags-list {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .flags-list .nova-form-check {
        padding: 0.4rem 0;
        border-bottom: 1px dashed #e3e6f0;
    }

    .flags-list .nova-form-check:last-child {
        border-bottom: none;
    }

    .flags-list .nova-form-check-label {
        cursor: pointer;
    }

    /* ===== Sticky إجراءات ===== -- */
    .sticky-actions {
        position: sticky;
        bottom: 1rem;
    }

    /* ===== Invalid State ===== */
    .nova-input.nova-invalid,
    .nova-select.nova-invalid {
        border-color: #e74a3b;
    }

    /* ===== Responsive ===== */
    @media (max-width: 1200px) {
        .form-layout {
            grid-template-columns: 1fr 320px;
        }
    }

    @media (max-width: 992px) {
        .form-layout {
            grid-template-columns: 1fr;
        }
        .form-side {
            position: static;
        }
        .form-grid-3 {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .form-grid-2,
        .form-grid-3 {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ===== الصورة Preview =====
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');

    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', function() {
            const file = this.files && this.files[0];
            if (!file) return;

            // Validate it's an image
            if (!file.type.startsWith('image/')) {
                alert('الرجاء اختيار ملف صورة صالح');
                this.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                // Replace content with img
                const wrapper = imagePreview.parentElement;
                const img = document.createElement('img');
                img.id = 'imagePreview';
                img.src = e.target.result;
                img.className = 'image-preview';
                img.alt = 'Preview';
                wrapper.innerHTML = '';
                wrapper.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    }

    // ===== إرسال Button Loading State =====
    const form = document.getElementById('itemForm');
    const submitBtn = document.getElementById('submitBtn');

    if (form && submitBtn) {
        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جارٍ التحديث...';
        });
    }

    // ===== Unsaved Changes تحذير =====
    let formChanged = false;
    const formInputs = form ? form.querySelectorالكل('input:not([type="hidden"]), select, textarea') : [];

    formInputs.forEach(input => {
        input.addEventListener('change', () => { formChanged = true; });
        input.addEventListener('input', () => { formChanged = true; });
    });

    if (form) {
        form.addEventListener('submit', () => { formChanged = false; });
    }

    window.addEventListener('beforeunload', function(e) {
        if (formChanged) {
            e.preventافتراضي();
            e.returnValue = '';
        }
    });

    // ===== Auto-dismiss alerts =====
    setTimeout(function() {
        document.querySelectorالكل('.nova-alert').forEach(function(alert) {
            alert.style.transition = 'opacity 0.3s, transform 0.3s';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        });
    }, 5000);
});
</script>
@endpush