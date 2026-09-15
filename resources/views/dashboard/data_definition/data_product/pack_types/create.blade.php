@extends('dashboard.partials.layout')

@section('title', 'إضافة نوع باكج جديد')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-plus-circle" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            إضافة نوع باكج جديد
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">قم بتعريف نوع باكج جديد (مثال: صندوق 3×3، صندوق 4×4، قطعة)</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('pack-types.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> رجوع
        </a>
    </div>
</div>

<div class="nova-card">
    <div class="nova-card-body">
        <form action="{{ route('pack-types.store') }}" method="POST">
            @csrf
            <div class="nova-row nova-gap-3">
                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">الاسم <span class="nova-text-danger">*</span></label>
                        <input type="text" name="name" class="nova-input" value="{{ old('name') }}" placeholder="مثال: صندوق 3×3" required>
                    </div>
                </div>
                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">الاسم (إنجليزي)</label>
                        <input type="text" name="name_en" class="nova-input" value="{{ old('name_en') }}" placeholder="Box 3x3">
                    </div>
                </div>
                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">الاسم (عربي)</label>
                        <input type="text" name="name_ar" class="nova-input" value="{{ old('name_ar') }}" placeholder="صندوق 3×3">
                    </div>
                </div>
                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">الكود</label>
                        <input type="text" name="code" class="nova-input" value="{{ old('code') }}" placeholder="BOX_3X3">
                    </div>
                </div>
                <div class="nova-col">
                    <div class="nova-form-group">
                        <label class="nova-label">عدد القطع في العبوة</label>
                        <input type="number" name="pieces_per_pack" class="nova-input" value="{{ old('pieces_per_pack') }}" min="1" placeholder="9">
                    </div>
                </div>
                <div class="nova-col" style="grid-column: 1 / -1;">
                    <div class="nova-form-group">
                        <label class="nova-label">الوصف</label>
                        <textarea name="description" class="nova-input" rows="3">{{ old('description') }}</textarea>
                    </div>
                </div>
                <div class="nova-col" style="grid-column: 1 / -1;">
                    <div class="nova-flex nova-items-center nova-gap-2">
                        <button type="submit" class="nova-btn nova-btn-primary">
                            <i class="fas fa-save"></i> حفظ نوع الباكج
                        </button>
                        <a href="{{ route('pack-types.index') }}" class="nova-btn nova-btn-outline">إلغاء</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
