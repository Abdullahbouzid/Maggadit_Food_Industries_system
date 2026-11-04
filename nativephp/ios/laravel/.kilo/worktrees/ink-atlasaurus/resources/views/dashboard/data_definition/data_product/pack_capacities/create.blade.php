@extends('dashboard.partials.layout')

@section('title', 'إضافة سعة باكج جديدة')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-plus-circle" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            إضافة سعة باكج جديدة
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">قم بتعريف سعة باكج جديدة مع وحدة القياس</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('pack-capacities.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<form action="{{ route('pack-capacities.store') }}" method="POST">
    @csrf
    <div class="nova-row">
        <div class="nova-col">
            <div class="nova-card nova-mb-4">
                <div class="nova-card-header">
                    <h5 class="nova-card-title"><i class="fas fa-info-circle nova-me-2"></i> معلومات السعة</h5>
                </div>
                <div class="nova-card-body">
                    <div class="nova-row nova-gap-3">
                        <div class="nova-col">
                            <div class="nova-form-group">
                                <label class="nova-label">القيمة <span class="nova-text-danger">*</span></label>
                                <input type="number" step="any" name="value" class="nova-input" value="{{ old('value') }}" placeholder="500" required>
                            </div>
                        </div>
                        <div class="nova-col">
                            <div class="nova-form-group">
                                <label class="nova-label">وحدة القياس (UOM) <span class="nova-text-danger">*</span></label>
                                <select name="uom_id" class="nova-select" required>
                                    <option value="">-- اختر --</option>
                                    @foreach($uoms as $uom)
                                        <option value="{{ $uom->id }}" {{ old('uom_id') == $uom->id ? 'selected' : '' }}>
                                            {{ $uom->name_en ?? $uom->name_ar }} ({{ $uom->uom_symbol }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="nova-col">
                            <div class="nova-form-group">
                                <label class="nova-label">الاسم (إنجليزي)</label>
                                <input type="text" name="name_en" class="nova-input" value="{{ old('name_en') }}" placeholder="500g">
                            </div>
                        </div>
                        <div class="nova-col">
                            <div class="nova-form-group">
                                <label class="nova-label">الاسم (عربي)</label>
                                <input type="text" name="name_ar" class="nova-input" value="{{ old('name_ar') }}" placeholder="500 جرام">
                            </div>
                        </div>
                        <div class="nova-col">
                            <div class="nova-form-group">
                                <label class="nova-label">الوصف</label>
                                <textarea name="description" class="nova-input" rows="3">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="nova-col">
            <div class="nova-card nova-mb-4">
                <div class="nova-card-header">
                    <div class="nova-flex nova-items-center nova-justify-between">
                        <h5 class="nova-card-title"><i class="fas fa-shuffle nova-me-2"></i> وحدات القياس (UOMs)</h5>
                        <a href="{{ route('uoms.create') }}" class="nova-btn nova-btn-primary nova-btn-sm" target="_blank">
                            <i class="fas fa-plus-circle"></i> إضافة
                        </a>
                    </div>
                </div>
                <div class="nova-card-body nova-p-0">
                    <div class="nova-table-scroll">
                        <table class="nova-table">
                            <thead>
                                <tr>
                                    <th>الكود</th>
                                    <th>الاسم</th>
                                    <th>الرمز</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($uoms as $uom)
                                    <tr>
                                        <td><code>{{ $uom->code }}</code></td>
                                        <td>{{ $uom->name_en ?? $uom->name_ar ?? '-' }}</td>
                                        <td><span class="nova-badge secondary">{{ $uom->uom_symbol }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($uoms->count() == 0)
                        <div class="nova-text-center nova-py-3">
                            <p class="nova-text-muted nova-mb-0">لا توجد وحدات قياس. قم بإنشاء وحدة قياس أولاً.</p>
                            <a href="{{ route('uoms.create') }}" class="nova-btn nova-btn-primary nova-btn-sm nova-mt-2" target="_blank">إضافة وحدة قياس</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="nova-row">
        <div class="nova-col nova-text-end">
            <button type="submit" class="nova-btn nova-btn-primary">
                <i class="fas fa-save"></i> حفظ سعة الباكج
            </button>
            <a href="{{ route('pack-capacities.index') }}" class="nova-btn nova-btn-outline">إلغاء</a>
        </div>
    </div>
</form>
@endsection
