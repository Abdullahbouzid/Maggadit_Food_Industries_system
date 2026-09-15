@extends('app_android.partials.mobile.app_mobile')

@section('{{ __('messages.app.page_') }}', 'استبيانات')

@section('content')
    @include('app_android.partials.mobile.header', ['{{ __('messages.app.page_') }}' => 'استبيانات'])

    <div class="p-4" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
        <!-- زر الرجوع -->
        <button onclick="window.history.back()" class="flex items-center gap-2 text-purple-600 bg-purple-50 px-4 py-2 rounded-full text-sm font-semibold transition hover:bg-purple-100 mb-5">
            <i class="fas fa-arrow-right"></i> رجوع
        </button>

        <!-- بانر الاستبيانات -->
        <div class="mb-5 rounded-2xl p-4 text-white" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-clipboard-list text-2xl"></i>
                </div>
                <div>
                    <h2 class="font-bold text-lg leading-tight">استبيانات ماجديت</h2>
                    <p class="text-white/80 text-xs">شاركنا رأيك وساعدنا على التطوير</p>
                </div>
            </div>
        </div>

        <!-- قائمة الاستبيانات -->
        <div id="surveys-list">
            <!-- Loading -->
            <div id="surveys-loading" class="text-center py-10">
                <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-purple-600 mx-auto mb-3"></div>
                <p class="text-gray-400 text-sm">جاري تحميل الاستبيانات...</p>
            </div>
        </div>

        <!-- نص تشجيعي -->
        <div class="mt-6 text-center">
            <p class="text-xs text-gray-400">آراؤكم تساعدنا على تحسين خدماتنا. شكراً لمشاركتكم!</p>
        </div>
        <div id="logout-btn" class="mt-4 rounded-xl p-4 flex items-center justify-center gap-3">
            <br><br>
        </div>
    </div>

    <!-- ===== مودال الاستبيان ===== -->
    <div id="survey-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-end justify-center z-50 transition-all duration-300 opacity-0 pointer-events-none">
        <div id="survey-sheet" class="bg-white w-full max-w-lg rounded-t-3xl shadow-2xl transform translate-y-full transition-all duration-400 flex flex-col" 
             style="max-height:90vh;" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
            
            <!-- شريط العنوان -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <div>
                    <div class="w-10 h-1 bg-gray-200 rounded-full mx-auto mb-3 -mt-1"></div>
                    <h3 id="modal-{{ __('messages.app.page_') }}" class="font-bold text-gray-800 text-base">استبيان</h3>
                    <p id="modal-desc" class="text-xs text-gray-400 mt-0.5"></p>
                </div>
                <button id="close-modal" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition">
                    <i class="fas fa-times text-gray-500 text-sm"></i>
                </button>
            </div>

            <!-- شريط التقدم -->
            <div class="px-5 pt-3">
                <div class="flex justify-between text-xs text-gray-400 mb-1">
                    <span>التقدم</span>
                    <span id="progress-text">0 / 0</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div id="progress-bar" class="h-2 rounded-full transition-all duration-300" 
                         style="background:linear-gradient(135deg,#6366f1,#8b5cf6); width:0%;"></div>
                </div>
            </div>

            <!-- محتوى الأسئلة -->
            <div id="questions-wrapper" class="flex-1 overflow-y-auto px-5 py-4 space-y-5">
                <!-- الأسئلة تُحمَّل هنا -->
            </div>

            <!-- أزرار الإجراء -->
            <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
                <button id="cancel-survey" class="flex-1 bg-gray-100 text-gray-600 py-3 rounded-xl font-semibold text-sm">إلغاء</button>
                <button id="submit-survey" class="flex-1 text-white py-3 rounded-xl font-semibold text-sm transition" 
                        style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                    <span id="submit-text">إرسال الإجابات</span>
                    <span id="submit-loader" class="hidden">
                        <i class="fas fa-spinner fa-spin end-1"></i> جاري الإرسال...
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div id="survey-toast" class="fixed bottom-24 left-1/2 transform -translate-x-1/2 text-white px-5 py-3 rounded-full shadow-lg text-sm z-[60] transition-all duration-300 opacity-0 translate-y-4 pointer-events-none whitespace-nowrap"></div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script>
    // ========== الإعدادات ==========
    const API_BASE = '/customer/proxy';
    let currentSurveyData = null;
    let answers = {};

    function showToast(msg, isError = false) {
        const t = document.getElementById('survey-toast');
        t.textContent = msg;
        t.style.background = isError ? '#dc2626' : '#1f2937';
        t.classList.remove('opacity-0', 'translate-y-4');
        t.classList.add('opacity-100', 'translate-y-0');
        setTimeout(() => {
            t.classList.remove('opacity-100', 'translate-y-0');
            t.classList.add('opacity-0', 'translate-y-4');
        }, 3500);
    }

    // ========== تحميل الاستبيانات ==========
    async function loadSurveys() {
        const container = document.getElementById('surveys-list');
        const loading = document.getElementById('surveys-loading');

        try {
            const res = await fetch(`${API_BASE}/surveys`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });

            if (res.status === 401) {
                loading.remove();
                container.innerHTML = `
                    <div class="text-center py-10">
                        <i class="fas fa-lock text-gray-300 text-4xl mb-3"></i>
                        <p class="text-gray-500 mb-3">الرجاء تسجيل الدخول لعرض الاستبيانات</p>
                        <a href="/mobile/login" class="bg-purple-600 text-white px-6 py-2 rounded-full text-sm">تسجيل الدخول</a>
                    </div>`;
                return;
            }

            const data = await res.json();
            loading.remove();

            if (!data.success || !data.surveys || data.surveys.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-10">
                        <i class="fas fa-clipboard text-gray-200 text-5xl mb-3"></i>
                        <p class="text-gray-400 font-semibold">لا توجد استبيانات متاحة حالياً</p>
                        <p class="text-gray-300 text-xs mt-1">تحقق لاحقاً من وجود استبيانات جديدة</p>
                    </div>`;
                return;
            }

            renderSurveys(data.surveys);

        } catch(e) {
            loading.remove();
            container.innerHTML = `
                <div class="text-center py-10">
                    <i class="fas fa-wifi text-red-300 text-4xl mb-3"></i>
                    <p class="text-gray-500 mb-3">تعذّر الاتصال بالخادم</p>
                    <button onclick="loadSurveys()" class="bg-purple-600 text-white px-5 py-2 rounded-full text-sm">
                        <i class="fas fa-sync-alt end-1"></i> إعادة المحاولة
                    </button>
                </div>`;
        }
    }

    // ========== عرض الاستبيانات ==========
    function renderSurveys(surveys) {
        const container = document.getElementById('surveys-list');
        let html = '<div class="space-y-3">';

        surveys.forEach(s => {
            const answered = s.is_answered;
            html += `
            <div class="bg-white rounded-2xl shadow-sm border ${answered ? 'border-green-100' : 'border-gray-100'} overflow-hidden transition hover:shadow-md">
                ${answered ? '<div class="bg-green-50 px-4 py-1.5 flex items-center gap-2"><i class="fas fa-check-circle text-green-500 text-xs"></i><span class="text-green-600 text-xs font-semibold">تم الإجابة على هذا الاستبيان</span></div>' : ''}
                <div class="p-4">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-800 text-sm leading-tight">${escapeHtml(s.{{ __('messages.app.page_') }})}</h3>
                            <p class="text-gray-400 text-xs mt-1 leading-relaxed">{{ __('messages.app.page__2') }}</p>
                        </div>
                        <span class="bg-purple-50 text-purple-600 text-xs font-bold px-2 py-1 rounded-full whitespace-nowrap shrink-0">
                            {{ __('messages.app.page__4') }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-50">
                        <div class="flex items-center gap-1 text-gray-400 text-xs">
                            <i class="far fa-clock"></i>
                            <span>{{ __('messages.app.page__5') }}</span>
                        </div>
                        ${answered
                            ? `<span class="text-green-600 text-xs font-semibold flex items-center gap-1"><i class="fas fa-check"></i> مكتمل</span>`
                            : `<button class="open-survey bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold px-4 py-2 rounded-full transition active:scale-95"
                                       data-id="${s.id}" data-{{ __('messages.app.page_') }}="${escapeHtml(s.{{ __('messages.app.page_') }})}" data-desc="{{ __('messages.app.page__2') }}">
                                  ابدأ الاستبيان
                               </button>`
                        }
                    </div>
                </div>
            </div>`;
        });

        html += '</div>';
        container.innerHTML = html;

        // ربط أزرار البدء
        document.querySelectorAll('.open-survey').forEach(btn => {
            btn.addEventListener('click', () => {
                openSurvey(
                    btn.getAttribute('data-id'),
                    btn.getAttribute('data-{{ __('messages.app.page_') }}'),
                    btn.getAttribute('data-desc')
                );
            });
        });
    }

    // ========== فتح مودال الاستبيان ==========
    async function openSurvey(id, {{ __('messages.app.page_') }}, desc) {

        // عرض المودال مع loading
        document.getElementById('modal-{{ __('messages.app.page_') }}').textContent = {{ __('messages.app.page_') }};
        document.getElementById('modal-desc').textContent = desc;
        document.getElementById('questions-wrapper').innerHTML = `
            <div class="text-center py-10">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600 mx-auto mb-3"></div>
                <p class="text-gray-400 text-sm">جاري تحميل الأسئلة...</p>
            </div>`;
        showModal();

        try {
            const res = await fetch(`${API_BASE}/surveys/${id}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            const data = await res.json();

            if (!data.success) {
                closeModal();
                showToast(data.message || 'خطأ في تحميل الاستبيان', true);
                return;
            }

            currentSurveyData = { id, questions: data.questions };
            answers = {};
            renderQuestions(data.questions);

        } catch(e) {
            closeModal();
            showToast('تعذّر الاتصال بالخادم', true);
        }
    }

    // ========== عرض الأسئلة ==========
    function renderQuestions(questions) {
        const wrapper = document.getElementById('questions-wrapper');
        updateProgress(0, questions.length);
        let html = '';

        questions.forEach((q, idx) => {
            html += `<div class="question-block" data-qid="${q.id}" data-required="${q.is_required}">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-6 h-6 rounded-full text-white text-xs font-bold flex items-center justify-center shrink-0" 
                          style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">{{ __('messages.app.page__6') }}</span>
                    <p class="text-gray-700 font-semibold text-sm leading-snug">{{ __('messages.app.page__3') }}</p>
                    ${q.is_required ? '<span class="text-red-400 text-xs">*</span>' : ''}
                </div>
                <div class="question-input">
                    ${buildQuestionInput(q)}
                </div>
            </div>`;
        });

        wrapper.innerHTML = html;

        // ربط أحداث الاختيار لتحديث التقدم
        wrapper.querySelectorAll('input, textarea, select').forEach(el => {
            el.addEventListener('change', updateProgressFromAnswers);
            el.addEventListener('input', updateProgressFromAnswers);
        });
    }

    function buildQuestionInput(q) {
        switch(q.question_type) {
            case 'text':
                return `<textarea name="answer_${q.id}" data-qid="${q.id}" rows="3" 
                            class="answer-input w-full p-3 border border-gray-200 rounded-xl text-sm text-end focus:border-purple-400 focus:ring-2 focus:ring-purple-100 outline-none transition resize-none"
                            placeholder="اكتب إجابتك هنا..."></textarea>`;
            
            case 'rating':
                return `<div class="flex gap-2 justify-center py-2 rating-group" data-qid="${q.id}">
                    ${[1,2,3,4,5].map(n => `
                        <label class="cursor-pointer">
                            <input type="radio" name="rating_${q.id}" value="${n}" class="answer-radio hidden" data-qid="${q.id}">
                            <span class="star-btn w-12 h-12 rounded-xl border-2 border-gray-200 flex items-center justify-center text-xl transition hover:border-purple-400 hover:bg-purple-50" 
                                  data-val="${n}">
                                {{ __('messages.app.page__7') }}
                            </span>
                        </label>`).join('')}
                </div>
                <p class="text-center text-xs text-gray-400" id="rating-label-${q.id}">اختر تقييمك</p>`;
            
            case 'yesno':
                const opts = q.options || ['نعم', 'لا'];
                return `<div class="flex gap-3">
                    ${opts.map(opt => `
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="yesno_${q.id}" value="{{ __('messages.app.page__8') }}" class="answer-radio hidden" data-qid="${q.id}">
                            <span class="choice-btn block text-center py-3 rounded-xl border-2 border-gray-200 text-sm font-semibold text-gray-600 transition hover:border-purple-400 hover:bg-purple-50">
                                {{ __('messages.app.page__8') }}
                            </span>
                        </label>`).join('')}
                </div>`;
            
            case 'multiselect':
                return `<div class="space-y-2">
                    ${(q.options || []).map(opt => `
                        <label class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl cursor-pointer hover:bg-purple-50 transition">
                            <input type="checkbox" name="multi_${q.id}[]" value="{{ __('messages.app.page__8') }}" 
                                   class="w-4 h-4 text-purple-600 answer-checkbox" data-qid="${q.id}" style="accent-color:#6366f1;">
                            <span class="text-sm text-gray-700">{{ __('messages.app.page__8') }}</span>
                        </label>`).join('')}
                </div>`;

            case 'choice':
            default:
                return `<div class="space-y-2">
                    ${(q.options || []).map(opt => `
                        <label class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl cursor-pointer hover:bg-purple-50 transition choice-label" data-qid="${q.id}">
                            <input type="radio" name="choice_${q.id}" value="{{ __('messages.app.page__8') }}" 
                                   class="answer-radio hidden" data-qid="${q.id}">
                            <span class="w-4 h-4 rounded-full border-2 border-gray-300 flex items-center justify-center shrink-0 radio-dot"></span>
                            <span class="text-sm text-gray-700 flex-1">{{ __('messages.app.page__8') }}</span>
                        </label>`).join('')}
                </div>`;
        }
    }

    // ========== تحديث التقدم ==========
    function updateProgress(answered, total) {
        document.getElementById('progress-text').textContent = `${answered} / ${total}`;
        const pct = total > 0 ? (answered / total * 100) : 0;
        document.getElementById('progress-bar').style.width = `${pct}%`;
    }

    function updateProgressFromAnswers() {
        if (!currentSurveyData) return;
        collectAnswers();
        const answered = Object.values(answers).filter(v => v !== null && v !== '' && v !== undefined).length;
        updateProgress(answered, currentSurveyData.questions.length);
    }

    // ========== جمع الإجابات ==========
    function collectAnswers() {
        answers = {};
        if (!currentSurveyData) return;

        currentSurveyData.questions.forEach(q => {
            switch(q.question_type) {
                case 'text': {
                    const el = document.querySelector(`textarea[data-qid="${q.id}"]`);
                    answers[q.id] = el ? el.value.trim() : null;
                    break;
                }
                case 'rating':
                case 'yesno':
                case 'choice': {
                    const el = document.querySelector(`input.answer-radio[data-qid="${q.id}"]:checked`);
                    answers[q.id] = el ? el.value : null;
                    break;
                }
                case 'multiselect': {
                    const els = document.querySelectorAll(`input.answer-checkbox[data-qid="${q.id}"]:checked`);
                    const vals = Array.from(els).map(e => e.value);
                    answers[q.id] = vals.length > 0 ? vals.join(', ') : null;
                    break;
                }
            }
        });
    }

    // ========== إرسال الاستبيان ==========
    async function submitSurvey() {
        collectAnswers();
        if (!currentSurveyData) return;

        // التحقق من الإجابات الإجبارية
        const requiredBlocks = document.querySelectorAll('.question-block[data-required="1"]');
        for (const block of requiredBlocks) {
            const qid = block.getAttribute('data-qid');
            if (!answers[qid]) {
                block.scrollIntoView({ behavior: 'smooth', block: 'center' });
                block.classList.add('ring-2', 'ring-red-400', 'rounded-2xl', 'p-3', '-mx-3');
                setTimeout(() => block.classList.remove('ring-2', 'ring-red-400', 'rounded-2xl', 'p-3', '-mx-3'), 2000);
                showToast('❗ الرجاء الإجابة على جميع الأسئلة الإجبارية', true);
                return;
            }
        }

        // تغيير حالة الزر
        document.getElementById('submit-text').classList.add('hidden');
        document.getElementById('submit-loader').classList.remove('hidden');
        document.getElementById('submit-survey').disabled = true;

        try {
            const res = await fetch(`${API_BASE}/surveys/${currentSurveyData.id}/respond`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ answers })
            });

            const data = await res.json();

            if (data.success) {
                closeModal();
                showToast('✅ ' + data.message);
                setTimeout(loadSurveys, 1500);
            } else {
                showToast(data.message || 'حدث خطأ', true);
            }
        } catch(e) {
            showToast('تعذّر الاتصال بالخادم', true);
        } finally {
            document.getElementById('submit-text').classList.remove('hidden');
            document.getElementById('submit-loader').classList.add('hidden');
            document.getElementById('submit-survey').disabled = false;
        }
    }

    // ========== المودال ==========
    function showModal() {
        const modal = document.getElementById('survey-modal');
        const sheet = document.getElementById('survey-sheet');
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100');
        setTimeout(() => sheet.classList.remove('translate-y-full'), 20);
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        const modal = document.getElementById('survey-modal');
        const sheet = document.getElementById('survey-sheet');
        sheet.classList.add('translate-y-full');
        setTimeout(() => {
            modal.classList.add('opacity-0', 'pointer-events-none');
            modal.classList.remove('opacity-100');
        }, 350);
        document.body.style.overflow = '';
        currentSurveyData = null;
        answers = {};
    }

    // ========== الأحداث ==========
    document.getElementById('close-modal').addEventListener('click', closeModal);
    document.getElementById('cancel-survey').addEventListener('click', closeModal);
    document.getElementById('submit-survey').addEventListener('click', submitSurvey);
    document.getElementById('survey-modal').addEventListener('click', (e) => {
        if (e.target === document.getElementById('survey-modal')) closeModal();
    });

    // الاختيار الجمالي (choice & yesno)
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('answer-radio')) {
            const qid = e.target.getAttribute('data-qid');
            // تحديث بصري للـ choice labels
            document.querySelectorAll(`.choice-label[data-qid="${qid}"]`).forEach(lbl => {
                lbl.classList.remove('border-purple-400', 'bg-purple-50');
                const dot = lbl.querySelector('.radio-dot');
                if (dot) { dot.classList.remove('border-purple-600', 'bg-purple-600'); dot.classList.add('border-gray-300'); }
            });
            const checkedLabel = e.target.closest('.choice-label');
            if (checkedLabel) {
                checkedLabel.classList.add('border-purple-400', 'bg-purple-50');
                const dot = checkedLabel.querySelector('.radio-dot');
                if (dot) { dot.classList.remove('border-gray-300'); dot.classList.add('border-purple-600', 'bg-purple-600'); }
            }
            // تحديث بصري للـ yesno
            const yesnoWrapper = e.target.closest('.flex');
            if (yesnoWrapper) {
                yesnoWrapper.querySelectorAll('.choice-btn').forEach(btn => btn.classList.remove('border-purple-500', 'bg-purple-50', 'text-purple-700'));
                const selectedBtn = e.target.nextElementSibling;
                if (selectedBtn && selectedBtn.classList.contains('choice-btn')) {
                    selectedBtn.classList.add('border-purple-500', 'bg-purple-50', 'text-purple-700');
                }
            }
            // تحديث بصري للتقييم
            const ratingGroup = e.target.closest('.rating-group');
            if (ratingGroup) {
                const val = parseInt(e.target.value);
                const qidR = e.target.getAttribute('data-qid');
                ratingGroup.querySelectorAll('.star-btn').forEach(btn => {
                    const bval = parseInt(btn.getAttribute('data-val'));
                    btn.classList.toggle('border-purple-500', bval <= val);
                    btn.classList.toggle('bg-purple-50', bval <= val);
                    btn.classList.toggle('border-gray-200', bval > val);
                    btn.classList.toggle('bg-white', bval > val);
                });
                const labels = ['', 'سيء', 'ضعيف', 'متوسط', 'جيد', 'ممتاز'];
                const lbl = document.getElementById(`rating-label-${qidR}`);
                if (lbl) lbl.textContent = labels[val] || '';
            }
            updateProgressFromAnswers();
        }
    });

    // ========== Helper ==========
    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    }

    // ========== تحميل عند بدء الصفحة ==========
    document.addEventListener('DOMContentLoaded', loadSurveys);
    </script>
@endsection