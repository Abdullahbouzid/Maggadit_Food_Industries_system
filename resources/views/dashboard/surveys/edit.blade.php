@extends('dashboard.partials.layout')

@section('title', 'تعديل الاستبيان')

@section('content')
<div class="container-fluid p-0">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1">تعديل: {{ $survey->title }}</h1>
                <p class="text-muted small mb-0">{{ $survey->questions->count() }} سؤال  •  {{ $survey->responses()->count() }} رد مستلم</p>
            </div>
            <a href="{{ route('surveys.index') }}" class="btn btn-outline-secondary">
                <i data-feather="arrow-right" style="width:16px"></i> العودة
            </a>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible">
        <button class="btn-close" data-bs-dismiss="alert"></button>
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    @if($survey->responses()->count() > 0)
    <div class="alert alert-warning d-flex align-items-center gap-2 mb-4">
        <i data-feather="alert-triangle" style="width:18px"></i>
        <span>تنبيه: يوجد <strong>{{ $survey->responses()->count() }}</strong> رد مسجل على هذا الاستبيان. تعديل الأسئلة قد يؤثر على البيانات الموجودة.</span>
    </div>
    @endif

    <form action="{{ route('surveys.update', $survey) }}" method="POST" id="survey-form">
        @csrf
        @method('PUT')

        <div class="row g-4">
            {{-- معلومات الاستبيان --}}
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
                    <div class="card-header py-3 px-4 border-0" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);border-radius:16px 16px 0 0;">
                        <h5 class="mb-0 text-white fw-bold">
                            <i data-feather="settings" style="width:18px"></i> إعدادات الاستبيان
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">عنوان الاستبيان <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" value="{{ old('title', $survey->title) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">الوصف</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $survey->description) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">الوقت التقديري (دقائق)</label>
                            <input type="number" name="estimated_minutes" class="form-control" 
                                   value="{{ old('estimated_minutes', $survey->estimated_minutes) }}" min="1" max="60">
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label fw-semibold small">تاريخ البداية</label>
                                <input type="datetime-local" name="starts_at" class="form-control form-control-sm" 
                                       value="{{ old('starts_at', $survey->starts_at?->format('Y-m-d\TH:i')) }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold small">تاريخ الانتهاء</label>
                                <input type="datetime-local" name="ends_at" class="form-control form-control-sm" 
                                       value="{{ old('ends_at', $survey->ends_at?->format('Y-m-d\TH:i')) }}">
                            </div>
                        </div>
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" name="is_active" id="isActive" 
                                   value="1" {{ $survey->is_active ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="isActive">نشط</label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- منشئ الأسئلة --}}
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm" style="border-radius:16px;">
                    <div class="card-header py-3 px-4 border-0 d-flex justify-content-between align-items-center" 
                         style="background:#f8fafc; border-radius:16px 16px 0 0;">
                        <h5 class="mb-0 fw-bold">الأسئلة</h5>
                        <button type="button" id="add-question" class="btn btn-primary btn-sm">
                            <i data-feather="plus" style="width:14px"></i> إضافة سؤال
                        </button>
                    </div>
                    <div class="card-body p-4">
                        <div id="questions-container"></div>
                        <div id="empty-questions" class="text-center py-4 text-muted" style="display:none;">
                            <p>لا توجد أسئلة. أضف سؤالاً.</p>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-3 mt-3 justify-content-end">
                    <a href="{{ route('surveys.index') }}" class="btn btn-outline-secondary px-4">إلغاء</a>
                    <button type="submit" class="btn btn-primary px-5 fw-semibold">
                        <i data-feather="save" style="width:16px"></i> حفظ التعديلات
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
.question-card {
    background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;
    padding: 16px; margin-bottom: 12px; transition: all 0.2s;
}
.question-card:hover { border-color: #6366f1; }
.question-number {
    width: 28px; height: 28px;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    border-radius: 50%; color: white; font-size: 0.75rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
}
.option-item { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; }
.option-item input { flex: 1; }
</style>

<script>
// الأسئلة الحالية من السيرفر
const existingQuestions = @json($survey->questions);
let questionCount = 0;

const addBtn = document.getElementById('add-question');
const container = document.getElementById('questions-container');
const emptyMsg = document.getElementById('empty-questions');

addBtn.addEventListener('click', () => addQuestion());

function addQuestion(data = null) {
    questionCount++;
    emptyMsg.style.display = 'none';
    const type = data ? data.question_type : 'choice';

    const div = document.createElement('div');
    div.className = 'question-card';
    div.id = `q-block-${questionCount}`;
    div.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                <span class="question-number q-num">${questionCount}</span>
                <span class="fw-semibold text-muted small">سؤال</span>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger remove-q">
                <i data-feather="trash-2" style="width:13px"></i>
            </button>
        </div>
        <div class="mb-3">
            <label class="form-label small fw-semibold">نص السؤال <span class="text-danger">*</span></label>
            <input type="text" name="questions[${questionCount}][question_text]" 
                   class="form-control" value="${data ? escapeHtml(data.question_text) : ''}" 
                   placeholder="اكتب نص السؤال هنا..." required>
        </div>
        <div class="row g-2 mb-3">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">نوع السؤال</label>
                <select name="questions[${questionCount}][question_type]" class="form-select q-type" data-id="${questionCount}">
                    <option value="choice" ${type==='choice'?'selected':''}>اختيار من متعدد</option>
                    <option value="text" ${type==='text'?'selected':''}>إجابة نصية</option>
                    <option value="rating" ${type==='rating'?'selected':''}>تقييم (نجوم)</option>
                    <option value="yesno" ${type==='yesno'?'selected':''}>نعم / لا</option>
                    <option value="multiselect" ${type==='multiselect'?'selected':''}>اختيار متعدد</option>
                </select>
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" 
                           name="questions[${questionCount}][is_required]" value="1" 
                           ${(!data || data.is_required) ? 'checked' : ''}>
                    <label class="form-check-label small">إجباري</label>
                </div>
            </div>
        </div>
        <div class="options-area" id="opts-${questionCount}">
            ${renderOptionsArea(questionCount, type, data ? data.options : null)}
        </div>
    `;
    container.appendChild(div);

    if (typeof feather !== 'undefined') feather.replace();

    div.querySelector('.q-type').addEventListener('change', function() {
        const id = this.getAttribute('data-id');
        document.getElementById(`opts-${id}`).innerHTML = renderOptionsArea(id, this.value, null);
        addOptionListeners(id);
    });

    div.querySelector('.remove-q').addEventListener('click', function() {
        div.remove();
        renumberQuestions();
        if (container.children.length === 0) emptyMsg.style.display = 'block';
    });

    addOptionListeners(questionCount);
}

function renderOptionsArea(id, type, existingOptions) {
    if (type === 'text') return `<div class="alert alert-light border small text-muted py-2">سيُعرض حقل نص حر.</div>`;
    if (type === 'rating') return `<div class="alert alert-light border small text-muted py-2">تقييم من 1 إلى 5 نجوم.</div>`;
    if (type === 'yesno') return `<div class="alert alert-light border small text-muted py-2">نعم / لا</div>
        <input type="hidden" name="questions[${id}][options][]" value="نعم">
        <input type="hidden" name="questions[${id}][options][]" value="لا">`;
    
    const opts = existingOptions && existingOptions.length > 0 ? existingOptions : ['', ''];
    let html = `<label class="form-label small fw-semibold">الخيارات</label><div id="options-list-${id}">`;
    opts.forEach((opt, i) => {
        html += `<div class="option-item">
            <span class="badge bg-light text-muted border" style="width:24px;height:24px;display:flex;align-items:center;justify-content:center;font-size:10px;border-radius:50%;">${i+1}</span>
            <input type="text" name="questions[${id}][options][]" class="form-control form-control-sm" value="${escapeHtml(opt)}" placeholder="خيار ${i+1}">
            <button type="button" class="btn btn-sm btn-outline-danger remove-opt" style="padding:2px 8px;">×</button>
        </div>`;
    });
    html += `</div><button type="button" class="btn btn-sm btn-outline-primary mt-2 add-opt" data-qid="${id}">+ إضافة خيار</button>`;
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
            item.className = 'option-item';
            item.innerHTML = `
                <span class="badge bg-light text-muted border" style="width:24px;height:24px;display:flex;align-items:center;justify-content:center;font-size:10px;border-radius:50%;">${count}</span>
                <input type="text" name="questions[${qid}][options][]" class="form-control form-control-sm" placeholder="خيار ${count}">
                <button type="button" class="btn btn-sm btn-outline-danger remove-opt" style="padding:2px 8px;">×</button>`;
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
        const badge = item.querySelector('.badge');
        if (badge) badge.textContent = i + 1;
    });
}

function renumberQuestions() {
    container.querySelectorAll('.question-card').forEach((card, i) => {
        const num = card.querySelector('.q-num');
        if (num) num.textContent = i + 1;
    });
    questionCount = container.children.length;
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// تحميل الأسئلة الموجودة
if (existingQuestions.length > 0) {
    existingQuestions.forEach(q => addQuestion(q));
} else {
    addQuestion();
}
</script>
@endsection
