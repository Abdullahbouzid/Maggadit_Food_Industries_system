@extends('app_android.partials.mobile.app_mobile')

@section('title', __('messages.dashboard_title'))

@section('content')
    @include('app_android.partials.mobile.header', ['title' => __('messages.dashboard_title')])

    <div class="px-4 py-4 space-y-4" style="direction: rtl;">
        <!-- User Info Card -->
        <div class="bg-gradient-to-br from-purple-600 to-indigo-700 rounded-2xl p-5 shadow-lg">
            <div class="flex justify-between items-start">
                <div>
                    <p id="user-name-label" class="text-white/80 text-sm">مرحباً،</p>
                    <h2 id="user-name" class="text-2xl font-bold text-white mt-1">تمر القوي</h2>
                    <p id="user-doc" class="text-white/70 text-sm mt-2">DOC-10-000001</p>
                </div>
                <i class="fas fa-user-circle text-5xl text-white/60"></i>
            </div>
        </div>

        <!-- بطاقات القائمة -->
        <div class="space-y-3">
            <div id="change-org" class="bg-white rounded-xl shadow-md p-4 flex items-center justify-between cursor-pointer transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5 active:scale-[0.98] border border-gray-100">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-600">
                        <i class="fas fa-building text-lg"></i>
                    </div>
                    <div>
                        <h3 id="change-org-title" class="font-bold text-gray-800">تغيير المنظمة</h3>
                        <p id="change-org-sub" class="text-xs text-gray-400">التبديل بين المنظمات</p>
                    </div>
                </div>
                <i class="fas fa-chevron-left text-gray-300 text-sm"></i>
            </div>

            <div id="financial-stmt" class="bg-white rounded-xl shadow-md p-4 flex items-center justify-between cursor-pointer transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5 active:scale-[0.98] border border-gray-100">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                        <i class="fas fa-chart-line text-lg"></i>
                    </div>
                    <div>
                        <h3 id="financial-title" class="font-bold text-gray-800">كشف حساب مالي</h3>
                        <p id="financial-sub" class="text-xs text-gray-400">عرض الحركات المالية</p>
                    </div>
                </div>
                <i class="fas fa-chevron-left text-gray-300 text-sm"></i>
            </div>

            <div id="change-pwd" class="bg-white rounded-xl shadow-md p-4 flex items-center justify-between cursor-pointer transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5 active:scale-[0.98] border border-gray-100">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-600">
                        <i class="fas fa-key text-lg"></i>
                    </div>
                    <div>
                        <h3 id="change-pwd-title" class="font-bold text-gray-800">تغيير كلمة السر</h3>
                        <p id="change-pwd-sub" class="text-xs text-gray-400">تحديث كلمة المرور</p>
                    </div>
                </div>
                <i class="fas fa-chevron-left text-gray-300 text-sm"></i>
            </div>

            <div id="order-history" class="bg-white rounded-xl shadow-md p-4 flex items-center justify-between cursor-pointer transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5 active:scale-[0.98] border border-gray-100">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-600">
                        <i class="fas fa-history text-lg"></i>
                    </div>
                    <div>
                        <h3 id="order-title" class="font-bold text-gray-800">تاريخ الطلبات</h3>
                        <p id="order-sub" class="text-xs text-gray-400">سجل طلباتك السابقة</p>
                    </div>
                </div>
                <i class="fas fa-chevron-left text-gray-300 text-sm"></i>
            </div>
        </div>

        <!-- زر تسجيل الخروج -->
        <div id="logout-btn" class="mt-6 bg-red-50 rounded-xl p-4 flex items-center justify-center gap-3 cursor-pointer transition-all duration-200 hover:bg-red-100 active:scale-[0.98] border border-red-200 shadow-sm">
            <i class="fas fa-sign-out-alt text-red-600 text-lg"></i>
            <span id="logout-text" class="font-bold text-red-700">تسجيل خروج</span>
        </div>
    </div>

    <!-- Toast الإشعار -->
    <div id="logout-toast" class="fixed bottom-20 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-5 py-3 rounded-full shadow-lg text-sm font-medium z-50 transition-all duration-300 opacity-0 translate-y-10 pointer-events-none" style="white-space: nowrap;">
        🚪 جاري تسجيل الخروج...
    </div>

    <!-- ========== مودال التأكيد الجميل ========== -->
    <div id="confirm-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 transition-all duration-300 opacity-0 pointer-events-none" style="direction: rtl;">
        <div class="bg-white rounded-2xl w-80 max-w-[85%] p-6 transform scale-95 transition-all duration-300 shadow-2xl">
            <div class="text-center">
                <!-- أيقونة -->
                <div class="w-16 h-16 mx-auto bg-red-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-sign-out-alt text-red-600 text-2xl"></i>
                </div>
                <!-- عنوان النص حسب اللغة -->
                <h3 id="modal-title" class="text-lg font-bold text-gray-800 mb-2">تسجيل الخروج</h3>
                <p id="modal-message" class="text-gray-500 text-sm mb-6">هل أنت متأكد من تسجيل الخروج؟</p>
                <!-- الأزرار -->
                <div class="flex gap-3">
                    <button id="modal-confirm" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 rounded-xl transition duration-200 shadow-md">
                        نعم، تسجيل خروج
                    </button>
                    <button id="modal-cancel" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2.5 rounded-xl transition duration-200">
                        إلغاء
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // بيانات الترجمة (نفس السابق مع إضافة نصوص المودال)
        const translations = {
            en: {
                page_title: "More",
                user_name_label: "Welcome,",
                user_name: "Tamer Alqawi",
                user_doc: "DOC-10-000001",
                change_org_title: "Change Organization",
                change_org_sub: "Switch between organizations",
                financial_title: "Financial Statement",
                financial_sub: "View transaction history",
                change_pwd_title: "Change Password",
                change_pwd_sub: "Update your password",
                order_title: "Order History",
                order_sub: "Your previous orders",
                logout_text: "Logout",
                nav_home: "Home",
                nav_offers: "Offers",
                nav_cart: "Cart",
                nav_favorite: "Favorite",
                nav_more: "More",
                lang_switch_text: "🇸🇦 العربية",
                dir: "ltr",
                text_align: "left",
                logout_toast_msg: "🚪 Logging out...",
                modal_title: "Logout",
                modal_message: "Are you sure you want to logout?",
                modal_confirm: "Yes, Logout",
                modal_cancel: "Cancel"
            },
            ar: {
                page_title: "المزيد",
                user_name_label: "مرحباً،",
                user_name: "تمر القوي",
                user_doc: "DOC-10-000001",
                change_org_title: "تغيير المنظمة",
                change_org_sub: "التبديل بين المنظمات",
                financial_title: "كشف حساب مالي",
                financial_sub: "عرض الحركات المالية",
                change_pwd_title: "تغيير كلمة السر",
                change_pwd_sub: "تحديث كلمة المرور",
                order_title: "تاريخ الطلبات",
                order_sub: "سجل طلباتك السابقة",
                logout_text: "تسجيل خروج",
                nav_home: "الرئيسية",
                nav_offers: "العروض",
                nav_cart: "السلة",
                nav_favorite: "المفضلة",
                nav_more: "المزيد",
                lang_switch_text: "🇬🇧 English",
                dir: "rtl",
                text_align: "right",
                logout_toast_msg: "🚪 جاري تسجيل الخروج...",
                modal_title: "تسجيل الخروج",
                modal_message: "هل أنت متأكد من تسجيل الخروج؟",
                modal_confirm: "نعم، تسجيل خروج",
                modal_cancel: "إلغاء"
            }
        };

        let currentLang = localStorage.getItem('more_lang') || 'en';

        function setLanguage(lang) {
            currentLang = lang;
            localStorage.setItem('more_lang', lang);
            const t = translations[lang];

            document.getElementById('page-title').innerText = t.page_title;
            document.getElementById('user-name-label').innerText = t.user_name_label;
            document.getElementById('user-name').innerText = t.user_name;
            document.getElementById('user-doc').innerText = t.user_doc;
            document.getElementById('change-org-title').innerText = t.change_org_title;
            document.getElementById('change-org-sub').innerText = t.change_org_sub;
            document.getElementById('financial-title').innerText = t.financial_title;
            document.getElementById('financial-sub').innerText = t.financial_sub;
            document.getElementById('change-pwd-title').innerText = t.change_pwd_title;
            document.getElementById('change-pwd-sub').innerText = t.change_pwd_sub;
            document.getElementById('order-title').innerText = t.order_title;
            document.getElementById('order-sub').innerText = t.order_sub;
            document.getElementById('logout-text').innerText = t.logout_text;
            document.getElementById('nav-home').innerText = t.nav_home;
            document.getElementById('nav-offers').innerText = t.nav_offers;
            document.getElementById('nav-cart').innerText = t.nav_cart;
            document.getElementById('nav-favorite').innerText = t.nav_favorite;
            document.getElementById('nav-more').innerText = t.nav_more;
            document.getElementById('lang-switch').innerText = t.lang_switch_text;

            document.documentElement.setAttribute('dir', t.dir);
            document.documentElement.lang = lang === 'ar' ? 'ar' : 'en';
            document.body.style.textAlign = t.text_align;

            // تحديث نصوص المودال
            document.getElementById('modal-title').innerText = t.modal_title;
            document.getElementById('modal-message').innerText = t.modal_message;
            document.getElementById('modal-confirm').innerText = t.modal_confirm;
            document.getElementById('modal-cancel').innerText = t.modal_cancel;
        }

        // دالة إظهار التوست
        function showLogoutToast(message) {
            const toast = document.getElementById('logout-toast');
            toast.innerText = message;
            toast.classList.remove('opacity-0', 'translate-y-10');
            toast.classList.add('opacity-100', 'translate-y-0');
            setTimeout(() => {
                toast.classList.remove('opacity-100', 'translate-y-0');
                toast.classList.add('opacity-0', 'translate-y-10');
            }, 1500);
        }

        // دوال التحكم في المودال
        const modal = document.getElementById('confirm-modal');
        let pendingLogout = null;

        function showModal() {
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.classList.add('opacity-100');
            const modalContent = modal.querySelector('.bg-white');
            modalContent.classList.remove('scale-95');
            modalContent.classList.add('scale-100');
        }

        function hideModal() {
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0', 'pointer-events-none');
            const modalContent = modal.querySelector('.bg-white');
            modalContent.classList.remove('scale-100');
            modalContent.classList.add('scale-95');
        }

        // تبديل اللغة
        document.getElementById('lang-switch').addEventListener('click', () => {
            const newLang = currentLang === 'en' ? 'ar' : 'en';
            setLanguage(newLang);
        });

        // التنقل عبر الشريط السفلي
        document.querySelectorAll('.nav-item-premium').forEach(item => {
            item.addEventListener('click', function(e) {
                const target = this.getAttribute('data-nav');
                const routes = {
                    home: "{{ route('customer.dashboard') }}",
                    offers: "{{ route('customer.offers') }}",
                    more: "{{ route('customer.more') }}"
                };
                if (routes[target]) {
                    if (target === 'more') return;
                    window.location.href = routes[target];
                } else {
                    alert('سيتم إضافة هذه الصفحة قريباً');
                }
            });
        });

        // أحداث القائمة
        document.getElementById('change-org').addEventListener('click', () => alert('تغيير المنظمة - سيتم التوجيه لاحقاً'));
        document.getElementById('financial-stmt').addEventListener('click', () => alert('كشف الحساب المالي - قيد التطوير'));
        document.getElementById('change-pwd').addEventListener('click', () => alert('تغيير كلمة السر - سيتم فتح نموذج'));
        document.getElementById('order-history').addEventListener('click', () => alert('تاريخ الطلبات - يعرض القائمة'));

        // تسجيل الخروج باستخدام المودال الجميل
        document.getElementById('logout-btn').addEventListener('click', () => {
            showModal();
        });

        // تأكيد الخروج من المودال
        document.getElementById('modal-confirm').addEventListener('click', () => {
            hideModal();
            const t = translations[currentLang];
            showLogoutToast(t.logout_toast_msg);
            setTimeout(() => {
                window.location.href = "{{ route('logout') }}";
            }, 1200);
        });

        // إلغاء الخروج
        document.getElementById('modal-cancel').addEventListener('click', () => {
            hideModal();
        });

        // إغلاق المودال عند الضغط على الخلفية
        modal.addEventListener('click', (e) => {
            if (e.target === modal) hideModal();
        });

        // تعيين اللغة الأولية
        setLanguage(currentLang);
    </script>
@endsection