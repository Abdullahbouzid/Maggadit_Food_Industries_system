@extends('app_android.partials.mobile.app_mobile')

@section('title', 'معلوماتي')

@section('content')
    @include('app_android.partials.mobile.header', ['title' => 'معلوماتي'])

    <div class="p-4" dir="rtl">
        <!-- زر الرجوع -->
        <button onclick="window.history.back()" class="flex items-center gap-2 text-purple-600 bg-purple-50 px-4 py-2 rounded-full text-sm font-semibold transition hover:bg-purple-100 mb-5">
            <i class="fas fa-arrow-right"></i> <span>رجوع</span>
        </button>

        <!-- التنبيهات ستظهر عن طريق الـ Toast في الأسفل -->

        <!-- الصورة الشخصية ومرحب -->
        <div class="text-center mb-6">
            <div class="relative inline-block cursor-pointer" onclick="openModal()">
                @if($customer->avatar)
                    <img src="{{ asset('uploads/customers/' . $customer->avatar) }}" alt="Avatar" class="w-24 h-24 mx-auto rounded-full object-cover shadow-lg" id="main-avatar">
                @else
                    <div class="w-24 h-24 mx-auto bg-gradient-to-br from-purple-600 to-indigo-700 rounded-full flex items-center justify-center shadow-lg">
                        <i class="fas fa-user text-white text-4xl"></i>
                    </div>
                @endif
                <button class="absolute bottom-0 right-0 bg-white rounded-full p-1.5 shadow-md">
                    <i class="fas fa-camera text-purple-600 text-xs"></i>
                </button>
            </div>
            <h2 class="text-xl font-bold text-gray-800 mt-2">{{ $customer->name ?? 'مستخدم' }}</h2>
            <p class="text-xs text-gray-400">عضو منذ {{ $customer->created_at ? $customer->created_at->format('Y') : date('Y') }}</p>
        </div>

        <!-- بطاقات البيانات الشخصية -->
        <div class="space-y-3">
            <div class="bg-white rounded-2xl shadow-md p-4 flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-400">الاسم الكامل</p>
                    <p class="font-semibold text-gray-800">{{ $customer->name }}</p>
                </div>
                <i class="fas fa-user text-gray-300 text-lg"></i>
            </div>

            <div class="bg-white rounded-2xl shadow-md p-4 flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-400">اسم المستخدم</p>
                    <p class="font-semibold text-gray-800">{{ $customer->username }}</p>
                </div>
                <i class="fas fa-user-tag text-gray-300 text-lg"></i>
            </div>

            <div class="bg-white rounded-2xl shadow-md p-4 flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-400">البريد الإلكتروني</p>
                    <p class="font-semibold text-gray-800">{{ $customer->email }}</p>
                </div>
                <i class="fas fa-envelope text-gray-300 text-lg"></i>
            </div>
        </div>

        <!-- زر تعديل البيانات -->
        <button id="edit-info-btn" class="mt-6 w-full bg-gradient-to-r from-purple-600 to-indigo-700 text-white font-bold py-3 rounded-xl shadow-md transition transform hover:scale-[1.02] active:scale-95">
            <i class="fas fa-edit ml-2"></i> تعديل البيانات الشخصية
        </button>
        <div id="logout-btn" class="mt-6 rounded-xl p-4 flex items-center justify-center gap-3">
            <br><br>
        </div>
    </div>

    <!-- مودال تعديل البيانات -->
    <div id="edit-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 transition-all duration-300 {{ $errors->any() ? 'opacity-100' : 'opacity-0 pointer-events-none' }}">
        <div class="bg-white rounded-2xl w-11/12 max-w-md p-5 transform {{ $errors->any() ? 'scale-100' : 'scale-95' }} transition-all duration-300 shadow-2xl max-h-screen overflow-y-auto" dir="rtl">
            <div class="text-center mb-4">
                <div class="w-14 h-14 mx-auto bg-purple-100 rounded-full flex items-center justify-center text-purple-600 text-2xl">
                    <i class="fas fa-user-edit"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mt-2">تعديل المعلومات</h3>
            </div>

            <form id="edit-info-form" action="{{ route('customer.my_info.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <!-- رفع الصورة -->
                <div class="text-center mb-4">
                    <div class="relative inline-block cursor-pointer" onclick="document.getElementById('avatar-input').click()">
                        @if($customer->avatar)
                            <img id="avatar-preview" src="{{ asset('uploads/customers/' . $customer->avatar) }}" class="w-20 h-20 mx-auto rounded-full object-cover shadow-md border-2 border-purple-200">
                        @else
                            <div id="avatar-preview-container" class="w-20 h-20 mx-auto bg-gray-100 rounded-full flex items-center justify-center shadow-md border-2 border-dashed border-gray-300">
                                <i class="fas fa-camera text-gray-400 text-2xl"></i>
                            </div>
                            <img id="avatar-preview" src="" class="w-20 h-20 mx-auto rounded-full object-cover shadow-md border-2 border-purple-200 hidden">
                        @endif
                        <div class="absolute bottom-0 right-0 bg-purple-600 rounded-full p-1.5 shadow-md">
                            <i class="fas fa-plus text-white text-[10px]"></i>
                        </div>
                    </div>
                    <input type="file" name="avatar" id="avatar-input" class="hidden" accept="image/*" onchange="previewImage(event)">
                    <p class="text-xs text-gray-500 mt-2">تغيير الصورة الشخصية</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">الاسم الكامل</label>
                    <input type="text" name="name" value="{{ old('name', $customer->name) }}" required class="w-full p-3 border border-gray-200 rounded-xl focus:border-purple-400 focus:ring-2 focus:ring-purple-200 transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">اسم المستخدم</label>
                    <input type="text" name="username" value="{{ old('username', $customer->username) }}" required class="w-full p-3 border border-gray-200 rounded-xl focus:border-purple-400 focus:ring-2 focus:ring-purple-200 transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ old('email', $customer->email) }}" required class="w-full p-3 border border-gray-200 rounded-xl focus:border-purple-400 focus:ring-2 focus:ring-purple-200 transition">
                </div>
                <hr class="my-4 border-gray-200">
                <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                    <p class="text-sm font-bold text-gray-800 mb-3"><i class="fas fa-lock text-purple-600 ml-1"></i> تغيير كلمة المرور <span class="text-xs text-gray-400 font-normal">(اختياري)</span></p>

                    <div class="mb-3">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">كلمة المرور الجديدة</label>
                        <div class="relative">
                            <input type="password" id="new_password" name="password" autocomplete="new-password" class="w-full p-3 pl-10 border border-gray-200 rounded-xl focus:border-purple-400 focus:ring-2 focus:ring-purple-200 transition text-sm">
                            <button type="button" onclick="togglePassword('new_password')" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-purple-600 focus:outline-none">
                                <i class="fas fa-eye" id="icon-new_password"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">تأكيد كلمة المرور</label>
                        <div class="relative">
                            <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" class="w-full p-3 pl-10 border border-gray-200 rounded-xl focus:border-purple-400 focus:ring-2 focus:ring-purple-200 transition text-sm">
                            <button type="button" onclick="togglePassword('password_confirmation')" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-purple-600 focus:outline-none">
                                <i class="fas fa-eye" id="icon-password_confirmation"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" id="cancel-edit" class="flex-1 bg-gray-100 text-gray-700 py-2 rounded-xl">إلغاء</button>
                    <button type="submit" class="flex-1 bg-purple-600 text-white py-2 rounded-xl">حفظ التغييرات</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Custom Mobile Toast Container -->
    <div id="toast-container" class="fixed top-4 left-1/2 transform -translate-x-1/2 w-[90%] max-w-sm z-[60] flex flex-col gap-2 pointer-events-none"></div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script>
        // دالة عرض الإشعارات
        function showMobileToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');

            const isSuccess = type === 'success';
            const bgColor = isSuccess ? 'bg-green-600' : 'bg-red-600';
            const iconClass = isSuccess ? 'fa-check-circle' : 'fa-exclamation-circle';

            toast.className = `${bgColor} text-white px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 transform -translate-y-10 opacity-0 transition-all duration-300 pointer-events-auto`;
            toast.innerHTML = `
                <i class="fas ${iconClass} text-lg"></i>
                <span class="text-sm font-medium flex-1">${message}</span>
                <button onclick="this.parentElement.remove()" class="text-white/80 hover:text-white focus:outline-none">
                    <i class="fas fa-times text-sm"></i>
                </button>
            `;

            container.appendChild(toast);

            // Animation in
            requestAnimationFrame(() => {
                toast.classList.remove('-translate-y-10', 'opacity-0');
                toast.classList.add('translate-y-0', 'opacity-100');
            });

            // Auto remove after 4 seconds
            setTimeout(() => {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('-translate-y-10', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        // دالة التبديل لإظهار وإخفاء كلمة المرور
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById('icon-' + inputId);

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash', 'text-purple-600');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash', 'text-purple-600');
                icon.classList.add('fa-eye');
            }
        }

        // عرض الإشعارات القادمة من السيرفر
        @if(session('success'))
            setTimeout(() => showMobileToast("{{ session('success') }}", 'success'), 500);
        @endif

        @if($errors->any())
            setTimeout(() => {
                @foreach($errors->all() as $error)
                    showMobileToast("{{ $error }}", 'error');
                @endforeach
            }, 500);
        @endif

        function previewImage(event) {
            const input = event.target;
            const preview = document.getElementById('avatar-preview');
            const previewContainer = document.getElementById('avatar-preview-container');

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    previewContainer.classList.add('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.src = '';
                preview.classList.add('hidden');
                previewContainer.classList.remove('hidden');
            }
        }

        // عناصر DOM
        const modal = document.getElementById('edit-modal');
        const editBtn = document.getElementById('edit-info-btn');
        const cancelBtn = document.getElementById('cancel-edit');

        function openModal() {
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

        editBtn.addEventListener('click', openModal);
        cancelBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

        const editForm = document.getElementById('edit-info-form');
        if (editForm) {
            editForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                const modal = document.getElementById('edit-modal');

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin ml-2"></i> جاري الحفظ...';

                const formData = new FormData(this);

                // Add CSRF token
                const token = document.querySelector('input[name="_token"]')?.value;
                if (token) {
                    formData.append('_token', token);
                }

                // Store selected file for preview update
                const fileInput = document.getElementById('avatar-input');
                const selectedFile = fileInput.files[0] ?? null;

                try {
                    const response = await fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                            // Let browser set Content-Type for multipart/form-data
                        }
                    });

                    const data = await response.json();

                    if (data.success || data.message) {
                        showMobileToast(data.message || 'تم تحديث البيانات بنجاح', 'success');

                        // Update avatar preview immediately if a new file was selected
                        if (selectedFile) {
                            const url = URL.createObjectURL(selectedFile);
                            const avatarPreview = document.getElementById('avatar-preview');
                            avatarPreview.src = url;
                            // Also update main avatar image if visible
                            const mainAvatar = document.getElementById('main-avatar');
                            if (mainAvatar) {
                                mainAvatar.src = url;
                            }
                        }

                        // Close modal and optionally reload after a short delay to ensure consistency
                        setTimeout(() => {
                            closeModal();
                            // Reload to fetch any server-side changes (e.g., cropped image)
                            window.location.reload();
                        }, 1500);
                    } else {
                        let errMsg = 'حدث خطأ غير متوقع';
                        if (data.errors) {
                            errMsg = Object.values(data.errors)[0][0] || 'تأكد من صحة البيانات';
                        }
                        showMobileToast(errMsg, 'error');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                } catch (error) {
                    console.error('Update Error:', error);
                    showMobileToast('فشل الاتصال بالسيرفر', 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            });
        }

        // إغلاق المودال عند الضغط على Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !modal.classList.contains('opacity-0')) {
                closeModal();
            }
        });
    </script>
@endsection