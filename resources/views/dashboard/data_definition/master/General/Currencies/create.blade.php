@extends('dashboard.partials.layout')

@section('title', 'إنشاء عملة جديدة')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-plus-circle" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            إنشاء عملة جديدة
        </h2>
        <p class="nova-page-subtitle">أدخل بيانات العملة الجديدة</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('currencies.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
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
            <i class="fas fa-file-alt"></i> بيانات العملة
        </h5>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('currencies.store') }}" method="POST">
            @csrf

            <div class="nova-row g-4">
                {{-- Name --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-tag me-1"></i> اسم العملة <span class="nova-text-danger">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               class="nova-input @error('name') nova-invalid @enderror"
                               value="{{ old('name') }}"
                               placeholder="مثال: الدولار الأمريكي، الريال السعوي..."
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
                               value="{{ old('code') }}"
                               placeholder="مثال: USD، SAR، EGP"
                               required>
                    </div>
                    @error('code') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                {{-- Coin الوصف --}}
                <div class="nova-col nova-col-12">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-coins me-1"></i> وصف العملة الفرعية
                        </label>
                        <input type="text"
                               name="coin_description"
                               class="nova-input @error('coin_description') nova-invalid @enderror"
                               value="{{ old('coin_description') }}"
                               placeholder="مثال: سنت، قرش، هلالة...">
                    </div>
                    @error('coin_description') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                {{-- Base Currency --}}
                <div class="nova-col nova-col-12">
                    <div class="nova-form-check">
                        <input type="hidden" name="base_currency" value="0">
                        <input class="nova-form-check-input" type="checkbox" name="base_currency" value="1" id="base_currency" {{ old('base_currency') ? 'checked' : '' }}>
                        <label class="nova-form-check-label fw-bold" for="base_currency">عملة أساسية</label>
                        <div class="nova-help-text">تعيين كعملة أساسية (عملة واحدة فقط يمكن أن تكون أساسية)</div>
                    </div>
                </div>

                {{-- Form إجراءات --}}
                <div class="nova-col nova-col-12">
                    <div class="nova-form-actions">
                        <a href="{{ route('currencies.index') }}" class="nova-btn nova-btn-outline">
                            <i class="fas fa-x me-1"></i> إلغاء
                        </a>
                        <button type="submit" class="nova-btn nova-btn-primary">
                            <i class="fas fa-save me-1"></i> حفظ
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
