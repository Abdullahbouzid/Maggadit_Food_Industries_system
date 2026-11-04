@extends('app_android.partials.mobile.app_mobile')

@section('title', $category->name)

@section('content')
    @include('app_android.partials.mobile.header', ['title' => $category->name])

    <div class="p-4" dir="rtl">
        <div class="mb-4">
            <button onclick="window.history.back()" class="flex items-center gap-2 text-purple-600 bg-purple-50 px-4 py-2 rounded-full text-sm font-semibold transition hover:bg-purple-100">
                <i class="fas fa-arrow-right"></i>
                <span>رجوع</span>
            </button>
        </div>

        <div class="bg-white rounded-3xl shadow-lg p-5 mb-5 text-center">
            <div class="w-20 h-20 mx-auto rounded-full overflow-hidden bg-gray-100 flex items-center justify-center mb-3 shadow-md">
                @if($category->image)
                    <img src="{{ $category->image }}" alt="{{ $category->name }}" class="w-full h-full object-cover">
                @else
                    <span class="text-4xl">📁</span>
                @endif
            </div>
            <h2 class="text-xl font-bold text-gray-800">{{ $category->name }}</h2>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                <div class="inline-flex items-center gap-1 bg-purple-50 text-purple-700 px-3 py-1 rounded-full">
                    <i class="fas fa-box-open text-sm"></i>
                    <span class="text-sm font-semibold">{{ $category->products_count ?? ($items->count() ?? 0) }} منتج</span>
                </div>
                <a href="{{ route('customer.products', ['category_id' => $category->id], false) }}"
                   class="inline-flex items-center gap-1 bg-gray-100 text-gray-700 px-3 py-1 rounded-full hover:bg-gray-200 transition">
                    <i class="fas fa-grip text-sm"></i>
                    <span class="text-sm font-semibold">عرض وتصفية</span>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            @forelse($items as $item)
                @php
                    $itemData = is_array($item) ? $item : (array) $item;
                    $packs = is_array($itemData['packs'] ?? null) ? $itemData['packs'] : [];
                    $hasMultiplePacks = count($packs) > 1;
                    $itemId = $itemData['id'] ?? ($item->id ?? 0);
                    $itemName = $itemData['name'] ?? ($item->name ?? 'منتج');
                    $itemImage = $itemData['image'] ?? ($item->image ?? '');
                    $itemFavorite = $itemData['favorite'] ?? false;
                @endphp
                @foreach($packs as $pack)
                    <a href="{{ route('customer.product.show', $itemId, false) }}" class="block">
                        <div class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100 transition hover:shadow-lg relative">
                            @if($hasMultiplePacks)
                                <div class="absolute top-2 right-2 z-10">
                                    <span class="bg-purple-600 text-white text-[10px] px-2 py-1 rounded-full font-semibold">
                                        {{ $pack['name'] ?? '' }}
                                    </span>
                                </div>
                            @endif
                            <button class="absolute top-2 left-2 z-10 p-1.5 rounded-full bg-white/80 backdrop-blur-sm shadow-sm toggle-favorite-btn transition-colors {{ $itemFavorite ? 'text-red-500' : 'text-gray-400' }}" data-id="{{ $itemId }}">
                                <i class="{{ $itemFavorite ? 'fas' : 'far' }} fa-heart text-lg"></i>
                            </button>
                            <div class="h-32 bg-gray-100 flex items-center justify-center overflow-hidden">
                                @if($itemImage)
                                    <img src="{{ $itemImage }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-5xl text-gray-400">📦</span>
                                @endif
                            </div>
                            <div class="p-3">
                                <h3 class="font-bold text-gray-800 text-sm">{{ $itemName }}</h3>
                                @if($hasMultiplePacks)
                                    <span class="inline-block mt-1 bg-gray-100 text-gray-700 text-[10px] px-2 py-0.5 rounded-full">
                                        {{ $pack['name'] ?? '' }}
                                    </span>
                                @endif
                                <div class="mt-2">
                                    <span class="text-purple-700 font-bold">{{ number_format($pack['price'] ?? 0) }} ر.س</span>
                                </div>
                                <button class="add-to-cart mt-2 w-full bg-purple-600 text-white text-xs py-2 rounded-full hover:bg-purple-700 transition"
                                        data-id="{{ $itemId }}"
                                        data-name="{{ $itemName }}"
                                        data-price="{{ $pack['price'] ?? 0 }}"
                                        data-image="{{ $itemImage ?? '📦' }}"
                                        data-pack-id="{{ $pack['id'] ?? '' }}"
                                        data-pack-name="{{ $pack['name'] ?? '' }}"
                                        data-uom-name="{{ $pack['uom_name'] ?? '' }}">
                                    <i class="fas fa-shopping-cart ml-1"></i> أضف إلى السلة
                                </button>
                            </div>
                        </div>
                    </a>
                @endforeach
            @empty
                <p class="text-center text-gray-500 col-span-2">لا توجد منتجات في هذه الفئة حالياً</p>
            @endforelse
        </div>

        @if($items instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-6">
                {{ $items->links() }}
            </div>
        @endif

        <div id="logout-btn" class="mt-6 rounded-xl p-4 flex items-center justify-center gap-3">
            <br><br>
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
    </style>

    <script>
        // ============================================================
        // CART TOAST — JavaScript
        // ============================================================
        let cartToastTimer = null;

        function showToast(msg, isError = false) {
            showCartToast(msg, isError ? '⚠️ خطأ' : '✅ تمت الإضافة');
        }

        function showCartToast(message, title = 'تمت الإضافة بنجاح') {
            const toast = document.getElementById('cart-toast');
            if (!toast) return;
            const titleEl = toast.querySelector('.cart-toast__title');
            const msgEl = toast.querySelector('.cart-toast__msg');
            if (titleEl) titleEl.textContent = title;
            if (msgEl) msgEl.textContent = message;

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
            cartToastEl.addEventListener('mouseleave', () => {
                cartToastTimer = setTimeout(hideCartToast, 2000);
            });
        }

        // ============================================================
        // إضافة للسلة — Event Delegation
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

            fetch("{{ route('customer.favorite.toggle', [], false) }}", {
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
                        btn.classList.remove('text-gray-400');
                        btn.classList.add('text-red-500');
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                        showCartToast('تمت الإضافة إلى المفضلة', '❤️ المفضلة');
                    } else {
                        btn.classList.remove('text-red-500');
                        btn.classList.add('text-gray-400');
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                        showCartToast('تمت الإزالة من المفضلة', '💔 المفضلة');
                    }
                }
            })
            .catch(() => showCartToast('خطأ في الاتصال، حاول مجددًا', '⚠️ خطأ'));
        });
    </script>
@endsection
