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
                <a href="{{ route('customer.categories.index', [], false) }}" class="text-xs text-purple-600 font-semibold">عرض الكل</a>
            </div>
            <div id="dash-categories" class="flex overflow-x-auto space-x-3 space-x-reverse pb-2 hide-scrollbar">
                @forelse($categories as $category)
                    <div class="flex-shrink-0 text-center w-20 cursor-pointer" onclick="window.location='{{ route('customer.categories.show', $category->id, false) }}'">
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
                <a href="{{ route('customer.brands', [], false) }}" class="text-xs text-purple-600 font-semibold">عرض الكل</a>
            </div>
            <div id="dash-brands" class="flex overflow-x-auto space-x-3 space-x-reverse pb-2 hide-scrollbar">
                @forelse($brands as $brand)
                    <div class="flex-shrink-0 text-center w-24 cursor-pointer" onclick="window.location='{{ route('customer.brands.show', $brand->id, false) }}'">
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
                <a href="{{ route('customer.products', [], false) }}" class="text-xs text-purple-600 font-semibold">عرض الكل</a>
            </div>
            <div id="dash-products" class="grid grid-cols-2 gap-4">
                @forelse($productCards ?? $products as $product)
                    @php
                        $productId = is_array($product) ? ($product['id'] ?? 0) : $product->id;
                        $productName = is_array($product) ? ($product['name'] ?? 'منتج') : ($product->name ?? $product->name_en ?? 'منتج');
                        $productImage = is_array($product) ? ($product['image'] ?? '') : ($product->image ?? '');
                        $productPrice = is_array($product) ? ($product['price'] ?? 0) : ($product->price ?? 0);
                        $packName = is_array($product) ? ($product['pack_name'] ?? '') : '';
                   @endphp
                    <a href="{{ route('customer.product.show', $productId, false) }}" class="block bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100 transition hover:shadow-lg relative cursor-pointer">
                        @if($packName)
                            <div class="absolute top-2 right-2 z-10">
                                <span class="bg-purple-600 text-white text-[10px] px-2 py-1 rounded-full font-semibold">
                                    {{ $packName }}
                                </span>
                            </div>
                        @endif
                        
                        <div class="h-32 bg-gray-100 flex items-center justify-center overflow-hidden">
                            @if($productImage)
                                <img src="{{ $productImage }}" alt="{{ $productName }}" class="w-full h-full object-cover" onerror="this.onerror=null; this.src='https://via.placeholder.com/150?text=📦';">
                            @else
                                <span class="text-5xl text-gray-400">📦</span>
                            @endif
                        </div>
                        <div class="p-3">
                            <h3 class="font-bold text-gray-800 text-sm line-clamp-1">{{ $productName }}</h3>
                            @if($packName)
                                <span class="inline-block mt-1 bg-gray-100 text-gray-700 text-[10px] px-2 py-0.5 rounded-full">
                                    {{ $packName }}
                                </span>
                            @endif
                            <div class="mt-2 flex items-center justify-between">
                                <div>
                                    <span class="text-purple-700 font-bold text-sm">{{ number_format($productPrice, 2) }} ر.س</span>
                                </div>
                                <button class="bg-purple-600 text-white text-xs px-3 py-1.5 rounded-full hover:bg-purple-700 transition add-to-cart" 
                                        data-id="{{ $productId }}"
                                        data-name="{{ $productName }}" 
                                        data-price="{{ $productPrice }}"
                                        data-image="{{ $productImage ?? '' }}"
                                        data-pack-name="{{ $packName }}"
                                        data-uom-name="">إضافة</button>
                            </div>
                        </div>
                    </a>
                @empty
                    <p class="text-center text-gray-500 col-span-2 py-4">لا توجد منتجات لعرضها</p>
                @endforelse
            </div>
        </div>

        {{-- ========== قسم العروض (Offers) مع صور ========== --}}
        <div>
            <div class="flex justify-between items-center mb-3">
                <h2 class="text-lg font-bold text-gray-800">عروض حصرية</h2>
                <a href="{{ route('customer.offers', [], false) }}" class="text-xs text-purple-600 font-semibold">عرض الكل</a>
            </div>
            <div id="dash-offers" class="space-y-4">
                @forelse($offers ?? [] as $offer)
                    @if(!empty($offer->item_id))
                    <a href="{{ route('customer.product.show', $offer->item_id, false) }}" class="block bg-white rounded-2xl shadow-lg overflow-hidden offer-card border border-gray-100 cursor-pointer">
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
                                    @if(!empty($offer->pack_name))
                                        <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                                            <span class="bg-purple-100 text-purple-700 text-[11px] px-2 py-0.5 rounded-full font-medium">{{ $offer->pack_name }}</span>
                                            @if(!empty($offer->pack_quantity))
                                                <span class="text-[11px] text-gray-500">السعة: {{ (float)$offer->pack_quantity }} قطعة</span>
                                            @endif
                                        </div>
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
                                        data-image="{{ $offer->image ?? '' }}"
                                        data-pack-id="{{ $offer->item_pack_id ?? '' }}"
                                        data-pack-name=""
                                        data-uom-name="{{ $offer->pack_type ?? '' }}">
                                    إضافة للسلة
                                </button>
                            </div>
                        </div>
                    </div>
                    </a>
                    @else
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden offer-card border border-gray-100">
                        @if($offer->discount_percentage > 0)
                            <div class="offer-badge">-{{ number_format($offer->discount_percentage, 0) }}%</div>
                        @endif
                        <div class="p-3">
                            <h3 class="font-bold text-base text-gray-800">{{ $offer->title ?: ($offer->name ?? 'عرض خاص') }}</h3>
                        </div>
                    </div>
                    @endif
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

    <!-- Toast container (bottom) -->
    <div id="cart-toast" class="fixed bottom-20 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-5 py-3 rounded-full shadow-lg text-sm z-50 transition-all duration-300 opacity-0 translate-y-10 pointer-events-none"></div>

    <!-- Centered popup for Add to Cart confirmation (matches logout modal style) -->
    <div id="add-success-popup" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[99999] opacity-0 pointer-events-none transition-opacity duration-300" style="direction: rtl;">
        <div class="bg-white rounded-2xl w-80 max-w-[85%] p-6 transform scale-95 transition-all duration-300 shadow-2xl" style="z-index: 100000;">
            <div class="text-center">
                <div class="w-16 h-16 mx-auto bg-green-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-check text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">تمت الإضافة للسلة!</h3>
                <p id="add-success-msg" class="text-gray-500 text-sm mb-6"></p>
            </div>
        </div>
    </div></div>

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
        // Show centered popup for Add to Cart confirmation
        function showAddSuccess(msg) {
            const popup = document.getElementById('add-success-popup');
            const msgEl = document.getElementById('add-success-msg');
            if (!popup || !msgEl) return;
            msgEl.innerText = msg;
            popup.classList.remove('opacity-0', 'pointer-events-none');
            popup.classList.add('opacity-100');
            const inner = popup.querySelector('.bg-white');
            inner.classList.remove('scale-95');
            inner.classList.add('scale-100');
            setTimeout(() => {
                popup.classList.remove('opacity-100');
                popup.classList.add('opacity-0', 'pointer-events-none');
                inner.classList.remove('scale-100');
                inner.classList.add('scale-95');
            }, 2000);
        }

        // Event delegation for dynamically loaded add-to-cart buttons
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.add-to-cart');
            if (btn) {
                e.preventDefault();
                e.stopPropagation();
                let id = btn.getAttribute('data-id');
                let name = btn.getAttribute('data-name');
                let price = parseFloat(btn.getAttribute('data-price')) || 0;
                let image = btn.getAttribute('data-image');

                let packName = btn.getAttribute('data-pack-name') || '';
                let uomName = btn.getAttribute('data-uom-name') || '';
                let packId = btn.getAttribute('data-pack-id') || null;
                
                let cartItems = JSON.parse(localStorage.getItem('maggadit_cart')) || [];
                let existingItemIndex = cartItems.findIndex(item => item.id == id && item.pack_name == packName);
                
                if (existingItemIndex > -1) {
                    cartItems[existingItemIndex].quantity += 1;
                } else {
                    cartItems.push({
                        id: id,
                        name: name,
                        pack_name: packName,
                        uom_name: uomName,
                        price: price,
                        quantity: 1,
                        image: image,
                        discount: 0,
                        pack_id: packId
                    });
                }
                
                localStorage.setItem('maggadit_cart', JSON.stringify(cartItems));
                updateCartCount();
                let displayText = packName ? `${name} (${packName})` : name;
                showAddSuccess(displayText);
            }
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
                    home: "{{ route('customer.dashboard', [], false) }}",
                    offers: "{{ route('customer.offers', [], false) }}",
                    more: "{{ route('customer.more', [], false) }}"
                };
                if (routes[target]) {
                    if (target === 'home') return;
                    window.location.href = routes[target];
                }
            });
        });

        // ========== Favorite Toggle (Event Delegation for Dynamic Elements) ==========
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.toggle-favorite-btn');
            if (!btn) return;
            
            e.preventDefault();
            e.stopPropagation();
            const icon = btn.querySelector('i');
            const productId = btn.getAttribute('data-id');
            
            fetch('{{ route('customer.proxy.favorites.toggle', [], false) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'include',
                body: JSON.stringify({ item_id: productId })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    if(data.is_favorite) {
                        btn.classList.remove('text-gray-400');
                        btn.classList.add('text-red-500');
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                    } else {
                        btn.classList.remove('text-red-500');
                        btn.classList.add('text-gray-400');
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                    }
                }
            })
            .catch(err => console.error(err));
        });

        // Dynamic Proxy Fallback Fetcher for Dashboard Categories
        const dashCategories = document.getElementById('dash-categories');
        if (dashCategories && dashCategories.children.length <= 1 && dashCategories.innerText.includes('لا توجد')) {
            fetch('{{ route('customer.proxy.categories', [], false) }}')
                .then(r => r.json())
                .then(data => {
                    if (data && data.categories && data.categories.length > 0) {
                        dashCategories.innerHTML = data.categories.map(c => `
                            <div class="flex-shrink-0 text-center w-20 cursor-pointer" onclick="window.location='/customer/categories/${c.id}'">
                                <div class="w-16 h-16 mx-auto rounded-full overflow-hidden shadow-sm transition hover:scale-105 bg-gray-100 flex items-center justify-center border border-gray-100">
                                    ${c.image ? `<img src="${c.image}" alt="${c.name}" class="w-full h-full object-cover" onerror="this.onerror=null; this.src='https://via.placeholder.com/64?text=📁';">` : '<span class="text-2xl">📁</span>'}
                                </div>
                                <p class="text-xs mt-1 text-gray-700 font-medium truncate">${c.name}</p>
                            </div>
                        `).join('');
                    }
                }).catch(e => console.error(e));
        }

        // Dynamic Proxy Fallback Fetcher for Dashboard Brands
        const dashBrands = document.getElementById('dash-brands');
        if (dashBrands && dashBrands.children.length <= 1 && dashBrands.innerText.includes('لا توجد')) {
            fetch('{{ route('customer.proxy.brands', [], false) }}')
                .then(r => r.json())
                .then(data => {
                    if (data && data.brands && data.brands.length > 0) {
                        dashBrands.innerHTML = data.brands.map(b => `
                            <div class="flex-shrink-0 text-center w-24 cursor-pointer" onclick="window.location='/customer/brands/${b.id}'">
                                <div class="w-20 h-20 rounded-2xl overflow-hidden shadow-sm transition hover:shadow-md bg-gray-100 flex items-center justify-center border border-gray-100">
                                    ${b.logo ? `<img src="${b.logo}" alt="${b.name}" class="w-full h-full object-contain p-2" onerror="this.onerror=null; this.src='https://via.placeholder.com/80?text=🏷️';">` : '<span class="text-3xl">🏷️</span>'}
                                </div>
                                <p class="text-xs mt-1 font-semibold text-gray-800 truncate">${b.name}</p>
                            </div>
                        `).join('');
                    }
                }).catch(e => console.error(e));
        }

        // Dynamic Proxy Fallback Fetcher for Dashboard Products
        const dashProducts = document.getElementById('dash-products');
        if (dashProducts && dashProducts.children.length <= 1 && dashProducts.innerText.includes('لا توجد')) {
            fetch('{{ route('customer.proxy.products', [], false) }}')
                .then(r => r.json())
                .then(data => {
                    if (data && data.products) {
                        const items = data.products.data || data.products;
                        if (Array.isArray(items) && items.length > 0) {
                            dashProducts.innerHTML = items.slice(0, 10).map(p => {
                                // Get pack details - use first pack or default
                                const packs = p.packs || [];
                                const defaultPack = packs.find(pack => pack.is_default) || packs[0] || {};
                                const packName = defaultPack.name || defaultPack.display_name || '';
                                const uomName = defaultPack.uom_name || defaultPack.uom_symbol || '';
                                const packQuantity = defaultPack.quantity || 1;
                                
                                return `
                                <div class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100 transition hover:shadow-lg relative cursor-pointer" onclick="window.location='/customer/product/${p.id}'">
                                    <button class="absolute top-2 left-2 z-10 p-1.5 rounded-full bg-white/80 backdrop-blur-sm shadow-sm toggle-favorite-btn transition-colors ${p.is_favorite ? 'text-red-500' : 'text-gray-400'}" data-id="${p.id}">
                                        <i class="${p.is_favorite ? 'fas' : 'far'} fa-heart text-lg"></i>
                                    </button>
                                    <div class="h-32 bg-gray-100 flex items-center justify-center overflow-hidden">
                                        ${p.image ? `<img src="${p.image}" alt="${p.name || ''}" class="w-full h-full object-cover" onerror="this.onerror=null; this.src='https://via.placeholder.com/150?text=📦';">` : '<span class="text-5xl text-gray-400">📦</span>'}
                                    </div>
                                    <div class="p-3">
                                        <h3 class="font-bold text-gray-800 text-sm line-clamp-1">${p.name || p.name_ar || 'منتج'}</h3>
                                        <div class="flex items-center gap-1 mt-1">
                                            <span class="text-yellow-500 text-xs">★</span>
                                            <span class="text-xs text-gray-600">${parseFloat(p.rate || 0).toFixed(1)}</span>
                                        </div>
                                        ${packName ? `
                                        <span class="inline-block mt-1 bg-purple-50 text-purple-700 text-[10px] px-2 py-0.5 rounded-full">
                                            ${packName}
                                            ${uomName ? ` ${uomName}` : ''}
                                        </span>` : ''}
                                        <div class="mt-2 flex items-center justify-between">
                                            <div>
                                                <span class="text-purple-700 font-bold text-sm">${parseFloat(p.price || 0).toFixed(2)} ر.س</span>
                                                ${p.old_price ? `<span class="text-gray-400 text-xs line-through block">${parseFloat(p.old_price).toFixed(2)} ر.س</span>` : ''}
                                            </div>
                                            <button class="bg-purple-600 text-white text-xs px-3 py-1.5 rounded-full hover:bg-purple-700 transition add-to-cart" 
                                                    data-id="${p.id}"
                                                    data-name="${p.name || p.name_ar || 'منتج'}" 
                                                    data-price="${p.price || 0}"
                                                    data-image="${p.image || ''}"
                                                    data-pack-name="${packName}"
                                                    data-uom-name="${uomName}"
                                                    data-pack-id="${defaultPack.id || ''}">إضافة</button>
                                        </div>
                                    </div>
                                </div>
                                `;
                            }).join('');
                        }
                    }
                }).catch(e => console.error(e));
        }

        // Dynamic Proxy Fallback Fetcher for Dashboard Offers
        const dashOffers = document.getElementById('dash-offers');
        if (dashOffers && dashOffers.children.length <= 1 && dashOffers.innerText.includes('لا توجد')) {
            fetch('{{ route('customer.proxy.offers', [], false) }}')
                .then(r => r.json())
                .then(data => {
                    if (data && data.offers) {
                        const items = data.offers.data || data.offers;
                        if (Array.isArray(items) && items.length > 0) {
                            dashOffers.innerHTML = items.map(o => `
                                <div class="bg-white rounded-2xl shadow-lg overflow-hidden offer-card border border-gray-100 cursor-pointer" onclick="window.location='/customer/product/${o.item_id || o.id}'">
                                    ${o.discount_percentage ? `<div class="offer-badge">-${parseFloat(o.discount_percentage).toFixed(0)}%</div>` : ''}
                                    <div class="flex flex-row">
                                        <div class="relative w-28 h-28 overflow-hidden bg-gray-100 flex-shrink-0">
                                            ${o.image ? `<img src="${o.image}" alt="${o.name || ''}" class="w-full h-full object-cover" onerror="this.onerror=null; this.src='https://via.placeholder.com/112?text=🛍️';">` : '<div class="w-full h-28 bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center text-white text-4xl">🛍️</div>'}
                                        </div>
                                        <div class="flex-1 p-3 text-right flex flex-col justify-between">
                                            <div>
                                                <h3 class="font-bold text-gray-800 text-base leading-tight line-clamp-1">${o.title || o.name || 'عرض خاص'}</h3>
                                                ${o.description ? `<p class="text-gray-500 text-xs mt-1 leading-relaxed line-clamp-2">${o.description}</p>` : ''}
                                            </div>
                                            <div class="flex justify-between items-center mt-2">
                                                <div class="flex items-center gap-1 text-[11px] text-gray-500">
                                                    <span class="text-amber-500">⏰</span>
                                                    <span>عرض مستمر</span>
                                                </div>
                                                <button class="add-to-cart bg-purple-600 text-white text-xs px-3 py-1.5 rounded-full hover:bg-purple-700 transition" 
                                                        data-id="${o.item_id || o.id}" 
                                                        data-name="${o.name || o.title || 'عرض'}" 
                                                        data-price="${o.new_price || 0}"
                                                        data-image="${o.image || ''}">إضافة للسلة</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `).join('');
                        }
                    }
                }).catch(e => console.error(e));
        }

        // Dynamic Proxy Fallback Fetcher for Dashboard Banners
        const adCarousel = document.getElementById('ad-carousel');
        if (adCarousel && adCarousel.querySelector('.bg-gray-200')) {
            fetch('{{ route('customer.proxy.dashboard', [], false) }}')
                .then(r => r.json())
                .then(data => {
                    if (data && data.banners && data.banners.length > 0) {
                        const carousel = document.getElementById('ad-carousel');
                        carousel.innerHTML = data.banners.map(banner => `
                            <div class="flex-shrink-0 w-full snap-start">
                                <a href="${banner.url || '#'}" class="block w-full h-40 mx-1 rounded-2xl overflow-hidden">
                                    <img src="${banner.image || 'https://via.placeholder.com/400x160?text=📢'}" alt="Banner" class="w-full h-full object-cover">
                                </a>
                            </div>
                        `).join('');
                        
                        const dotsContainer = document.getElementById('ad-dots');
                        if (dotsContainer) {
                            dotsContainer.innerHTML = data.banners.map((_, i) => `
                                <div class="w-2 h-2 rounded-full bg-gray-300 transition-all duration-300 ${i === 0 ? 'w-4 bg-purple-600' : ''}" data-index="${i}"></div>
                            `).join('');
                        }
                    }
                }).catch(e => console.error(e));
        }
    </script>
@endsection
