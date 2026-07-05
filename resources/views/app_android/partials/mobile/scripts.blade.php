<script>
    // Heart toggle functionality
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

    // Bottom nav active state
    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', function() {
            document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Language switcher (temporary client-side, but uses backend locale)
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

<!-- Firebase SDKs -->
<script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-messaging-compat.js"></script>

<script>
    // إعدادات Firebase (يجب تغييرها ببيانات مشروعك الحقيقية)
    const firebaseConfig = {
        apiKey: "YOUR_API_KEY",
        authDomain: "YOUR_PROJECT_ID.firebaseapp.com",
        projectId: "YOUR_PROJECT_ID",
        storageBucket: "YOUR_PROJECT_ID.appspot.com",
        messagingSenderId: "YOUR_SENDER_ID",
        appId: "YOUR_APP_ID"
    };

    try {
        if (!firebase.apps.length) {
            firebase.initializeApp(firebaseConfig);
        }

        const messaging = firebase.messaging();

        // طلب صلاحية الإشعارات
        function requestNotificationPermission() {
            if (!('Notification' in window)) {
                console.log('This environment does not support Web Notifications (likely an Android WebView).');
                return;
            }

            Notification.requestPermission().then((permission) => {
                if (permission === 'granted') {
                    console.log('Notification permission granted.');
                    // الحصول على التوكن
                    messaging.getToken({ vapidKey: "YOUR_VAPID_KEY" }) // اختياري حسب نوع الإعداد
                    .then((currentToken) => {
                        if (currentToken) {
                            sendTokenToServer(currentToken);
                        } else {
                            console.log('No registration token available. Request permission to generate one.');
                        }
                    }).catch((err) => {
                        console.log('An error occurred while retrieving token. ', err);
                    });
                } else {
                    console.log('Unable to get permission to notify.');
                }
            });
        }

        function sendTokenToServer(token) {
            const apiToken = '{{ session("api_token") }}';
            if (!apiToken) return;

            fetch('https://blue-beaver-923812.hostingersite.com/api/customer/fcm-token', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + apiToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ fcm_token: token })
            })
            .then(response => response.json())
            .then(data => {
                console.log('FCM Token updated successfully:', data);
            })
            .catch(error => {
                console.error('Error updating FCM token:', error);
            });
        }

        // استدعاء الطلب
        // يُفضل استدعاءها عند تفاعل المستخدم أو بعد ثواني من فتح التطبيق
        setTimeout(() => {
            requestNotificationPermission();
        }, 5000);

        // استقبال الرسائل في الأمامية (Foreground)
        messaging.onMessage((payload) => {
            console.log('Message received. ', payload);
            if (typeof showToast === 'function') {
                showToast(`🔔 ${payload.notification.title}`, 'info', 5000);
            }
            // تحديث العداد
            if (typeof fetchUnreadNotifications === 'function') {
                fetchUnreadNotifications();
            }
        });

    } catch(err) {
        console.log("Firebase not configured correctly yet.", err);
    }
</script>