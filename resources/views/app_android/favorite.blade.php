@extends('app_android.partials.mobile.app_mobile')

@section('title', __('messages.favorite_title'))

@section('content')
    @include('app_android.partials.mobile.header', ['title' => __('messages.favorite_title')])

    <div class="px-4 py-4 mb-5 space-y-4" dir="rtl">
        {{-- حالة المفضلة فارغة --}}
        <div id="empty-fav-state" class="hidden text-center py-10">
            <div class="w-32 h-32 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                <i class="far fa-heart text-5xl text-gray-300"></i>
            </div>
            <h3 id="empty-title" class="text-xl font-bold text-gray-700 mb-2">المفضلة فارغة</h3>
            <p id="empty-message" class="text-gray-400 text-sm mb-6">أضف المنتجات التي تعجبك إلى قائمة مفضلاتك</p>
            <a href="{{ route('customer.products', [], false) }}" id="empty-shop-btn" class="inline-block bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 rounded-full shadow-lg transition">
                استعرض المنتجات
            </a>
        </div>

        {{-- محتوى المفضلة --}}
        <div id="fav-content">
            <div class="grid grid-cols-1 gap-4" id="fav-items-list">
                {{-- عناصر المفضلة ستضاف ديناميكياً هنا --}}
            </div>
        </div>
          <div id="logout-btn" class="mt-6 rounded-xl p-4 flex items-center justify-center gap-3  ">
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

    <script>
        let favoriteItems = @json($favoriteItems ?? []);
        const productUrls = @json(
            collect($favoriteItems ?? [])
                ->mapWithKeys(function ($item) {
                    $id = is_array($item) ? ($item['id'] ?? null) : ($item->id ?? null);
                    return $id ? [(string) $id => route('customer.product.show', $id, false)] : [];
                })
                ->all()
        );
        const favoriteProductBase = @json(rtrim(url('/'), '/'));
        const productUrlFor = (id) => productUrls[String(id)] || `${favoriteProductBase}/customer/product/${encodeURIComponent(id)}`;

        // ========== ترجمة الواجهة ==========
        const translations = {
            en: {
                empty_title: "Your Wishlist is Empty",
                empty_message: "Add your favorite products to your wishlist",
                empty_btn: "Browse Products",
                remove_btn: "Remove",
                add_to_cart: "Add to Cart",
                out_of_stock: "Out of Stock",
                in_stock: "In Stock",
                currency: "SAR"
            },
            ar: {
                empty_title: "المفضلة فارغة",
                empty_message: "أضف المنتجات التي تعجبك إلى قائمة مفضلاتك",
                empty_btn: "استعرض المنتجات",
                remove_btn: "إزالة",
                add_to_cart: "أضف إلى السلة",
                out_of_stock: "غير متوفر",
                in_stock: "متوفر",
                currency: "ر.س"
            }
        };

        let currentLang = localStorage.getItem('fav_lang') || 'ar';

        function updateUILanguage() {
            const t = translations[currentLang];
            document.getElementById('empty-title').innerText = t.empty_title;
            document.getElementById('empty-message').innerText = t.empty_message;
            document.getElementById('empty-shop-btn').innerText = t.empty_btn;
        }

        function formatPrice(price) {
            const t = translations[currentLang];
            return `${price.toFixed(2)} ${t.currency}`;
        }

        function showToastMessage(msg, isError = false) {
            showCartToast(msg, isError ? '⚠️ خطأ' : '✅ تمت الإضافة');
        }

        function addToCart(product, pack = null) {
            let cart = JSON.parse(localStorage.getItem('maggadit_cart') || '[]');
            const packId = pack ? pack.id : (product.pack_id || null);
            const packName = pack ? pack.name : (product.pack_name || product.uom_name || '');
            const uomName = pack ? (pack.uom_name || '') : (product.uom_name || '');
            const price = pack ? pack.price : product.price;

            const existingIndex = cart.findIndex(item => item.id === product.id && (item.pack_id === packId || (!item.pack_id && !packId)));
            if (existingIndex > -1) {
                cart[existingIndex].quantity = (cart[existingIndex].quantity || 1) + 1;
            } else {
                cart.push({
                    id: product.id,
                    pack_id: packId,
                    name: product.name_ar || product.name,
                    name_ar: product.name_ar || product.name,
                    name_en: product.name_en || product.name_ar || product.name,
                    pack_name: packName,
                    uom_name: uomName,
                    price: price,
                    image: product.image,
                    quantity: 1,
                    discount: product.discount || 0
                });
            }
            localStorage.setItem('maggadit_cart', JSON.stringify(cart));
            const msg = currentLang === 'ar' ? `تم إضافة ${product.name_ar || product.name} ${packName ? '('+packName+')' : ''} إلى السلة` : `Added ${product.name_en || product.name} to cart`;
            showCartToast(msg, '✅ تمت الإضافة');
        }

        function removeFromFav(id) {
            fetch('{{ route('customer.favorite.toggle', [], false) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'include',
                body: JSON.stringify({ item_id: id })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    favoriteItems = favoriteItems.filter(item => item.id !== id);
                    renderFavorites();
                    showToastMessage(currentLang === 'ar' ? 'تمت الإزالة من المفضلة' : 'Removed from wishlist', false);
                } else {
                    showToastMessage(currentLang === 'ar' ? 'حدث خطأ' : 'Error occurred', true);
                }
            })
            .catch(err => {
                showToastMessage(currentLang === 'ar' ? 'حدث خطأ' : 'Error occurred', true);
            });
        }

        function renderFavorites() {
            const container = document.getElementById('fav-items-list');
            const emptyState = document.getElementById('empty-fav-state');
            const favContent = document.getElementById('fav-content');
            const t = translations[currentLang];

            if (favoriteItems.length === 0) {
                emptyState.classList.remove('hidden');
                favContent.classList.add('hidden');
                return;
            }
            emptyState.classList.add('hidden');
            favContent.classList.remove('hidden');

            container.innerHTML = '';
            favoriteItems.forEach((item, index) => {
                const itemCard = document.createElement('div');
                itemCard.className = 'bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100 transition hover:shadow-lg p-3.5';
                
                let uomBadge = item.uom_name ? `<span class="bg-purple-100 text-purple-700 text-xs px-2.5 py-0.5 rounded-full font-bold"><i class="fas fa-box ml-1"></i>${item.uom_name}</span>` : '';

                itemCard.innerHTML = `
                    <div class="flex flex-row gap-3.5">
                        <a href="${productUrlFor(item.id)}" class="w-24 h-24 bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl flex items-center justify-center text-4xl overflow-hidden border border-purple-100 flex-shrink-0">
                            ${item.image ? `<img src="${item.image}" alt="${currentLang === 'ar' ? item.name_ar : item.name_en}" class="w-full h-full object-cover rounded-xl">` : '📦'}
                        </a>
                        <div class="flex-1 text-right flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start gap-1">
                                    <a href="${productUrlFor(item.id)}" class="font-bold text-gray-800 text-sm leading-snug hover:text-purple-700">${currentLang === 'ar' ? item.name_ar : item.name_en}</a>
                                    <button class="remove-fav text-red-500 hover:text-red-700 text-xs font-medium p-1 transition" data-id="${item.id}" title="${t.remove_btn}">
                                        <i class="fas fa-heart text-red-500 text-base"></i>
                                    </button>
                                </div>
                                <div class="mt-1 flex items-center gap-2">
                                    ${uomBadge}
                                </div>
                            </div>
                            <div class="mt-3 flex items-center justify-between">
                                <span class="font-extrabold text-purple-700 text-base">${formatPrice(item.price)}</span>
                                    ${item.inStock ? 
                                        `<button class="add-to-cart-fav bg-purple-600 hover:bg-purple-700 text-white font-bold px-3.5 py-1.5 rounded-xl text-xs transition flex items-center gap-1 shadow-sm active:scale-95" data-id="${item.id}" data-pack-id="${item.pack_id || ''}" data-pack-name="${item.pack_name || ''}" data-uom-name="${item.uom_name || ''}">
                                            <i class="fas fa-cart-plus"></i> ${t.add_to_cart}
                                        </button>` :
                                        `<span class="text-red-500 text-xs font-semibold">${t.out_of_stock}</span>`
                                    }
                            </div>
                        </div>
                    </div>
                `;
                container.appendChild(itemCard);
            });

            // ربط أحداث الإزالة — Event Delegation يدعم الأزرار الديناميكية
            // ملاحظة: زر الإضافة للسلة يتم معالجته عبر Event Delegation في الأسفل
        }

        function handleRemoveClick(e) {
            const id = parseInt(e.currentTarget.getAttribute('data-id'));
            removeFromFav(id);
        }

        function handleAddToCartClick(e) {
            const id = parseInt(e.currentTarget.getAttribute('data-id'));
            const packName = e.currentTarget.getAttribute('data-pack-name') || '';
            const uomName = e.currentTarget.getAttribute('data-uom-name') || '';
            const product = favoriteItems.find(item => item.id === id);
            if (product && product.inStock) {
                addToCart({ ...product, pack_name: packName, uom_name: uomName });
                // اختياري: إزالة من المفضلة بعد الإضافة (يمكنك تفعيل السطر التالي)
                // removeFromFav(id);
            } else {
                showToastMessage(currentLang === 'ar' ? 'المنتج غير متوفر حالياً' : 'Product is out of stock', true);
            }
        }

        // تبديل اللغة
        function toggleLanguage() {
            currentLang = currentLang === 'ar' ? 'en' : 'ar';
            localStorage.setItem('fav_lang', currentLang);
            updateUILanguage();
            renderFavorites();
        }

        // زر استعراض المنتجات الآن يعمل عبر الـ href مباشرة

        // زر تبديل اللغة (إذا كان موجوداً في الهيدر)
        const langSwitch = document.getElementById('lang-switch');
        if (langSwitch) {
            langSwitch.addEventListener('click', toggleLanguage);
        } else {
            // زر تبديل مؤقت
            const tempBtn = document.createElement('button');
            tempBtn.id = 'temp-lang-switch-fav';
            tempBtn.innerText = currentLang === 'ar' ? '🇬🇧 English' : '🇸🇦 العربية';
            tempBtn.className = 'fixed top-20 left-4 z-50 bg-white shadow-md rounded-full px-3 py-1 text-sm font-semibold text-purple-600';
            tempBtn.onclick = toggleLanguage;
            document.body.appendChild(tempBtn);
        }

        // تحميل البداية
        updateUILanguage();
        renderFavorites();

        if (favoriteItems.length === 0) {
            const apiToken = localStorage.getItem('api_token') || localStorage.getItem('customer_token') || '';
            const headers = {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            };
            if (apiToken) headers['Authorization'] = 'Bearer ' + apiToken;
            fetch('{{ route('customer.proxy.favorites', [], false) }}', { headers })
                .then(res => res.json())
                .then(data => {
                    if (data && data.data && Array.isArray(data.data) && data.data.length > 0) {
                        favoriteItems = data.data.map(item => ({
                            id: item.id,
                            name_ar: item.name_ar || item.name || 'منتج',
                            name_en: item.name_en || item.name || 'Product',
                            price: parseFloat(item.price || 0),
                            image: item.image || null,
                            discount: 0,
                            inStock: true
                        }));
                        renderFavorites();
                    }
                })
                .catch(err => console.error("Favorites proxy error:", err));
        }
    </script>

    <!-- Fontawesome -->
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

        function showCartToast(message, title = 'تمت الإضافة بنجاح') {
            const toast  = document.getElementById('cart-toast');
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
            cartToastEl.addEventListener('mouseleave', () => {
                cartToastTimer = setTimeout(hideCartToast, 2000);
            });
        }

        // ============================================================
        // إضافة للسلة — Event Delegation (للأزرار الديناميكية)
        // ============================================================
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.add-to-cart-fav');
            if (!btn) return;

            e.preventDefault();
            e.stopPropagation();

            let id = btn.getAttribute('data-id');
            let packId = btn.getAttribute('data-pack-id') || null;
            let packName = btn.getAttribute('data-pack-name') || '';
            let uomName = btn.getAttribute('data-uom-name') || '';

            const product = favoriteItems.find(item => item.id == id);
            if (product && product.inStock) {
                addToCart({ ...product, pack_id: packId, pack_name: packName, uom_name: uomName });
            }
        });

        // ============================================================
        // إزالة من المفضلة — Event Delegation (للأزرار الديناميكية)
        // ============================================================
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.remove-fav');
            if (!btn) return;

            e.preventDefault();
            e.stopPropagation();

            const id = parseInt(btn.getAttribute('data-id'));
            removeFromFav(id);
        });
    </script>
@endsection
