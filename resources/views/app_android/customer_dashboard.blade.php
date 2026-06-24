@extends('app_android.partials.mobile.app_mobile')

@section('title', __('messages.dashboard_title'))

@section('content')
    @include('app_android.partials.mobile.header', ['title' => __('messages.dashboard_title')])

    <div class="p-4 space-y-6 mb-5" dir="rtl">

   {{-- ========== شريط الدعايات (Carousel) مع 6 بطاقات وتمرير تلقائي ========== --}}
        <div class="relative">
            <div id="ad-carousel" class="flex overflow-x-auto scroll-smooth snap-x snap-mandatory hide-scrollbar rounded-2xl shadow-md" style="scrollbar-width: none; -webkit-overflow-scrolling: touch;">
                @php
                    $ads = [
                        ['id' => 1, 'title' => 'خصم 50% على الملابس', 'subtitle' => 'سارع بالشراء', 'icon' => '🛍️', 'bg' => 'from-pink-500 to-rose-600'],
                        ['id' => 2, 'title' => 'عرض الإلكترونيات', 'subtitle' => 'خصم 30%', 'icon' => '📱', 'bg' => 'from-blue-500 to-cyan-600'],
                        ['id' => 3, 'title' => 'توصيل مجاني', 'subtitle' => 'للطلبات فوق 200 ر.س', 'icon' => '🚚', 'bg' => 'from-green-500 to-emerald-600'],
                        ['id' => 4, 'title' => 'عطور فاخرة', 'subtitle' => 'خصم 25%', 'icon' => '🌸', 'bg' => 'from-purple-500 to-indigo-600'],
                        ['id' => 5, 'title' => 'أحذية رياضية', 'subtitle' => 'تخفيضات تصل 40%', 'icon' => '👟', 'bg' => 'from-amber-500 to-orange-600'],
                        ['id' => 6, 'title' => 'ساعات ذكية', 'subtitle' => 'عرض خاص', 'icon' => '⌚', 'bg' => 'from-gray-500 to-gray-700'],
                    ];
                @endphp
                @foreach($ads as $ad)
                    <div class="flex-shrink-0 w-full snap-start">
                        <div class="bg-gradient-to-r {{ $ad['bg'] }} rounded-2xl p-5 text-white flex items-center justify-between mx-1">
                            <div>
                                <p class="text-lg font-bold">{{ $ad['title'] }}</p>
                                <p class="text-sm opacity-90">{{ $ad['subtitle'] }}</p>
                                <button class="mt-3 bg-white/20 backdrop-blur-sm text-white text-xs px-4 py-1.5 rounded-full">اشتري الآن</button>
                            </div>
                            <div class="text-6xl drop-shadow-lg">
                                {{ $ad['icon'] }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <!-- نقاط التنقل -->
            <div class="flex justify-center gap-1 mt-3" id="ad-dots">
                @foreach($ads as $index => $ad)
                    <div class="w-2 h-2 rounded-full bg-gray-300 transition-all duration-300 {{ $index === 0 ? 'w-4 bg-purple-600' : '' }}" data-index="{{ $index }}"></div>
                @endforeach
            </div>
        </div>

        {{-- ========== قسم الفئات (Categories) ديناميكي مع صور ========== --}}
        <div>
            <div class="flex justify-between items-center mb-3">
                <h2 class="text-lg font-bold text-gray-800">الفئات</h2>
                <a href="{{ route('customer.categories.index') }}" class="text-xs text-purple-600 font-semibold">عرض الكل</a>
            </div>
            <div class="flex overflow-x-auto space-x-3 space-x-reverse pb-2">
                @forelse($categories as $category)
                    <div class="flex-shrink-0 text-center w-20 cursor-pointer" onclick="window.location='{{ route('customer.categories.show', $category->id) }}'">
                        <div class="w-16 h-16 mx-auto rounded-full overflow-hidden shadow-sm transition hover:scale-105 bg-gray-100 flex items-center justify-center">
                            @if($category->image)
                                <img src="{{ $category->image }}" alt="{{ $category->name }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-2xl">{{ $category->icon ?? '📁' }}</span>
                            @endif
                        </div>
                        <p class="text-xs mt-1 text-gray-700">{{ $category->name }}</p>
                    </div>
                @empty
                    <div class="text-center text-gray-500 w-full">لا توجد فئات لعرضها</div>
                @endforelse
            </div>
        </div>

        {{-- ========== قسم الماركات (Brands) ديناميكي مع صور ========== --}}
        <div>
            <div class="flex justify-between items-center mb-3">
                <h2 class="text-lg font-bold text-gray-800">ماركات شهيرة</h2>
                <a href="{{ route('customer.brands') }}" class="text-xs text-purple-600 font-semibold">عرض الكل</a>
            </div>
            <div class="flex overflow-x-auto space-x-3 space-x-reverse pb-2">
                @forelse($brands as $brand)
                    <div class="flex-shrink-0 text-center w-24 cursor-pointer" onclick="window.location='{{ route('customer.brands.show', $brand->id) }}'">
                        <div class="w-20 h-20 rounded-2xl overflow-hidden shadow-sm transition hover:shadow-md bg-gray-100 flex items-center justify-center">
                            @if($brand->logo)
                                <img src="{{ $brand->logo }}" alt="{{ $brand->name }}" class="w-full h-full object-contain p-2">
                            @else
                                <span class="text-3xl">{{ $brand->logo_emoji ?? '🏷️' }}</span>
                            @endif
                        </div>
                        <p class="text-xs mt-1 font-semibold text-gray-800">{{ $brand->name }}</p>
                    </div>
                @empty
                    <div class="text-center text-gray-500 w-full">لا توجد ماركات لعرضها</div>
                @endforelse
            </div>
        </div>

    {{-- ========== قسم المنتجات (Products) ديناميكي مع صور ========== --}}
        <div class="mb-5">
            <div class="flex justify-between items-center mb-3">
                <h2 class="text-lg font-bold text-gray-800">أفضل المنتجات</h2>
                <a href="{{ route('customer.products') }}" class="text-xs text-purple-600 font-semibold">عرض الكل</a>
            </div>
            <div class="grid grid-cols-2 gap-4">
                @forelse($products as $product)
                    <div class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100 transition hover:shadow-lg">
                        <div class="h-32 bg-gray-100 flex items-center justify-center overflow-hidden">
                            @if($product->image)
                                <img src="{{ $product->image }}" alt="{{ $product->name ?? $product->name_en }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-5xl text-gray-400">📦</span>
                            @endif
                        </div>
                        <div class="p-3">
                            <h3 class="font-bold text-gray-800 text-sm">{{ $product->name ?? $product->name_en }}</h3>
                            <div class="flex items-center gap-1 mt-1">
                                <span class="text-yellow-500 text-xs">★</span>
                                <span class="text-xs text-gray-600">{{ number_format($product->rate, 1) }}</span>
                            </div>
                            <div class="mt-2 flex items-center justify-between">
                                <div>
                                    <span class="text-purple-700 font-bold">{{ number_format($product->price) }} ر.س</span>
                                    @if($product->old_price)
                                        <span class="text-gray-400 text-xs line-through mr-1">{{ number_format($product->old_price) }} ر.س</span>
                                    @endif
                                </div>
                                <button class="bg-purple-600 text-white text-xs px-3 py-1.5 rounded-full hover:bg-purple-700 transition add-to-cart" data-name="{{ $product->name ?? $product->name_en }}" data-price="{{ $product->price }}">شراء</button>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-500 col-span-2">لا توجد منتجات لعرضها</p>
                @endforelse
            </div>
        </div>

        {{-- ========== قسم العروض (Offers) مع صور ========== --}}
        <div>
            <div class="flex justify-between items-center mb-3">
                <h2 class="text-lg font-bold text-gray-800">عروض حصرية</h2>
                <a href="{{ route('customer.offer') }}" class="text-xs text-purple-600 font-semibold">عرض الكل</a>
            </div>
            <div class="space-y-4">
                @forelse($offers ?? [] as $offer)
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100 transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                        <div class="flex flex-row">
                            <div class="relative w-28 h-28 overflow-hidden">
                                @if($offer->image)
                                    <img src="{{ $offer->image }}" alt="{{ $offer->title }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center text-white text-4xl">
                                        🛍️
                                    </div>
                                @endif
                                <div class="absolute top-1 left-1 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full shadow-md">{{ $offer->discount ?? '-50%' }}</div>
                            </div>
                            <div class="flex-1 p-3 text-right">
                                <h3 class="font-bold text-gray-800 text-base">{{ $offer->title }}</h3>
                                <p class="text-gray-500 text-xs mt-1 leading-relaxed line-clamp-2">{{ $offer->description }}</p>
                                <div class="flex justify-between items-center mt-3">
                                    <div class="flex items-center gap-1 text-[11px] text-gray-500">
                                        <span class="text-amber-500">⏰</span>
                                        <span>ينتهي: {{ \Carbon\Carbon::parse($offer->expiry_date)->format('d M Y') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <!-- بيانات تجريبية ثابتة -->
                    @php
                        $offers = [
                            ['id' => 1, 'title' => 'تخفيضات الصيف الكبرى', 'description' => 'خصم يصل إلى 50% على جميع الملابس الصيفية والإكسسوارات الأصلية. سارع بالشراء!', 'discount' => '-50%', 'expiry' => '31 يوليو 2025'],
                            ['id' => 2, 'title' => 'عرض خاص على الإلكترونيات', 'description' => 'احصل على خصم 30% على أحدث الأجهزة الإلكترونية لفترة محدودة.', 'discount' => '-30%', 'expiry' => '15 أغسطس 2025'],
                        ];
                    @endphp
                    @foreach($offers as $offer)
                        <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100 transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                            <div class="flex flex-row">
                                <div class="relative w-28 h-28 bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center text-white text-4xl overflow-hidden">
                                    <span>🛍️</span>
                                    <div class="absolute top-1 left-1 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full shadow-md">{{ $offer['discount'] }}</div>
                                </div>
                                <div class="flex-1 p-3 text-right">
                                    <h3 class="font-bold text-gray-800 text-base">{{ $offer['title'] }}</h3>
                                    <p class="text-gray-500 text-xs mt-1 leading-relaxed line-clamp-2">{{ $offer['description'] }}</p>
                                    <div class="flex justify-between items-center mt-3">
                                        <div class="flex items-center gap-1 text-[11px] text-gray-500">
                                            <span class="text-amber-500">⏰</span>
                                            <span>ينتهي: {{ $offer['expiry'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endforelse
            </div>
            <br><br><br>
        </div>

    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script>
        // ========== Carousel with Auto-Scroll ==========
        const carousel = document.getElementById('ad-carousel');
        const dots = document.querySelectorAll('#ad-dots .rounded-full');

        let autoScrollInterval = null;
        let isUserInteracting = false;
        let interactionTimeout = null;
        const AUTO_SCROLL_DELAY = 4000;

        function updateDots() {
            if (!carousel || !dots.length) return;
            const scrollPos = carousel.scrollLeft;
            const itemWidth = carousel.clientWidth;
            if (itemWidth === 0) return;
            const activeIndex = Math.round(scrollPos / itemWidth);
            dots.forEach((dot, idx) => {
                if (idx === activeIndex) {
                    dot.classList.add('w-4', 'bg-purple-600');
                    dot.classList.remove('w-2', 'bg-gray-300');
                } else {
                    dot.classList.add('w-2', 'bg-gray-300');
                    dot.classList.remove('w-4', 'bg-purple-600');
                }
            });
        }

        function scrollToSlide(index) {
            if (!carousel) return;
            const itemWidth = carousel.clientWidth;
            if (itemWidth === 0) return;
            carousel.scrollTo({
                left: index * itemWidth,
                behavior: 'smooth'
            });
        }

        function autoScroll() {
            if (!carousel || isUserInteracting) return;
            const itemWidth = carousel.clientWidth;
            if (itemWidth === 0) return;
            const totalSlides = dots.length;
            let currentIndex = Math.round(carousel.scrollLeft / itemWidth);
            let nextIndex = (currentIndex + 1) % totalSlides;
            scrollToSlide(nextIndex);
        }

        function startAutoScroll() {
            if (autoScrollInterval) clearInterval(autoScrollInterval);
            autoScrollInterval = setInterval(autoScroll, AUTO_SCROLL_DELAY);
        }

        function pauseAutoScroll() {
            isUserInteracting = true;
            if (interactionTimeout) clearTimeout(interactionTimeout);
            interactionTimeout = setTimeout(() => {
                isUserInteracting = false;
            }, AUTO_SCROLL_DELAY + 500);
        }

        if (carousel) {
            carousel.addEventListener('scroll', () => {
                updateDots();
                pauseAutoScroll();
            });
            carousel.addEventListener('touchstart', pauseAutoScroll);
            carousel.addEventListener('mousedown', pauseAutoScroll);
            carousel.addEventListener('mouseenter', pauseAutoScroll);
            updateDots();
            startAutoScroll();
        }

        dots.forEach((dot, idx) => {
            dot.addEventListener('click', () => {
                scrollToSlide(idx);
                pauseAutoScroll();
            });
        });

        // ========== Bottom Navigation ==========
        document.querySelectorAll('.nav-item-premium').forEach(item => {
            item.addEventListener('click', function() {
                const target = this.getAttribute('data-nav');
                const routes = {
                    home: "{{ route('customer.dashboard') }}",
                    offers: "{{ route('customer.offers') }}",
                    more: "{{ route('customer.more') }}"
                };
                if (routes[target]) {
                    if (target === 'home') return;
                    window.location.href = routes[target];
                }
            });
        });
    </script>
@endsection
