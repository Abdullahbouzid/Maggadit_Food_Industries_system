@extends('app_android.partials.mobile.app_mobile')

@section('title', __('messages.cart_title'))

@section('content')
    @include('app_android.partials.mobile.header', ['title' => __('messages.cart_title')])

    <div class="px-4 py-4 mb-5 space-y-4" dir="rtl">
        {{-- حالة السلة فارغة --}}
        <div id="empty-cart-state" class="hidden text-center py-10">
            <div class="w-32 h-32 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-shopping-cart text-5xl text-gray-300"></i>
            </div>
            <h3 id="empty-title" class="text-xl font-bold text-gray-700 mb-2">سلة التسوق فارغة</h3>
            <p id="empty-message" class="text-gray-400 text-sm mb-6">أضف بعض المنتجات الرائعة إلى سلتك</p>
            <a href="{{ route('customer.products', [], false) }}" id="empty-shop-btn" class="inline-block bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 rounded-full shadow-lg transition">
                استعرض العروض
            </a>
        </div>

        {{-- محتوى السلة --}}
        <div id="cart-content">
            <div class="space-y-3" id="cart-items-list">
                {{-- عناصر السلة ستضاف ديناميكياً هنا --}}
            </div>

            {{-- بطاقة الخلاصة --}}
            <div class="bg-white rounded-2xl shadow-md p-5 mt-6 border border-gray-100">
                <h3 id="summary-title" class="font-bold text-gray-800 text-lg mb-3">ملخص الطلب</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between border-t border-gray-200 pt-2 mt-2">
                        <span id="total-label" class="text-lg font-bold text-gray-800">الإجمالي</span>
                        <span id="total-value" class="text-lg font-bold text-purple-600">0.00 ر.س</span>
                    </div>
                </div>

                {{-- نموذج تفاصيل التوصيل --}}
                <div id="checkout-form" class="mt-6 space-y-4 hidden">
                    <h4 class="font-bold text-gray-800 text-base mb-3 flex items-center gap-2">
                        <i class="fas fa-truck text-purple-600"></i> تفاصيل التوصيل
                    </h4>
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">عنوان التوصيل <span class="text-red-500">*</span></label>
                        <textarea id="delivery_address" rows="2" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm" placeholder="العنوان بالتفصيل (شارع، حي، رقم المبنى، الدور، الشقة)" required></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">رقم الهاتف <span class="text-red-500">*</span></label>
                        <input type="tel" id="delivery_phone" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm" placeholder="05XXXXXXXX" required>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">موعد التوصيل المفضل (اختياري)</label>
                        <input type="date" id="scheduled_delivery_date" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">ملاحظات إضافية (اختياري)</label>
                        <textarea id="notes" rows="2" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm" placeholder="أي تعليمات خاصة بالتوصيل أو الطلب..."></textarea>
                    </div>
                </div>

                <button id="checkout-btn" class="w-full bg-gradient-to-r from-purple-600 to-indigo-700 hover:from-purple-700 hover:to-indigo-800 text-white font-bold py-3 rounded-xl mt-5 shadow-md transition transform hover:scale-[1.02] active:scale-95">
                    إنشاء الطلبية
                </button>
            </div>
        </div>
        <br><br>
      <div id="logout-btn" class="mt-6 rounded-xl p-4 flex items-center justify-center gap-3  ">
            <br><br>
        </div>
    </div>

    <!-- ============================================================
         CART TOAST NOTIFICATION — إشعار علوي احترافي بلون #9333ea
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

    <!-- Beautiful Success Modal for Order Completion -->
    <div id="order-success-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl max-w-md w-full overflow-hidden transform transition-all duration-300 scale-95 opacity-0" id="modal-content">
            <div class="p-8 text-center">
                <div class="w-20 h-20 mx-auto mb-5 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full flex items-center justify-center shadow-lg">
                    <i class="fas fa-check text-3xl text-white"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">تم إنشاء الطلبية بنجاح!</h3>
                <p class="text-gray-600 mb-6" id="order-modal-message">رقم الطلبية: <span id="order-number" class="font-bold text-purple-700"></span></p>
                <button onclick="closeOrderSuccessModal()" class="w-full bg-gradient-to-r from-purple-600 to-indigo-700 text-white font-bold py-3 rounded-xl shadow-md transition hover:from-purple-700 hover:to-indigo-800 active:scale-[0.98]">
                    <i class="fas fa-check-circle ml-2"></i> عرض الطلبيات
                </button>
            </div>
        </div>
    </div>

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
        console.log('Cart script loaded, cartItems:', JSON.parse(localStorage.getItem('maggadit_cart')) || []);
        // استرجاع السلة من التخزين المحلي
        let cartItems = JSON.parse(localStorage.getItem('maggadit_cart')) || [];
        let deliveryFee = 0; // تم إلغاء رسوم التوصيل بناء على طلب العميل

        function saveCart() {
            localStorage.setItem('maggadit_cart', JSON.stringify(cartItems));
        }

        // ========== ترجمة الواجهة ==========
        const translations = {
            en: {
                empty_title: "Your Cart is Empty",
                empty_message: "Add some awesome products to your cart",
                empty_btn: "Browse Offers",
                summary_title: "Order Summary",
                subtotal_label: "Subtotal",
                total_label: "Total",
                checkout_btn: "Create Order",
                currency: "SAR",
                delete_btn: "Remove",
                quantity_label: "Qty"
            },
            ar: {
                empty_title: "سلة التسوق فارغة",
                empty_message: "أضف بعض المنتجات الرائعة إلى سلتك",
                empty_btn: "استعرض العروض",
                summary_title: "ملخص الطلب",
                subtotal_label: "المجموع الفرعي",
                total_label: "الإجمالي",
                checkout_btn: "إنشاء الطلبية",
                currency: "ر.س",
                delete_btn: "حذف",
                quantity_label: "الكمية"
            }
        };

        let currentLang = localStorage.getItem('cart_lang') || 'ar';

        function updateUILanguage() {
            const t = translations[currentLang];
            document.getElementById('empty-title').innerText = t.empty_title;
            document.getElementById('empty-message').innerText = t.empty_message;
            document.getElementById('empty-shop-btn').innerText = t.empty_btn;
            document.getElementById('summary-title').innerText = t.summary_title;
            document.getElementById('total-label').innerText = t.total_label;
            document.getElementById('checkout-btn').innerText = t.checkout_btn;
            // تحديث العملة على الشاشة
            document.querySelectorAll('.currency-symbol').forEach(el => el.innerText = t.currency);
        }

        // تنسيق العملة
        function formatPrice(price) {
            const t = translations[currentLang];
            return `${parseFloat(price).toFixed(2)} ${t.currency}`;
        }

        // حساب المجموع الفرعي والإجمالي
        function calculateTotals() {
            let subtotal = cartItems.reduce((sum, item) => sum + (parseFloat(item.price) * parseInt(item.quantity)), 0);
            return { subtotal, total: subtotal };
        }

        // دالة التوست
        let cartToastTimer = null;

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

        function showToastMessage(msg, isError = false) {
            showCartToast(msg, isError ? '⚠️ خطأ' : '✅ تمت العملية بنجاح');
        }

        // تحديث واجهة السلة
        function renderCart() {
            const container = document.getElementById('cart-items-list');
            const emptyState = document.getElementById('empty-cart-state');
            const cartContent = document.getElementById('cart-content');
            const t = translations[currentLang];

            if (cartItems.length === 0) {
                emptyState.classList.remove('hidden');
                cartContent.classList.add('hidden');
                return;
            }
            emptyState.classList.add('hidden');
            cartContent.classList.remove('hidden');

            // بناء عناصر السلة
            container.innerHTML = '';
            cartItems.forEach((item, index) => {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100 transition hover:shadow-lg p-3';
                
                let imageContent = item.image && item.image.startsWith('http') 
                    ? `<img src="${item.image}" class="w-full h-full object-cover rounded-xl">`
                    : `<span class="text-3xl">${item.image || '📦'}</span>`;

                let packBadge = (item.pack_name || item.uom_name) 
                    ? `<div class="mt-1"><span class="inline-flex items-center gap-1 bg-purple-100 text-purple-700 text-xs px-2.5 py-0.5 rounded-full font-bold"><i class="fas fa-box"></i> ${item.pack_name || item.uom_name}</span></div>` 
                    : '';

                itemDiv.innerHTML = `
                    <div class="flex flex-row items-center gap-3">
                        <div class="w-20 h-20 bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl flex items-center justify-center overflow-hidden flex-shrink-0 border border-purple-100">
                            ${imageContent}
                        </div>
                        <div class="flex-1 text-right">
                            <div class="flex justify-between items-start">
                                <h3 class="font-bold text-gray-800 text-sm leading-snug">${currentLang === 'ar' ? (item.name_ar || item.name) : (item.name_en || item.name)}</h3>
                            </div>
                            ${packBadge}
                            <div class="flex justify-between items-center mt-2.5">
                                <div class="flex items-center gap-2 bg-gray-100 rounded-full px-2.5 py-1">
                                    <button class="decrement-qty w-6 h-6 rounded-full bg-white text-gray-600 font-bold text-center leading-6 shadow-sm hover:bg-gray-200 active:scale-95" data-index="${index}">-</button>
                                    <span class="item-qty w-6 text-center font-bold text-xs text-gray-800" data-index="${index}">${item.quantity}</span>
                                    <button class="increment-qty w-6 h-6 rounded-full bg-white text-gray-600 font-bold text-center leading-6 shadow-sm hover:bg-gray-200 active:scale-95" data-index="${index}">+</button>
                                </div>
                                <div class="text-left">
                                    <span class="font-extrabold text-purple-700 text-sm">${formatPrice(item.price)}</span>
                                    ${item.quantity > 1 ? `<span class="text-xs text-gray-400 block font-semibold">${formatPrice(item.price * item.quantity)}</span>` : ''}
                                </div>
                            </div>
                            <div class="mt-2 text-left">
                                <button class="remove-item text-red-500 text-xs font-semibold hover:text-red-700" data-index="${index}"><i class="fas fa-trash-alt ml-1"></i> ${t.delete_btn}</button>
                            </div>
                        </div>
                    </div>
                `;
                container.appendChild(itemDiv);
            });

            // تحديث الملخص
            const { total } = calculateTotals();
            document.getElementById('total-value').innerHTML = formatPrice(total);

            // إعادة ربط الأحداث
            attachCartEvents();
        }

        function attachCartEvents() {
            // زيادة الكمية
            document.querySelectorAll('.increment-qty').forEach(btn => {
                btn.removeEventListener('click', handleIncrement);
                btn.addEventListener('click', handleIncrement);
            });
            // إنقاص الكمية
            document.querySelectorAll('.decrement-qty').forEach(btn => {
                btn.removeEventListener('click', handleDecrement);
                btn.addEventListener('click', handleDecrement);
            });
            // حذف العنصر
            document.querySelectorAll('.remove-item').forEach(btn => {
                btn.removeEventListener('click', handleRemove);
                btn.addEventListener('click', handleRemove);
            });
        }

        function handleIncrement(e) {
            const index = parseInt(e.currentTarget.getAttribute('data-index'));
            if (!isNaN(index) && cartItems[index]) {
                cartItems[index].quantity++;
                saveCart();
                renderCart();
            }
        }

        function handleDecrement(e) {
            const index = parseInt(e.currentTarget.getAttribute('data-index'));
            if (!isNaN(index) && cartItems[index]) {
                if (cartItems[index].quantity > 1) {
                    cartItems[index].quantity--;
                } else {
                    cartItems.splice(index, 1);
                }
                saveCart();
                renderCart();
            }
        }

        function handleRemove(e) {
            const index = parseInt(e.currentTarget.getAttribute('data-index'));
            if (!isNaN(index) && cartItems[index]) {
                cartItems.splice(index, 1);
                saveCart();
                renderCart();
                showCartToast('تم حذف المنتج من السلة', '🗑️ حذف');
            }
        }

        // تبديل اللغة (مع وجود زر في الهيدر أو زر مؤقت)
        function toggleLanguage() {
            currentLang = currentLang === 'ar' ? 'en' : 'ar';
            localStorage.setItem('cart_lang', currentLang);
            updateUILanguage();
            renderCart();
        }

        // زر الدفع
        console.log('Attaching checkout event listener...');
        const checkoutBtn = document.getElementById('checkout-btn');
        if (!checkoutBtn) {
            console.error('Checkout button NOT found in DOM!');
        } else {
            console.log('Checkout button found:', checkoutBtn);
        }
        checkoutBtn?.addEventListener('click', () => {
            console.log('Checkout button clicked');
            if (cartItems.length === 0) {
                showToastMessage(currentLang === 'ar' ? 'السلة فارغة' : 'Cart is empty', true);
                return;
            }

            const checkoutForm = document.getElementById('checkout-form');
            const isFormVisible = !checkoutForm.classList.contains('hidden');

            if (!isFormVisible) {
                // First click: show the form
                checkoutForm.classList.remove('hidden');
                checkoutBtn.innerText = currentLang === 'ar' ? 'تأكيد إنشاء الطلبية' : 'Confirm Order';
                checkoutForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            // Form is visible, validate and submit
            console.log('Checkout form submitted');
            const originalText = checkoutBtn.innerText;
            checkoutBtn.innerText = currentLang === 'ar' ? 'جاري المعالجة...' : 'Processing...';
            checkoutBtn.disabled = true;

            // Validate required fields
            const deliveryAddress = document.getElementById('delivery_address')?.value?.trim();
            const deliveryPhone = document.getElementById('delivery_phone')?.value?.trim();
            
            if (!deliveryAddress) {
                showToastMessage(currentLang === 'ar' ? 'يرجى إدخال عنوان التوصيل' : 'Please enter delivery address', true);
                checkoutBtn.innerText = originalText;
                checkoutBtn.disabled = false;
                return;
            }
            if (!deliveryPhone) {
                showToastMessage(currentLang === 'ar' ? 'يرجى إدخال رقم الهاتف' : 'Please enter phone number', true);
                checkoutBtn.innerText = originalText;
                checkoutBtn.disabled = false;
                return;
            }

            const validItems = cartItems.filter(item => item.id && item.price > 0).map(item => ({
                id: item.id,
                quantity: item.quantity,
                pack_id: item.pack_id || null,
                pack_name: item.pack_name || '',
                uom_name: item.uom_name || '',
                name: item.name || item.name_ar || item.name_en || ''
            }));
            console.log('Valid items:', validItems);
            if (validItems.length === 0) {
                showToastMessage(currentLang === 'ar' ? 'لا توجد منتجات صالحة في السلة' : 'No valid items in cart', true);
                checkoutBtn.innerText = originalText;
                checkoutBtn.disabled = false;
                return;
            }

            const token = localStorage.getItem('customer_token') || localStorage.getItem('api_token');
            console.log('Token found:', token ? 'yes' : 'no');
            const headers = {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            };
            if (token) {
                headers['Authorization'] = 'Bearer ' + token;
            }

            const checkoutUrl = '{{ route('customer.proxy.checkout_proxy', [], false) }}';
            console.log('Checkout URL:', checkoutUrl);

            // Collect form data
            const formData = {
                items: validItems,
                delivery_address: document.getElementById('delivery_address')?.value?.trim() || '',
                delivery_phone: document.getElementById('delivery_phone')?.value?.trim() || '',
                scheduled_delivery_date: document.getElementById('scheduled_delivery_date')?.value || null,
                notes: document.getElementById('notes')?.value?.trim() || ''
            };
            console.log('Form data:', formData);

            fetch(checkoutUrl, {
                method: 'POST',
                headers: {
                    ...headers,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'include',
                body: JSON.stringify(formData)
            })
            .then(response => {
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    return response.text().then(text => {
                        throw new Error('Invalid response from server');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Show beautiful success modal
                    showOrderSuccessModal(data.order?.order_number, data.message);
                    cartItems = [];
                    saveCart();
                    renderCart();
                } else {
                    showToastMessage(data.message || (currentLang === 'ar' ? 'حدث خطأ أثناء الطلب' : 'Error occurred'), true);
                    checkoutBtn.innerText = originalText;
                    checkoutBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Checkout error:', error);
                console.error('Error message:', error.message);
                console.error('Error stack:', error.stack);
                showToastMessage(currentLang === 'ar' ? 'حدث خطأ في الاتصال: ' + error.message : 'Connection error: ' + error.message, true);
                checkoutBtn.innerText = originalText;
                checkoutBtn.disabled = false;
            });
        });

        // زر استعراض العروض (ارتباط وهمي)
        document.getElementById('empty-shop-btn')?.addEventListener('click', (e) => {
            e.preventDefault();
            window.location.href = '{{ route('customer.products', [], false) }}';
        });

        // زر تبديل اللغة (إذا كان موجوداً في الهيدر)
        const langSwitch = document.getElementById('lang-switch');
        if (langSwitch) {
            langSwitch.addEventListener('click', toggleLanguage);
        } else {
            // زر تبديل مؤقت إذا لم يكن موجوداً
            const tempBtn = document.createElement('button');
            tempBtn.id = 'temp-lang-switch';
            tempBtn.innerText = currentLang === 'ar' ? '🇬🇧 English' : '🇸🇦 العربية';
            tempBtn.className = 'fixed top-20 left-4 z-50 bg-white shadow-md rounded-full px-3 py-1 text-sm font-semibold text-purple-600';
            tempBtn.onclick = toggleLanguage;
            document.body.appendChild(tempBtn);
        }

        // تحميل البداية
        updateUILanguage();
        renderCart();

        // ========== Order Success Modal Functions ==========
        function showOrderSuccessModal(orderNumber, message) {
            const modal = document.getElementById('order-success-modal');
            const content = document.getElementById('modal-content');
            if (orderNumber) {
                document.getElementById('order-number').innerText = orderNumber;
            }
            if (message) {
                document.getElementById('order-modal-message').innerText = message;
            }
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            // Trigger animation
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeOrderSuccessModal() {
            const modal = document.getElementById('order-success-modal');
            const content = document.getElementById('modal-content');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                // Redirect to order history after modal closes
                window.location.href = '{{ route('customer.order_history', [], false) }}';
            }, 300);
        }

    </script>

    <!-- أيقونات Fontawesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
@endsection