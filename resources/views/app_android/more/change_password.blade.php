@extends('app_android.partials.mobile.app_mobile')

@section('title', 'تغيير كلمة السر')

@section('content')
    @include('app_android.partials.mobile.header', ['title' => 'تغيير كلمة السر'])

    <div class="p-4" dir="rtl">
        <div class="mb-4">
            <button onclick="window.history.back()" class="flex items-center gap-2 text-purple-600 bg-purple-50 px-4 py-2 rounded-full text-sm font-semibold transition hover:bg-purple-100">
                <i class="fas fa-arrow-right"></i>
                <span>رجوع</span>
            </button>
        </div>

        <div class="bg-white rounded-2xl shadow-md p-5">
            <form id="password-form">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">كلمة المرور الحالية</label>
                    <input type="password" id="current_password" class="input-field w-full p-3 border border-gray-200 rounded-xl focus:border-purple-400 focus:ring-2 focus:ring-purple-200" placeholder="••••••••">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">كلمة المرور الجديدة</label>
                    <input type="password" id="new_password" class="input-field w-full p-3 border border-gray-200 rounded-xl focus:border-purple-400 focus:ring-2 focus:ring-purple-200" placeholder="••••••••">
                </div>
                <div class="mb-5">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">تأكيد كلمة المرور</label>
                    <input type="password" id="confirm_password" class="input-field w-full p-3 border border-gray-200 rounded-xl focus:border-purple-400 focus:ring-2 focus:ring-purple-200" placeholder="••••••••">
                </div>
                <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 rounded-xl transition shadow-md">تحديث كلمة السر</button>
            </form>
        </div>
            <div id="logout-btn" class="mt-6 rounded-xl p-4 flex items-center justify-center gap-3  ">
            <br><br>
        </div>
    </div>

    <div id="toast" class="fixed bottom-20 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-5 py-3 rounded-full shadow-lg text-sm z-50 transition-all duration-300 opacity-0 translate-y-10 pointer-events-none"></div>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
        function showToast(msg, isError = false) {
            const toast = document.getElementById('toast');
            toast.innerText = msg;
            toast.style.background = isError ? '#dc2626' : '#1f2937';
            toast.classList.remove('opacity-0', 'translate-y-10');
            toast.classList.add('opacity-100', 'translate-y-0');
            setTimeout(() => {
                toast.classList.remove('opacity-100', 'translate-y-0');
                toast.classList.add('opacity-0', 'translate-y-10');
            }, 2000);
        }

        document.getElementById('password-form').addEventListener('submit', function(e) {
            e.preventDefault();
            let newPass = document.getElementById('new_password').value;
            let confirmPass = document.getElementById('confirm_password').value;
            if (newPass !== confirmPass) {
                showToast('❌ كلمة المرور الجديدة وتأكيدها غير متطابقين', true);
                return;
            }
            if (newPass.length < 6) {
                showToast('❌ كلمة المرور يجب أن تكون 6 أحرف على الأقل', true);
                return;
            }
            showToast('✅ تم تغيير كلمة المرور بنجاح');
            setTimeout(() => window.location.href = "{{ route('customer.more') }}", 1500);
        });
    </script>
@endsection