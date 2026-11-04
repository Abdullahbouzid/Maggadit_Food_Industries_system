@extends('dashboard.partials.layout')

@section('title', 'إنشاء استبيان جديد')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-plus-circle" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            إنشاء استبيان جديد
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">صمّم استبيانك وأضف أسئلتك بحرية</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('surveys.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> العودة
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

<form action="{{ route('surveys.store') }}" method="POST" id="survey-form">
    @csrf

    <div class="nova-row g-4">
        {{-- معلومات الاستبيان --}}
        <div class="nova-col nova-col-lg-4">
            <div class="nova-form-card h-100">
                <div class="nova-form-card-header">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-cog"></i> إعدادات الاستبيان
                    </h5>
                </div>
                <div class="nova-form-card-body">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-heading me-1"></i> عنوان الاستبيان <span class="nova-text-danger">*</span>
                        </label>
                        <input type="text" name="title" class="nova-input" value="{{ old('title') }}" placeholder="مثال: استبيان رضا العملاء" required>
                    </div>

                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-align-left me-1"></i> الوصف
                        </label>
                        <textarea name="description" class="nova-textarea" rows="3" placeholder="وصف مختصر للاستبيان...">{{ old('description') }}</textarea>
                    </div>

                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-clock me-1"></i> الوقت التقديري (دقائق)
                        </label>
                        <input type="number" name="estimated_minutes" class="nova-input" value="{{ old('estimated_minutes', 5) }}" min="1" max="60">
                    </div>

                    <div class="nova-row g-2">
                        <div class="nova-col nova-col-md-6">
                            <label class="nova-label small">
                                <i class="fas fa-calendar me-1"></i> تاريخ البداية
                            </label>
                            <input type="datetime-local" name="starts_at" class="nova-input nova-input-sm" value="{{ old('starts_at') }}">
                        </div>
                        <div class="nova-col nova-col-md-6">
                            <label class="nova-label small">
                                <i class="fas fa-calendar-alt me-1"></i> تاريخ الانتهاء
                            </label>
                            <input type="datetime-local" name="ends_at" class="nova-input nova-input-sm" value="{{ old('ends_at') }}">
                        </div>
                    </div>

                    <div class="nova-form-check mt-3">
                        <input class="nova-form-check-input" type="checkbox" name="is_active" id="isActive" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                        <label class="nova-form-check-label" for="isActive">نشط فور الإنشاء</label>
                    </div>
                </div>
            </div>
        </div>

        {{-- منشئ الأسئلة --}}
        <div class="nova-col nova-col-lg-8">
            <div class="nova-form-card">
                <div class="nova-form-card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-list"></i> الأسئلة
                    </h5>
                    <button type="button" id="add-question" class="nova-btn nova-btn-primary nova-btn-sm">
                        <i class="fas fa-plus"></i> إضافة سؤال
                    </button>
                </div>
                <div class="nova-form-card-body">
                    <div id="questions-container" class="space-y-3">
                        {{-- الأسئلة ستُضاف هنا --}}
                    </div>
                    <div id="empty-questions" class="nova-empty">
                        <div class="nova-empty-icon">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <h3 class="nova-empty-title">لا توجد أسئلة</h3>
                        <p class="nova-empty-text">لم تضف أي أسئلة بعد. اضغط على "إضافة سؤال" للبدء.</p>
                    </div>
                </div>
            </div>

            <div class="nova-form-actions">
                <a href="{{ route('surveys.index') }}" class="nova-btn nova-btn-outline">
                    <i class="fas fa-times"></i> إلغاء
                </a>
                <button type="submit" class="nova-btn nova-btn-primary fw-semibold">
                    <i class="fas fa-save"></i> حفظ الاستبيان
                </button>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
const questionCount = { value: 0 };

const addBtn = document.getElementById('add-question');
const container = document.getElementById('questions-container');
const emptyMsg = document.getElementById('empty-questions');

addBtn.addEventListener('click', addQuestion);

function addQuestion() {
    questionCount.value++;
    const id = questionCount.value;
    emptyMsg.style.display = 'none';

    const div = document.createElement('div');
    div.className = 'nova-form-card';
    div.id = `q-block-${id}`;
    div.innerHTML = `
        <div class="nova-form-card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <span class="question-number q-num">${id}</span>
                <span class="fw-semibold text-muted small">سؤال</span>
            </div>
            <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger remove-q">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
        <div class="nova-form-card-body">
            <div class="nova-form-group mb-3">
                <label class="nova-label small fw-semibold">
                    <i class="fas fa-heading me-1"></i> نص السؤال <span class="nova-text-danger">*</span>
                </label>
                <input type="text" name="questions[${id}][question_text]"
                       class="nova-input nova-input-sm" placeholder="اكتب نص السؤال هنا..." required>
            </div>
            <div class="nova-row g-2 mb-3">
                <div class="nova-col nova-col-md-6">
                    <label class="nova-label small fw-semibold">
                        <i class="fas fa-tag me-1"></i> نوع السؤال
                    </label>
                    <select name="questions[${id}][question_type]" class="nova-select nova-input-sm q-type" data-id="${id}">
                        <option value="choice">اختيار من متعدد</option>
                        <option value="text">إجابة نصية</option>
                        <option value="rating">تقييم (نجوم)</option>
                        <option value="yesno">نعم / لا</option>
                        <option value="multiselect">اختيار متعدد</option>
                    </select>
                </div>
                <div class="nova-col nova-col-md-6 d-flex align-items-end">
                    <div class="nova-form-check">
                        <input class="nova-form-check-input" type="checkbox"
                               name="questions[${id}][is_required]" value="1" checked>
                        <label class="nova-form-check-label small">سؤال إجباري</label>
                    </div>
                </div>
            </div>
            <div class="options-area" id="opts-${id}">
                ${renderOptionsArea(id, 'choice')}
            </div>
        </div>
    `;
    container.appendChild(div);

    div.querySelector('.q-type').addEventListener('change', function() {
        const optId = this.getAttribute('data-id');
        document.getElementById(`opts-${optId}`).innerHTML = renderOptionsArea(optId, this.value);
        addOptionListeners(optId);
    });

    div.querySelector('.remove-q').addEventListener('click', function() {
        div.remove();
        renumberQuestions();
        if (container.children.length === 0) emptyMsg.style.display = 'block';
    });

    addOptionListeners(id);
}

function renderOptionsArea(id, type) {
    if (type === 'text') {
        return `<div class="nova-alert info">
                    <i class="fas fa-font"></i> سيُعرض للعميل حقل نص حر.
                </div>`;
    }
    if (type === 'rating') {
        return `<div class="nova-alert info">
                    <i class="fas fa-star"></i> سيُعرض للعميل تقييم من 1 إلى 5 نجوم.
                </div>`;
    }
    if (type === 'yesno') {
        return `<div class="nova-alert info">
                    <i class="fas fa-check-circle"></i> سيُعرض للعميل خيارا "نعم" و"لا".
                </div>
                <input type="hidden" name="questions[${id}][options][]" value="نعم">
                <input type="hidden" name="questions[${id}][options][]" value="لا">`;
    }
    // choice & multiselect
    return `
        <label class="nova-label small fw-semibold">
            <i class="fas fa-list me-1"></i> الخيارات
        </label>
        <div id="options-list-${id}">
            <div class="option-item d-flex align-items-center gap-2 mb-2">
                <span class="nova-badge secondary">1</span>
                <input type="text" name="questions[${id}][options][]" class="nova-input nova-input-sm" placeholder="خيار 1" required>
                <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger remove-opt">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="option-item d-flex align-items-center gap-2 mb-2">
                <span class="nova-badge secondary">2</span>
                <input type="text" name="questions[${id}][options][]" class="nova-input nova-input-sm" placeholder="خيار 2" required>
                <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger remove-opt">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <button type="button" class="nova-btn nova-btn-outline nova-btn-sm mt-2 add-opt" data-qid="${id}">
            <i class="fas fa-plus"></i> إضافة خيار
        </button>
    `;
}

function addOptionListeners(qid) {
    const area = document.getElementById(`opts-${qid}`);
    if (!area) return;

    const addOptBtn = area.querySelector('.add-opt');
    if (addOptBtn) {
        addOptBtn.addEventListener('click', function() {
            const list = document.getElementById(`options-list-${qid}`);
            const count = list.querySelectorAll('.option-item').length + 1;
            const item = document.createElement('div');
            item.className = 'option-item d-flex align-items-center gap-2 mb-2';
            item.innerHTML = `
                <span class="nova-badge secondary">${count}</span>
                <input type="text" name="questions[${qid}][options][]" class="nova-input nova-input-sm" placeholder="خيار ${count}">
                <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger remove-opt">
                    <i class="fas fa-times"></i>
                </button>`;
            list.appendChild(item);
            item.querySelector('.remove-opt').addEventListener('click', function() {
                item.remove();
                renumberOptions(qid);
            });
        });
    }

    area.querySelectorAll('.remove-opt').forEach(btn => {
        btn.addEventListener('click', function() {
            const optItem = btn.closest('.option-item');
            const list = document.getElementById(`options-list-${qid}`);
            if (list && list.querySelectorAll('.option-item').length > 1) {
                optItem.remove();
                renumberOptions(qid);
            }
        });
    });
}

function renumberOptions(qid) {
    const list = document.getElementById(`options-list-${qid}`);
    if (!list) return;
    list.querySelectorAll('.option-item').forEach((item, i) => {
        const badge = item.querySelector('.nova-badge');
        if (badge) badge.textContent = i + 1;
        const input = item.querySelector('input');
        if (input) input.placeholder = `خيار ${i + 1}`;
    });
}

function renumberQuestions() {
    container.querySelectorAll('.nova-form-card').forEach((card, i) => {
        const num = card.querySelector('.q-num');
        if (num) num.textContent = i + 1;
    });
    questionCount.value = container.children.length;
}

// إضافة سؤال أول تلقائياً
emptyMsg.style.display = 'none';
addQuestion();
</script>
@endpush

<style>
.question-number {
    width: 28px; height: 28px;
    background: linear-gradient(135deg, var(--nova-primary), var(--nova-secondary));
    border-radius: 50%; color: white; font-size: 0.75rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
}
.option-item { display: flex; align-items-center; gap: 8px; }
</style>
