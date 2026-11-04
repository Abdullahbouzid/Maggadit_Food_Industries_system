@extends('app_android.partials.mobile.app_mobile')

@section('title', 'الأسئلة الشائعة')

@section('content')
    @include('app_android.partials.mobile.header', ['title' => 'الأسئلة الشائعة'])

    @php($faqsByCategory = $faqsByCategory ?? collect())

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
            @php($firstFaq = true)
            @foreach($faqsByCategory as $category => $items)
                @foreach($items as $faq)
                    <div class="faq-item {{ $firstFaq ? 'is-open' : '' }} bg-white rounded-xl shadow-sm overflow-hidden transition-all duration-300"
                         data-category="{{ $category ?: 'عام' }}">
                        <div class="faq-question p-4 flex justify-between items-center cursor-pointer hover:bg-gray-50 transition"
                             role="button" tabindex="0" aria-expanded="{{ $firstFaq ? 'true' : 'false' }}">
                            <h3 class="font-semibold text-gray-800 text-sm">{{ $faq->question }}</h3>
                            <i class="fas fa-chevron-down text-gray-400 transition-transform duration-300"></i>
                        </div>
                        <div class="faq-answer px-4">
                            <p class="text-gray-600 text-sm leading-relaxed pb-4 border-t border-gray-100 pt-3">{{ $faq->answer }}</p>
                        </div>
                    </div>
                    @php($firstFaq = false)
                @endforeach
            @endforeach
        </div>

        <!-- رسالة عند عدم وجود أسئلة -->
        <div id="no-faqs" class="{{ $faqsByCategory->isEmpty() ? '' : 'hidden' }} text-center py-10">
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

    <style>
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        /* COLLAPSE OWNED BY PAGE CSS, NOT BY THE TAILWIND CDN */
        .faq-answer {
            max-height: 0;
            overflow: hidden;
            padding-bottom: 0;
            transition: max-height .3s ease, padding-bottom .3s ease;
        }
        .faq-item.is-open > .faq-answer {
            max-height: 5000px;
            padding-bottom: 12px;
        }
        .faq-item.is-open > .faq-question i {
            transform: rotate(180deg);
        }
        .faq-item.is-hidden {
            display: none !important;
        }
        .faq-question:focus {
            outline: 2px solid #a855f7;
            outline-offset: -2px;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var container = document.getElementById('faq-container');
            var searchInput = document.getElementById('search-faq');
            var noFaqsEl = document.getElementById('no-faqs');
            var noResultsEl = document.getElementById('no-results');

            function getItems() {
                return container ? Array.prototype.slice.call(container.querySelectorAll('.faq-item')) : [];
            }

            function textOf(el) {
                return (el && el.textContent ? el.textContent : '').trim().toLowerCase();
            }

            function closeItem(item) {
                item.classList.remove('is-open');
                var header = item.querySelector('.faq-question');
                if (header) header.setAttribute('aria-expanded', 'false');
            }

            function toggleItem(item) {
                var willOpen = !item.classList.contains('is-open');
                getItems().forEach(closeItem);
                if (willOpen) {
                    item.classList.add('is-open');
                    var header = item.querySelector('.faq-question');
                    if (header) header.setAttribute('aria-expanded', 'true');
                }
            }

            function filterFaqs() {
                var currentSearch = searchInput ? searchInput.value.trim().toLowerCase() : '';
                var activeBtn = document.querySelector('.cat-filter.active');
                var currentCategory = activeBtn ? activeBtn.getAttribute('data-cat') : 'all';
                var anyVisible = false;

                getItems().forEach(function (item) {
                    if (item.classList.contains('is-hidden')) return;

                    var haystack = textOf(item).indexOf(currentSearch) !== -1;
                    var category = item.getAttribute('data-category') || '';
                    var matchesCategory = currentCategory === 'all' || category === currentCategory;

                    if (haystack && matchesCategory) {
                        anyVisible = true;
                    } else {
                        item.classList.add('is-hidden');
                        closeItem(item);
                    }
                });

                if (noResultsEl) noResultsEl.classList.toggle('hidden', anyVisible);
                if (noFaqsEl) noFaqsEl.classList.toggle('hidden', getItems().length > 0);
            }

            // EVENT DELEGATION: works for the server-rendered markup AND for any
            // item added later, so nothing depends on a second script pass.
            if (container) {
                container.addEventListener('click', function (e) {
                    var header = e.target.closest ? e.target.closest('.faq-question') : null;
                    if (!header || !container.contains(header)) return;
                    var item = header.closest('.faq-item');
                    if (!item || item.classList.contains('is-hidden')) return;
                    toggleItem(item);
                });

                container.addEventListener('keydown', function (e) {
                    if (e.key !== 'Enter' && e.key !== ' ' && e.keyCode !== 13 && e.keyCode !== 32) return;
                    var header = e.target.closest ? e.target.closest('.faq-question') : null;
                    if (!header || !container.contains(header)) return;
                    e.preventDefault();
                    var item = header.closest('.faq-item');
                    if (!item || item.classList.contains('is-hidden')) return;
                    toggleItem(item);
                });
            }

            if (searchInput) {
                searchInput.addEventListener('input', filterFaqs);
                searchInput.addEventListener('search', filterFaqs);
            }

            document.querySelectorAll('.cat-filter').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    document.querySelectorAll('.cat-filter').forEach(function (b) {
                        b.classList.remove('active', 'bg-purple-600', 'text-white');
                        b.classList.add('bg-gray-100', 'text-gray-700');
                    });
                    this.classList.add('active', 'bg-purple-600', 'text-white');
                    this.classList.remove('bg-gray-100', 'text-gray-700');
                    filterFaqs();
                });
            });

            if (noFaqsEl) noFaqsEl.classList.toggle('hidden', getItems().length > 0);
            filterFaqs();
        });
    </script>
@endsection
