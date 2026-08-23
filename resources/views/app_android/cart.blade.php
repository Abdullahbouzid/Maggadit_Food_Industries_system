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
            <a href="{{ route('customer.products') }}" id="empty-shop-btn" class="inline-block bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 rounded-full shadow-lg transition">
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
                    <div class="flex justify-between">
                        <span id="subtotal-label" class="text-gray-500">المجموع الفرعي</span>
                        <span id="subtotal-value" class="font-semibold text-gray-700">0.00 ر.س</span>
                    </div>
                    <div class="flex justify-between">
                        <span id="delivery-label" class="text-gray-500">رسوم التوصيل</span>
                        <span id="delivery-value" class="font-semibold text-gray-700">0.00 ر.س</span>
                    </div>
                    <div class="flex justify-between border-t border-gray-200 pt-2 mt-2">
                        <span id="total-label" class="text-lg font-bold text-gray-800">الإجمالي</span>
                        <span id="total-value" class="text-lg font-bold text-purple-600">0.00 ر.س</span>
                    </div>
                </div>
                <button id="checkout-btn" class="w-full bg-gradient-to-r from-purple-600 to-indigo-700 hover:from-purple-700 hover:to-indigo-800 text-white font-bold py-3 rounded-xl mt-5 shadow-md transition transform hover:scale-[1.02] active:scale-95">
                    متابعة إلى الدفع
                </button>
            </div>
        </div>
        <br><br>
      <div id="logout-btn" class="mt-6 rounded-xl p-4 flex items-center justify-center gap-3  ">
            <br><br>
        </div>
    </div>

    <!-- Toast إشعارات -->
    <div id="cart-toast" class="fixed bottom-20 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-5 py-3 rounded-full shadow-lg text-sm font-medium z-50 transition-all duration-300 opacity-0 translate-y-10 pointer-events-none"></div>

    <script>
        // استرجاع السلة من التخزين المحلي
        let cartItems = JSON.parse(localStorage.getItem('maggadit_cart')) || [];
        let deliveryFee = 15; // رسوم توصيل ثابتة

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
                delivery_label: "Delivery Fee",
                total_label: "Total",
                checkout_btn: "Proceed to Checkout",
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
                delivery_label: "رسوم التوصيل",
                total_label: "الإجمالي",
                checkout_btn: "متابعة إلى الدفع",
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
            document.getElementById('subtotal-label').innerText = t.subtotal_label;
            document.getElementById('delivery-label').innerText = t.delivery_label;
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
            let total = subtotal > 0 ? subtotal + deliveryFee : 0;
            return { subtotal, total };
        }

        // دالة التوست
        function showToastMessage(msg, isError = false) {
            const toast = document.getElementById('cart-toast');
            toast.innerText = msg;
            toast.style.background = isError ? '#dc2626' : '#1f2937';
            toast.classList.remove('opacity-0', 'translate-y-10');
            toast.classList.add('opacity-100', 'translate-y-0');
            setTimeout(() => {
                toast.classList.remove('opacity-100', 'translate-y-0');
                toast.classList.add('opacity-0', 'translate-y-10');
            }, 2000);
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
                itemDiv.className = 'bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100 transition hover:shadow-lg';
                
                let imageContent = item.image && item.image.startsWith('http') 
                    ? `<img src="${item.image}" class="w-full h-full object-cover">`
                    : `<span>${item.image || '📦'}</span>`;

                itemDiv.innerHTML = `
                    <div class="flex flex-row">
                        <div class="w-24 h-24 bg-gradient-to-br from-purple-100 to-indigo-100 flex items-center justify-center text-4xl overflow-hidden">
                            ${imageContent}
                        </div>
                        <div class="flex-1 p-3 text-right">
                            <div class="flex justify-between items-start">
                                <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full">${item.discount > 0 ? `-${item.discount}%` : 'جديد'}</span>
                                <h3 class="font-bold text-gray-800">${currentLang === 'ar' ? (item.name_ar || item.name) : (item.name_en || item.name)}</h3>
                            </div>
                            <div class="flex justify-between items-center mt-2">
                                <div class="flex items-center gap-2 bg-gray-100 rounded-full px-3 py-1">
                                    <button class="decrement-qty w-6 h-6 rounded-full bg-white text-gray-600 font-bold text-center leading-6 shadow-sm hover:bg-gray-200" data-index="${index}">-</button>
                                    <span class="item-qty w-8 text-center font-semibold" data-index="${index}">${item.quantity}</span>
                                    <button class="increment-qty w-6 h-6 rounded-full bg-white text-gray-600 font-bold text-center leading-6 shadow-sm hover:bg-gray-200" data-index="${index}">+</button>
                                </div>
                                <div class="text-left">
                                    <span class="font-bold text-purple-700">${formatPrice(item.price)}</span>
                                    ${item.quantity > 1 ? `<span class="text-xs text-gray-400 block">${formatPrice(item.price * item.quantity)}</span>` : ''}
                                </div>
                            </div>
                            <div class="mt-2 text-left">
                                <button class="remove-item text-red-500 text-xs font-semibold" data-index="${index}"><i class="fas fa-trash-alt ml-1"></i> ${t.delete_btn}</button>
                            </div>
                        </div>
                    </div>
                `;
                container.appendChild(itemDiv);
            });

            // تحديث الملخص
            const { subtotal, total } = calculateTotals();
            document.getElementById('subtotal-value').innerHTML = formatPrice(subtotal);
            document.getElementById('delivery-value').innerHTML = formatPrice(deliveryFee);
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
                showToastMessage(translations[currentLang].delete_btn + ' ' + (currentLang === 'ar' ? 'تم الحذف' : 'removed'), false);
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
        document.getElementById('checkout-btn')?.addEventListener('click', () => {
            if (cartItems.length === 0) {
                showToastMessage(currentLang === 'ar' ? 'السلة فارغة' : 'Cart is empty', true);
                return;
            }

            const checkoutBtn = document.getElementById('checkout-btn');
            const originalText = checkoutBtn.innerText;
            checkoutBtn.innerText = currentLang === 'ar' ? 'جاري المعالجة...' : 'Processing...';
            checkoutBtn.disabled = true;

            const token = localStorage.getItem('customer_token') || localStorage.getItem('api_token');
            const headers = {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            };
            if (token) {
                headers['Authorization'] = 'Bearer ' + token;
            }

            fetch('{{ route('customer.checkout') }}', {
                method: 'POST',
                headers: {
                    ...headers,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ items: cartItems })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToastMessage(data.message, false);
                    cartItems = []; // تفريغ السلة
                    saveCart(); // تحديث التخزين المحلي
                    renderCart();
                    
                    // توجيه المستخدم لصفحة الطلبات بعد ثانيتين
                    setTimeout(() => {
                        window.location.href = '{{ route('customer.order_history') }}';
                    }, 2000);
                } else {
                    showToastMessage(data.message || (currentLang === 'ar' ? 'حدث خطأ أثناء الطلب' : 'Error occurred'), true);
                    checkoutBtn.innerText = originalText;
                    checkoutBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToastMessage(currentLang === 'ar' ? 'حدث خطأ في الاتصال' : 'Connection error', true);
                checkoutBtn.innerText = originalText;
                checkoutBtn.disabled = false;
            });
        });

        // زر استعراض العروض (ارتباط وهمي)
        document.getElementById('empty-shop-btn')?.addEventListener('click', (e) => {
            e.preventDefault();
            window.location.href = '{{ route('customer.products') }}';
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
    </script>

    <!-- أيقونات Fontawesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
@endsection