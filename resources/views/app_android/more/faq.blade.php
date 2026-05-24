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
        <div class="flex gap-2 overflow-x-auto pb-2 mb-4">
            <button data-cat="all" class="cat-filter active px-4 py-2 rounded-full text-sm font-semibold bg-purple-600 text-white whitespace-nowrap">الكل</button>
            <button data-cat="account" class="cat-filter px-4 py-2 rounded-full text-sm font-semibold bg-gray-100 text-gray-700 whitespace-nowrap">الحساب</button>
            <button data-cat="orders" class="cat-filter px-4 py-2 rounded-full text-sm font-semibold bg-gray-100 text-gray-700 whitespace-nowrap">الطلبات</button>
            <button data-cat="payment" class="cat-filter px-4 py-2 rounded-full text-sm font-semibold bg-gray-100 text-gray-700 whitespace-nowrap">الدفع والشحن</button>
            <button data-cat="returns" class="cat-filter px-4 py-2 rounded-full text-sm font-semibold bg-gray-100 text-gray-700 whitespace-nowrap">الإرجاع والاستبدال</button>
        </div>

        <!-- قائمة الأسئلة الشائعة -->
        <div id="faq-container" class="space-y-3">
            @php
                $faqs = [
                    ['id' => 1, 'question' => 'كيف يمكنني تغيير كلمة المرور؟', 'answer' => 'يمكنك تغيير كلمة المرور من خلال الذهاب إلى "المزيد" ثم اختيار "تغيير كلمة السر"، ثم إدخال كلمة المرور الجديدة وتأكيدها.', 'category' => 'account'],
                    ['id' => 2, 'question' => 'ماذا أفعل إذا تأخر الطلب؟', 'answer' => 'في حالة تأخر الطلب، يمكنك التواصل مع خدمة العملاء عبر الواتساب أو الهاتف، وسيتم متابعة طلبك.', 'category' => 'orders'],
                    ['id' => 3, 'question' => 'هل توجد رسوم توصيل؟', 'answer' => 'رسوم التوصيل تعتمد على قيمة الطلب. الطلبات التي تزيد عن 200 ريال توصيل مجاني، وأقل من ذلك برسوم 15 ريالاً.', 'category' => 'payment'],
                    ['id' => 4, 'question' => 'كيف يمكنني إرجاع منتج؟', 'answer' => 'يمكنك إرجاع المنتج خلال 14 يوماً من تاريخ الاستلام بشرط أن يكون بحالته الأصلية. تواصل مع خدمة العملاء لإنشاء طلب إرجاع.', 'category' => 'returns'],
                    ['id' => 5, 'question' => 'ما هي طرق الدفع المتاحة؟', 'answer' => 'نقبل الدفع عبر بطاقات الائتمان (فيزا، ماستركارد)، والمحافظ الرقمية، والتحويل البنكي، والدفع عند الاستلام.', 'category' => 'payment'],
                    ['id' => 6, 'question' => 'كيف يمكنني متابعة طلبي؟', 'answer' => 'يمكنك متابعة طلبك من خلال قسم "تاريخ الطلبات" في قائمة "المزيد"، ستجد حالة الطلب وتفاصيله.', 'category' => 'orders'],
                ];
            @endphp

            @foreach($faqs as $faq)
                <div class="faq-item bg-white rounded-xl shadow-sm overflow-hidden transition-all duration-300" data-category="{{ $faq['category'] }}" data-question="{{ $faq['question'] }}" data-answer="{{ $faq['answer'] }}">
                    <div class="faq-question p-4 flex justify-between items-center cursor-pointer hover:bg-gray-50 transition">
                        <h3 class="font-semibold text-gray-800 text-sm">{{ $faq['question'] }}</h3>
                        <i class="fas fa-chevron-down text-gray-400 transition-transform duration-300"></i>
                    </div>
                    <div class="faq-answer max-h-0 overflow-hidden transition-all duration-300 px-4 pb-0">
                        <p class="text-gray-600 text-sm leading-relaxed pb-4 border-t border-gray-100 pt-3">{{ $faq['answer'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- رسالة عند عدم وجود نتائج -->
        <div id="no-results" class="hidden text-center py-8">
            <i class="fas fa-search text-gray-300 text-5xl mb-3"></i>
            <p class="text-gray-400">لا توجد أسئلة مطابقة لبحثك</p>
        </div>
        <div id="logout-btn" class="mt-6 rounded-xl p-4 flex items-center justify-center gap-3  ">
            <br><br>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script>
        // ========== البحث والتصفية ==========
        const searchInput = document.getElementById('search-faq');
        const categoryBtns = document.querySelectorAll('.cat-filter');
        const faqItems = document.querySelectorAll('.faq-item');
        const noResults = document.getElementById('no-results');
        let currentCategory = 'all';
        let currentSearch = '';

        function filterFaqs() {
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

        searchInput.addEventListener('input', function(e) {
            currentSearch = e.target.value.trim().toLowerCase();
            filterFaqs();
        });

        categoryBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                categoryBtns.forEach(b => {
                    b.classList.remove('active', 'bg-purple-600', 'text-white');
                    b.classList.add('bg-gray-100', 'text-gray-700');
                });
                btn.classList.add('active', 'bg-purple-600', 'text-white');
                btn.classList.remove('bg-gray-100', 'text-gray-700');
                currentCategory = btn.getAttribute('data-cat');
                filterFaqs();
            });
        });

        // ========== خاصية الإظهار/الإخفاء (accordion) ==========
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', () => {
                const parent = question.parentElement;
                const answerDiv = parent.querySelector('.faq-answer');
                const icon = question.querySelector('i');
                const isOpen = answerDiv.style.maxHeight && answerDiv.style.maxHeight !== '0px';
                
                // إغلاق جميع الفقرات المفتوحة (اختياري - يمكن إلغاء تعليق السطر التالي لفتح واحدة فقط)
                // document.querySelectorAll('.faq-answer').forEach(ans => { ans.style.maxHeight = null; ans.style.paddingBottom = '0px'; });
                // document.querySelectorAll('.faq-question i').forEach(ic => ic.classList.remove('rotate-180'));
                
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

        // إضافة كلاس للدوران
        const style = document.createElement('style');
        style.textContent = `
            .rotate-180 { transform: rotate(180deg); }
            .faq-question i { transition: transform 0.3s ease; }
            .faq-item.hidden { display: none; }
        `;
        document.head.appendChild(style);
    </script>
@endsection