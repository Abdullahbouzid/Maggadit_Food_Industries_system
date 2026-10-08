@extends('app_android.partials.mobile.app_mobile')

@section('title', $product->name ?? 'منتج')

@section('content')
    @include('app_android.partials.mobile.header', ['title' => $product->name ?? 'تفاصيل المنتج'])

    <style>
        /* ============================================================
           PACKS SECTION — تصميم بطاقات عصرية
           ============================================================ */
        .packs-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .pack-card {
            position: relative;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 13px;
            background: #ffffff;
            border: 1.5px solid #f1f5f9;
            border-radius: 16px;
            transition: all .25s cubic-bezier(.4,0,.2,1);
            overflow: hidden;
        }

        .pack-card:hover {
            border-color: #d8b4fe;
            box-shadow: 0 8px 20px -10px rgba(147, 51, 234, .35);
            transform: translateY(-1px);
        }

        .pack-card--active {
            border-color: #9333ea;
            background: linear-gradient(135deg, #faf5ff 0%, #ffffff 100%);
            box-shadow: 0 8px 20px -10px rgba(147, 51, 234, .4);
        }

        /* شريط جانبي بنفسجي عند التحديد */
        .pack-card--active::before {
            content: '';
            position: absolute;
            top: 12px;
            bottom: 12px;
            right: 0;
            width: 3px;
            background: linear-gradient(180deg, #9333ea, #6b21a8);
            border-radius: 3px 0 0 3px;
        }

        /* رقم ترتيبي */
        .pack-card__num {
            flex-shrink: 0;
            width: 36px;
            height: 36px;
            border-radius: 12px;
            background: linear-gradient(135deg, #9333ea 0%, #6b21a8 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 800;
            box-shadow: 0 6px 12px -4px rgba(147, 51, 234, .5);
        }

        .pack-card__body {
            flex: 1;
            min-width: 0;
        }

        .pack-card__name {
            font-size: 14px;
            font-weight: 800;
            color: #1e293b;
            margin: 0 0 4px;
            line-height: 1.3;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .pack-card__meta {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
            font-size: 11px;
            color: #64748b;
        }

        .pack-card__chip {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            padding: 2px 8px;
            border-radius: 999px;
            font-weight: 600;
            font-size: 10.5px;
            line-height: 1.4;
        }

        .pack-card__chip--uom {
            background: #f3e8ff;
            color: #7e22ce;
        }

        .pack-card__chip--qty {
            background: #f1f5f9;
            color: #475569;
        }

        .pack-card__price {
            font-size: 15px;
            font-weight: 800;
            color: #9333ea;
            white-space: nowrap;
        }

        .pack-card__price small {
            font-size: 10.5px;
            color: #94a3b8;
            font-weight: 500;
            margin-right: 2px;
        }

        /* زر الإضافة داخل البطاقة */
        .pack-card__btn {
            flex-shrink: 0;
            width: 42px;
            height: 42px;
            border-radius: 14px;
            border: 0;
            background: linear-gradient(135deg, #9333ea 0%, #6b21a8 100%);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            cursor: pointer;
            transition: all .2s ease;
            box-shadow: 0 6px 14px -6px rgba(147, 51, 234, .6);
            -webkit-tap-highlight-color: transparent;
        }

        .pack-card__btn:hover {
            transform: scale(1.06);
            box-shadow: 0 8px 18px -6px rgba(147, 51, 234, .8);
        }

        .pack-card__btn:active {
            transform: scale(.94);
        }

        /* شارة "الأكثر طلبًا" */
        .pack-card__badge {
            position: absolute;
            top: -1px;
            left: 12px;
            font-size: 9.5px;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 0 0 8px 8px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #ffffff;
            letter-spacing: .3px;
            box-shadow: 0 2px 6px rgba(245, 158, 11, .4);
        }

        /* البطاقة القياسية (الوحدة المفردة) */
        .pack-standard {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 16px;
            border-radius: 18px;
            background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
            border: 1.5px solid #e9d5ff;
            position: relative;
            overflow: hidden;
        }

        .pack-standard::before {
            content: '';
            position: absolute;
            top: -20px;
            left: -20px;
            width: 80px;
            height: 80px;
            background: #c084fc;
            border-radius: 50%;
            filter: blur(30px);
            opacity: .35;
            pointer-events: none;
        }

        .pack-standard__info {
            flex: 1;
            min-width: 0;
            position: relative;
        }

        .pack-standard__label {
            font-size: 12.5px;
            font-weight: 700;
            color: #6b21a8;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 2px;
        }

        .pack-standard__price {
            font-size: 20px;
            font-weight: 900;
            color: #9333ea;
            letter-spacing: -.5px;
        }

        .pack-standard__price small {
            font-size: 12px;
            color: #a855f7;
            font-weight: 600;
            margin-right: 3px;
        }

        .pack-standard__btn {
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            border: 0;
            border-radius: 14px;
            background: linear-gradient(135deg, #9333ea 0%, #6b21a8 100%);
            color: #ffffff;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
            transition: all .2s ease;
            box-shadow: 0 8px 18px -8px rgba(147, 51, 234, .65);
            -webkit-tap-highlight-color: transparent;
        }

        .pack-standard__btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 24px -10px rgba(147, 51, 234, .85);
        }

        .pack-standard__btn:active {
            transform: scale(.96);
        }

        /* عنوان القسم */
        .packs-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 12px;
        }

        .packs-title__icon {
            width: 28px;
            height: 28px;
            border-radius: 9px;
            background: linear-gradient(135deg, #9333ea, #6b21a8);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            box-shadow: 0 4px 10px -3px rgba(147, 51, 234, .55);
        }

        .packs-title__count {
            margin-right: auto;
            font-size: 11px;
            font-weight: 600;
            color: #9333ea;
            background: #f3e8ff;
            padding: 3px 9px;
            border-radius: 999px;
        }

        @media (max-width: 360px) {
            .pack-card__num { width: 32px; height: 32px; font-size: 12px; }
            .pack-card__name { font-size: 13px; }
            .pack-card__price { font-size: 14px; }
            .pack-card__btn { width: 38px; height: 38px; font-size: 13px; }
            .pack-standard__price { font-size: 18px; }
            .pack-standard__btn { padding: 9px 13px; font-size: 12px; }
        }
    </style>

    <div class="p-4" dir="rtl">
        <div class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100 p-4">

            <!-- صورة المنتج -->
            <div class="h-64 bg-gray-100 flex items-center justify-center overflow-hidden mb-4 relative rounded-2xl">
                @if($product->image)
                    <img src="{{ $product->image }}" class="w-full h-full object-cover" alt="{{ $product->name }}" id="main-product-image">
                @else
                    <span class="text-5xl text-gray-400">📦</span>
                @endif
                @if(isset($product->images) && count($product->images) > 1)
                    <div class="absolute bottom-2 left-1/2 transform -translate-x-1/2 flex gap-1" id="image-thumbnails">
                        @foreach($product->images as $index => $img)
                            <button type="button" 
                                    onclick="document.getElementById('main-product-image').src='{{ $img }}'"
                                    class="w-10 h-10 rounded overflow-hidden border-2 {{ $index === 0 ? 'border-purple-500' : 'border-transparent' }} hover:border-purple-300 transition"
                                    aria-label="صورة {{ $index + 1 }}">
                                <img src="{{ $img }}" class="w-full h-full object-cover" alt="صورة {{ $index + 1 }}">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- معلومات المنتج -->
            <h3 class="font-bold text-gray-800 text-xl mb-2">{{ $product->name }}</h3>
            <div class="flex items-baseline gap-2 mb-2">
                <p class="text-purple-700 font-extrabold text-2xl">{{ number_format($product->price) }} <span class="text-sm font-bold">ر.س</span></p>
                @if(isset($product->old_price) && $product->old_price)
                    <p class="text-gray-400 line-through text-sm">{{ number_format($product->old_price) }} ر.س</p>
                @endif
            </div>
            @if($product->description)
                <p class="text-gray-600 text-sm mb-4 leading-relaxed">{{ $product->description }}</p>
            @endif

            {{-- ============================================================
                 قسم العبوات — بطاقات عصرية
                 ============================================================ --}}
            @if(isset($product->packs) && count($product->packs) > 0)
                <div class="mb-5">
                    <!-- عنوان القسم -->
                    <div class="packs-title">
                        <span class="packs-title__icon"><i class="fas fa-boxes"></i></span>
                        <span>العبوات المتاحة</span>
                        <span class="packs-title__count">{{ count($product->packs) }} خيار</span>
                    </div>

                    <!-- شبكة البطاقات -->
                    <div class="packs-grid">
                        @foreach($product->packs as $index => $pack)
                            <div class="pack-card {{ $index === 0 ? 'pack-card--active' : '' }}">
                                @if($index === 0 && count($product->packs) > 1)
                                    <span class="pack-card__badge">الأكثر طلبًا</span>
                                @endif

                                <!-- رقم العبوة -->
                                <div class="pack-card__num">{{ $index + 1 }}</div>

                                <!-- التفاصيل -->
                                <div class="pack-card__body">
                                    <h4 class="pack-card__name">{{ $pack['name'] }}</h4>
                                    <div class="pack-card__meta">
                                        @if(!empty($pack['uom_name']))
                                            <span class="pack-card__chip pack-card__chip--uom">
                                                <i class="fas fa-ruler"></i>
                                                {{ $pack['uom_name'] }}
                                            </span>
                                        @endif
                                        @if(!empty($pack['quantity']))
                                            <span class="pack-card__chip pack-card__chip--qty">
                                                <i class="fas fa-cubes"></i>
                                                {{ (float)$pack['quantity'] }} قطعة
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- السعر -->
                                <div class="pack-card__price">
                                    {{ number_format($pack['price'], 2) }}
                                    <small>ر.س</small>
                                </div>

                                <!-- زر الإضافة -->
                                <button class="pack-card__btn add-to-cart-pack"
                                        data-id="{{ $product->id }}"
                                        data-pack-id="{{ $pack['id'] }}"
                                        data-name="{{ $product->name }}"
                                        data-pack-name="{{ $pack['name'] }}"
                                        data-uom-name="{{ $pack['uom_name'] ?? '' }}"
                                        data-price="{{ $pack['price'] }}"
                                        data-image="{{ $product->image ?? '📦' }}"
                                        aria-label="إضافة {{ $pack['name'] }} إلى السلة">
                                    <i class="fas fa-cart-plus"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                {{-- الوحدة القياسية --}}
                <div class="mb-5">
                    <div class="pack-standard">
                        <div class="pack-standard__info">
                            <div class="pack-standard__label">
                                <i class="fas fa-cube"></i>
                                <span>الوحدة القياسية</span>
                            </div>
                            <div class="pack-standard__price">
                                {{ number_format($product->price, 2) }} <small>ر.س</small>
                            </div>
                        </div>
                        <button class="pack-standard__btn add-to-cart-pack"
                                data-id="{{ $product->id }}"
                                data-pack-id=""
                                data-name="{{ $product->name }}"
                                data-pack-name="الوحدة القياسية"
                                data-uom-name=""
                                data-price="{{ $product->price }}"
                                data-image="{{ $product->image ?? '📦' }}">
                            <i class="fas fa-cart-plus"></i>
                            <span>أضف للسلة</span>
                        </button>
                    </div>
                </div>
            @endif

            <!-- زر المفضلة -->
            <button class="toggle-favorite-btn mt-2 w-full flex items-center justify-center py-3 rounded-xl border border-gray-300 transition text-sm font-semibold {{ in_array($product->id, $favoriteItemIds ?? []) ? 'text-red-500 border-red-200 bg-red-50' : 'text-gray-600 hover:bg-gray-50' }}"
                    data-id="{{ $product->id }}">
                <i class="{{ in_array($product->id, $favoriteItemIds ?? []) ? 'fas' : 'far' }} fa-heart"></i>
                <span class="mr-2">{{ in_array($product->id, $favoriteItemIds ?? []) ? 'إزالة من المفضلة' : 'إضافة إلى المفضلة' }}</span>
            </button>
        </div>
    </div>

    <!-- ============================================================
         CART TOAST NOTIFICATION
         ============================================================ -->
    <div id="cart-toast" class="cart-toast" role="status" aria-live="polite" dir="rtl">
        <div class="cart-toast__inner">
            <div class="cart-toast__icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <polyline class="cart-toast__check" points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <div class="cart-toast__body">
                <h4 class="cart-toast__title">تمت الإضافة بنجاح</h4>
                <p class="cart-toast__msg">تم إضافة المنتج إلى السلة</p>
            </div>
            <button type="button" class="cart-toast__close" id="cart-toast-close" aria-label="إغلاق">
                <i class="fas fa-times"></i>
            </button>
            <div class="cart-toast__progress"></div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        /* CART TOAST — بلون #9333ea */
        .cart-toast {
            position: fixed; top: 16px; left: 50%;
            z-index: 999999;
            width: calc(100% - 28px); max-width: 430px;
            transform: translateX(-50%) translateY(-140%);
            opacity: 0; pointer-events: none;
            transition: transform .55s cubic-bezier(.34,1.56,.64,1), opacity .3s ease;
            direction: rtl; font-family: inherit; box-sizing: border-box;
        }
        .cart-toast.is-visible {
            transform: translateX(-50%) translateY(0);
            opacity: 1; pointer-events: auto;
        }
        .cart-toast__inner {
            position: relative; display: flex; align-items: center; gap: 12px;
            padding: 13px 14px 15px; border-radius: 18px;
            background: linear-gradient(135deg, #9333ea 0%, #6b21a8 100%);
            box-shadow: 0 20px 40px -12px rgba(147,51,234,.55),
                        0 8px 20px -8px rgba(0,0,0,.35),
                        inset 0 1px 0 rgba(255,255,255,.25);
            color: #ffffff; overflow: hidden;
        }
        .cart-toast__inner::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.55), transparent);
            pointer-events: none;
        }
        .cart-toast__icon {
            position: relative; flex-shrink: 0; width: 44px; height: 44px;
            border-radius: 50%; background: rgba(255,255,255,.22);
            display: flex; align-items: center; justify-content: center;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,.3);
        }
        .cart-toast__icon::after {
            content: ''; position: absolute; inset: -2px;
            border-radius: 50%; border: 2px solid rgba(255,255,255,.6); opacity: 0;
        }
        .cart-toast.is-visible .cart-toast__icon::after { animation: cart-toast-ring 1.6s ease-out .35s 2; }
        @keyframes cart-toast-ring {
            0% { transform: scale(.85); opacity: .85; }
            100% { transform: scale(1.55); opacity: 0; }
        }
        .cart-toast__icon svg { width: 22px; height: 22px; color: #fff; filter: drop-shadow(0 1px 1px rgba(0,0,0,.15)); }
        .cart-toast__check { stroke-dasharray: 32; stroke-dashoffset: 32; }
        .cart-toast.is-visible .cart-toast__check { animation: cart-toast-check .5s cubic-bezier(.65,0,.45,1) .25s forwards; }
        @keyframes cart-toast-check { to { stroke-dashoffset: 0; } }
        .cart-toast__body { flex: 1; min-width: 0; text-align: right; }
        .cart-toast__title { margin: 0 0 2px; font-size: 14.5px; font-weight: 800; color: #fff; line-height: 1.3; }
        .cart-toast__msg {
            margin: 0; font-size: 12.5px; font-weight: 500; color: rgba(255,255,255,.92);
            line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2;
            -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis;
        }
        .cart-toast__close {
            flex-shrink: 0; width: 30px; height: 30px; border: 0; border-radius: 50%;
            background: rgba(255,255,255,.18); color: #fff; font-size: 12px;
            display: inline-flex; align-items: center; justify-content: center;
            cursor: pointer; transition: background-color .2s, transform .2s;
            -webkit-tap-highlight-color: transparent;
        }
        .cart-toast__close:hover { background: rgba(255,255,255,.32); transform: rotate(90deg); }
        .cart-toast__close:active { transform: rotate(90deg) scale(.9); }
        .cart-toast__progress {
            position: absolute; left: 0; right: 0; bottom: 0; height: 3px;
            background: rgba(255,255,255,.22); overflow: hidden;
        }
        .cart-toast__progress::after {
            content: ''; position: absolute; inset: 0; background: #fff;
            transform-origin: right center; transform: scaleX(0);
        }
        .cart-toast.is-visible .cart-toast__progress::after { animation: cart-toast-progress 3s linear forwards; }
        @keyframes cart-toast-progress {
            0% { transform: scaleX(1); }
            100% { transform: scaleX(0); }
        }
        @media (orientation: landscape) and (max-height: 500px) {
            .cart-toast { top: 10px; max-width: 560px; width: calc(100% - 40px); }
            .cart-toast__inner { padding: 9px 14px 12px; border-radius: 14px; gap: 10px; }
            .cart-toast__icon { width: 36px; height: 36px; }
            .cart-toast__icon svg { width: 18px; height: 18px; }
            .cart-toast__title { font-size: 13px; }
            .cart-toast__msg { font-size: 11.5px; -webkit-line-clamp: 1; }
            .cart-toast__close { width: 26px; height: 26px; font-size: 11px; }
        }
        @media (max-width: 360px) {
            .cart-toast { width: calc(100% - 20px); }
            .cart-toast__inner { padding: 12px 12px 14px; gap: 10px; }
            .cart-toast__icon { width: 40px; height: 40px; }
            .cart-toast__title { font-size: 13.5px; }
            .cart-toast__msg { font-size: 12px; }
        }
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
        // CART TOAST
        // ============================================================
        let cartToastTimer = null;

        function showCartToast(message, title = 'تمت الإضافة بنجاح') {
            const toast = document.getElementById('cart-toast');
            if (!toast) return;
            const titleEl = toast.querySelector('.cart-toast__title');
            const msgEl   = toast.querySelector('.cart-toast__msg');
            if (titleEl) titleEl.textContent = title;
            if (msgEl)   msgEl.textContent   = message;

            toast.classList.remove('is-visible');
            void toast.offsetWidth;
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
        document.getElementById('cart-toast-close')?.addEventListener('click', hideCartToast);
        document.addEventListener('keydown', e => { if (e.key === 'Escape') hideCartToast(); });
        const cartToastEl = document.getElementById('cart-toast');
        if (cartToastEl) {
            cartToastEl.addEventListener('mouseenter', () => clearTimeout(cartToastTimer));
            cartToastEl.addEventListener('mouseleave', () => { cartToastTimer = setTimeout(hideCartToast, 2000); });
        }

        // ============================================================
        // إضافة للسلة — Event Delegation (يعمل مع كل العبوات)
        // ============================================================
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.add-to-cart-pack');
            if (!btn) return;

            e.preventDefault();
            e.stopPropagation();

            let id       = btn.getAttribute('data-id');
            let packId   = btn.getAttribute('data-pack-id') || null;
            let name     = btn.getAttribute('data-name');
            let packName = btn.getAttribute('data-pack-name') || '';
            let uomName  = btn.getAttribute('data-uom-name') || '';
            let price    = parseFloat(btn.getAttribute('data-price')) || 0;
            let image    = btn.getAttribute('data-image');

            let cartItems = JSON.parse(localStorage.getItem('maggadit_cart')) || [];
            let existingItemIndex = cartItems.findIndex(item => item.id == id && (item.pack_id == packId || (!item.pack_id && !packId)));

            if (existingItemIndex > -1) {
                cartItems[existingItemIndex].quantity += 1;
            } else {
                cartItems.push({
                    id: id, pack_id: packId, name: name,
                    pack_name: packName, uom_name: uomName,
                    price: price, quantity: 1, image: image, discount: 0
                });
            }

            localStorage.setItem('maggadit_cart', JSON.stringify(cartItems));

            // تأثير بصري على الزر
            btn.style.transform = 'scale(.85)';
            setTimeout(() => { btn.style.transform = ''; }, 150);

            const displayName = name + (packName ? ' (' + packName + ')' : '');
            showCartToast(displayName + ' تمت إضافته إلى السلة', '✅ تمت الإضافة');
        });

        // ============================================================
        // المفضلة — Event Delegation
        // ============================================================
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.toggle-favorite-btn');
            if (!btn) return;

            e.preventDefault();
            e.stopPropagation();
            const icon = btn.querySelector('i');
            const productId = btn.getAttribute('data-id');

            fetch('{{ route('customer.favorite.toggle', [], false) }}', {
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
                if (data.success) {
                    if (data.is_favorite) {
                        btn.classList.remove('text-gray-600', 'border-gray-300', 'hover:bg-gray-50');
                        btn.classList.add('text-red-500', 'border-red-200', 'bg-red-50');
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                        btn.querySelector('span').textContent = 'إزالة من المفضلة';
                        showCartToast('تمت الإضافة إلى المفضلة', '❤️ المفضلة');
                    } else {
                        btn.classList.remove('text-red-500', 'border-red-200', 'bg-red-50');
                        btn.classList.add('text-gray-600', 'border-gray-300', 'hover:bg-gray-50');
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                        btn.querySelector('span').textContent = 'إضافة إلى المفضلة';
                        showCartToast('تمت الإزالة من المفضلة', '💔 المفضلة');
                    }
                }
            })
            .catch(() => showCartToast('خطأ في الاتصال، حاول مجددًا', '⚠️ خطأ'));
        });
    </script>
@endsection