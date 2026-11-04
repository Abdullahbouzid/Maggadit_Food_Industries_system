@extends('app_android.partials.mobile.app_mobile')

@section('title', 'العروض الحصرية')

@section('content')
    @include('app_android.partials.mobile.header', ['title' => 'عروض حصرية'])

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
            transform: rotate(0deg);
        }
        .offer-badge::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 0 25px 25px 0;
            border-color: transparent rgba(0,0,0,0.1) transparent transparent;
        }
    </style>

    <div id="offers-container" class="p-4 space-y-4" dir="rtl">
        <div class="mb-4">
            <button onclick="window.history.back()" class="flex items-center gap-2 text-purple-600 bg-purple-50 px-4 py-2 rounded-full text-sm font-semibold transition hover:bg-purple-100">
                <i class="fas fa-arrow-right"></i>
                <span>رجوع</span>
            </button>
        </div>

        @forelse($offers as $offer)
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden offer-card">
                <div class="offer-badge">
                    -{{ $offer->discount_percentage ?? 0 }}%
                </div>
                <div class="bg-gradient-to-r from-amber-500 to-orange-600 p-3 text-white">
                    <br>
                    <div class="flex justify-between items-center">
                        <span class="text-sm">⏰ حتى {{ \Carbon\Carbon::parse($offer->end_date)->format('d M Y') }}</span>
                    </div>
                    <h3 class="font-bold text-lg mt-1"><a href="{{ $offer->item_id ? route('customer.product.show', $offer->item_id, false) : '#' }}" class="hover:underline">{{ $offer->title ?: ($offer->name ?? 'عرض') }}</a></h3>
                    @if($offer->description)
                        <p class="text-xs opacity-90">{{ $offer->description }}</p>
                    @endif
                </div>
                <div class="p-3 space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-20 h-20 bg-gray-100 rounded-xl flex items-center justify-center text-2xl overflow-hidden flex-shrink-0">
                            @if($offer->image)
                                <img src="{{ $offer->image }}" alt="{{ $offer->name }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-4xl">🛍️</span>
                            @endif
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-sm"><a href="{{ $offer->item_id ? route('customer.product.show', $offer->item_id, false) : '#' }}" class="hover:underline">{{ $offer->name }}</a></p>
                            @if($offer->pack_type)
                                <small class="text-muted">{{ $offer->pack_type }}</small>
                            @endif
                            <div>
                                <span class="text-purple-700 font-bold">{{ number_format($offer->new_price, 2) }} ر.س</span>
                                <span class="text-gray-400 text-xs line-through mr-1">{{ number_format($offer->old_price, 2) }} ر.س</span>
                            </div>
                        </div>
                        <button class="add-to-cart bg-purple-600 text-white text-xs px-3 py-2 rounded-full flex-shrink-0" 
                                data-id="{{ $offer->item_id }}" 
                                data-name="{{ $offer->name }}" 
                                data-price="{{ $offer->new_price }}"
                                data-image="{{ $offer->image ?? '' }}"
                                data-pack-id="{{ $offer->itemPack->id ?? '' }}"
                                data-pack-name="{{ $offer->pack_type ?? '' }}"
                                data-uom-name="{{ $offer->pack_type ?? '' }}">
                            إضافة إلى السلة
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-10">
                <i class="fas fa-tag text-gray-300 text-5xl mb-3"></i>
                <p class="text-gray-500">لا توجد عروض متاحة حالياً</p>
            </div>
        @endforelse
    </div>

    <!-- ============================================================
         CART TOAST NOTIFICATION — إشعار علوي احترافي بلون #9333ea
         ============================================================ -->
    <div id="cart-toast"
         class="cart-toast"
         role="status"
         aria-live="polite"
         dir="rtl">

        <div class="cart-toast__inner">

            <!-- الأيقونة -->
            <div class="cart-toast__icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <polyline class="cart-toast__check" points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>

            <!-- المحتوى -->
            <div class="cart-toast__body">
                <h4 class="cart-toast__title">تمت الإضافة بنجاح</h4>
                <p class="cart-toast__msg">تم إضافة المنتج إلى السلة</p>
            </div>

            <!-- زر الإغلاق -->
            <button type="button"
                    class="cart-toast__close"
                    id="cart-toast-close"
                    aria-label="إغلاق">
                <i class="fas fa-times"></i>
            </button>

            <!-- شريط التقدم -->
            <div class="cart-toast__progress"></div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        /* ============================================================
           CART TOAST NOTIFICATION — اللون الرئيسي #9333ea (بنفسجي)
           ============================================================ */

        .cart-toast {
            position: fixed;
            top: 16px;
            left: 50%;
            z-index: 999999;
            width: calc(100% - 28px);
            max-width: 430px;
            transform: translateX(-50%) translateY(-140%);
            opacity: 0;
            pointer-events: none;
            transition:
                transform .55s cubic-bezier(.34, 1.56, .64, 1),
                opacity   .3s  ease;
            direction: rtl;
            font-family: inherit;
            box-sizing: border-box;
        }

        .cart-toast.is-visible {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
            pointer-events: auto;
        }

        /* ---------- البطاقة الداخلية ---------- */
        .cart-toast__inner {
            position: relative;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px 14px 15px;
            border-radius: 18px;
            background: linear-gradient(135deg, #9333ea 0%, #6b21a8 100%);
            box-shadow:
                0 20px 40px -12px rgba(147, 51, 234, .55),
                0 8px 20px -8px rgba(0, 0, 0, .35),
                inset 0 1px 0 rgba(255, 255, 255, .25);
            color: #ffffff;
            overflow: hidden;
        }

        /* لمعة علوية */
        .cart-toast__inner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.55), transparent);
            pointer-events: none;
        }

        /* ---------- الأيقونة ---------- */
        .cart-toast__icon {
            position: relative;
            flex-shrink: 0;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .22);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,.3);
        }

        .cart-toast__icon::after {
            content: '';
            position: absolute;
            inset: -2px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, .6);
            opacity: 0;
        }
        .cart-toast.is-visible .cart-toast__icon::after {
            animation: cart-toast-ring 1.6s ease-out .35s 2;
        }
        @keyframes cart-toast-ring {
            0%   { transform: scale(.85); opacity: .85; }
            100% { transform: scale(1.55); opacity: 0; }
        }

        .cart-toast__icon svg {
            width: 22px;
            height: 22px;
            color: #ffffff;
            filter: drop-shadow(0 1px 1px rgba(0,0,0,.15));
        }

        .cart-toast__check {
            stroke-dasharray: 32;
            stroke-dashoffset: 32;
        }
        .cart-toast.is-visible .cart-toast__check {
            animation: cart-toast-check .5s cubic-bezier(.65, 0, .45, 1) .25s forwards;
        }
        @keyframes cart-toast-check {
            to { stroke-dashoffset: 0; }
        }

        /* ---------- المحتوى ---------- */
        .cart-toast__body {
            flex: 1;
            min-width: 0;
            text-align: right;
        }

        .cart-toast__title {
            margin: 0 0 2px;
            font-size: 14.5px;
            font-weight: 800;
            letter-spacing: -.2px;
            color: #ffffff;
            line-height: 1.3;
        }

        .cart-toast__msg {
            margin: 0;
            font-size: 12.5px;
            font-weight: 500;
            color: rgba(255, 255, 255, .92);
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ---------- زر الإغلاق ---------- */
        .cart-toast__close {
            flex-shrink: 0;
            width: 30px;
            height: 30px;
            border: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, .18);
            color: #ffffff;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color .2s ease, transform .2s ease;
            -webkit-tap-highlight-color: transparent;
        }
        .cart-toast__close:hover {
            background: rgba(255, 255, 255, .32);
            transform: rotate(90deg);
        }
        .cart-toast__close:active {
            transform: rotate(90deg) scale(.9);
        }

        /* ---------- شريط التقدم ---------- */
        .cart-toast__progress {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 3px;
            background: rgba(255, 255, 255, .22);
            overflow: hidden;
        }
        .cart-toast__progress::after {
            content: '';
            position: absolute;
            inset: 0;
            background: #ffffff;
            transform-origin: right center;
            transform: scaleX(0);
        }
        .cart-toast.is-visible .cart-toast__progress::after {
            animation: cart-toast-progress 3s linear forwards;
        }
        @keyframes cart-toast-progress {
            0%   { transform: scaleX(1); }
            100% { transform: scaleX(0); }
        }

        /* ============================================================
           📱 Landscape — ضبط الأبعاد للوضع الأفقي
           ============================================================ */
        @media (orientation: landscape) and (max-height: 500px) {
            .cart-toast {
                top: 10px;
                max-width: 560px;
                width: calc(100% - 40px);
            }
            .cart-toast__inner {
                padding: 9px 14px 12px;
                border-radius: 14px;
                gap: 10px;
            }
            .cart-toast__icon {
                width: 36px;
                height: 36px;
            }
            .cart-toast__icon svg { width: 18px; height: 18px; }
            .cart-toast__title { font-size: 13px; }
            .cart-toast__msg   { font-size: 11.5px; -webkit-line-clamp: 1; }
            .cart-toast__close { width: 26px; height: 26px; font-size: 11px; }
        }

        /* 📱 شاشات ضيقة جدًا (≤360px) */
        @media (max-width: 360px) {
            .cart-toast { width: calc(100% - 20px); }
            .cart-toast__inner { padding: 12px 12px 14px; gap: 10px; }
            .cart-toast__icon { width: 40px; height: 40px; }
            .cart-toast__title { font-size: 13.5px; }
            .cart-toast__msg   { font-size: 12px; }
        }

        /* ♿ تقليل الحركة */
        @media (prefers-reduced-motion: reduce) {
            .cart-toast, .cart-toast * {
                animation-duration: .001ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .001ms !important;
            }
        }
    </style>

    <script>
        // ============================================================
        // CART TOAST — JavaScript
        // ============================================================
        let cartToastTimer = null;

        function showCartToast(message, title = 'تمت الإضافة بنجاح') {
            const toast  = document.getElementById('cart-toast');
            if (!toast) return;

            const titleEl = toast.querySelector('.cart-toast__title');
            const msgEl   = toast.querySelector('.cart-toast__msg');

            if (titleEl) titleEl.textContent = title;
            if (msgEl)   msgEl.textContent   = message;

            // إعادة ضبط الأنيميشن
            toast.classList.remove('is-visible');
            void toast.offsetWidth; // force reflow
            toast.classList.add('is-visible');

            clearTimeout(cartToastTimer);
            cartToastTimer = setTimeout(hideCartToast, 3000);
        }

        function hideCartToast() {
            const toast = document.getElementById('cart-toast');
            if (!toast) return;
            clearTimeout(cartToastTimer);
            toast.classList.remove('is-visible');
        }

        // أزرار الإغلاق
        document.getElementById('cart-toast-close')?.addEventListener('click', hideCartToast);

        // إغلاق بـ Escape
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') hideCartToast();
        });

        // إيقاف الإغلاق التلقائي عند المرور
        const cartToastEl = document.getElementById('cart-toast');
        if (cartToastEl) {
            cartToastEl.addEventListener('mouseenter', () => clearTimeout(cartToastTimer));
            cartToastEl.addEventListener('mouseleave', () => {
                cartToastTimer = setTimeout(hideCartToast, 2000);
            });
        }

        // ============================================================
        // Event delegation لإضافة المنتج للسلة (يعمل مع العناصر الثابتة والديناميكية)
        // ============================================================
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.add-to-cart');
            if (!btn) return;

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

            // عرض الإشعار
            const displayName = name + (packName ? ' (' + packName + ')' : '');
            showCartToast(displayName + ' تمت إضافته إلى السلة', '✅ تمت الإضافة');
        });

        // ============================================================
        // Dynamic Proxy Fallback Fetcher for Offers
        // ============================================================
        const offersContainer = document.getElementById('offers-container');
        const customerProductBase = @json(rtrim(url('/'), '/'));
        const customerProductUrl = (id) => `${customerProductBase}/customer/product/${encodeURIComponent(id)}`;

        if (offersContainer && !offersContainer.querySelector('.offer-card')) {
            const apiToken = localStorage.getItem('api_token') || localStorage.getItem('customer_token') || '';
            const headers = {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            };
            if (apiToken) headers['Authorization'] = 'Bearer ' + apiToken;
            fetch('{{ route('customer.proxy.offers', [], false) }}', { headers })
                .then(res => res.json())
                .then(data => {
                    if (data && data.offers) {
                        const items = data.offers.data || data.offers;
                        if (Array.isArray(items) && items.length > 0) {
                            offersContainer.innerHTML = items.map(offer => `
                                <div class="bg-white rounded-2xl shadow-lg overflow-hidden offer-card">
                                    <div class="offer-badge">-${offer.discount_percentage || 0}%</div>
                                    <div class="bg-gradient-to-r from-amber-500 to-orange-600 p-3 text-white">
                                        <br>
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm">⏰ عرض خاص</span>
                                        </div>
                                        <h3 class="font-bold text-lg mt-1">${offer.item_id ? `<a href="${customerProductUrl(offer.item_id)}" class="hover:underline">${offer.title || offer.name || 'عرض'}</a>` : (offer.title || offer.name || 'عرض')}</h3>
                                        ${offer.description ? `<p class="text-xs opacity-90">${offer.description}</p>` : ''}
                                    </div>
                                    <div class="p-3 space-y-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-20 h-20 bg-gray-100 rounded-xl flex items-center justify-center text-2xl overflow-hidden flex-shrink-0">
                                                ${offer.image ? `<img src="${offer.image}" alt="${offer.name || ''}" class="w-full h-full object-cover" onerror="this.outerHTML='<span class=\\'text-4xl\\'>🛍️</span>'">` : '<span class="text-4xl">🛍️</span>'}
                                            </div>
                                            <div class="flex-1">
                                                <p class="font-semibold text-sm">${offer.name || offer.title || 'عرض'}</p>
                                                <div>
                                                    <span class="text-purple-700 font-bold">${parseFloat(offer.new_price || 0).toFixed(2)} ر.س</span>
                                                    ${offer.old_price ? `<span class="text-gray-400 text-xs line-through mr-1">${parseFloat(offer.old_price).toFixed(2)} ر.س</span>` : ''}
                                                </div>
                                            </div>
                                            <button class="add-to-cart bg-purple-600 text-white text-xs px-3 py-2 rounded-full flex-shrink-0" 
                                                    data-id="${offer.item_id || offer.id}" 
                                                    data-name="${offer.name || offer.title || 'عرض'}" 
                                                    data-price="${offer.new_price || 0}"
                                                    data-image="${offer.image || ''}"
                                                    data-pack-id="${offer.pack_id || ''}"
                                                    data-pack-name="${offer.pack_name || offer.pack_type || ''}"
                                                    data-uom-name="${offer.uom_name || offer.pack_type || ''}">
                                                إضافة إلى السلة
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            `).join('');
                            // ملاحظة: لم نعد بحاجة لإعادة ربط الأحداث — Event delegation في الأعلى يتولى ذلك تلقائيًا
                        }
                    }
                })
                .catch(err => console.error("Offers proxy error:", err));
        }
    </script>
@endsection