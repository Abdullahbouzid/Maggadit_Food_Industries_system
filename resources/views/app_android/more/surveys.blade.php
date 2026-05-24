@extends('app_android.partials.mobile.app_mobile')

@section('title', 'استبيانات')

@section('content')
    @include('app_android.partials.mobile.header', ['title' => 'استبيانات'])

    <div class="p-4" dir="rtl">
        <!-- زر الرجوع -->
        <button onclick="window.history.back()" class="flex items-center gap-2 text-purple-600 bg-purple-50 px-4 py-2 rounded-full text-sm font-semibold transition hover:bg-purple-100 mb-5">
            <i class="fas fa-arrow-right"></i> رجوع
        </button>

        <!-- بطاقات الاستبيانات -->
        <div class="space-y-4">
            @php
                $surveys = [
                    ['id' => 1, 'title' => 'استبيان رضا العملاء', 'description' => 'شاركنا رأيك في جودة المنتجات وسرعة التوصيل', 'questions_count' => 5, 'duration' => '3 دقائق', 'active' => true],
                    ['id' => 2, 'title' => 'تقييم تجربة التسوق', 'description' => 'ساعدنا في تحسين تطبيقنا بتقييم تجربتك', 'questions_count' => 4, 'duration' => '2 دقائق', 'active' => true],
                    ['id' => 3, 'title' => 'تقييم المنتجات الجديدة', 'description' => 'ما رأيك بالمنتجات التي تم إضافتها مؤخراً؟', 'questions_count' => 6, 'duration' => '4 دقائق', 'active' => false],
                ];
            @endphp

            @foreach($surveys as $survey)
                <div class="bg-white rounded-2xl shadow-md overflow-hidden transition hover:shadow-lg">
                    <div class="p-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-bold text-gray-800 text-base">{{ $survey['title'] }}</h3>
                                <p class="text-gray-500 text-xs mt-1">{{ $survey['description'] }}</p>
                            </div>
                            <div class="bg-purple-100 rounded-full px-2 py-1">
                                <span class="text-purple-600 text-xs font-semibold">{{ $survey['questions_count'] }} أسئلة</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center mt-3">
                            <div class="flex items-center gap-2">
                                <i class="far fa-clock text-gray-400 text-xs"></i>
                                <span class="text-gray-400 text-xs">{{ $survey['duration'] }}</span>
                            </div>
                            @if($survey['active'])
                                <button class="start-survey bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold px-4 py-1.5 rounded-full transition"
                                        data-id="{{ $survey['id'] }}"
                                        data-title="{{ $survey['title'] }}">
                                    ابدأ الاستبيان
                                </button>
                            @else
                                <span class="text-gray-400 text-xs">قريباً</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- إشارة إلى أن الاستبيانات تساعدنا -->
        <div class="mt-6 text-center">
            <p class="text-xs text-gray-400">آراؤكم تساعدنا على تحسين خدماتنا. شكراً لمشاركتكم!</p>
        </div>
        <div id="logout-btn" class="mt-6 rounded-xl p-4 flex items-center justify-center gap-3  ">
            <br><br>
        </div>
    </div>

    <!-- مودال الاستبيان -->
    <div id="survey-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 transition-all duration-300 opacity-0 pointer-events-none">
        <div class="bg-white rounded-2xl w-11/12 max-w-md p-5 transform scale-95 transition-all duration-300 shadow-2xl max-h-[90vh] overflow-y-auto" dir="rtl">
            <div class="flex justify-between items-center mb-4 border-b border-gray-100 pb-3">
                <h3 id="survey-modal-title" class="text-lg font-bold text-gray-800">استبيان</h3>
                <button id="close-survey-modal" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div id="survey-questions-container" class="space-y-5">
                <!-- الأسئلة ستُحمل ديناميكياً عبر JS -->
            </div>

            <div class="flex gap-3 mt-6">
                <button id="submit-survey" class="flex-1 bg-purple-600 text-white py-2 rounded-xl font-semibold">إرسال</button>
                <button id="cancel-survey" class="flex-1 bg-gray-100 text-gray-700 py-2 rounded-xl">إلغاء</button>
            </div>
        </div>
    </div>

    <!-- Toast الإشعار -->
    <div id="survey-toast" class="fixed bottom-20 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-5 py-3 rounded-full shadow-lg text-sm z-50 transition-all duration-300 opacity-0 translate-y-10 pointer-events-none"></div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <script>
        // بيانات الأسئلة النموذجية لكل استبيان
        const surveyQuestions = {
            1: [
                { id: 'q1', type: 'rating', text: 'ما مدى رضاك عن جودة المنتجات؟', options: ['سيء', 'ضعيف', 'متوسط', 'جيد', 'ممتاز'] },
                { id: 'q2', type: 'choice', text: 'كيف تقيم سرعة التوصيل؟', options: ['سريع جداً', 'سريع', 'متوسط', 'بطيء'] },
                { id: 'q3', type: 'choice', text: 'هل وجدت ما تبحث عنه بسهولة؟', options: ['نعم', 'لا', 'إلى حد ما'] },
                { id: 'q4', type: 'text', text: 'ما هي الاقتراحات التي لديك لتحسين التطبيق؟' },
                { id: 'q5', type: 'rating', text: 'ما مدى احتمالية أن توصي أصدقائك بالتطبيق؟', options: ['غير مرجح', 'مرجح قليلاً', 'محايد', 'مرجح', 'مرجح جداً'] }
            ],
            2: [
                { id: 'q1', type: 'choice', text: 'كم مرة تتسوق من ماجديت شهرياً؟', options: ['أقل من مرة', '1-2 مرات', '3-5 مرات', 'أكثر من 5 مرات'] },
                { id: 'q2', type: 'rating', text: 'تقييمك لسهولة الاستخدام', options: ['1', '2', '3', '4', '5'] },
                { id: 'q3', type: 'choice', text: 'هل تناسبك طرق الدفع المتاحة؟', options: ['نعم', 'لا', 'إلى حد ما'] },
                { id: 'q4', type: 'text', text: 'ما هي الميزة التي تود إضافتها للتطبيق؟' }
            ]
        };

        let currentSurveyId = null;
        let currentAnswers = {};

        const modal = document.getElementById('survey-modal');
        const modalTitle = document.getElementById('survey-modal-title');
        const questionsContainer = document.getElementById('survey-questions-container');
        const closeModalBtn = document.getElementById('close-survey-modal');
        const cancelBtn = document.getElementById('cancel-survey');
        const submitBtn = document.getElementById('submit-survey');
        const toast = document.getElementById('survey-toast');

        function showToast(msg, isError = false) {
            toast.innerText = msg;
            toast.style.background = isError ? '#dc2626' : '#1f2937';
            toast.classList.remove('opacity-0', 'translate-y-10');
            toast.classList.add('opacity-100', 'translate-y-0');
            setTimeout(() => {
                toast.classList.remove('opacity-100', 'translate-y-0');
                toast.classList.add('opacity-0', 'translate-y-10');
            }, 3000);
        }

        function openModal(surveyId, surveyTitle) {
            currentSurveyId = surveyId;
            currentAnswers = {};
            modalTitle.innerText = surveyTitle;
            loadQuestions(surveyId);
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.classList.add('opacity-100');
            document.querySelector('#survey-modal .bg-white').classList.remove('scale-95');
            document.querySelector('#survey-modal .bg-white').classList.add('scale-100');
        }

        function closeModal() {
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0', 'pointer-events-none');
            document.querySelector('#survey-modal .bg-white').classList.remove('scale-100');
            document.querySelector('#survey-modal .bg-white').classList.add('scale-95');
        }

        function loadQuestions(surveyId) {
            const questions = surveyQuestions[surveyId];
            if (!questions) return;
            let html = '';
            questions.forEach((q, idx) => {
                html += `<div class="question-item border-b border-gray-100 pb-4">
                            <p class="font-semibold text-gray-700 text-sm mb-2">${q.text}</p>`;
                if (q.type === 'rating') {
                    html += `<div class="flex flex-wrap gap-2 rating-group" data-qid="${q.id}">`;
                    q.options.forEach((opt, i) => {
                        html += `<label class="flex flex-col items-center cursor-pointer">
                                    <input type="radio" name="${q.id}" value="${opt}" class="rating-radio hidden">
                                    <span class="block w-8 h-8 rounded-full bg-gray-100 text-center leading-8 text-xs hover:bg-purple-100 transition ${i === 2 ? 'rating-star' : ''}">${opt.charAt(0)}</span>
                                    <span class="text-[10px] text-gray-500 mt-1">${opt}</span>
                                </label>`;
                    });
                    html += `</div>`;
                } else if (q.type === 'choice') {
                    html += `<div class="space-y-2">`;
                    q.options.forEach(opt => {
                        html += `<label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="${q.id}" value="${opt}" class="w-4 h-4 text-purple-600">
                                    <span class="text-sm text-gray-700">${opt}</span>
                                </label>`;
                    });
                    html += `</div>`;
                } else if (q.type === 'text') {
                    html += `<textarea name="${q.id}" rows="2" class="w-full p-2 border border-gray-200 rounded-xl text-sm focus:border-purple-400 focus:ring-2 focus:ring-purple-200" placeholder="اكتب إجابتك هنا..."></textarea>`;
                }
                html += `</div>`;
            });
            questionsContainer.innerHTML = html;

            // إضافة تفعيل نمط النجوم للتقييم
            document.querySelectorAll('.rating-group').forEach(group => {
                const radios = group.querySelectorAll('input[type="radio"]');
                radios.forEach(radio => {
                    radio.addEventListener('change', () => {
                        // إزالة الخلفية البنفسجية من جميع
                        group.querySelectorAll('.rating-star').forEach(span => span.classList.remove('bg-purple-600', 'text-white'));
                        const selectedSpan = radio.nextElementSibling;
                        if (selectedSpan) selectedSpan.classList.add('bg-purple-600', 'text-white');
                    });
                });
            });
        }

        function collectAnswers() {
            const questions = surveyQuestions[currentSurveyId];
            if (!questions) return {};
            let answers = {};
            questions.forEach(q => {
                if (q.type === 'text') {
                    const textarea = document.querySelector(`textarea[name="${q.id}"]`);
                    if (textarea) answers[q.id] = textarea.value.trim();
                } else {
                    const selected = document.querySelector(`input[name="${q.id}"]:checked`);
                    if (selected) answers[q.id] = selected.value;
                    else answers[q.id] = null;
                }
            });
            return answers;
        }

        function validateAnswers(answers) {
            const questions = surveyQuestions[currentSurveyId];
            for (let q of questions) {
                if (!answers[q.id] && q.type !== 'text') {
                    return false;
                }
            }
            return true;
        }

        submitBtn.addEventListener('click', () => {
            const answers = collectAnswers();
            if (!validateAnswers(answers)) {
                showToast('❌ الرجاء الإجابة على جميع الأسئلة', true);
                return;
            }
            // محاكاة الإرسال (هنا يمكن ربطه بـ AJAX)
            console.log('Survey submitted:', { surveyId: currentSurveyId, answers });
            showToast('✅ تم إرسال الاستبيان بنجاح! شكراً لمشاركتك');
            closeModal();
        });

        closeModalBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

        // ربط أزرار بدء الاستبيان
        document.querySelectorAll('.start-survey').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                const title = btn.getAttribute('data-title');
                openModal(parseInt(id), title);
            });
        });
    </script>
@endsection