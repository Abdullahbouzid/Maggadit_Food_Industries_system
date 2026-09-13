@extends('dashboard.partials.layout')

@section('title', 'تعديل الاستبيان')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-edit" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            تعديل: {{ $survey->title }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">
            <i class="fas fa-question-circle"></i> {{ $survey->questions->count() }} سؤال • <i class="fas fa-comments"></i> {{ $survey->responses()->count() }} رد مستلم
        </p>
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

@if($survey->responses()->count() > 0)
    <div class="nova-alert warning d-flex align-items-center gap-2 mb-4">
        <i class="fas fa-exclamation-triangle"></i>
        <span>تنبيه: يوجد <strong>{{ $survey->responses()->count() }}</strong> رد مسجل على هذا الاستبيان. تعديل الأسئلة قد يؤثر على البيانات الموجودة.</span>
    </div>
@endif

<form action="{{ route('surveys.update', $survey) }}" method="POST" id="survey-form">
    @csrf
    @method('PUT')

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
                        <input type="text" name="title" class="nova-input" value="{{ old('title', $survey->title) }}" required>
                    </div>

                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-align-left me-1"></i> الوصف
                        </label>
                        <textarea name="description" class="nova-textarea" rows="3">{{ old('description', $survey->description) }}</textarea>
                    </div>

                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-clock me-1"></i> الوقت التقديري (دقائق)
                        </label>
                        <input type="number" name="estimated_minutes" class="nova-input"
                               value="{{ old('estimated_minutes', $survey->estimated_minutes) }}" min="1" max="60">
                    </div>

                    <div class="nova-row g-2">
                        <div class="nova-col nova-col-md-6">
                            <label class="nova-label small">
                                <i class="fas fa-calendar me-1"></i> تاريخ البداية
                            </label>
                            <input type="datetime-local" name="starts_at" class="nova-input nova-input-sm"
                                   value="{{ old('starts_at', $survey->starts_at?->format('Y-m-d\TH:i')) }}">
                        </div>
                        <div class="nova-col nova-col-md-6">
                            <label class="nova-label small">
                                <i class="fas fa-calendar-alt me-1"></i> تاريخ الانتهاء
                            </label>
                            <input type="datetime-local" name="ends_at" class="nova-input nova-input-sm"
                                   value="{{ old('ends_at', $survey->ends_at?->format('Y-m-d\TH:i')) }}">
                        </div>
                    </div>

                    <div class="nova-form-check mt-3">
                        <input class="nova-form-check-input" type="checkbox" name="is_active" id="isActive"
                               value="1" {{ $survey->is_active ? 'checked' : '' }}>
                        <label class="nova-form-check-label" for="isActive">نشط</label>
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
                    <div id="questions-container"></div>
                    <div id="empty-questions" class="nova-empty">
                        <div class="nova-empty-icon">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <h3 class="nova-empty-title">لا توجد أسئلة</h3>
                        <p class="nova-empty-text">أضف سؤالاً.</p>
                    </div>
                </div>
            </div>

            <div class="nova-form-actions">
                <a href="{{ route('surveys.index') }}" class="nova-btn nova-btn-outline">
                    <i class="fas fa-times"></i> إلغاء
                </a>
                <button type="submit" class="nova-btn nova-btn-primary fw-semibold">
                    <i class="fas fa-save"></i> حفظ التعديلات
                </button>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
const existingQuestions = @json($survey->questions);
const questionCount = { value: 0 };

const addBtn = document.getElementById('add-question');
const container = document.getElementById('questions-container');
const emptyMsg = document.getElementById('empty-questions');

addBtn.addEventListener('click', () => addQuestion());

function addQuestion(data = null) {
    questionCount.value++;
    const id = questionCount.value;
    emptyMsg.style.display = 'none';
    const type = data ? data.question_type : 'choice';

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
                       class="nova-input nova-input-sm" value="${data ? escapeHtml(data.question_text) : ''}"
                       placeholder="اكتب نص السؤال هنا..." required>
            </div>
            <div class="nova-row g-2 mb-3">
                <div class="nova-col nova-col-md-6">
                    <label class="nova-label small fw-semibold">
                        <i class="fas fa-tag me-1"></i> نوع السؤال
                    </label>
                    <select name="questions[${id}][question_type]" class="nova-select nova-input-sm q-type" data-id="${id}">
                        <option value="choice" ${type==='choice'?'selected':''}>اختيار من متعدد</option>
                        <option value="text" ${type==='text'?'selected':''}>إجابة نصية</option>
                        <option value="rating" ${type==='rating'?'selected':''}>تقييم (نجوم)</option>
                        <option value="yesno" ${type==='yesno'?'selected':''}>نعم / لا</option>
                        <option value="multiselect" ${type==='multiselect'?'selected':''}>اختيار متعدد</option>
                    </select>
                </div>
                <div class="nova-col nova-col-md-6 d-flex align-items-end">
                    <div class="nova-form-check">
                        <input class="nova-form-check-input" type="checkbox"
                               name="questions[${id}][is_required]" value="1"
                               ${(!data || data.is_required) ? 'checked' : ''}>
                        <label class="nova-form-check-label small">إجباري</label>
                    </div>
                </div>
            </div>
            <div class="options-area" id="opts-${id}">
                ${renderOptionsArea(id, type, data ? data.options : null)}
            </div>
        </div>
    `;
    container.appendChild(div);

    div.querySelector('.q-type').addEventListener('change', function() {
        const optId = this.getAttribute('data-id');
        document.getElementById(`opts-${optId}`).innerHTML = renderOptionsArea(optId, this.value, null);
        addOptionListeners(optId);
    });

    div.querySelector('.remove-q').addEventListener('click', function() {
        div.remove();
        renumberQuestions();
        if (container.children.length === 0) emptyMsg.style.display = 'block';
    });

    addOptionListeners(id);
}

function renderOptionsArea(id, type, existingOptions) {
    if (type === 'text') return `<div class="nova-alert info"><i class="fas fa-font"></i> سيُعرض حقل نص حر.</div>`;
    if (type === 'rating') return `<div class="nova-alert info"><i class="fas fa-star"></i> سيُعرض للعميل تقييم من 1 إلى 5 نجوم.</div>`;
    if (type === 'yesno') return `<div class="nova-alert info"><i class="fas fa-check-circle"></i> سيُعرض للعميل خيارا "نعم" و"لا".</div>
        <input type="hidden" name="questions[${id}][options][]" value="نعم">
        <input type="hidden" name="questions[${id}][options][]" value="لا">`;

    const opts = existingOptions && existingOptions.length > 0 ? existingOptions : ['', ''];
    let html = `<label class="nova-label small fw-semibold"><i class="fas fa-list me-1"></i> الخيارات</label><div id="options-list-${id}">`;
    opts.forEach((opt, i) => {
        html += `<div class="option-item d-flex align-items-center gap-2 mb-2">
            <span class="nova-badge secondary">${i+1}</span>
            <input type="text" name="questions[${id}][options][]" class="nova-input nova-input-sm" value="${escapeHtml(opt)}" placeholder="خيار ${i+1}">
            <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger remove-opt">
                <i class="fas fa-times"></i>
            </button>
        </div>`;
    });
    html += `</div><button type="button" class="nova-btn nova-btn-outline nova-btn-sm mt-2 add-opt" data-qid="${id}">
        <i class="fas fa-plus"></i> إضافة خيار
    </button>`;
    return html;
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
            item.querySelector('.remove-opt').addEventListener('click', () => { item.remove(); renumberOptions(qid); });
        });
    }
    area.querySelectorAll('.remove-opt').forEach(btn => {
        btn.addEventListener('click', function() {
            const item = btn.closest('.option-item');
            const list = document.getElementById(`options-list-${qid}`);
            if (list && list.querySelectorAll('.option-item').length > 1) { item.remove(); renumberOptions(qid); }
        });
    });
}

function renumberOptions(qid) {
    const list = document.getElementById(`options-list-${qid}`);
    if (!list) return;
    list.querySelectorAll('.option-item').forEach((item, i) => {
        const badge = item.querySelector('.nova-badge');
        if (badge) badge.textContent = i + 1;
    });
}

function renumberQuestions() {
    container.querySelectorAll('.nova-form-card').forEach((card, i) => {
        const num = card.querySelector('.q-num');
        if (num) num.textContent = i + 1;
    });
    questionCount.value = container.children.length;
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// تحميل الأسئلة الموجودة
if (existingQuestions.length > 0) {
    existingQuestions.forEach(q => addQuestion(q));
} else {
    emptyMsg.style.display = 'block';
}
</script>
@endpush

<style>
.question-number {
    width: 28px; height: 28px;
    background: linear-gradient(135deg, var(--nova-primary), var(--nova-secondary));
    border-radius: 50%; color: white; font-size: 0.75rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
}
.option-item { display: flex; align-items: center; gap: 8px; }
</style>
