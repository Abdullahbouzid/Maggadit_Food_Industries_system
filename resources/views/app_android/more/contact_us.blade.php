@extends('app_android.partials.mobile.app_mobile')
@section('title', 'التواصل معنا')
@section('content')
    @include('app_android.partials.mobile.header', ['title' => 'التواصل معنا'])
    
    <div class="p-4" dir="rtl">
        <!-- زر الرجوع -->
        <button onclick="window.history.back()" 
                class="flex items-center gap-2 text-purple-600 bg-purple-50 px-4 py-2 rounded-full text-sm font-semibold transition hover:bg-purple-100 mb-5">
            <i class="fas fa-arrow-right"></i> 
            <span>رجوع</span>
        </button>

        <!-- معلومات الاتصال السريعة -->
        <div class="grid grid-cols-1 gap-4 mb-6">
            <div class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-2xl p-4 flex items-center gap-3">
                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center text-purple-600">
                    <i class="fas fa-phone-alt text-lg"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-400">اتصل بنا</p>
                    <p class="font-semibold text-gray-800">+966 12 345 6789</p>
                </div>
            </div>
            <div class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-2xl p-4 flex items-center gap-3">
                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center text-purple-600">
                    <i class="fas fa-envelope text-lg"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-400">بريد إلكتروني</p>
                    <p class="font-semibold text-gray-800">support@maggadit.com</p>
                </div>
            </div>
            <div class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-2xl p-4 flex items-center gap-3">
                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center text-purple-600">
                    <i class="fab fa-whatsapp text-lg"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-400">واتساب</p>
                    <p class="font-semibold text-gray-800">+966 55 555 5555</p>
                </div>
            </div>
        </div>

        <!-- نموذج التواصل -->
        <div class="bg-white rounded-2xl shadow-md p-5">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-paper-plane text-purple-600"></i>
                <span>أرسل رسالتك</span>
            </h3>
            <form id="contact-form" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">الاسم الكامل</label>
                    <input type="text" id="contact-name" placeholder="أدخل اسمك" 
                           class="w-full p-3 border border-gray-200 rounded-xl focus:border-purple-400 focus:ring-2 focus:ring-purple-200 transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">البريد الإلكتروني</label>
                    <input type="email" id="contact-email" placeholder="بريدك الإلكتروني" 
                           class="w-full p-3 border border-gray-200 rounded-xl focus:border-purple-400 focus:ring-2 focus:ring-purple-200 transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">الرسالة</label>
                    <textarea id="contact-message" rows="4" placeholder="اكتب رسالتك هنا..." 
                              class="w-full p-3 border border-gray-200 rounded-xl focus:border-purple-400 focus:ring-2 focus:ring-purple-200 transition"></textarea>
                </div>
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-purple-600 to-indigo-700 text-white font-bold py-3 rounded-xl shadow-md transition transform hover:scale-[1.02] active:scale-95">
                    <i class="fas fa-paper-plane ml-2"></i> إرسال الرسالة
                </button>
            </form>
        </div>

        <!-- خريطة افتراضية (يمكن استبدالها بخريطة حقيقية) -->
        <div class="mt-6 bg-white rounded-2xl shadow-md p-3 text-center">
            <p class="text-sm text-gray-500 mb-2">📍 موقعنا</p>
            <div class="bg-gray-100 rounded-xl p-4 text-gray-400 text-sm">
                <i class="fas fa-map-marker-alt text-purple-600 ml-1"></i> 
         طرابلس - ليبيا 
            </div>
        </div>
            <div id="logout-btn" class="mt-6 rounded-xl p-4 flex items-center justify-center gap-3  ">
            <br><br>
        </div>
    </div>

    <!-- Toast الإشعار -->
    <div id="contact-toast" 
         class="fixed bottom-20 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-5 py-3 rounded-full shadow-lg text-sm z-50 transition-all duration-300 opacity-0 translate-y-10 pointer-events-none">
        ✅ تم إرسال رسالتك بنجاح
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <script>
        const toast = document.getElementById('contact-toast');
        function showToast(msg, isError = false) {
            toast.innerText = msg;
            toast.style.background = isError ? '#dc2626' : '#1f2937';
            toast.classList.remove('opacity-0', 'translate-y-10');
            toast.classList.add('opacity-100', 'translate-y-0');
            setTimeout(() => {
                toast.classList.remove('opacity-100', 'translate-y-0');
                toast.classList.add('opacity-0', 'translate-y-10');
            }, 3000);
        }

        document.getElementById('contact-form').addEventListener('submit', function(e) {
            e.preventDefault();
            let name = document.getElementById('contact-name').value.trim();
            let email = document.getElementById('contact-email').value.trim();
            let message = document.getElementById('contact-message').value.trim();
            
            if (!name || !email || !message) {
                showToast('❌ الرجاء ملء جميع الحقول', true);
                return;
            }
            if (!email.includes('@')) {
                showToast('❌ البريد الإلكتروني غير صحيح', true);
                return;
            }
            
            // محاكاة الإرسال (يمكن ربطه بـ AJAX لاحقاً)
            showToast('✅ جاري إرسال رسالتك...');
            setTimeout(() => {
                showToast('✅ تم إرسال رسالتك بنجاح، سنتواصل معك قريباً');
                document.getElementById('contact-form').reset();
            }, 1500);
        });
    </script>
@endsection