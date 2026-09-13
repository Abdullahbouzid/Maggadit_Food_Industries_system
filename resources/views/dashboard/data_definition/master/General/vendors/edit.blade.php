@extends('dashboard.partials.layout')

@section('title', 'تعديل المورد')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-edit" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            تعديل المورد
        </h2>
        <p class="nova-page-subtitle">تحديث بيانات {{ $vendor->vendor_name }}</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('vendors.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
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
            <i class="fas fa-file-alt"></i> بيانات المورد
        </h5>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('vendors.update', $vendor) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="nova-row g-4">
                {{-- Vendor Name --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-truck me-1"></i> اسم المورد <span class="nova-text-danger">*</span>
                        </label>
                        <input type="text"
                               name="vendor_name"
                               class="nova-input @error('vendor_name') nova-invalid @enderror"
                               value="{{ old('vendor_name', $vendor->vendor_name) }}"
                               required>
                    </div>
                    @error('vendor_name') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                {{-- Code --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-hashtag me-1"></i> الشيفرة <span class="nova-text-danger">*</span>
                        </label>
                        <input type="text"
                               name="code"
                               class="nova-input @error('code') nova-invalid @enderror"
                               value="{{ old('code', $vendor->code) }}"
                               required>
                    </div>
                    @error('code') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                {{-- Phone --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label"><i class="fas fa-phone me-1"></i> الهاتف</label>
                        <input type="text" name="phone" class="nova-input" value="{{ old('phone', $vendor->phone) }}" placeholder="+966 12 345 6789">
                    </div>
                </div>

                {{-- Fax --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label"><i class="fas fa-fax me-1"></i> الفاكس</label>
                        <input type="text" name="fax" class="nova-input" value="{{ old('fax', $vendor->fax) }}" placeholder="+966 12 345 6790">
                    </div>
                </div>

                {{-- Contact Person --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label"><i class="fas fa-user me-1"></i> شخص التواصل</label>
                        <input type="text" name="contact_person" class="nova-input" value="{{ old('contact_person', $vendor->contact_person) }}">
                    </div>
                </div>

                {{-- Contact Number --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label"><i class="fas fa-mobile-alt me-1"></i> رقم التواصل</label>
                        <input type="text" name="contact_number" class="nova-input" value="{{ old('contact_number', $vendor->contact_number) }}" placeholder="+966 50 123 4567">
                    </div>
                </div>

                {{-- Street Address --}}
                <div class="nova-col nova-col-12">
                    <div class="nova-form-group">
                        <label class="nova-label"><i class="fas fa-map-marker-alt me-1"></i> العنوان</label>
                        <textarea name="street" class="nova-textarea" rows="2" placeholder="أدخل العنوان الكامل...">{{ old('street', $vendor->street) }}</textarea>
                    </div>
                </div>

                {{-- Commission Percentage --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label"><i class="fas fa-percentage me-1"></i> نسبة العمولة (%)</label>
                        <input type="number" step="0.01" name="commission_percentage" class="nova-input" value="{{ old('commission_percentage', $vendor->commission_percentage) }}">
                    </div>
                </div>

                {{-- Inactive Toggle --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-check">
                        <input type="checkbox" name="inactive" class="nova-form-check-input" id="inactive" value="1" {{ old('inactive', $vendor->inactive) ? 'checked' : '' }}>
                        <label class="nova-form-check-label fw-bold" for="inactive">غير نشط</label>
                        <div class="nova-help-text">إذا تم تحديده، سيتم إيقاف المورد.</div>
                    </div>
                </div>

                {{-- Form Actions --}}
                <div class="nova-col nova-col-12">
                    <div class="nova-form-actions">
                        <a href="{{ route('vendors.index') }}" class="nova-btn nova-btn-outline">
                            <i class="fas fa-x me-1"></i> إلغاء
                        </a>
                        <button type="submit" class="nova-btn nova-btn-primary">
                            <i class="fas fa-save me-1"></i> تحديث المورد
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
