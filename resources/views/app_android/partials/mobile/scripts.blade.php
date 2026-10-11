<script>
    // ========== Heart toggle ==========
    document.querySelectorAll('.heart-icon').forEach(heart => {
        heart.addEventListener('click', function(e) {
            e.preventDefault();
            const isLiked = this.classList.contains('fas');
            if (isLiked) {
                this.classList.remove('fas', 'liked');
                this.classList.add('far');
                this.setAttribute('data-liked', 'false');
            } else {
                this.classList.remove('far');
                this.classList.add('fas', 'liked');
                this.setAttribute('data-liked', 'true');
            }
        });
    });

    // ========== Bottom nav active state ==========
    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', function() {
            document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // ========== Language switcher ==========
    const langSwitch = document.getElementById('lang-switch');
    if (langSwitch) {
        langSwitch.addEventListener('click', function() {
            let currentUrl = new URL(window.location.href);
            let currentLang = currentUrl.searchParams.get('lang') || '{{ app()->getLocale() }}';
            let newLang = currentLang === 'ar' ? 'en' : 'ar';
            currentUrl.searchParams.set('lang', newLang);
            window.location.href = currentUrl.toString();
        });
    }
</script>

{{-- ===== Smart In-App Notification Engine ===== --}}
<script>
(function() {
    'use strict';

    const CHECK_NEW_URL = '{{ route("customer.notifications.check_new", [], false) }}';
    const POLL_INTERVAL = 10000; // 10 ثواني
    const LS_LAST_ID  = 'mgd_last_notif_id';
    const LS_UNREAD   = 'mgd_unread_count';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
                    || '{{ csrf_token() }}';

    // --- أدوات مساعدة ---
    function getLastId()     { return parseInt(localStorage.getItem(LS_LAST_ID) || '0', 10); }
    function setLastId(id)   { if (id > getLastId()) localStorage.setItem(LS_LAST_ID, id); }
    function getUnread()     { return parseInt(localStorage.getItem(LS_UNREAD) || '0', 10); }
    function setUnread(n)    { localStorage.setItem(LS_UNREAD, n); }
    function getApiToken()   { return localStorage.getItem('api_token') || localStorage.getItem('customer_token') || ''; }

    // --- تحديث شارة الجرس ---
    window.updateNotificationBadge = function(count) {
        const badge = document.getElementById('notif-badge');
        if (!badge) return;
        setUnread(count);
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.classList.remove('hidden');
            // animation رج للجرس
            const bell = document.getElementById('notification-bell');
            if (bell) {
                bell.classList.add('notif-shake');
                setTimeout(() => bell.classList.remove('notif-shake'), 600);
            }
        } else {
            badge.classList.add('hidden');
        }
    };

    // --- إظهر Toast منبثق للإشعار الجديد ---
    function showNotifToast(notif) {
        if (typeof showToast !== 'function') return;
        const icons = {
            offer: '🏷️', delivery: '🚚', payment: '💳',
            receive: '📦', notification: '🔔'
        };
        const icon = icons[notif.icon_type] || '🔔';
        showToast(`${icon} ${notif.subject}`, 'info', 5000);
    }

    // --- إظهار Native Notification ---
    function showNativeNotification(title, body) {
        // إرسال إشعار Native للمستخدمين على التطبيق الأصلي (Android/iOS)
        // يستخدم Dialog facade لإظهار Toast/Snackbar
        if (typeof nativephp_call !== 'undefined') {
            try {
                nativephp_call('Dialog.Toast', JSON.stringify({
                    message: body || title,
                    duration: 'short'
                }));
            } catch (e) {
                // فشل بصمت
            }
        }
    }

    // --- فحص الإشعارات الجديدة ---
    async function checkNewNotifications() {
        try {
            const lastId = getLastId();
            const apiToken = getApiToken();
            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            };
            if (apiToken) {
                headers['Authorization'] = 'Bearer ' + apiToken;
            }
            const resp = await fetch(CHECK_NEW_URL, {
                method: 'POST',
                headers: headers,
                credentials: 'same-origin',
                body: JSON.stringify({ last_id: lastId })
            });

            // 401 يعني أن الجلسة انتهت: أوقف الاستطلاع بصمت.
            if (resp.status === 401) return;
            if (!resp.ok) return;
            const data = await resp.json();
            if (!data.success) return;

            const newNotifs = data.new_notifications || [];

            // تحديث الشارة دائماً من عدّاد السيرفر، حتى بلا إشعارات جديدة،
            // وإلا بقي عدد قديم ظاهر على الجرس بعد قراءة الإشعارات.
            if (data.unread_count !== undefined) {
                updateNotificationBadge(data.unread_count);
            }

            if (newNotifs.length === 0) return;

            // تحديث آخر ID معروف
            const maxId = Math.max(...newNotifs.map(n => n.id));
            setLastId(maxId);

            // عرض Toast لكل إشعار جديد (أو للأول فقط إذا كانت أكثر)
            if (newNotifs.length === 1) {
                showNotifToast(newNotifs[0]);
            } else {
                if (typeof showToast === 'function') {
                    showToast(`🔔 لديك ${newNotifs.length} إشعارات جديدة`, 'info', 5000);
                }
            }

            // إرسال إشعار NativePHP للمستخدمين على التطبيق الأصلي (Android)
            // هذا يرسل إشعارNative إلى Android via Dialog facade
            if (typeof window.showNativeNotification === 'function') {
                window.showNativeNotification(newNotifs[0].subject, newNotifs[0].body || '');
            }

            // إذا كنا في صفحة الإشعارات، أضف الجديدة في الأعلى دون إعادة تحميل
            if (typeof window.prependNewNotifications === 'function') {
                window.prependNewNotifications(newNotifs);
            }

        } catch (e) {
            // صمت تام عند الفشل - لا نزعج المستخدم
        }
    }

     // --- استعادة الجلسة عند تحميل الصفحة ---
    function restoreSession() {
        const apiToken = getApiToken();
        if (apiToken) {
            // للاعتماد على Middleware السيرفر في التحقق من الصلاحية،
            // نبدأ فقط است polling للإشعارات دون إعادة توجيه.
            // إعادة التوجيه تتم فقط إذا لم يوجد Token أصلا ونحن على صفحة محمية.
            startPolling();
        } else {
            // لا يوجد توكن محفوظ ولا نريد إزعاج المستخدم،
            // المسار سيعالجه Middleware السيرفر (CheckCustomerSession).
            startPolling();
        }
    }

    // --- تهيئة: اقرأ العدد المحفوظ وحدّث الشارة ---
    const savedUnread = getUnread();
    if (savedUnread > 0) {
        setTimeout(() => updateNotificationBadge(savedUnread), 500);
    }

    // --- بدء الـ Polling ---
    let pollingTimer = null;
    function startPolling() {
        checkNewNotifications();
        pollingTimer = setInterval(checkNewNotifications, POLL_INTERVAL);
    }

    // أوقف الـ Polling عند إخفاء الصفحة وأعد تشغيله عند الرجوع
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            clearInterval(pollingTimer);
        } else {
            startPolling();
        }
    });

    // --- استعادة الجلسة عند تحميل الصفحة ---
    document.addEventListener('DOMContentLoaded', function() {
        restoreSession();
    });

    // مكشوف للخارج لإعادة ضبط العداد عند فتح صفحة الإشعارات
    window.resetNotifBadge = function() {
        setUnread(0);
        updateNotificationBadge(0);
    };

})();
</script>