<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Maggadit - Sign In</title>
    @vite(['resources/css/app.css'])
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        body {
            background: linear-gradient(145deg, #f5f7fc 0%, #e9eef4 100%);
            min-height: 100vh;
            position: relative;
        }
        .login-card {
            background: white;
            border-radius: 32px;
            box-shadow: 0 25px 45px -12px rgba(0, 0, 0, 0.2), 0 1px 1px rgba(0,0,0,0.02);
        }
        .input-field {
            transition: all 0.2s;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 12px 16px;
            width: 100%;
            font-size: 0.95rem;
        }
        .input-field:focus {
            outline: none;
            border-color: #9879b6;
            box-shadow: 0 0 0 3px rgba(152, 121, 182, 0.2);
            background: white;
        }
        .btn-signin {
            background: #9879b6;
            border-radius: 40px;
            padding: 12px;
            font-weight: 600;
            transition: 0.2s;
            box-shadow: 0 4px 10px rgba(152, 121, 182, 0.3);
        }
        .btn-signin:hover {
            background: #8367a1;
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(152, 121, 182, 0.4);
        }
        .signup-link {
            color: #9879b6;
            font-weight: 600;
            text-decoration: none;
        }
        .signup-link:hover {
            color: #6b4c8a;
            text-decoration: underline;
        }
        .brand-icon {
            width: 120px;
            height: 120px;
            background: #6b4c8a;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            overflow: hidden;
        }
        .brand-icon img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        hr {
            background: #e2e8f0;
            height: 1px;
            border: none;
        }
        .lang-switch {
            position: absolute;
            top: 80px;
            right: 20px;
            cursor: pointer;
            background: white;
            padding: 6px 14px;
            border-radius: 40px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #9879b6;
            border: 1px solid #e2e8f0;
            transition: 0.2s;
            z-index: 10;
        }
        .lang-switch:hover {
            background: #9879b6;
            color: white;
            border-color: #9879b6;
        }
        /* Toast style */
        .toast-notification {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: #1f2937;
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 500;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.2);
            z-index: 100;
            transition: transform 0.3s ease, opacity 0.3s ease;
            opacity: 0;
            pointer-events: none;
            white-space: nowrap;
            direction: ltr;
        }
        .toast-notification.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
        /* error styling */
        .error-message {
            color: #dc2626;
            font-size: 0.75rem;
            margin-top: 4px;
            text-align: right;
        }
    </style>
</head>
<body class="flex items-center justify-center p-5 min-h-screen">

    <!-- زر تبديل اللغة -->
    <div id="lang-switch" class="lang-switch">🇱🇾  العربية</div>

    <div class="w-full max-w-md login-card p-6 md:p-8">
        <div class="brand-icon">
            <img src="{{ asset('img/logo/logo.png') }}" alt="Maggadit Logo">
        </div>

        <div class="text-center mb-2">
            <h1 id="brand-title" class="text-3xl font-bold text-gray-800 tracking-tight">ماغاديت</h1>
            <p id="brand-sub" class="text-gray-500 text-sm mt-1">ماغاديت للصناعات الغذائية</p>
        </div>

        <hr class="my-6">

        <!-- عرض رسائل الخطأ من الجلسة (مثلاً بعد فشل تسجيل الدخول) -->
        @if(session('error'))
            <div id="server-error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm mb-4 text-right">
                {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div id="server-error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm mb-4 text-right">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="/mobile/login" id="login-form">
            @csrf
            <div class="mb-4">
                <label id="username-label" class="block text-gray-700 text-sm font-semibold mb-2">Username</label>
                <input type="text" name="username" id="username-input" required
                       class="input-field"
                       placeholder="Enter your username">
            </div>

            <div class="mb-5">
                <label id="password-label" class="block text-gray-700 text-sm font-semibold mb-2">Password</label>
                <input type="password" name="password" id="password-input" required
                       class="input-field"
                       placeholder="Enter your password">
            </div>

            <button type="submit" id="signin-btn" class="btn-signin w-full text-white text-base font-semibold transition">Sign In</button>
        </form>

        <!-- رسالة التسجيل -->
        <div class="text-center mt-6 text-sm text-gray-500">
            <span id="signup-text">Are you new user?</span>
            <a href="/mobile/register" id="signup-link" class="signup-link mr-1">Sign Up</a>
        </div>
    </div>

    <!-- Toast الإشعار -->
    <div id="login-toast" class="toast-notification"></div>

    <script>
        // تعريف النصوص لكل لغة
        const translations = {
            en: {
                brand_title: "Maggadit",
                brand_sub: "Maggadit Food Industries",
                brand_en: "MAGGADIT FOOD INDUSTRIES",
                username_label: "Username",
                username_placeholder: "Enter your username",
                password_label: "Password",
                password_placeholder: "Enter your password",
                signin_btn: "Sign In",
                signup_text: "Are you new user?",
                signup_link: "Sign Up",
                lang_switch_text: "🇸🇦 العربية",
                dir: "ltr",
                text_align: "left",
                toast_logging: "🔐 Logging in, please wait...",
                toast_error: "❌ Login failed. Please check your credentials."
            },
            ar: {
                brand_title: "ماغاديت",
                brand_sub: "ماغاديت للصناعات الغذائية",
                brand_en: "ماغاديت للصناعات الغذائية",
                username_label: "اسم المستخدم",
                username_placeholder: "أدخل اسم المستخدم",
                password_label: "كلمة المرور",
                password_placeholder: "أدخل كلمة المرور",
                signin_btn: "تسجيل الدخول",
                signup_text: "ليس لديك حساب؟",
                signup_link: "إنشاء حساب",
                lang_switch_text: "🇬🇧 English",
                dir: "rtl",
                text_align: "right",
                toast_logging: "🔐 جاري تسجيل الدخول، انتظر قليلاً...",
                toast_error: "❌ فشل تسجيل الدخول. يرجى التحقق من بياناتك."
            }
        };

        let currentLang = localStorage.getItem('lang') || 'en';

        // دالة إظهار التوست
        function showToast(message, isError = false) {
            const toast = document.getElementById('login-toast');
            toast.innerText = message;
            toast.classList.add('show');
            if (isError) {
                toast.style.background = '#dc2626';
            } else {
                toast.style.background = '#1f2937';
            }
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        function setLanguage(lang) {
            currentLang = lang;
            localStorage.setItem('lang', lang);
            const t = translations[lang];

            document.getElementById('brand-title').innerText = t.brand_title;
            document.getElementById('brand-sub').innerText = t.brand_sub;
            document.getElementById('brand-en').innerText = t.brand_en;
            document.getElementById('username-label').innerText = t.username_label;
            document.getElementById('username-input').placeholder = t.username_placeholder;
            document.getElementById('password-label').innerText = t.password_label;
            document.getElementById('password-input').placeholder = t.password_placeholder;
            document.getElementById('signin-btn').innerText = t.signin_btn;
            document.getElementById('signup-text').innerText = t.signup_text;
            document.getElementById('signup-link').innerText = t.signup_link;
            document.getElementById('lang-switch').innerText = t.lang_switch_text;

            document.documentElement.setAttribute('dir', t.dir);
            document.documentElement.lang = lang === 'ar' ? 'ar' : 'en';
            document.body.style.textAlign = t.text_align;
            const card = document.querySelector('.login-card');
            if (card) card.style.textAlign = t.text_align;
        }

        // تبديل اللغة
        document.getElementById('lang-switch').addEventListener('click', () => {
            const newLang = currentLang === 'en' ? 'ar' : 'en';
            setLanguage(newLang);
        });

        // إشعار تسجيل الدخول عند إرسال النموذج باستخدام AJAX لتفادي مشاكل WebView
  const API_BASE = "https://blue-beaver-923812.hostingersite.com"; // إذا عندك دومين ضع هنا https://domain.com

form.addEventListener('submit', async function(e) {
    e.preventDefault();

    const t = translations[currentLang];
    showToast(t.toast_logging, false);

    const btn = document.getElementById('signin-btn');
    btn.disabled = true;
    btn.style.opacity = '0.7';

    try {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        const response = await fetch(API_BASE + '/customer/login', {
            method: 'POST',
            body: JSON.stringify(data),
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (response.ok && result.success) {
            // حفظ التوكن (مهم للـ APK)
            localStorage.setItem('token', result.token);

            window.location.href = '/customer/dashboard';
        } else {
            showToast(t.toast_error, true);
            btn.disabled = false;
            btn.style.opacity = '1';
        }

    } catch (error) {
        console.error(error);
        showToast(t.toast_error, true);
        btn.disabled = false;
        btn.style.opacity = '1';
    }
});

        // إذا كان هناك رسالة خطأ من الخادم (عند تحميل الصفحة لأول مرة مع session error)
        window.addEventListener('DOMContentLoaded', function() {
            const errorDiv = document.getElementById('server-error');
            if (errorDiv && errorDiv.innerText.trim() !== '') {
                const t = translations[currentLang];
                showToast(t.toast_error, true);
            }
        });

        // تعيين اللغة الأولية
        setLanguage(currentLang);
    </script>
</body>
</html>