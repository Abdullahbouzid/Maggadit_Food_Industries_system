@extends('app_android.partials.mobile.app_mobile')

@section('title', 'معلوماتي')

@section('content')
    @include('app_android.partials.mobile.header', ['title' => 'معلوماتي'])
    
    <div class="p-4" dir="rtl">
        <!-- زر الرجوع -->
        <button onclick="window.history.back()" class="flex items-center gap-2 text-purple-600 bg-purple-50 px-4 py-2 rounded-full text-sm font-semibold transition hover:bg-purple-100 mb-5">
            <i class="fas fa-arrow-right"></i> <span>رجوع</span>
        </button>

        <!-- الصورة الشخصية ومرحب -->
        <div class="text-center mb-6">
            <div class="relative inline-block">
                <div class="w-24 h-24 mx-auto bg-gradient-to-br from-purple-600 to-indigo-700 rounded-full flex items-center justify-center shadow-lg cursor-pointer" id="profile-avatar-btn">
                    <i class="fas fa-user text-white text-4xl"></i>
                </div>
                <button id="edit-avatar-btn" class="absolute bottom-0 right-0 bg-white rounded-full p-1 shadow-md">
                    <i class="fas fa-camera text-purple-600 text-xs"></i>
                </button>
            </div>
            <h2 class="text-xl font-bold text-gray-800 mt-2" id="display-name">تمر القوي</h2>
            <p class="text-xs text-gray-400">عضو منذ 2023</p>
        </div>

        <!-- بطاقات البيانات الشخصية -->
        <div class="space-y-3">
            <div class="bg-white rounded-2xl shadow-md p-4 flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-400">الاسم الكامل</p>
                    <p class="font-semibold text-gray-800" id="info-name">تمر القوي</p>
                </div>
                <i class="fas fa-user text-gray-300 text-lg"></i>
            </div>

            <div class="bg-white rounded-2xl shadow-md p-4 flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-400">البريد الإلكتروني</p>
                    <p class="font-semibold text-gray-800" id="info-email">tamer@example.com</p>
                </div>
                <i class="fas fa-envelope text-gray-300 text-lg"></i>
            </div>

            <div class="bg-white rounded-2xl shadow-md p-4 flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-400">رقم الجوال</p>
                    <p class="font-semibold text-gray-800" id="info-phone">05xxxxxxxx</p>
                </div>
                <i class="fas fa-phone-alt text-gray-300 text-lg"></i>
            </div>

            <div class="bg-white rounded-2xl shadow-md p-4 flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-400">المنظمة</p>
                    <p class="font-semibold text-gray-800" id="info-org">ماجديت للصناعات الغذائية</p>
                </div>
                <i class="fas fa-building text-gray-300 text-lg"></i>
            </div>

            <div class="bg-white rounded-2xl shadow-md p-4 flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-400">تاريخ الميلاد</p>
                    <p class="font-semibold text-gray-800" id="info-dob">15 مارس 1985</p>
                </div>
                <i class="fas fa-calendar-alt text-gray-300 text-lg"></i>
            </div>
        </div>

        <!-- زر تعديل البيانات -->
        <button id="edit-info-btn" class="mt-6 w-full bg-gradient-to-r from-purple-600 to-indigo-700 text-white font-bold py-3 rounded-xl shadow-md transition transform hover:scale-[1.02] active:scale-95">
            <i class="fas fa-edit ml-2"></i> تعديل البيانات الشخصية
        </button>
       <div id="logout-btn" class="mt-6 rounded-xl p-4 flex items-center justify-center gap-3  ">
            <br><br>
        </div>
    </div>

    <!-- مودال تعديل البيانات -->
    <div id="edit-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 transition-all duration-300 opacity-0 pointer-events-none">
        <div class="bg-white rounded-2xl w-11/12 max-w-md p-5 transform scale-95 transition-all duration-300 shadow-2xl" dir="rtl">
            <div class="text-center mb-4">
                <div class="w-14 h-14 mx-auto bg-purple-100 rounded-full flex items-center justify-center text-purple-600 text-2xl">
                    <i class="fas fa-user-edit"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mt-2">تعديل المعلومات</h3>
            </div>

            <form id="edit-info-form" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">الاسم الكامل</label>
                    <input type="text" id="edit-name" class="w-full p-3 border border-gray-200 rounded-xl focus:border-purple-400 focus:ring-2 focus:ring-purple-200 transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">البريد الإلكتروني</label>
                    <input type="email" id="edit-email" class="w-full p-3 border border-gray-200 rounded-xl focus:border-purple-400 focus:ring-2 focus:ring-purple-200 transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">رقم الجوال</label>
                    <input type="tel" id="edit-phone" class="w-full p-3 border border-gray-200 rounded-xl focus:border-purple-400 focus:ring-2 focus:ring-purple-200 transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">تاريخ الميلاد</label>
                    <input type="date" id="edit-dob" class="w-full p-3 border border-gray-200 rounded-xl focus:border-purple-400 focus:ring-2 focus:ring-purple-200 transition">
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" id="cancel-edit" class="flex-1 bg-gray-100 text-gray-700 py-2 rounded-xl">إلغاء</button>
                    <button type="submit" class="flex-1 bg-purple-600 text-white py-2 rounded-xl">حفظ التغييرات</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast الإشعار -->
    <div id="info-toast" class="fixed bottom-20 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-5 py-3 rounded-full shadow-lg text-sm z-50 transition-all duration-300 opacity-0 translate-y-10 pointer-events-none"></div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <script>
        // بيانات المستخدم الحالية
        let userData = {
            name: "تمر القوي",
            email: "tamer@example.com",
            phone: "05xxxxxxxx",
            org: "ماجديت للصناعات الغذائية",
            dob: "1985-03-15"
        };

        // عناصر DOM
        const modal = document.getElementById('edit-modal');
        const editBtn = document.getElementById('edit-info-btn');
        const cancelBtn = document.getElementById('cancel-edit');
        const editForm = document.getElementById('edit-info-form');
        const toast = document.getElementById('info-toast');

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

        function openModal() {
            // ملء الحقول بالقيم الحالية
            document.getElementById('edit-name').value = userData.name;
            document.getElementById('edit-email').value = userData.email;
            document.getElementById('edit-phone').value = userData.phone;
            document.getElementById('edit-dob').value = userData.dob;
            
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.classList.add('opacity-100');
            document.querySelector('#edit-modal .bg-white').classList.remove('scale-95');
            document.querySelector('#edit-modal .bg-white').classList.add('scale-100');
        }

        function closeModal() {
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0', 'pointer-events-none');
            document.querySelector('#edit-modal .bg-white').classList.remove('scale-100');
            document.querySelector('#edit-modal .bg-white').classList.add('scale-95');
        }

        function updateUI() {
            document.getElementById('display-name').innerText = userData.name;
            document.getElementById('info-name').innerText = userData.name;
            document.getElementById('info-email').innerText = userData.email;
            document.getElementById('info-phone').innerText = userData.phone;
            document.getElementById('info-org').innerText = userData.org;
            // تنسيق التاريخ للعرض
            const dateObj = new Date(userData.dob);
            const formattedDate = dateObj.toLocaleDateString('ar-EG', { year: 'numeric', month: 'long', day: 'numeric' });
            document.getElementById('info-dob').innerText = formattedDate;
        }

        editBtn.addEventListener('click', openModal);
        cancelBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

        editForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const newName = document.getElementById('edit-name').value.trim();
            const newEmail = document.getElementById('edit-email').value.trim();
            const newPhone = document.getElementById('edit-phone').value.trim();
            const newDob = document.getElementById('edit-dob').value;

            if (!newName || !newEmail || !newPhone) {
                showToast('❌ الرجاء ملء جميع الحقول', true);
                return;
            }
            if (!newEmail.includes('@')) {
                showToast('❌ البريد الإلكتروني غير صحيح', true);
                return;
            }

            // تحديث البيانات
            userData.name = newName;
            userData.email = newEmail;
            userData.phone = newPhone;
            userData.dob = newDob;
            updateUI();
            closeModal();
            showToast('✅ تم تحديث بياناتك بنجاح');
        });

        // إغلاق المودال عند الضغط على Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('opacity-100')) closeModal();
        });
    </script>
@endsection