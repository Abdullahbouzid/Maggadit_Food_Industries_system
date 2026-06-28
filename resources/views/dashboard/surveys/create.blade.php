@extends('dashboard.partials.layout')

@section('title', 'إنشاء استبيان جديد')

@section('content')
<div class="container-fluid p-0">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1">إنشاء استبيان جديد</h1>
                <p class="text-muted small mb-0">صمّم استبيانك وأضف أسئلتك بحرية</p>
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

    <form action="{{ route('surveys.store') }}" method="POST" id="survey-form">
        @csrf

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
                            <input type="text" name="title" class="form-control" value="{{ old('title') }}" 
                                   placeholder="مثال: استبيان رضا العملاء" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">الوصف</label>
                            <textarea name="description" class="form-control" rows="3" 
                                      placeholder="وصف مختصر للاستبيان...">{{ old('description') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">الوقت التقديري (دقائق)</label>
                            <input type="number" name="estimated_minutes" class="form-control" 
                                   value="{{ old('estimated_minutes', 5) }}" min="1" max="60">
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label fw-semibold small">تاريخ البداية</label>
                                <input type="datetime-local" name="starts_at" class="form-control form-control-sm" 
                                       value="{{ old('starts_at') }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold small">تاريخ الانتهاء</label>
                                <input type="datetime-local" name="ends_at" class="form-control form-control-sm" 
                                       value="{{ old('ends_at') }}">
                            </div>
                        </div>
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" name="is_active" id="isActive" 
                                   value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="isActive">نشط فور الإنشاء</label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- منشئ الأسئلة --}}
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm" style="border-radius:16px;">
                    <div class="card-header py-3 px-4 border-0 d-flex justify-content-between align-items-center" 
                         style="background:#f8fafc; border-radius:16px 16px 0 0;">
                        <h5 class="mb-0 fw-bold text-gray-800">
                            <i data-feather="list" style="width:18px"></i> الأسئلة
                        </h5>
                        <button type="button" id="add-question" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
                            <i data-feather="plus" style="width:14px"></i> إضافة سؤال
                        </button>
                    </div>
                    <div class="card-body p-4">
                        <div id="questions-container" class="space-y-3">
                            {{-- الأسئلة ستُضاف هنا --}}
                        </div>
                        <div id="empty-questions" class="text-center py-5 text-muted">
                            <i data-feather="help-circle" style="width:48px;height:48px;color:#cbd5e1;" class="mb-2"></i>
                            <p>لم تضف أي أسئلة بعد.<br>اضغط على "إضافة سؤال" للبدء.</p>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-3 mt-3 justify-content-end">
                    <a href="{{ route('surveys.index') }}" class="btn btn-outline-secondary px-4">إلغاء</a>
                    <button type="submit" class="btn btn-primary px-5 fw-semibold">
                        <i data-feather="save" style="width:16px"></i> حفظ الاستبيان
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
.question-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 12px;
    transition: all 0.2s;
}
.question-card:hover { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.08); }
.question-number {
    width: 28px; height: 28px;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    border-radius: 50%;
    color: white;
    font-size: 0.75rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
}
.option-item { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; }
.option-item input { flex: 1; }
</style>

<script>
let questionCount = 0;

const addBtn = document.getElementById('add-question');
const container = document.getElementById('questions-container');
const emptyMsg = document.getElementById('empty-questions');

addBtn.addEventListener('click', addQuestion);

function addQuestion() {
    questionCount++;
    emptyMsg.style.display = 'none';

    const div = document.createElement('div');
    div.className = 'question-card';
    div.id = `q-block-${questionCount}`;
    div.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                <span class="question-number q-num">${questionCount}</span>
                <span class="fw-semibold text-muted small">سؤال</span>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger remove-q" data-id="${questionCount}">
                <i data-feather="trash-2" style="width:13px"></i>
            </button>
        </div>
        <div class="mb-3">
            <label class="form-label small fw-semibold">نص السؤال <span class="text-danger">*</span></label>
            <input type="text" name="questions[${questionCount}][question_text]" 
                   class="form-control" placeholder="اكتب نص السؤال هنا..." required>
        </div>
        <div class="row g-2 mb-3">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">نوع السؤال</label>
                <select name="questions[${questionCount}][question_type]" 
                        class="form-select q-type" data-id="${questionCount}">
                    <option value="choice">اختيار من متعدد</option>
                    <option value="text">إجابة نصية</option>
                    <option value="rating">تقييم (نجوم)</option>
                    <option value="yesno">نعم / لا</option>
                    <option value="multiselect">اختيار متعدد</option>
                </select>
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" 
                           name="questions[${questionCount}][is_required]" value="1" checked>
                    <label class="form-check-label small">سؤال إجباري</label>
                </div>
            </div>
        </div>
        <div class="options-area" id="opts-${questionCount}">
            ${renderOptionsArea(questionCount, 'choice')}
        </div>
    `;
    container.appendChild(div);

    // إعادة تحميل Feather Icons
    if (typeof feather !== 'undefined') feather.replace();

    // حدث تغيير النوع
    div.querySelector('.q-type').addEventListener('change', function() {
        const id = this.getAttribute('data-id');
        document.getElementById(`opts-${id}`).innerHTML = renderOptionsArea(id, this.value);
        addOptionListeners(id);
    });

    // حدث حذف السؤال
    div.querySelector('.remove-q').addEventListener('click', function() {
        div.remove();
        renumberQuestions();
        if (container.children.length === 0) {
            emptyMsg.style.display = 'block';
        }
    });

    addOptionListeners(questionCount);
}

function renderOptionsArea(id, type) {
    if (type === 'text') {
        return `<div class="alert alert-light border small text-muted py-2">
                    <i data-feather="type" style="width:13px"></i> سيُعرض للعميل حقل نص حر.
                </div>`;
    }
    if (type === 'rating') {
        return `<div class="alert alert-light border small text-muted py-2">
                    <i data-feather="star" style="width:13px"></i> سيُعرض للعميل تقييم من 1 إلى 5 نجوم.
                </div>`;
    }
    if (type === 'yesno') {
        return `<div class="alert alert-light border small text-muted py-2">
                    <i data-feather="check-circle" style="width:13px"></i> سيُعرض للعميل خيارا "نعم" و"لا".
                </div>
                <input type="hidden" name="questions[${id}][options][]" value="نعم">
                <input type="hidden" name="questions[${id}][options][]" value="لا">`;
    }
    // choice & multiselect
    return `
        <label class="form-label small fw-semibold">الخيارات</label>
        <div id="options-list-${id}">
            <div class="option-item">
                <span class="badge bg-light text-muted border" style="width:24px;height:24px;display:flex;align-items:center;justify-content:center;font-size:10px;border-radius:50%;">1</span>
                <input type="text" name="questions[${id}][options][]" class="form-control form-control-sm" placeholder="خيار 1" required>
                <button type="button" class="btn btn-sm btn-outline-danger remove-opt" style="padding:2px 8px;">×</button>
            </div>
            <div class="option-item">
                <span class="badge bg-light text-muted border" style="width:24px;height:24px;display:flex;align-items:center;justify-content:center;font-size:10px;border-radius:50%;">2</span>
                <input type="text" name="questions[${id}][options][]" class="form-control form-control-sm" placeholder="خيار 2" required>
                <button type="button" class="btn btn-sm btn-outline-danger remove-opt" style="padding:2px 8px;">×</button>
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary mt-2 add-opt" data-qid="${id}">
            + إضافة خيار
        </button>
    `;
}

function addOptionListeners(qid) {
    const area = document.getElementById(`opts-${qid}`);
    if (!area) return;

    // زر إضافة خيار
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
                <button type="button" class="btn btn-sm btn-outline-danger remove-opt" style="padding:2px 8px;">×</button>
            `;
            list.appendChild(item);
            item.querySelector('.remove-opt').addEventListener('click', function() {
                item.remove();
                renumberOptions(qid);
            });
        });
    }

    // أزرار حذف الخيارات الأولية
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
        const badge = item.querySelector('.badge');
        if (badge) badge.textContent = i + 1;
        const input = item.querySelector('input');
        if (input) input.placeholder = `خيار ${i + 1}`;
    });
}

function renumberQuestions() {
    container.querySelectorAll('.question-card').forEach((card, i) => {
        const num = card.querySelector('.q-num');
        if (num) num.textContent = i + 1;
    });
    questionCount = container.children.length;
}

// إضافة سؤال أول تلقائياً
addQuestion();
</script>
@endsection
