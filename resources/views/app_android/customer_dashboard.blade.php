@extends('app_android.partials.mobile.app_mobile')

@section('title', __('messages.dashboard_title'))

@section('content')
    @include('app_android.partials.mobile.header', ['title' => __('messages.dashboard_title')])

    <div class="p-4 space-y-6 mb-5" dir="rtl">
    <style>
        .offer-card {
            position: relative;
            overflow: hidden;
        }
        .offer-badge {
            position: absolute;
            top: 0;
            right: 0;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            padding: 8px 14px;
            border-radius: 0 0 0 20px;
            font-size: 13px;
            font-weight: bold;
            box-shadow: -2px 2px 8px rgba(0,0,0,0.2);
            z-index: 10;
        }
    </style>

   {{-- ========== شريط الدعايات (Carousel) مع بطاقات وتمرير تلقائي ========== --}}
        <div class="relative">
            <div id="ad-carousel" class="flex overflow-x-auto scroll-smooth snap-x snap-mandatory hide-scrollbar rounded-2xl shadow-md" style="scrollbar-width: none; -webkit-overflow-scrolling: touch;">
                @forelse($banners as $banner)
                    <div class="flex-shrink-0 w-full snap-start">
                        <a href="{{ $banner->url ?? '#' }}" class="block w-full h-40 mx-1 rounded-2xl overflow-hidden">
                            <img src="{{ $banner->image ?? asset('assets/images/default-banner.jpg') }}" alt="Banner" class="w-full h-full object-cover">
                        </a>
                    </div>
                @empty
                    <div class="flex-shrink-0 w-full snap-start">
                        <div class="w-full h-40 mx-1 rounded-2xl bg-gray-200 flex items-center justify-center text-gray-500">
                            لا توجد إعلانات
                        </div>
                    </div>
                @endforelse
            </div>
            <!-- نقاط التنقل -->
            <div class="flex justify-center gap-1 mt-3" id="ad-dots">
                @php
                    $dotsCount = $banners->count() > 0 ? $banners->count() : 1;
                @endphp
                @for($i = 0; $i < $dotsCount; $i++)
                    <div class="w-2 h-2 rounded-full bg-gray-300 transition-all duration-300 {{ $i === 0 ? 'w-4 bg-purple-600' : '' }}" data-index="{{ $i }}"></div>
                @endfor
            </div>
        </div>

        {{-- ========== قسم الفئات (Categories) ديناميكي مع صور ========== --}}
        <div>
            <div class="flex justify-between items-center mb-3">
                <h2 class="text-lg font-bold text-gray-800">الفئات</h2>
                <a href="{{ route('customer.categories.index') }}" class="text-xs text-purple-600 font-semibold">عرض الكل</a>
            </div>
            <div class="flex overflow-x-auto space-x-3 space-x-reverse pb-2 hide-scrollbar">
                @forelse($categories as $category)
                    <div class="flex-shrink-0 text-center w-20 cursor-pointer" onclick="window.location='{{ route('customer.categories.show', $category->id) }}'">
                        <div class="w-16 h-16 mx-auto rounded-full overflow-hidden shadow-sm transition hover:scale-105 bg-gray-100 flex items-center justify-center border border-gray-100">
                            @if($category->image)
                                <img src="{{ $category->image }}" alt="{{ $category->name }}" class="w-full h-full object-cover" onerror="this.onerror=null; this.src='https://via.placeholder.com/64?text=📁';">
                            @else
                                <span class="text-2xl">{{ $category->icon ?? '📁' }}</span>
                            @endif
                        </div>
                        <p class="text-xs mt-1 text-gray-700 font-medium truncate">{{ $category->name }}</p>
                    </div>
                @empty
                    <div class="text-center text-gray-500 w-full py-2">لا توجد فئات لعرضها</div>
                @endforelse
            </div>
        </div>

        {{-- ========== قسم الماركات (Brands) ديناميكي مع صور ========== --}}
        <div>
            <div class="flex justify-between items-center mb-3">
                <h2 class="text-lg font-bold text-gray-800">ماركات شهيرة</h2>
                <a href="{{ route('customer.brands') }}" class="text-xs text-purple-600 font-semibold">عرض الكل</a>
            </div>
            <div class="flex overflow-x-auto space-x-3 space-x-reverse pb-2 hide-scrollbar">
                @forelse($brands as $brand)
                    <div class="flex-shrink-0 text-center w-24 cursor-pointer" onclick="window.location='{{ route('customer.brands.show', $brand->id) }}'">
                        <div class="w-20 h-20 rounded-2xl overflow-hidden shadow-sm transition hover:shadow-md bg-gray-100 flex items-center justify-center border border-gray-100">
                            @if($brand->logo)
                                <img src="{{ $brand->logo }}" alt="{{ $brand->name }}" class="w-full h-full object-contain p-2" onerror="this.onerror=null; this.src='https://via.placeholder.com/80?text=🏷️';">
                            @else
                                <span class="text-3xl">{{ $brand->logo_emoji ?? '🏷️' }}</span>
                            @endif
                        </div>
                        <p class="text-xs mt-1 font-semibold text-gray-800 truncate">{{ $brand->name }}</p>
                    </div>
                @empty
                    <div class="text-center text-gray-500 w-full py-2">لا توجد ماركات لعرضها</div>
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
                    <div class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100 transition hover:shadow-lg relative cursor-pointer" onclick="window.location='{{ route('customer.product.show', $product->id) }}'">
                        <button class="absolute top-2 left-2 z-10 p-1.5 rounded-full bg-white/80 backdrop-blur-sm shadow-sm toggle-favorite-btn transition-colors {{ in_array($product->id, $favoriteItemIds ?? []) ? 'text-red-500' : 'text-gray-400' }}" data-id="{{ $product->id }}">
                            <i class="{{ in_array($product->id, $favoriteItemIds ?? []) ? 'fas' : 'far' }} fa-heart text-lg"></i>
                        </button>
                        <div class="h-32 bg-gray-100 flex items-center justify-center overflow-hidden">
                            @if($product->image)
                                <img src="{{ $product->image }}" alt="{{ $product->name ?? $product->name_en }}" class="w-full h-full object-cover" onerror="this.onerror=null; this.src='https://via.placeholder.com/150?text=📦';">
                            @else
                                <span class="text-5xl text-gray-400">📦</span>
                            @endif
                        </div>
                        <div class="p-3">
                            <h3 class="font-bold text-gray-800 text-sm line-clamp-1">{{ $product->name ?? $product->name_en }}</h3>
                            <div class="flex items-center gap-1 mt-1">
                                <span class="text-yellow-500 text-xs">★</span>
                                <span class="text-xs text-gray-600">{{ number_format($product->rate ?? 0, 1) }}</span>
                            </div>
                            <div class="mt-2 flex items-center justify-between">
                                <div>
                                    @if(isset($product->price) && $product->price > 0)
                                        <span class="text-purple-700 font-bold text-sm">{{ number_format($product->price, 2) }} ر.س</span>
                                    @else
                                        <span class="text-purple-700 font-bold text-sm">حسب الطلب</span>
                                    @endif
                                    @if(isset($product->old_price) && $product->old_price > 0)
                                        <span class="text-gray-400 text-xs line-through block">{{ number_format($product->old_price, 2) }} ر.س</span>
                                    @endif
                                </div>
                                <button class="bg-purple-600 text-white text-xs px-3 py-1.5 rounded-full hover:bg-purple-700 transition add-to-cart" 
                                        data-id="{{ $product->id }}"
                                        data-name="{{ $product->name ?? $product->name_en }}" 
                                        data-price="{{ $product->price ?? 0 }}"
                                        data-image="{{ $product->image ?? '' }}">إضافة</button>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-500 col-span-2 py-4">لا توجد منتجات لعرضها</p>
                @endforelse
            </div>
        </div>

        {{-- ========== قسم العروض (Offers) مع صور ========== --}}
        <div>
            <div class="flex justify-between items-center mb-3">
                <h2 class="text-lg font-bold text-gray-800">عروض حصرية</h2>
                <a href="{{ route('customer.offers') }}" class="text-xs text-purple-600 font-semibold">عرض الكل</a>
            </div>
            <div class="space-y-4">
                @forelse($offers ?? [] as $offer)
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden offer-card border border-gray-100 cursor-pointer" onclick="window.location='{{ route('customer.product.show', $offer->item_id) }}'">
                        @if($offer->discount_percentage > 0)
                            <div class="offer-badge">
                                -{{ number_format($offer->discount_percentage, 0) }}%
                            </div>
                        @endif
                        <div class="flex flex-row">
                            <div class="relative w-28 h-28 overflow-hidden bg-gray-100 flex-shrink-0">
                                @if($offer->image)
                                    <img src="{{ $offer->image }}" alt="{{ $offer->title }}" class="w-full h-full object-cover" onerror="this.onerror=null; this.src='https://via.placeholder.com/112?text=🛍️';">
                                @else
                                    <div class="w-full h-28 bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center text-white text-4xl">
                                        🛍️
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 p-3 text-right flex flex-col justify-between">
                                <div>
                                    <h3 class="font-bold text-gray-800 text-base leading-tight line-clamp-1">{{ $offer->title ?: ($offer->name ?? 'عرض خاص') }}</h3>
                                    @if($offer->description)
                                        <p class="text-gray-500 text-xs mt-1 leading-relaxed line-clamp-2">{{ $offer->description }}</p>
                                    @endif
                                </div>
                                <div class="flex justify-between items-center mt-2">
                                    <div class="flex items-center gap-1 text-[11px] text-gray-500">
                                        <span class="text-amber-500">⏰</span>
                                        <span>{{ $offer->end_date ? 'ينتهي: ' . \Carbon\Carbon::parse($offer->end_date)->format('d M Y') : 'عرض مستمر' }}</span>
                                    </div>
                                    <button class="add-to-cart bg-purple-600 text-white text-xs px-3 py-1.5 rounded-full hover:bg-purple-700 transition" 
                                            data-id="{{ $offer->item_id }}" 
                                            data-name="{{ $offer->name ?? $offer->title ?? 'عرض' }}" 
                                            data-price="{{ $offer->new_price }}"
                                            data-image="{{ $offer->image ?? '' }}">
                                        إضافة للسلة
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 bg-white rounded-2xl border border-gray-100 shadow-sm">
                        <i class="fas fa-tag text-gray-300 text-4xl mb-3"></i>
                        <p class="text-gray-500">لا توجد عروض متاحة حالياً</p>
                    </div>
                @endforelse
            </div>
        </div>
        <br>
        <br>
       
    </div>

    <!-- Toast container -->
    <div id="cart-toast" class="fixed bottom-20 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-5 py-3 rounded-full shadow-lg text-sm z-50 transition-all duration-300 opacity-0 translate-y-10 pointer-events-none"></div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script>
        function showToast(msg, isError = false) {
            const toast = document.getElementById('cart-toast');
            if (!toast) return;
            toast.innerText = msg;
            toast.style.background = isError ? '#dc2626' : '#1f2937';
            toast.classList.remove('opacity-0', 'translate-y-10');
            toast.classList.add('opacity-100', 'translate-y-0');
            setTimeout(() => {
                toast.classList.remove('opacity-100', 'translate-y-0');
                toast.classList.add('opacity-0', 'translate-y-10');
            }, 2000);
        }

        document.querySelectorAll('.add-to-cart').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                let id = this.getAttribute('data-id');
                let name = this.getAttribute('data-name');
                let price = this.getAttribute('data-price');
                let image = this.getAttribute('data-image');
                
                let cartItems = JSON.parse(localStorage.getItem('maggadit_cart')) || [];
                let existingItemIndex = cartItems.findIndex(item => item.id == id);
                
                if (existingItemIndex > -1) {
                    cartItems[existingItemIndex].quantity += 1;
                } else {
                    cartItems.push({
                        id: id,
                        name: name,
                        price: price,
                        quantity: 1,
                        image: image,
                        discount: 0
                    });
                }
                
                localStorage.setItem('maggadit_cart', JSON.stringify(cartItems));
                showToast(`✅ تم إضافة "${name}" إلى السلة`);
            });
        });
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

        // ========== Favorite Toggle ==========
        document.querySelectorAll('.toggle-favorite-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const icon = this.querySelector('i');
                const productId = this.getAttribute('data-id');
                
                fetch('{{ route("customer.favorite.toggle") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ item_id: productId })
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        if(data.is_favorite) {
                            this.classList.remove('text-gray-400');
                            this.classList.add('text-red-500');
                            icon.classList.remove('far');
                            icon.classList.add('fas');
                        } else {
                            this.classList.remove('text-red-500');
                            this.classList.add('text-gray-400');
                            icon.classList.remove('fas');
                            icon.classList.add('far');
                        }
                    }
                })
                .catch(err => console.error(err));
            });
        });
    </script>
@endsection
