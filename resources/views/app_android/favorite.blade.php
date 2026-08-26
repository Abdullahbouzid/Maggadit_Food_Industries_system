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
            <a href="{{ route('customer.products') }}" id="empty-shop-btn" class="inline-block bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 rounded-full shadow-lg transition">
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

    <!-- Toast إشعارات -->
    <div id="fav-toast" class="fixed bottom-20 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-5 py-3 rounded-full shadow-lg text-sm font-medium z-50 transition-all duration-300 opacity-0 translate-y-10 pointer-events-none"></div>

    <script>
        let favoriteItems = @json($favoriteItems ?? []);

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
            const toast = document.getElementById('fav-toast');
            toast.innerText = msg;
            toast.style.background = isError ? '#dc2626' : '#1f2937';
            toast.classList.remove('opacity-0', 'translate-y-10');
            toast.classList.add('opacity-100', 'translate-y-0');
            setTimeout(() => {
                toast.classList.remove('opacity-100', 'translate-y-0');
                toast.classList.add('opacity-0', 'translate-y-10');
            }, 2000);
        }

        function addToCart(product) {
            let cart = JSON.parse(localStorage.getItem('maggadit_cart') || '[]');
            const existingIndex = cart.findIndex(item => item.id === product.id);
            if (existingIndex > -1) {
                cart[existingIndex].quantity = (cart[existingIndex].quantity || 1) + 1;
            } else {
                cart.push({
                    id: product.id,
                    name_ar: product.name_ar,
                    name_en: product.name_en || product.name_ar,
                    price: product.price,
                    image: product.image,
                    quantity: 1,
                    discount: product.discount || 0
                });
            }
            localStorage.setItem('maggadit_cart', JSON.stringify(cart));
            showToastMessage(currentLang === 'ar' ? `تم إضافة ${currentLang === 'ar' ? product.name_ar : product.name_en} إلى السلة` : `Added ${currentLang === 'ar' ? product.name_ar : product.name_en} to cart`, false);
        }

        function removeFromFav(id) {
            fetch('{{ route('customer.favorite.toggle') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
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
                itemCard.className = 'bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100 transition hover:shadow-lg';
                itemCard.innerHTML = `
                    <div class="flex flex-row">
                        <div class="w-24 h-24 bg-gray-100 flex items-center justify-center text-4xl overflow-hidden">
                            ${item.image ? `<img src="${item.image}" alt="${currentLang === 'ar' ? item.name_ar : item.name_en}" class="w-full h-full object-cover">` : '📦'}
                        </div>
                        <div class="flex-1 p-3 text-right">
                            <div class="flex justify-between items-start">
                                <span class="text-xs ${item.discount > 0 ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600'} px-2 py-0.5 rounded-full">
                                    ${item.discount > 0 ? `-${item.discount}%` : (currentLang === 'ar' ? 'جديد' : 'New')}
                                </span>
                                <h3 class="font-bold text-gray-800">${currentLang === 'ar' ? item.name_ar : item.name_en}</h3>
                            </div>
                            <div class="mt-2 flex justify-between items-center">
                                <div class="text-left">
                                    <span class="font-bold text-purple-700 text-lg">${formatPrice(item.price)}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button class="remove-fav bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold px-3 py-1.5 rounded-full transition" data-id="${item.id}">
                                        <i class="fas fa-trash-alt ml-1"></i> ${t.remove_btn}
                                    </button>
                                </div>
                            </div>
                            <div class="mt-3">
                                ${item.inStock ? 
                                    `<button class="add-to-cart-fav w-full bg-purple-100 hover:bg-purple-200 text-purple-700 font-semibold py-2 rounded-xl text-sm transition" data-id="${item.id}">
                                        <i class="fas fa-shopping-cart ml-1"></i> ${t.add_to_cart}
                                    </button>` :
                                    `<div class="text-center text-red-500 text-xs font-semibold py-2">${t.out_of_stock}</div>`
                                }
                            </div>
                        </div>
                    </div>
                `;
                container.appendChild(itemCard);
            });

            // ربط الأحداث
            document.querySelectorAll('.remove-fav').forEach(btn => {
                btn.removeEventListener('click', handleRemoveClick);
                btn.addEventListener('click', handleRemoveClick);
            });
            document.querySelectorAll('.add-to-cart-fav').forEach(btn => {
                btn.removeEventListener('click', handleAddToCartClick);
                btn.addEventListener('click', handleAddToCartClick);
            });
        }

        function handleRemoveClick(e) {
            const id = parseInt(e.currentTarget.getAttribute('data-id'));
            removeFromFav(id);
        }

        function handleAddToCartClick(e) {
            const id = parseInt(e.currentTarget.getAttribute('data-id'));
            const product = favoriteItems.find(item => item.id === id);
            if (product && product.inStock) {
                addToCart(product);
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
            fetch('/customer/proxy/favorites')
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
@endsection