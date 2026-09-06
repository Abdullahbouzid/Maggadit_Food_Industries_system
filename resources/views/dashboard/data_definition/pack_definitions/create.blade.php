@extends('dashboard.partials.layout')

@section('title', 'إضافة تعريف باكج جديد')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="plus-circle"></i> إضافة تعريف باكج جديد
                        </h1>
                        <p class="text-muted">قم بتعريف باكج جديد يمكن استخدامه للمنتجات</p>
                    </div>
                </div>

                <form action="{{ route('pack-definitions.store') }}" method="POST" id="packDefinitionForm">
                    @csrf
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-light border-0 py-3">
                                    <h6 class="fw-bold mb-0"><i data-feather="info"></i> المعلومات الأساسية</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">الاسم <span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="مثال: صندوق شوكولاتة">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">الاسم (إنجليزي)</label>
                                            <input type="text" name="name_en" class="form-control" value="{{ old('name_en') }}" placeholder="Chocolate Box">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">الاسم (عربي)</label>
                                            <input type="text" name="name_ar" class="form-control" value="{{ old('name_ar') }}" placeholder="صندوق شوكولاتة">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">الكود</label>
                                            <input type="text" name="code" class="form-control" value="{{ old('code') }}" placeholder="CHOCO-BOX-3X3">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">الترتيب</label>
                                            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-light border-0 py-3">
                                    <h6 class="fw-bold mb-0"><i data-feather="box"></i> نوع العبوة والسعة</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">نوع الباكج (Pack Type) <span class="text-danger">*</span></label>
                                            <select name="pack_type_id" class="form-select" required id="packTypeSelect">
                                                <option value="">-- اختر --</option>
                                                @foreach($packTypes as $type)
                                                    <option value="{{ $type->id }}" data-pieces="{{ $type->pieces_per_pack ?? '' }}" {{ old('pack_type_id') == $type->id ? 'selected' : '' }}>
                                                        {{ $type->name_en ?? $type->name_ar }} ({{ $type->code }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted">اختر نوع باكج موجود أو اكتب اسماً جديداً</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">سعة الباكج (Pack Capacity) <span class="text-danger">*</span></label>
                                            <select name="pack_capacity_id" class="form-select" required id="packCapacitySelect">
                                                <option value="">-- اختر --</option>
                                                @foreach($packCapacities as $capacity)
                                                    <option value="{{ $capacity->id }}" {{ old('pack_capacity_id') == $capacity->id ? 'selected' : '' }}>
                                                        {{ $capacity->name_en ?? $capacity->name_ar }} ({{ $capacity->uom->uom_symbol ?? '' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="mt-3">
                                                <div class="card border-0 shadow-sm">
                                                    <div class="card-header bg-light border-0 py-2">
                                                        <small class="fw-bold"><i data-feather="box" style="width: 12px;"></i> السعات المتاحة - اختر السعة المناسبة</small>
                                                    </div>
                                                    <div class="card-body p-2">
                                                        <div class="row g-2">
                                                            @foreach($packCapacities as $capacity)
                                                                <div class="col-md-6 col-lg-4">
                                                                    <div class="card border capacity-card h-100" data-capacity-id="{{ $capacity->id }}" style="cursor: pointer;">
                                                                        <div class="card-body p-2 text-center">
                                                                            <h5 class="fw-bold text-primary mb-1">{{ $capacity->value }}</h5>
                                                                            <span class="badge bg-secondary mb-1">{{ $capacity->uom->uom_symbol ?? '' }}</span>
                                                                            <p class="small text-muted mb-0">{{ $capacity->name_en ?? $capacity->name_ar ?? '-' }}</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        @if($packCapacities->count() == 0)
                                                            <div class="text-muted text-center py-3">
                                                                <p class="mb-0">لا توجد سعات باكجات متاحة.</p>
                                                                <a href="{{ route('pack-capacities.create') }}" class="btn btn-sm btn-primary mt-2" target="_blank">إضافة سعة جديدة</a>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">الكمية في العبوة <span class="text-danger">*</span></label>
                                            <input type="number" name="quantity_in_pack" class="form-control" value="{{ old('quantity_in_pack', 1) }}" min="1" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">الباركود</label>
                                            <input type="text" name="barcode" class="form-control" value="{{ old('barcode') }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">فاصل التسلسل</label>
                                            <input type="text" name="serial_separator" class="form-control" value="{{ old('serial_separator') }}" placeholder="-">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">معامل المعادلة</label>
                                            <input type="number" step="0.01" name="equivalency_factor" class="form-control" value="{{ old('equivalency_factor') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-light border-0 py-3">
                                    <h6 class="fw-bold mb-0"><i data-feather="maximize"></i> الأبعاد والوزن</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label">الارتفاع</label>
                                            <input type="number" step="any" name="height" class="form-control" value="{{ old('height') }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">العرض</label>
                                            <input type="number" step="any" name="width" class="form-control" value="{{ old('width') }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">العمق</label>
                                            <input type="number" step="any" name="depth" class="form-control" value="{{ old('depth') }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">الوزن</label>
                                            <input type="number" step="any" name="weight" class="form-control" value="{{ old('weight') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-light border-0 py-3">
                                    <h6 class="fw-bold mb-0"><i data-feather="settings"></i> الإعدادات</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" name="is_weight_priced" id="is_weight_priced" value="1" {{ old('is_weight_priced') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_weight_priced">تسعير بالوزن</label>
                                    </div>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" name="allow_fraction_in_qty" id="allow_fraction_in_qty" value="1" {{ old('allow_fraction_in_qty') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="allow_fraction_in_qty">السماح بكسور في الكمية</label>
                                    </div>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" name="is_default_pack" id="is_default_pack" value="1" {{ old('is_default_pack') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_default_pack">باكج افتراضي</label>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-light border-0 py-3">
                                    <h6 class="fw-bold mb-0"><i data-feather="tag"></i> عمليات الباكج</h6>
                                </div>
                                <div class="card-body">
                                    @foreach($packOperations as $operation)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="pack_operations[]" value="{{ $operation->id }}" id="op_{{ $operation->id }}" {{ in_array($operation->id, old('pack_operations', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="op_{{ $operation->id }}">
                                                {{ $operation->name_en ?? $operation->name_ar }}
                                                <small class="text-muted d-block">({{ $operation->code }})</small>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-light border-0 py-3">
                                    <h6 class="fw-bold mb-0"><i data-feather="layers"></i> مجموعات الباكج</h6>
                                </div>
                                <div class="card-body">
                                    @foreach($packGroups as $group)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="pack_groups[]" value="{{ $group->id }}" id="grp_{{ $group->id }}" {{ in_array($group->id, old('pack_groups', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="grp_{{ $group->id }}">
                                                {{ $group->name_en ?? $group->name_ar }}
                                                <small class="text-muted d-block">({{ $group->code }})</small>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-success btn-lg shadow-sm">
                                <i data-feather="save"></i> حفظ تعريف الباكج
                            </button>
                            <a href="{{ route('pack-definitions.index') }}" class="btn btn-secondary btn-lg shadow-sm ms-2">إلغاء</a>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const packTypeSelect = document.getElementById('packTypeSelect');
        const quantityInPackInput = document.querySelector('input[name="quantity_in_pack"]');

        if (packTypeSelect && quantityInPackInput) {
            packTypeSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const pieces = selectedOption.getAttribute('data-pieces');
                if (pieces) {
                    quantityInPackInput.value = pieces;
                }
            });
        }

        // Make capacity cards clickable
        document.querySelectorAll('.capacity-card').forEach(card => {
            card.addEventListener('click', function() {
                const capacityId = this.getAttribute('data-capacity-id');
                const select = document.getElementById('packCapacitySelect');
                if (select && capacityId) {
                    select.value = capacityId;
                    // Highlight selected card
                    document.querySelectorAll('.capacity-card').forEach(c => {
                        c.classList.remove('border-primary', 'bg-light');
                    });
                    this.classList.add('border-primary', 'bg-light');
                }
            });
        });

        // Highlight selected capacity on load
        const select = document.getElementById('packCapacitySelect');
        if (select && select.value) {
            const selectedCard = document.querySelector(`.capacity-card[data-capacity-id="${select.value}"]`);
            if (selectedCard) {
                selectedCard.classList.add('border-primary', 'bg-light');
            }
        }
    });
</script>
@endpush
@endsection
