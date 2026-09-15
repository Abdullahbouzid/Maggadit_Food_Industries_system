@extends('dashboard.partials.layout')

@section('title', 'إضافة نبذة منظمة')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-{{ __('messages.app.page_') }}">
            <i class="fas fa-plus-circle" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            إضافة نبذة منظمة
        </h2>
        <p class="nova-page-sub{{ __('messages.app.page_') }}" style="margin: 0.25rem 0 0;">أدخل بيانات نبذة المنظمة</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('organization-about.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> {{ __('messages.app.page__2') }}
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
        <h5 class="nova-fw-bold mb-0">
            <i class="fas fa-edit"></i> {{ __('messages.app.page__1') }}
        </h5>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('organization-about.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="nova-row g-4">
                {{-- Organization --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-building me-1"></i> المنظمة <span class="nova-text-danger">*</span>
                        </label>
                        <select name="organization_id" id="organization-select" class="nova-select" required>
                            <option value="">اختر المنظمة</option>
                            @foreach($organizations as $id => $name)
                                <option value="{{ $id }}" {{ old('organization_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('organization_id') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Title --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-heading me-1"></i> العنوان الرئيسي
                        </label>
                        <input type="text" name="{{ __('messages.app.page_') }}" class="nova-input" value="{{ old('{{ __('messages.app.page_') }}') }}" placeholder="مثل: ماجاديت للصناعات الغذائية">
                        @error('{{ __('messages.app.page_') }}') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Description --}}
                <div class="nova-col nova-col-12">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-align-left me-1"></i> الوصف / القصة
                        </label>
                        <textarea name="description" rows="4" class="nova-textarea" placeholder="أدخل وصف المنظمة...">{{ old('description') }}</textarea>
                        @error('description') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Vision --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-bullseye me-1"></i> الرؤية
                        </label>
                        <input type="text" name="vision" class="nova-input" value="{{ old('vision') }}" placeholder="الرؤية المستقبلية">
                        @error('vision') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Mission --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-target me-1"></i> الرسالة
                        </label>
                        <input type="text" name="mission" class="nova-input" value="{{ old('mission') }}" placeholder="رسالة المنظمة">
                        @error('mission') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Values (JSON array) --}}
                <div class="nova-col nova-col-12">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-list me-1"></i> القيم الأساسية
                        </label>
                        <div id="values-container">
                            @if(old('values'))
                                @foreach(old('values') as $value)
                                    <div class="nova-input-group nova-mb-2">
                                        <input type="text" name="values[]" class="nova-input" value="{{ $value }}" placeholder="أدخل قيمة">
                                        <button type="button" class="nova-btn nova-btn-outline nova-btn-sm nova-text-danger remove-value">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                @endforeach
                            @else
                                <div class="nova-input-group nova-mb-2">
                                    <input type="text" name="values[]" class="nova-input" placeholder="مثال: الجودة أولاً">
                                    <button type="button" class="nova-btn nova-btn-outline nova-btn-sm nova-text-danger remove-value">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                        <button type="button" id="add-value" class="nova-btn nova-btn-outline nova-btn-sm nova-mt-1">
                            <i class="fas fa-plus"></i> إضافة قيمة
                        </button>
                        @error('values')
                            <div class="nova-form-error d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Statistics (JSON) --}}
                <div class="nova-col nova-col-12">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-chart-bar me-1"></i> الإحصائيات (مفتاح: قيمة)
                        </label>
                        <div id="statistics-fields">
                            <div class="nova-row g-2 nova-mb-2 statistic-item">
                                <div class="nova-col nova-col-md-5">
                                    <input type="text" name="statistics[0][key]" class="nova-input" placeholder="المفتاح (مثلاً: سنوات)">
                                </div>
                                <div class="nova-col nova-col-md-5">
                                    <input type="text" name="statistics[0][value]" class="nova-input" placeholder="القيمة (مثلاً: 15)">
                                </div>
                                <div class="nova-col nova-col-md-2">
                                    <button type="button" class="nova-btn nova-btn-outline nova-btn-sm nova-text-danger remove-statistic">
                                        <i class="fas fa-times"></i> حذف
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="add-statistic" class="nova-btn nova-btn-outline nova-btn-sm">
                            <i class="fas fa-plus"></i> إضافة إحصائية
                        </button>
                    </div>
                </div>

                {{-- Image --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-image me-1"></i> صورة الشعار
                        </label>
                        <input type="file" name="image" class="nova-input" accept="image/*">
                        @error('image') <div class="nova-form-error">{{ $message }}</div> @enderror
                        <div class="nova-help-text">يُفضل أبعاد 200x200 بكسل، حجم أقل من 2 ميجابايت</div>
                    </div>
                </div>

                {{-- Cover Image --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-images me-1"></i> صورة الغلاف
                        </label>
                        <input type="file" name="cover_image" class="nova-input" accept="image/*">
                        @error('cover_image') <div class="nova-form-error">{{ $message }}</div> @enderror
                        <div class="nova-help-text">يُفضل أبعاد 1200x400 بكسل، حجم أقل من 4 ميجابايت</div>
                    </div>
                </div>

                {{-- Is Active --}}
                <div class="nova-col nova-col-12">
                    <div class="nova-form-check">
                        <input type="checkbox" name="is_active" class="nova-form-check-input" id="isActive" value="1" {{ old('is_active') ? 'checked' : '' }}>
                        <label class="nova-form-check-label" for="isActive">نشط</label>
                        <div class="nova-help-text">عند التفعيل، ستظهر هذه النبذة للعملاء في تطبيقهم</div>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="nova-col nova-col-12">
                    <div class="nova-form-actions">
                        <a href="{{ route('organization-about.index') }}" class="nova-btn nova-btn-outline">
                            <i class="fas fa-times"></i> إلغاء
                        </a>
                        <button type="submit" class="nova-btn nova-btn-primary">
                            <i class="fas fa-save"></i> حفظ
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('add-value').addEventListener('click', function() {
        const container = document.getElementById('values-container');
        const div = document.createElement('div');
        div.className = 'nova-input-group nova-mb-2';
        div.innerHTML = `
            <input type="text" name="values[]" class="nova-input" placeholder="أدخل قيمة">
            <button type="button" class="nova-btn nova-btn-outline nova-btn-sm nova-text-danger remove-value">
                <i class="fas fa-times"></i>
            </button>`;
        container.appendChild(div);
        div.querySelector('.remove-value').addEventListener('click', function() {
            div.remove();
        });
    });

    document.querySelectorAll('.remove-value').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.nova-input-group').remove();
        });
    });

    document.getElementById('add-statistic').addEventListener('click', function() {
        const container = document.getElementById('statistics-fields');
        const count = container.querySelectorAll('.statistic-item').length;
        const div = document.createElement('div');
        div.className = 'nova-row g-2 nova-mb-2 statistic-item';
        div.innerHTML = `
            <div class="nova-col nova-col-md-5">
                <input type="text" name="statistics[${count}][key]" class="nova-input" placeholder="المفتاح (مثلاً: سنوات)">
            </div>
            <div class="nova-col nova-col-md-5">
                <input type="text" name="statistics[${count}][value]" class="nova-input" placeholder="القيمة (مثلاً: 15)">
            </div>
            <div class="nova-col nova-col-md-2">
                <button type="button" class="nova-btn nova-btn-outline nova-btn-sm nova-text-danger remove-statistic">
                    <i class="fas fa-times"></i> حذف
                </button>
            </div>`;
        container.appendChild(div);
        div.querySelector('.remove-statistic').addEventListener('click', function() {
            div.remove();
        });
    });

    document.querySelectorAll('.remove-statistic').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.statistic-item').remove();
        });
    });
</script>
@endpush

<style>
.nova-input-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.nova-input-group input {
    flex: 1;
}
.nova-mb-2 {
    margin-bottom: 0.5rem;
}
.nova-mt-1 {
    margin-top: 0.25rem;
}
</style>
