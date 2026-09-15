@extends('dashboard.partials.layout')

@section('title', 'إضافة تعريف باكج جديد')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            إضافة تعريف باكج جديد
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">قم بتعريف باكج جديد يمكن استخدامه للمنتجات</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('pack-definitions.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> __('messages.dashboard.back')
        </a>
    </div>
</div>

{{-- ===== Validation خطأs ===== --}}
@if($errors->any())
    <div class="nova-alert danger">
        <h6 class="nova-fw-bold"><i class="fas fa-exclamation-circle nova-me-1"></i> يرجى تصحيح الأخطاء التالية:</h6>
        <ul class="nova-mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('pack-definitions.store') }}" method="POST" id="packDefinitionForm">
    @csrf

    <div class="form-layout">
        {{-- ===== MAIN COLUMN ===== --}}
        <div class="form-main">

            {{-- ===== Basic Info ===== --}}
            <div class="nova-card nova-mb-4">
                <div class="nova-card-header">
                    <h5 class="nova-card-title"><i class="fas fa-info-circle me-2"></i> المعلومات الأساسية</h5>
                </div>
                <div class="nova-card-body">
                    <div class="form-grid form-grid-2">
                        <div class="nova-form-group">
                            <label class="nova-label">الاسم <span class="nova-text-danger">*</span></label>
                            <input type="text" name="name" class="nova-input @error('name') nova-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="مثال: صندوق شوكولاتة" required>
                            @error('name') <div class="nova-form-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">الاسم (إنجليزي)</label>
                            <input type="text" name="name_en" class="nova-input @error('name_en') nova-invalid @enderror"
                                   value="{{ old('name_en') }}" placeholder="Chocolate Box">
                            @error('name_en') <div class="nova-form-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">الاسم (عربي)</label>
                            <input type="text" name="name_ar" class="nova-input @error('name_ar') nova-invalid @enderror"
                                   value="{{ old('name_ar') }}" placeholder="صندوق شوكولاتة">
                            @error('name_ar') <div class="nova-form-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">الكود</label>
                            <input type="text" name="code" class="nova-input @error('code') nova-invalid @enderror"
                                   value="{{ old('code') }}" placeholder="CHOCO-BOX-3X3">
                            @error('code') <div class="nova-form-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">الترتيب</label>
                            <input type="number" name="sort_order" class="nova-input"
                                   value="{{ old('sort_order', 0) }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== Pack Type & Capacity ===== --}}
            <div class="nova-card nova-mb-4">
                <div class="nova-card-header">
                    <h5 class="nova-card-title"><i class="fas fa-box me-2"></i> نوع العبوة والسعة</h5>
                </div>
                <div class="nova-card-body">
                    <div class="form-grid form-grid-2">
                        <div class="nova-form-group">
                            <label class="nova-label">نوع الباكج (Pack Type) <span class="nova-text-danger">*</span></label>
                            <select name="pack_type_id" class="nova-select @error('pack_type_id') nova-invalid @enderror"
                                    required id="packTypeSelect">
                                <option value="">-- اختر --</option>
                                @foreach($packTypes as $type)
                                    <option value="{{ $type->id }}"
                                            data-pieces="{{ $type->pieces_per_pack ?? '' }}"
                                            {{ old('pack_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name_en ?? $type->name_ar }} ({{ $type->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('pack_type_id') <div class="nova-form-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">سعة الباكج (Pack Capacity) <span class="nova-text-danger">*</span></label>
                            <select name="pack_capacity_id" class="nova-select @error('pack_capacity_id') nova-invalid @enderror"
                                    required id="packCapacitySelect">
                                <option value="">-- اختر --</option>
                                @foreach($packCapacities as $capacity)
                                    <option value="{{ $capacity->id }}"
                                            {{ old('pack_capacity_id') == $capacity->id ? 'selected' : '' }}>
                                        {{ $capacity->name_en ?? $capacity->name_ar }} ({{ $capacity->uom->uom_symbol ?? '' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('pack_capacity_id') <div class="nova-form-error">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Capacity Cards --}}
                    @if($packCapacities->count())
                        <div class="capacity-section">
                            <div class="capacity-header">
                                <i class="fas fa-box"></i>
                                <span>السعات المتاحة - اختر السعة المناسبة</span>
                            </div>
                            <div class="capacity-grid">
                                @foreach($packCapacities as $capacity)
                                    <div class="capacity-card" data-capacity-id="{{ $capacity->id }}">
                                        <div class="capacity-card-value">{{ $capacity->value }}</div>
                                        <span class="nova-badge secondary">{{ $capacity->uom->uom_symbol ?? '' }}</span>
                                        <p class="capacity-card-name">{{ $capacity->name_en ?? $capacity->name_ar ?? '-' }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="nova-text-center nova-py-3">
                            <p class="nova-text-muted mb-0">لا توجد سعات باكجات متاحة.</p>
                            <a href="{{ route('pack-capacities.create') }}"
                               class="nova-btn nova-btn-primary nova-btn-sm mt-2" target="_blank">
                                <i class="fas fa-plus-circle"></i> إضافة سعة جديدة
                            </a>
                        </div>
                    @endif

                    <div class="form-grid form-grid-2 nova-mt-3">
                        <div class="nova-form-group">
                            <label class="nova-label">الكمية في العبوة <span class="nova-text-danger">*</span></label>
                            <input type="number" name="quantity_in_pack"
                                   class="nova-input @error('quantity_in_pack') nova-invalid @enderror"
                                   value="{{ old('quantity_in_pack', 1) }}" min="1" required>
                            @error('quantity_in_pack') <div class="nova-form-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">الباركود</label>
                            <input type="text" name="barcode" class="nova-input"
                                   value="{{ old('barcode') }}">
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">فاصل التسلسل</label>
                            <input type="text" name="serial_separator" class="nova-input"
                                   value="{{ old('serial_separator') }}" placeholder="-">
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">معامل المعادلة</label>
                            <input type="number" step="0.01" name="equivalency_factor" class="nova-input"
                                   value="{{ old('equivalency_factor') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== Dimensions & Weight ===== --}}
            <div class="nova-card nova-mb-4">
                <div class="nova-card-header">
                    <h5 class="nova-card-title"><i class="fas fa-ruler me-2"></i> الأبعاد والوزن</h5>
                </div>
                <div class="nova-card-body">
                    <div class="form-grid form-grid-4">
                        <div class="nova-form-group">
                            <label class="nova-label">الارتفاع</label>
                            <input type="number" step="any" name="height" class="nova-input"
                                   value="{{ old('height') }}">
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">العرض</label>
                            <input type="number" step="any" name="width" class="nova-input"
                                   value="{{ old('width') }}">
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">العمق</label>
                            <input type="number" step="any" name="depth" class="nova-input"
                                   value="{{ old('depth') }}">
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">الوزن</label>
                            <input type="number" step="any" name="weight" class="nova-input"
                                   value="{{ old('weight') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== SIDEBAR COLUMN ===== --}}
        <div class="form-side">

            {{-- ===== Settings ===== --}}
            <div class="nova-card nova-mb-4">
                <div class="nova-card-header">
                    <h5 class="nova-card-title"><i class="fas fa-cog me-2"></i> الإعدادات</h5>
                </div>
                <div class="nova-card-body">
                    <div class="nova-form-check nova-mb-3">
                        <input class="nova-form-check-input" type="checkbox"
                               name="is_weight_priced" id="is_weight_priced" value="1"
                               {{ old('is_weight_priced') ? 'checked' : '' }}>
                        <label class="nova-form-check-label" for="is_weight_priced">
                            تسعير بالوزن
                            <small class="nova-text-muted d-block">يتم حساب السعر بناءً على الوزن</small>
                        </label>
                    </div>
                    <div class="nova-form-check nova-mb-3">
                        <input class="nova-form-check-input" type="checkbox"
                               name="allow_fraction_in_qty" id="allow_fraction_in_qty" value="1"
                               {{ old('allow_fraction_in_qty') ? 'checked' : '' }}>
                        <label class="nova-form-check-label" for="allow_fraction_in_qty">
                            السماح بكسور في الكمية
                            <small class="nova-text-muted d-block">مثل 1.5 عبوة</small>
                        </label>
                    </div>
                    <div class="nova-form-check nova-mb-3">
                        <input class="nova-form-check-input" type="checkbox"
                               name="is_default_pack" id="is_default_pack" value="1"
                               {{ old('is_default_pack') ? 'checked' : '' }}>
                        <label class="nova-form-check-label" for="is_default_pack">
                            باكج افتراضي
                            <small class="nova-text-muted d-block">يُستخدم تلقائياً عند إنشاء منتج جديد</small>
                        </label>
                    </div>
                </div>
            </div>

            {{-- ===== Pack Operations ===== --}}
            <div class="nova-card nova-mb-4">
                <div class="nova-card-header">
                    <h5 class="nova-card-title">
                        <i class="fas fa-tag me-2"></i> عمليات الباكج
                        @if($packOperations->count())
                            <span class="nova-badge primary">{{ $packOperations->count() }}</span>
                        @endif
                    </h5>
                </div>
                <div class="nova-card-body">
                    @if($packOperations->count())
                        <div class="checkbox-list">
                            @foreach($packOperations as $operation)
                                <div class="nova-form-check">
                                    <input class="nova-form-check-input" type="checkbox"
                                           name="pack_operations[]" value="{{ $operation->id }}"
                                           id="op_{{ $operation->id }}"
                                           {{ in_array($operation->id, old('pack_operations', [])) ? 'checked' : '' }}>
                                    <label class="nova-form-check-label" for="op_{{ $operation->id }}">
                                        {{ $operation->name_en ?? $operation->name_ar }}
                                        <div class="nova-text-muted nova-fs-sm">({{ $operation->code }})</div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="nova-text-muted nova-fs-sm mb-0">لا توجد عمليات متاحة</p>
                    @endif
                </div>
            </div>

            {{-- ===== Pack Groups ===== --}}
            <div class="nova-card nova-mb-4">
                <div class="nova-card-header">
                    <h5 class="nova-card-title">
                        <i class="fas fa-layer-group me-2"></i> مجموعات الباكج
                        @if($packGroups->count())
                            <span class="nova-badge primary">{{ $packGroups->count() }}</span>
                        @endif
                    </h5>
                </div>
                <div class="nova-card-body">
                    @if($packGroups->count())
                        <div class="checkbox-list">
                            @foreach($packGroups as $group)
                                <div class="nova-form-check">
                                    <input class="nova-form-check-input" type="checkbox"
                                           name="pack_groups[]" value="{{ $group->id }}"
                                           id="grp_{{ $group->id }}"
                                           {{ in_array($group->id, old('pack_groups', [])) ? 'checked' : '' }}>
                                    <label class="nova-form-check-label" for="grp_{{ $group->id }}">
                                        {{ $group->name_en ?? $group->name_ar }}
                                        <div class="nova-text-muted nova-fs-sm">({{ $group->code }})</div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="nova-text-muted nova-fs-sm mb-0">لا توجد مجموعات متاحة</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Form إجراءات ===== --}}
    <div class="form-actions">
        <a href="{{ route('pack-definitions.index') }}" class="nova-btn nova-btn-outline">
            <i class="fas fa-times"></i> إلغاء
        </a>
        <button type="submit" class="nova-btn nova-btn-primary" id="submitBtn">
            <i class="fas fa-save"></i> حفظ تعريف الباكج
        </button>
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

    .form-grid-4 {
        grid-template-columns: repeat(4, 1fr);
    }

    /* ===== Capacity Section ===== */
    .capacity-section {
        margin-top: 1.25rem;
        border: 1px solid #e3e6f0;
        border-radius: 0.5rem;
        overflow: hidden;
    }

    .capacity-header {
        background: #f8f9fc;
        padding: 0.6rem 0.85rem;
        font-size: 0.85rem;
        font-weight: 700;
        color: #5a5c69;
        border-bottom: 1px solid #e3e6f0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .capacity-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 0.6rem;
        padding: 0.85rem;
        max-height: 280px;
        overflow-y: auto;
    }

    .capacity-card {
        border: 2px solid #e3e6f0;
        border-radius: 0.4rem;
        padding: 0.6rem 0.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.15s ease;
        background: #fff;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.25rem;
    }

    .capacity-card:hover {
        border-color: #4e73df;
        background: #f8f9fc;
        transform: translateY(-2px);
    }

    .capacity-card.selected {
        border-color: #4e73df;
        background: rgba(78, 115, 223, 0.08);
        box-shadow: 0 0 0 2px rgba(78, 115, 223, 0.15);
    }

    .capacity-card-value {
        font-size: 1.1rem;
        font-weight: 700;
        color: #4e73df;
        line-height: 1;
    }

    .capacity-card-name {
        font-size: 0.7rem;
        color: #858796;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        width: 100%;
    }

    /* ===== Checkbox List ===== */
    .checkbox-list {
        max-height: 260px;
        overflow-y: auto;
        padding-right: 0.25rem;
    }

    .checkbox-list .nova-form-check {
        padding: 0.4rem 0;
        border-bottom: 1px dashed #e3e6f0;
    }

    .checkbox-list .nova-form-check:last-child {
        border-bottom: none;
    }

    /* ===== Form إجراءات ===== */
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
        padding: 1rem 0;
        position: sticky;
        bottom: 0;
        background: #fff;
        border-top: 1px solid #e3e6f0;
        margin-top: 1rem;
        z-index: 10;
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
        .form-grid-4 {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .form-grid-2,
        .form-grid-4 {
            grid-template-columns: 1fr;
        }
        .form-actions {
            flex-direction: column-reverse;
        }
        .form-actions .nova-btn {
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ===== Pack Type → Quantity Auto-fill =====
    const packTypeSelect = document.getElementById('packTypeSelect');
    const quantityInPackInput = document.querySelector('input[name="quantity_in_pack"]');

    if (packTypeSelect && quantityInPackInput) {
        packTypeSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const pieces = selectedOption.getAttribute('data-pieces');
            if (pieces) {
                quantityInPackInput.value = pieces;
                // عاليlight effect
                quantityInPackInput.style.transition = 'background 0.3s';
                quantityInPackInput.style.background = 'rgba(28, 200, 138, 0.15)';
                setTimeout(() => {
                    quantityInPackInput.style.background = '';
                }, 800);
            }
        });
    }

    // ===== Capacity Cards Selection =====
    const capacitySelect = document.getElementById('packCapacitySelect');
    const capacityCards = document.querySelectorالكل('.capacity-card');

    function syncCapacitySelection(capacityId) {
        capacityCards.forEach(card => {
            if (card.getAttribute('data-capacity-id') === String(capacityId)) {
                card.classList.add('selected');
            } else {
                card.classList.remove('selected');
            }
        });
    }

    capacityCards.forEach(card => {
        card.addEventListener('click', function() {
            const capacityId = this.getAttribute('data-capacity-id');
            if (capacitySelect && capacityId) {
                capacitySelect.value = capacityId;
                syncCapacitySelection(capacityId);
                // Trigger change for any listeners
                capacitySelect.dispatchEvent(new Event('change'));
            }
        });
    });

    if (capacitySelect) {
        // Sync on select change
        capacitySelect.addEventListener('change', function() {
            syncCapacitySelection(this.value);
        });
        // Initial sync
        if (capacitySelect.value) {
            syncCapacitySelection(capacitySelect.value);
        }
    }

    // ===== إرسال Button Loading State =====
    const form = document.getElementById('packDefinitionForm');
    const submitBtn = document.getElementById('submitBtn');

    if (form && submitBtn) {
        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جارٍ الحفظ...';
        });
    }

    // ===== Auto-focus first empty field =====
    const firstInput = document.querySelector('input[name="name"]');
    if (firstInput && !firstInput.value) {
        firstInput.focus();
    }
});
</script>
@endpush