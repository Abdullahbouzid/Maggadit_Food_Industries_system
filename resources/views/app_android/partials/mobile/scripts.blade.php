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

    const API_BASE    = window.location.origin + '/api/customer';
    const API_TOKEN   = '{{ session("api_token") }}';
    const POLL_INTERVAL = 10000; // 10 ثواني
    const LS_LAST_ID  = 'mgd_last_notif_id';
    const LS_UNREAD   = 'mgd_unread_count';

    if (!API_TOKEN) return; // لا تشغّل المحرك إذا لم يكن المستخدم مسجلاً

    // --- أدوات مساعدة ---
    function getLastId()     { return parseInt(localStorage.getItem(LS_LAST_ID) || '0', 10); }
    function setLastId(id)   { if (id > getLastId()) localStorage.setItem(LS_LAST_ID, id); }
    function getUnread()     { return parseInt(localStorage.getItem(LS_UNREAD) || '0', 10); }
    function setUnread(n)    { localStorage.setItem(LS_UNREAD, n); }

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

    // --- إظهار Toast منبثق للإشعار الجديد ---
    function showNotifToast(notif) {
        if (typeof showToast !== 'function') return;
        const icons = {
            offer: '🏷️', delivery: '🚚', payment: '💳',
            receive: '📦', notification: '🔔'
        };
        const icon = icons[notif.icon_type] || '🔔';
        showToast(`${icon} ${notif.subject}`, 'info', 5000);
    }

    // --- فحص الإشعارات الجديدة ---
    async function checkNewNotifications() {
        try {
            const lastId = getLastId();
            const resp = await fetch(`${API_BASE}/notifications/check-new`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + API_TOKEN,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ last_id: lastId })
            });
            if (!resp.ok) return;
            const data = await resp.json();
            if (!data.success) return;

            const newNotifs = data.new_notifications || [];
            if (newNotifs.length === 0) return;

            // تحديث آخر ID معروف
            const maxId = Math.max(...newNotifs.map(n => n.id));
            setLastId(maxId);

            // تحديث عداد الإشعارات غير المقروءة من السيرفر
            if (data.unread_count !== undefined) {
                updateNotificationBadge(data.unread_count);
            } else {
                const currentUnread = getUnread();
                const newUnread = currentUnread + newNotifs.length;
                updateNotificationBadge(newUnread);
            }

            // عرض Toast لكل إشعار جديد (أو للأول فقط إذا كانت أكثر)
            if (newNotifs.length === 1) {
                showNotifToast(newNotifs[0]);
            } else {
                if (typeof showToast === 'function') {
                    showToast(`🔔 لديك ${newNotifs.length} إشعارات جديدة`, 'info', 5000);
                }
            }

            // إذا كنا في صفحة الإشعارات، أضف الجديدة في الأعلى دون إعادة تحميل
            if (typeof window.prependNewNotifications === 'function') {
                window.prependNewNotifications(newNotifs);
            }

        } catch (e) {
            // صمت تام عند الفشل - لا نزعج المستخدم
        }
    }

    // --- تهيئة: اقرأ العدد المحفوظ وحدّث الشارة ---
    const savedUnread = getUnread();
    if (savedUnread > 0) {
        // تأجيل قليل حتى يكتمل رسم الصفحة
        setTimeout(() => updateNotificationBadge(savedUnread), 500);
    }

    // --- ابدأ الـ Polling بعد 5 ثوان من تحميل الصفحة ---
    let pollingTimer = null;
    function startPolling() {
        checkNewNotifications(); // فحص فوري عند البدء
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

    window.addEventListener('load', function() {
        setTimeout(startPolling, 5000);
    });

    // مكشوف للخارج لإعادة ضبط العداد عند فتح صفحة الإشعارات
    window.resetNotifBadge = function() {
        setUnread(0);
        updateNotificationBadge(0);
    };

})();
</script>