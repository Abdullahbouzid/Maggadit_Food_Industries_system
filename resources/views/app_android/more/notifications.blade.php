@extends('app_android.partials.mobile.app_mobile')

@section('title', 'الإشعارات')

@section('content')
    @include('app_android.partials.mobile.header', ['title' => 'الإشعارات'])

    <div class="p-4" dir="rtl">
        <!-- زر الرجوع -->
        <button onclick="window.history.back()" class="flex items-center gap-2 text-purple-600 bg-purple-50 px-4 py-2 rounded-full text-sm font-semibold transition hover:bg-purple-100 mb-4">
            <i class="fas fa-arrow-right"></i> رجوع
        </button>

        <!-- رأس الإشعارات مع زر تحديد الكل كمقروء -->
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-bold text-gray-800">جميع الإشعارات</h2>
            <button id="mark-all-read" class="text-xs text-purple-600 font-semibold bg-purple-50 px-3 py-1.5 rounded-full">
                <i class="fas fa-check-double ml-1"></i> تحديد الكل كمقروء
            </button>
        </div>

        <!-- حاوية الإشعارات -->
        <div id="notifications-container" class="space-y-4">
            @php
                // تحويل بيانات الخادم (collection) إلى مصفوفة لتتمكن الجافاسكربت من قراءتها
                $mappedNotifications = $notifications->map(function($notif) {
                    return [
                        'id' => $notif->id ?? rand(100, 900),
                        'title' => $notif->subject ?? 'إشعار جديد',
                        'message' => $notif->body ?? '',
                        'type' => $notif->icon_type ?? 'notification',
                        'date' => $notif->created_at ?? date('Y-m-d H:i:s'),
                        'is_read' => false, // حالياً كل الإشعارات الواردة تعتبر غير مقروءة حتى نطبق نظام التتبع
                    ];
                })->toArray();
            @endphp

            {{-- سيتم رسم الإشعارات عبر جافاسكربت لتسهيل التفاعل --}}
        </div>

        <!-- رسالة عند عدم وجود إشعارات -->
        <div id="no-notifications" class="hidden text-center py-8">
            <i class="fas fa-bell-slash text-gray-300 text-5xl mb-3"></i>
            <p class="text-gray-400">لا توجد إشعارات جديدة</p>
        </div>
        <div id="logout-btn" class="mt-6 rounded-xl p-4 flex items-center justify-center gap-3  ">
            <br><br>
        </div>
    </div>

    <!-- مودال تفاصيل الإشعار -->
    <div id="notification-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 transition-all duration-300 opacity-0 pointer-events-none">
        <div class="bg-white rounded-2xl w-11/12 max-w-md p-5 transform scale-95 transition-all duration-300 shadow-2xl" dir="rtl">
            <div class="text-center mb-3">
                <div class="w-14 h-14 mx-auto rounded-full bg-purple-100 flex items-center justify-center text-purple-600 text-2xl" id="modal-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mt-2" id="modal-title">عنوان الإشعار</h3>
            </div>
            <p class="text-gray-600 text-sm leading-relaxed text-center mb-4" id="modal-message">محتوى الإشعار هنا...</p>
            <p class="text-xs text-gray-400 text-center mb-4" id="modal-date"></p>
            <button id="close-modal" class="w-full bg-purple-600 text-white py-2 rounded-xl">إغلاق</button>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .line-clamp-1 {
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
    <script>
        // بيانات الإشعارات القادمة من السيرفر
        let notificationsData = @json($mappedNotifications);
        
        // عناصر DOM
        const container = document.getElementById('notifications-container');
        const noNotificationsDiv = document.getElementById('no-notifications');
        const markAllBtn = document.getElementById('mark-all-read');
        const modal = document.getElementById('notification-modal');
        const modalTitle = document.getElementById('modal-title');
        const modalMessage = document.getElementById('modal-message');
        const modalDate = document.getElementById('modal-date');
        const modalIcon = document.getElementById('modal-icon');
        const closeModalBtn = document.getElementById('close-modal');
        
        let currentLang = 'ar'; // يمكن جلبها من localStorage لاحقاً
        
        function getIconClass(type) {
            switch(type) {
                case 'offer': return 'fa-tags';
                case 'delivery': return 'fa-truck';
                case 'payment': return 'fa-credit-card';
                case 'receive': return 'fa-box-open';
                default: return 'fa-bell';
            }
        }
        
        function getIconColor(type) {
            switch(type) {
                case 'offer': return 'bg-amber-100 text-amber-600';
                case 'delivery': return 'bg-green-100 text-green-600';
                case 'payment': return 'bg-red-100 text-red-600';
                case 'receive': return 'bg-blue-100 text-blue-600';
                default: return 'bg-purple-100 text-purple-600';
            }
        }
        
        function formatDate(dateStr) {
            let date = new Date(dateStr);
            return date.toLocaleDateString('ar-EG', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' });
        }
        
        function updateUI() {
            const unreadExists = notificationsData.some(n => !n.is_read);
            if (notificationsData.length === 0) {
                container.classList.add('hidden');
                noNotificationsDiv.classList.remove('hidden');
                markAllBtn.style.display = 'none';
            } else {
                container.classList.remove('hidden');
                noNotificationsDiv.classList.add('hidden');
                markAllBtn.style.display = 'flex';
                // إعادة بناء القائمة
                container.innerHTML = '';
                notificationsData.forEach(notif => {
                    const card = document.createElement('div');
                    card.className = 'notification-item bg-white rounded-xl shadow-sm overflow-hidden transition-all duration-200 hover:shadow-md';
                    card.setAttribute('data-id', notif.id);
                    card.setAttribute('data-read', notif.is_read);
                    card.innerHTML = `
                        <div class="flex p-3 gap-3 items-start">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center ${getIconColor(notif.type)}">
                                    <i class="fas ${getIconClass(notif.type)} text-lg"></i>
                                </div>
                            </div>
                            <div class="flex-1 space-y-2">
                                <div class="flex justify-between items-start">
                                    <h3 class="font-bold text-gray-800 text-sm ${notif.is_read ? '' : 'text-purple-700'}">${notif.title}</h3>
                                    ${!notif.is_read ? '<span class="w-2 h-2 bg-purple-600 rounded-full"></span>' : ''}
                                </div>
                                <p class="text-gray-500 text-xs line-clamp-1">${notif.message}</p>
                                <div class="flex justify-between items-center text-[10px]">
                                    <p class="text-gray-400">${new Date(notif.date).toLocaleDateString('ar-EG')}</p>
                                    <button onclick="resendNotification(event, ${notif.id})" class="resend-btn flex items-center gap-1 text-xs text-purple-600 bg-purple-50 px-2 py-1 rounded hover:bg-purple-100 transition">
                                        <i class="fas fa-redo"></i> إعادة الإرسال
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    container.appendChild(card);
                });
            }
        }
        
        function markAsRead(notifId) {
            const index = notificationsData.findIndex(n => n.id == notifId);
            if (index !== -1 && !notificationsData[index].is_read) {
                notificationsData[index].is_read = true;
                updateUI();
            }
        }
        
        function markAllAsRead() {
            let changed = false;
            notificationsData.forEach(n => {
                if (!n.is_read) {
                    n.is_read = true;
                    changed = true;
                }
            });
            if (changed) updateUI();
        }
        
        function showNotificationDetails(notif) {
            // إذا كان غير مقروء، نعده كمقروء
            if (!notif.is_read) {
                markAsRead(notif.id);
                notif.is_read = true; // تحديث للنسخة المستخدمة داخل المودال
            }
            modalTitle.innerText = notif.title;
            modalMessage.innerText = notif.message;
            modalDate.innerText = formatDate(notif.date);
            // تحديث الأيقونة في المودال
            modalIcon.innerHTML = `<i class="fas ${getIconClass(notif.type)}"></i>`;
            modalIcon.className = `w-14 h-14 mx-auto rounded-full flex items-center justify-center text-2xl ${getIconColor(notif.type)}`;

            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.classList.add('opacity-100');
            document.querySelector('#notification-modal .bg-white').classList.remove('scale-95');
            document.querySelector('#notification-modal .bg-white').classList.add('scale-100');
        }

        function resendNotification(event, notifId) {
            // منع transpa
            event.stopPropagation();

            // العثور على الإشعار
            const notif = notificationsData.find(n => n.id == notifId);
            if (!notif) return;

            // طلب تأكيد من المستخدم
            if (!confirm('هل أنت متأكد من إعادة إرسال هذا الإشعار؟')) {
                return;
            }

            // أظهر حالة التحميل
            const btn = event.target;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الإرسال...';
            btn.disabled = true;

            // محاكاة عملية إعادة الإرسال (في التطبيق الحقيقي، سيستدعي هذا نهاية適절ه)
            setTimeout(() => {
                // في تطبيق حقيقي، ستستدعي هنا نقطة نهاية لإعادة الإرسال
                // مثال: await fetch('/api/resend-notification/' + notifId, { method: 'POST' });

                // محاكاة النجاح
                btn.innerHTML = '<i class="fas fa-check"></i> تم الإرسال';
                btn.style.backgroundColor = '#dcfce7';
                btn.style.color = '#166534';

                // إعادة الحالة بعد ثانيتين
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                    btn.style.backgroundColor = '';
                    btn.style.color = '';
                }, 2000);
            }, 1000);
        }

        function closeModal() {
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0', 'pointer-events-none');
            document.querySelector('#notification-modal .bg-white').classList.remove('scale-100');
            document.querySelector('#notification-modal .bg-white').classList.add('scale-95');
        }
        
        markAllBtn.addEventListener('click', () => {
            markAllAsRead();
        });
        closeModalBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
        
        // إضافة كلاس إضافي للـ animation
        updateUI();
    </script>
@endsection