@extends('dashboard.partials.layout')

@section('title', 'Edit Sales Order Type')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-edit" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            تعديل نوع طلب البيع: {{ $salesOrderType->name }}
        </h2>
        <p class="nova-page-subtitle">تحرير بيانات نوع طلب البيع</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('sales-order-types.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> العودة
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
    <div class="nova-card-header">
        <h5 class="mb-0 fw-bold">
            <i class="fas fa-file-alt"></i> بيانات نوع طلب البيع
        </h5>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('sales-order-types.update', $salesOrderType) }}" method="POST">
            @csrf @method('PUT')

            <div class="nova-row g-4">
                {{-- Name --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-tag me-1"></i> اسم نوع طلب البيع <span class="nova-text-danger">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               class="nova-input @error('name') nova-invalid @enderror"
                               value="{{ old('name', $salesOrderType->name) }}"
                               placeholder="أدخل اسم نوع طلب البيع..."
                               required>
                    </div>
                    @error('name') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                {{-- Code --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-qrcode me-1"></i> الشيفرة <span class="nova-text-danger">*</span>
                        </label>
                        <input type="text"
                               name="code"
                               class="nova-input @error('code') nova-invalid @enderror"
                               value="{{ old('code', $salesOrderType->code) }}"
                               placeholder="أدخل الشيفرة..."
                               required>
                    </div>
                    @error('code') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                {{-- Boolean Flags --}}
                <div class="nova-col nova-col-12">
                    <hr class="nova-hr">
                    <h4 class="nova-fw-semibold mb-3">
                        <i class="fas fa-cog me-1"></i> خصائص نوع طلب البيع
                    </h4>
                    <div class="nova-row g-3">
                        <div class="nova-col nova-col-md-4">
                            <div class="nova-form-check">
                                <input class="nova-form-check-input" type="checkbox" name="active" value="1" id="active" {{ old('active', $salesOrderType->active) ? 'checked' : '' }}>
                                <label class="nova-form-check-label fw-bold" for="active">نشط</label>
                                <div class="nova-help-text">تفعيل هذا النوع</div>
                            </div>
                        </div>
                        <div class="nova-col nova-col-md-4">
                            <div class="nova-form-check">
                                <input class="nova-form-check-input" type="checkbox" name="default" value="1" id="default" {{ old('default', $salesOrderType->default) ? 'checked' : '' }}>
                                <label class="nova-form-check-label fw-bold" for="default">افتراضي</label>
                                <div class="nova-help-text">تعيين كنوع افتراضي (نوع واحد فقط)</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Form Actions --}}
                <div class="nova-col nova-col-12">
                    <div class="nova-form-actions">
                        <a href="{{ route('sales-order-types.index') }}" class="nova-btn nova-btn-outline">
                            <i class="fas fa-x me-1"></i> إلغاء
                        </a>
                        <button type="submit" class="nova-btn nova-btn-primary">
                            <i class="fas fa-save me-1"></i> تحديث
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
