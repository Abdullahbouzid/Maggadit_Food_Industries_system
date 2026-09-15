@extends('dashboard.partials.layout')

@section('title', 'Create Item Pack')

@section('content')
{{-- ===== Action Bar ===== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-plus-circle" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            Create Item Pack
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">
            Add a new item pack with packaging details
        </p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('item-packs.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
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

<form action="{{ route('item-packs.store') }}" method="POST" id="itemPackForm">
    @csrf

    {{-- ===== Basic Info ===== --}}
    <div class="nova-card nova-mb-4">
        <div class="nova-card-header">
            <h5 class="nova-card-title"><i class="fas fa-info-circle me-2"></i> Basic Information</h5>
        </div>
        <div class="nova-card-body">
            <div class="form-grid form-grid-2">
                <div class="nova-form-group">
                    <label class="nova-label">Item <span class="nova-text-danger">*</span></label>
                    <select name="item_id"
                            class="nova-select @error('item_id') is-invalid @enderror" required>
                        <option value="">Select item</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}"
                                    {{ old('item_id') == $item->id ? 'selected' : '' }}>
                                {{ $item->name_en ?? $item->name_ar }} ({{ $item->code }})
                            </option>
                        @endforeach
                    </select>
                    @error('item_id') <small class="nova-text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="nova-form-group">
                    <label class="nova-label">
                        Pack Definition
                        <small class="nova-text-muted">(اختياري - لتعبئة تلقائية)</small>
                    </label>
                    <select name="pack_definition_id" class="nova-select" id="packDefinitionSelect">
                        <option value="">Select pack definition</option>
                        @foreach($packDefinitions as $def)
                            <option value="{{ $def->id }}"
                                    data-pack-type="{{ $def->pack_type_id }}"
                                    data-pack-capacity="{{ $def->pack_capacity_id }}"
                                    data-quantity="{{ $def->quantity_in_pack }}"
                                    data-barcode="{{ $def->barcode }}"
                                    data-serial="{{ $def->serial_separator }}"
                                    data-equivalency="{{ $def->equivalency_factor }}"
                                    data-height="{{ $def->height }}"
                                    data-width="{{ $def->width }}"
                                    data-depth="{{ $def->depth }}"
                                    data-weight="{{ $def->weight }}"
                                    data-weight-priced="{{ $def->is_weight_priced ? 1 : 0 }}"
                                    data-allow-fraction="{{ $def->allow_fraction_in_qty ? 1 : 0 }}"
                                    data-default-pack="{{ $def->is_default_pack ? 1 : 0 }}"
                                    {{ old('pack_definition_id') == $def->id ? 'selected' : '' }}>
                                {{ $def->name_en ?? $def->name_ar }} ({{ $def->code }})
                            </option>
                        @endforeach
                    </select>
                    @error('pack_definition_id') <small class="nova-text-danger">{{ $message }}</small> @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Pack Info ===== --}}
    <div class="nova-card nova-mb-4">
        <div class="nova-card-header">
            <h5 class="nova-card-title"><i class="fas fa-box me-2"></i> Pack Information</h5>
        </div>
        <div class="nova-card-body">
            <div class="form-grid form-grid-3">
                <div class="nova-form-group">
                    <label class="nova-label">Pack Type <span class="nova-text-danger">*</span></label>
                    <select name="pack_type_id"
                            class="nova-select @error('pack_type_id') is-invalid @enderror" required>
                        <option value="">Select pack type</option>
                        @foreach($packTypes as $packType)
                            <option value="{{ $packType->id }}"
                                    {{ old('pack_type_id') == $packType->id ? 'selected' : '' }}>
                                {{ $packType->name_en ?? $packType->name_ar }}
                            </option>
                        @endforeach
                    </select>
                    @error('pack_type_id') <small class="nova-text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="nova-form-group">
                    <label class="nova-label">Pack Capacity <span class="nova-text-danger">*</span></label>
                    <select name="pack_capacity_id"
                            class="nova-select @error('pack_capacity_id') is-invalid @enderror" required>
                        <option value="">Select capacity</option>
                        @foreach($packCapacities as $capacity)
                            <option value="{{ $capacity->id }}"
                                    {{ old('pack_capacity_id') == $capacity->id ? 'selected' : '' }}>
                                {{ $capacity->name_en ?? $capacity->name_ar }}
                                ({{ $capacity->uom->uom_symbol ?? '' }})
                            </option>
                        @endforeach
                    </select>
                    @error('pack_capacity_id') <small class="nova-text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="nova-form-group">
                    <label class="nova-label">Quantity in Pack <span class="nova-text-danger">*</span></label>
                    <input type="number" step="0.001" min="0.001" name="quantity_in_pack"
                           class="nova-input @error('quantity_in_pack') is-invalid @enderror"
                           value="{{ old('quantity_in_pack') }}" required>
                    @error('quantity_in_pack') <small class="nova-text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="nova-form-group">
                    <label class="nova-label">Barcode <span class="nova-text-danger">*</span></label>
                    <input type="text" name="barcode"
                           class="nova-input @error('barcode') is-invalid @enderror"
                           value="{{ old('barcode') }}" required>
                    @error('barcode') <small class="nova-text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="nova-form-group">
                    <label class="nova-label">Serial Separator</label>
                    <input type="text" name="serial_separator" class="nova-input"
                           value="{{ old('serial_separator') }}">
                    @error('serial_separator') <small class="nova-text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="nova-form-group">
                    <label class="nova-label">Equivalency Factor</label>
                    <input type="number" step="0.0001" min="0" name="equivalency_factor"
                           class="nova-input @error('equivalency_factor') is-invalid @enderror"
                           value="{{ old('equivalency_factor') }}">
                    @error('equivalency_factor') <small class="nova-text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="nova-form-group">
                    <label class="nova-label">ترتيب الفرز</label>
                    <input type="number" name="sort_order" class="nova-input"
                           value="{{ old('sort_order') }}">
                    @error('sort_order') <small class="nova-text-danger">{{ $message }}</small> @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Dimensions & Weight ===== --}}
    <div class="nova-card nova-mb-4">
        <div class="nova-card-header">
            <h5 class="nova-card-title"><i class="fas fa-ruler me-2"></i> Dimensions & Weight</h5>
        </div>
        <div class="nova-card-body">
            <div class="form-grid form-grid-4">
                <div class="nova-form-group">
                    <label class="nova-label">Height</label>
                    <input type="number" step="0.01" min="0" name="height" class="nova-input"
                           value="{{ old('height') }}">
                    @error('height') <small class="nova-text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="nova-form-group">
                    <label class="nova-label">Width</label>
                    <input type="number" step="0.01" min="0" name="width" class="nova-input"
                           value="{{ old('width') }}">
                    @error('width') <small class="nova-text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="nova-form-group">
                    <label class="nova-label">Depth</label>
                    <input type="number" step="0.01" min="0" name="depth" class="nova-input"
                           value="{{ old('depth') }}">
                    @error('depth') <small class="nova-text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="nova-form-group">
                    <label class="nova-label">Weight</label>
                    <input type="number" step="0.01" min="0" name="weight" class="nova-input"
                           value="{{ old('weight') }}">
                    @error('weight') <small class="nova-text-danger">{{ $message }}</small> @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Flags ===== --}}
    <div class="nova-card nova-mb-4">
        <div class="nova-card-header">
            <h5 class="nova-card-title"><i class="fas fa-flag me-2"></i> Flags</h5>
        </div>
        <div class="nova-card-body">
            <div class="flags-list">
                <div class="nova-form-check">
                    <input type="hidden" name="is_default_pack" value="0">
                    <input type="checkbox" name="is_default_pack"
                           class="nova-form-check-input" value="1" id="is_default_pack"
                           {{ old('is_default_pack') ? 'checked' : '' }}>
                    <label class="nova-form-check-label" for="is_default_pack">
                        <i class="fas fa-check-circle nova-me-1"></i>
                        افتراضي Pack
                        <small class="nova-text-muted d-block">الباكج الافتراضي للعنصر</small>
                    </label>
                </div>
                <div class="nova-form-check">
                    <input type="hidden" name="is_weight_priced" value="0">
                    <input type="checkbox" name="is_weight_priced"
                           class="nova-form-check-input" value="1" id="is_weight_priced"
                           {{ old('is_weight_priced') ? 'checked' : '' }}>
                    <label class="nova-form-check-label" for="is_weight_priced">
                        <i class="fas fa-weight-hanging nova-me-1"></i>
                        Weight Priced
                        <small class="nova-text-muted d-block">التسعير بناءً على الوزن</small>
                    </label>
                </div>
                <div class="nova-form-check">
                    <input type="hidden" name="allow_fraction_in_qty" value="0">
                    <input type="checkbox" name="allow_fraction_in_qty"
                           class="nova-form-check-input" value="1" id="allow_fraction_in_qty"
                           {{ old('allow_fraction_in_qty') ? 'checked' : '' }}>
                    <label class="nova-form-check-label" for="allow_fraction_in_qty">
                        <i class="fas fa-divide nova-me-1"></i>
                        الكلow Fraction Qty
                        <small class="nova-text-muted d-block">السماح بكميات كسرية</small>
                    </label>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Relations ===== --}}
    <div class="nova-card nova-mb-4">
        <div class="nova-card-header">
            <h5 class="nova-card-title"><i class="fas fa-link me-2"></i> Relations</h5>
        </div>
        <div class="nova-card-body">
            <div class="form-grid form-grid-2">
                <div class="nova-form-group">
                    <label class="nova-label">
                        Pack Operations
                        @if($packOperations->count())
                            <span class="nova-badge primary">{{ $packOperations->count() }}</span>
                        @endif
                    </label>
                    @if($packOperations->count())
                        <div class="multi-select-list">
                            @php
                                $selectedOps = old('pack_operation_ids', []);
                                if (!is_array($selectedOps)) $selectedOps = [];
                            @endphp
                            @foreach($packOperations as $operation)
                                <div class="nova-form-check">
                                    <input type="checkbox"
                                           name="pack_operation_ids[]"
                                           class="nova-form-check-input"
                                           value="{{ $operation->id }}"
                                           id="op_{{ $operation->id }}"
                                           {{ in_array($operation->id, $selectedOps) ? 'checked' : '' }}>
                                    <label class="nova-form-check-label" for="op_{{ $operation->id }}">
                                        {{ $operation->name_en ?? $operation->name_ar }}
                                        <small class="nova-text-muted">({{ $operation->code }})</small>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="nova-text-muted nova-fs-sm mb-0">لا توجد عمليات متاحة</p>
                    @endif
                    @error('pack_operation_ids') <small class="nova-text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="nova-form-group">
                    <label class="nova-label">
                        Pack Groups
                        @if($packGroups->count())
                            <span class="nova-badge primary">{{ $packGroups->count() }}</span>
                        @endif
                    </label>
                    @if($packGroups->count())
                        <div class="multi-select-list">
                            @php
                                $selectedGroups = old('pack_group_ids', []);
                                if (!is_array($selectedGroups)) $selectedGroups = [];
                            @endphp
                            @foreach($packGroups as $group)
                                <div class="nova-form-check">
                                    <input type="checkbox"
                                           name="pack_group_ids[]"
                                           class="nova-form-check-input"
                                           value="{{ $group->id }}"
                                           id="grp_{{ $group->id }}"
                                           {{ in_array($group->id, $selectedGroups) ? 'checked' : '' }}>
                                    <label class="nova-form-check-label" for="grp_{{ $group->id }}">
                                        {{ $group->name_en ?? $group->name_ar }}
                                        <small class="nova-text-muted">({{ $group->code }})</small>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="nova-text-muted nova-fs-sm mb-0">لا توجد مجموعات متاحة</p>
                    @endif
                    @error('pack_group_ids') <small class="nova-text-danger">{{ $message }}</small> @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Form إجراءات ===== --}}
    <div class="form-actions">
        <a href="{{ route('item-packs.index') }}" class="nova-btn nova-btn-outline">
            <i class="fas fa-times"></i> إلغاء
        </a>
        <button type="submit" class="nova-btn nova-btn-primary" id="submitBtn">
            <i class="fas fa-save"></i> Create Item Pack
        </button>
    </div>
</form>
@endsection

@push('styles')
<style>
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

    .form-grid-4 {
        grid-template-columns: repeat(4, 1fr);
    }

    /* ===== Flags List ===== */
    .flags-list {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.75rem;
    }

    .flags-list .nova-form-check {
        padding: 0.6rem;
        border: 1px solid #e3e6f0;
        border-radius: 0.4rem;
        background: #fff;
        transition: all 0.15s ease;
        margin: 0;
    }

    .flags-list .nova-form-check:hover {
        border-color: #4e73df;
        background: #f8f9fc;
    }

    .flags-list .nova-form-check-label {
        cursor: pointer;
        font-size: 0.85rem;
    }

    /* ===== Multi Select List ===== */
    .multi-select-list {
        max-height: 220px;
        overflow-y: auto;
        padding: 0.5rem;
        border: 1px solid #e3e6f0;
        border-radius: 0.4rem;
        background: #fff;
    }

    .multi-select-list .nova-form-check {
        padding: 0.35rem 0;
        border-bottom: 1px dashed #e3e6f0;
        margin: 0;
    }

    .multi-select-list .nova-form-check:last-child {
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
    .nova-input.is-invalid,
    .nova-select.is-invalid {
        border-color: #e74a3b;
    }

    /* ===== Responsive ===== */
    @media (max-width: 992px) {
        .form-grid-3,
        .form-grid-4,
        .flags-list {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .form-grid-2,
        .form-grid-3,
        .form-grid-4,
        .flags-list {
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

    // ===== Pack Definition → Auto-fill =====
    const packDefinitionSelect = document.getElementById('packDefinitionSelect');

    if (packDefinitionSelect) {
        packDefinitionSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (!selectedOption || !selectedOption.value) return;

            const setVal = (name, value) => {
                const el = document.querySelector(`[name="${name}"]`);
                if (el && value !== undefined && value !== null && value !== '') {
                    el.value = value;
                    el.style.transition = 'background 0.3s';
                    el.style.background = 'rgba(28, 200, 138, 0.15)';
                    setTimeout(() => { el.style.background = ''; }, 800);
                }
            };

            const setChecked = (name, checked) => {
                const el = document.querySelector(`[name="${name}"]`);
                if (el) {
                    el.checked = !!parseInt(checked);
                }
            };

            setVal('pack_type_id',       selectedOption.dataset.packType);
            setVal('pack_capacity_id',   selectedOption.dataset.packCapacity);
            setVal('quantity_in_pack',   selectedOption.dataset.quantity);
            setVal('barcode',            selectedOption.dataset.barcode);
            setVal('serial_separator',   selectedOption.dataset.serial);
            setVal('equivalency_factor', selectedOption.dataset.equivalency);
            setVal('height',             selectedOption.dataset.height);
            setVal('width',              selectedOption.dataset.width);
            setVal('depth',              selectedOption.dataset.depth);
            setVal('weight',             selectedOption.dataset.weight);

            setChecked('is_weight_priced',      selectedOption.dataset.weightPriced);
            setChecked('allow_fraction_in_qty', selectedOption.dataset.allowFraction);
            setChecked('is_default_pack',       selectedOption.dataset.defaultPack);
        });
    }

    // ===== إرسال Button Loading State =====
    const form = document.getElementById('itemPackForm');
    const submitBtn = document.getElementById('submitBtn');

    if (form && submitBtn) {
        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جارٍ الإنشاء...';
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