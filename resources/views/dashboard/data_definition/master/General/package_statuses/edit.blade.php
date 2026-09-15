@extends('dashboard.partials.layout')

@section('title', 'تعديل حالة الطرد')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-edit" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            تعديل حالة الطرد
        </h2>
        <p class="nova-page-subtitle">تحديث بيانات {{ $packageState->status_name }}</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('package-statuses.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> العودة
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
            <i class="fas fa-file-alt"></i> بيانات حالة الطرد
        </h5>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('package-statuses.update', $packageState) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="nova-row g-4">
                {{-- الحالة Name --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-tag me-1"></i> اسم الحالة <span class="nova-text-danger">*</span>
                        </label>
                        <input type="text"
                               name="status_name"
                               class="nova-input @error('status_name') nova-invalid @enderror"
                               value="{{ old('status_name', $packageState->status_name) }}"
                               placeholder="مثال: نشط، قيد الإرسال، مستلم"
                               required>
                    </div>
                    @error('status_name') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                {{-- Organization --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-building me-1"></i> المنظمة <span class="nova-text-danger">*</span>
                        </label>
                        <select name="organization_id" class="nova-select @error('organization_id') nova-invalid @enderror" required>
                            <option value="">اختر المنظمة</option>
                            @foreach($organizations as $org)
                                <option value="{{ $org->id }}" {{ old('organization_id', $packageState->organization_id) == $org->id ? 'selected' : '' }}>
                                    {{ $org->name }} ({{ $org->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('organization_id') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                {{-- Boolean Flags --}}
                <div class="nova-col nova-col-12">
                    <hr class="nova-hr">
                    <h4 class="nova-fw-semibold mb-3">
                        <i class="fas fa-cog me-1"></i> خصائص الحالة
                    </h4>
                    <div class="nova-row g-3">
                        <div class="nova-col nova-col-md-4">
                            <div class="nova-form-check">
                                <input class="nova-form-check-input" type="checkbox" name="resellable" value="1" id="resellable" {{ old('resellable', $packageState->resellable) ? 'checked' : '' }}>
                                <label class="nova-form-check-label fw-bold" for="resellable">قابل لإعادة البيع</label>
                                <div class="nova-help-text">هل يمكن إعادة بيع هذه الحالة؟</div>
                            </div>
                        </div>
                        <div class="nova-col nova-col-md-4">
                            <div class="nova-form-check">
                                <input class="nova-form-check-input" type="checkbox" name="expired" value="1" id="expired" {{ old('expired', $packageState->expired) ? 'checked' : '' }}>
                                <label class="nova-form-check-label fw-bold" for="expired">منتهية الصلاحية</label>
                                <div class="nova-help-text">وضع علامة على الحالة منتهية الصلاحية</div>
                            </div>
                        </div>
                        <div class="nova-col nova-col-md-4">
                            <div class="nova-form-check">
                                <input class="nova-form-check-input" type="checkbox" name="default" value="1" id="default" {{ old('default', $packageState->default) ? 'checked' : '' }}>
                                <label class="nova-form-check-label fw-bold" for="default">افتراضي</label>
                                <div class="nova-help-text">تعيين كحالة افتراضية</div>
                            </div>
                        </div>
                        <div class="nova-col nova-col-md-4">
                            <div class="nova-form-check">
                                <input class="nova-form-check-input" type="checkbox" name="lpo_required" value="1" id="lpo_required" {{ old('lpo_required', $packageState->lpo_required) ? 'checked' : '' }}>
                                <label class="nova-form-check-label fw-bold" for="lpo_required">مطلوب LPO</label>
                                <div class="nova-help-text">إلزامي لهذه الحالة</div>
                            </div>
                        </div>
                        <div class="nova-col nova-col-md-4">
                            <div class="nova-form-check">
                                <input class="nova-form-check-input" type="checkbox" name="is_related_to_cn_request" value="1" id="is_related_to_cn_request" {{ old('is_related_to_cn_request', $packageState->is_related_to_cn_request) ? 'checked' : '' }}>
                                <label class="nova-form-check-label fw-bold" for="is_related_to_cn_request">مرتبط بـ CN Request</label>
                                <div class="nova-help-text">مرتبط بطلبات CN</div>
                            </div>
                        </div>
                        <div class="nova-col nova-col-md-4">
                            <div class="nova-form-check">
                                <input class="nova-form-check-input" type="checkbox" name="is_price_editable" value="1" id="is_price_editable" {{ old('is_price_editable', $packageState->is_price_editable) ? 'checked' : '' }}>
                                <label class="nova-form-check-label fw-bold" for="is_price_editable">قابل لتحرير السعر</label>
                                <div class="nova-help-text">السماح بتغيير الأسعار</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Form إجراءات --}}
                <div class="nova-col nova-col-12">
                    <div class="nova-form-actions">
                        <a href="{{ route('package-statuses.index') }}" class="nova-btn nova-btn-outline">
                            <i class="fas fa-x me-1"></i> إلغاء
                        </a>
                        <button type="submit" class="nova-btn nova-btn-primary">
                            <i class="fas fa-save me-1"></i> تحديث الحالة
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
