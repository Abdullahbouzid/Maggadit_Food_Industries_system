@extends('app_android.partials.mobile.app_mobile')

@section('title', 'الأسئلة الشائعة')

@section('content')
    @include('app_android.partials.mobile.header', ['title' => 'الأسئلة الشائعة'])

    <div class="p-4" dir="rtl">
        <!-- زر الرجوع -->
        <button onclick="window.history.back()" class="flex items-center gap-2 text-purple-600 bg-purple-50 px-4 py-2 rounded-full text-sm font-semibold transition hover:bg-purple-100 mb-4">
            <i class="fas fa-arrow-right"></i> رجوع
        </button>

        <!-- شريط البحث -->
        <div class="relative mb-5">
            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
            <input type="text" id="search-faq" placeholder="ابحث في الأسئلة الشائعة..."
                   class="w-full pr-10 pl-4 py-3 border border-gray-200 rounded-xl focus:border-purple-400 focus:ring-2 focus:ring-purple-200 transition text-sm">
        </div>

        <!-- تبويب الفئات -->
        <div class="flex gap-2 overflow-x-auto pb-2 mb-4 hide-scrollbar">
            <button data-cat="all" class="cat-filter active px-4 py-2 rounded-full text-sm font-semibold bg-purple-600 text-white whitespace-nowrap">الكل</button>
            @foreach($faqsByCategory as $category => $items)
                <button data-cat="{{ $category ?: 'عام' }}" class="cat-filter px-4 py-2 rounded-full text-sm font-semibold bg-gray-100 text-gray-700 whitespace-nowrap">
                    {{ $category ?: 'عام' }}
                </button>
            @endforeach
        </div>

        <!-- قائمة الأسئلة الشائعة -->
        <div id="faq-container" class="space-y-3">
            @foreach($faqsByCategory as $category => $items)
                @foreach($items as $faq)
                    <div class="faq-item bg-white rounded-xl shadow-sm overflow-hidden transition-all duration-300"
                         data-category="{{ $category ?: 'عام' }}"
                         data-question="{{ $faq->question }}"
                         data-answer="{{ $faq->answer }}">
                        <div class="faq-question p-4 flex justify-between items-center cursor-pointer hover:bg-gray-50 transition">
                            <h3 class="font-semibold text-gray-800 text-sm">{{ $faq->question }}</h3>
                            <i class="fas fa-chevron-down text-gray-400 transition-transform duration-300"></i>
                        </div>
                        <div class="faq-answer max-h-0 overflow-hidden transition-all duration-300 px-4 pb-0">
                            <p class="text-gray-600 text-sm leading-relaxed pb-4 border-t border-gray-100 pt-3">{{ $faq->answer }}</p>
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>

        <!-- رسالة عند عدم وجود أسئلة -->
        <div id="no-faqs" class="hidden text-center py-10">
            <div class="w-32 h-32 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-question-circle text-5xl text-gray-300"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-700 mb-2">لا توجد أسئلة شائعة</h3>
            <p class="text-gray-400 text-sm">لم يتم إضافة أسئلة شائعة بعد</p>
        </div>

        <!-- رسالة عند عدم وجود نتائج بحث -->
        <div id="no-results" class="hidden text-center py-8">
            <i class="fas fa-search text-gray-300 text-5xl mb-3"></i>
            <p class="text-gray-400">لا توجد أسئلة مطابقة لبحثك</p>
        </div>
        <div id="logout-btn" class="mt-6 rounded-xl p-4 flex items-center justify-center gap-3">
            <br><br>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .rotate-180 {
            transform: rotate(180deg);
        }
        .faq-question i {
            transition: transform 0.3s ease;
        }
        .faq-item.hidden {
            display: none !important;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ===== عناصر DOM =====
            const searchInput = document.getElementById('search-faq');
            const categoryBtns = document.querySelectorAll('.cat-filter');
            const faqItems = document.querySelectorAll('.faq-item');
            const noResults = document.getElementById('no-results');
            let currentCategory = 'all';
            let currentSearch = '';

            // ===== دالة لإغلاق جميع الإجابات المفتوحة =====
            function closeAllAnswers() {
                document.querySelectorAll('.faq-answer').forEach(ans => {
                    ans.style.maxHeight = null;
                    ans.style.paddingBottom = '0px';
                });
                document.querySelectorAll('.faq-question i').forEach(icon => {
                    icon.classList.remove('rotate-180');
                });
            }

            // ===== دالة التصفية والبحث =====
            function filterFaqs() {
                // إغلاق جميع الإجابات أولاً لتجنب التداخل
                closeAllAnswers();

                let anyVisible = false;
                faqItems.forEach(item => {
                    const question = item.getAttribute('data-question').toLowerCase();
                    const answer = item.getAttribute('data-answer').toLowerCase();
                    const category = item.getAttribute('data-category');
                    const matchesSearch = currentSearch === '' || question.includes(currentSearch) || answer.includes(currentSearch);
                    const matchesCategory = currentCategory === 'all' || category === currentCategory;

                    if (matchesSearch && matchesCategory) {
                        item.classList.remove('hidden');
                        anyVisible = true;
                    } else {
                        item.classList.add('hidden');
                    }
                });
                noResults.classList.toggle('hidden', anyVisible);
            }

            // ===== حدث البحث =====
            searchInput.addEventListener('input', function(e) {
                currentSearch = e.target.value.trim().toLowerCase();
                filterFaqs();
            });

            // ===== أزرار الفئات =====
            categoryBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    // إزالة التفعيل عن الكل
                    categoryBtns.forEach(b => {
                        b.classList.remove('active', 'bg-purple-600', 'text-white');
                        b.classList.add('bg-gray-100', 'text-gray-700');
                    });
                    // تفعيل الزر المضغوط
                    this.classList.add('active', 'bg-purple-600', 'text-white');
                    this.classList.remove('bg-gray-100', 'text-gray-700');

                    currentCategory = this.getAttribute('data-cat');
                    filterFaqs();
                });
            });

            // ===== خاصية الإظهار/الإخفاء (Accordion) =====
            document.querySelectorAll('.faq-question').forEach(question => {
                question.addEventListener('click', function(e) {
                    const parent = this.closest('.faq-item');
                    // تأكد من أن العنصر غير مخفي (مرئي)
                    if (parent.classList.contains('hidden')) return;

                    const answerDiv = parent.querySelector('.faq-answer');
                    const icon = this.querySelector('i');
                    const isOpen = answerDiv.style.maxHeight && answerDiv.style.maxHeight !== '0px';

                    // (اختياري) إغلاق جميع الإجابات الأخرى - يمكن تفعيله بإزالة التعليق
                    // document.querySelectorAll('.faq-answer').forEach(ans => {
                    //     if (ans !== answerDiv) {
                    //         ans.style.maxHeight = null;
                    //         ans.style.paddingBottom = '0px';
                    //         ans.closest('.faq-item').querySelector('.faq-question i').classList.remove('rotate-180');
                    //     }
                    // });

                    if (!isOpen) {
                        answerDiv.style.maxHeight = answerDiv.scrollHeight + 'px';
                        answerDiv.style.paddingBottom = '12px';
                        icon.classList.add('rotate-180');
                    } else {
                        answerDiv.style.maxHeight = null;
                        answerDiv.style.paddingBottom = '0px';
                        icon.classList.remove('rotate-180');
                    }
                });
            });

            // ===== تنفيذ التصفية الأولية (تأكد من أن الكل ظاهر) =====
            const faqItems = document.querySelectorAll('.faq-item');
            const noFaqsEl = document.getElementById('no-faqs');
            
            if (faqItems.length === 0) {
                noFaqsEl.classList.remove('hidden');
            }
            
            filterFaqs();
        });
        
        // ===== Proxy Fallback for FAQs =====
        if (document.querySelectorAll('.faq-item').length === 0) {
            fetch('/customer/proxy/faqs')
                .then(r => r.json())
                .then(res => {
                    if (res && res.data) {
                        const faqs = res.data;
                        const container = document.getElementById('faq-container');
                        container.innerHTML = '';
                        
                        Object.entries(faqs).forEach(([category, items]) => {
                            items.forEach(faq => {
                                const div = document.createElement('div');
                                div.className = 'faq-item bg-white rounded-xl shadow-sm overflow-hidden transition-all duration-300';
                                div.setAttribute('data-category', category || 'عام');
                                div.setAttribute('data-question', faq.question);
                                div.setAttribute('data-answer', faq.answer);
                                div.innerHTML = `
                                    <div class="faq-question p-4 flex justify-between items-center cursor-pointer hover:bg-gray-50 transition">
                                        <h3 class="font-semibold text-gray-800 text-sm">${faq.question}</h3>
                                        <i class="fas fa-chevron-down text-gray-400 transition-transform duration-300"></i>
                                    </div>
                                    <div class="faq-answer max-h-0 overflow-hidden transition-all duration-300 px-4 pb-0">
                                        <p class="text-gray-600 text-sm leading-relaxed pb-4 border-t border-gray-100 pt-3">${faq.answer}</p>
                                    </div>
                                `;
                                container.appendChild(div);
                            });
                        });
                        
                        document.getElementById('no-faqs').classList.add('hidden');
                        
                        document.querySelectorAll('.faq-question').forEach(question => {
                            question.addEventListener('click', function(e) {
                                const parent = this.closest('.faq-item');
                                if (parent.classList.contains('hidden')) return;
                                const answerDiv = parent.querySelector('.faq-answer');
                                const icon = this.querySelector('i');
                                const isOpen = answerDiv.style.maxHeight && answerDiv.style.maxHeight !== '0px';
                                if (!isOpen) {
                                    answerDiv.style.maxHeight = answerDiv.scrollHeight + 'px';
                                    answerDiv.style.paddingBottom = '12px';
                                    icon.classList.add('rotate-180');
                                } else {
                                    answerDiv.style.maxHeight = null;
                                    answerDiv.style.paddingBottom = '0px';
                                    icon.classList.remove('rotate-180');
                                }
                            });
                        });
                    }
                })
                .catch(err => console.error("FAQs proxy error:", err));
        }
    </script>
@endsection
