<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Maggadit - Sign Up</title>
    @vite(['resources/css/app.css'])
    {{-- Tailwind is compiled into app.css via Vite. Loading cdn.tailwindcss.com
         on top of it duplicated the framework and left every screen unstyled
         whenever the packaged app had no network access. --}}
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
            width: 100px;
            height: 100px;
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
            top: 20px;
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
    </style>
</head>
<body class="flex items-center justify-center p-5 min-h-screen">

    <div id="lang-switch" class="lang-switch">🇱🇾  العربية</div>

    <div class="w-full max-w-md login-card p-6 md:p-8 mt-10 mb-10">
        <div class="brand-icon">
            <img src="{{ asset('img/logo/logo.png') }}" alt="Maggadit Logo">
        </div>

        <div class="text-center mb-2">
            <h1 id="brand-title" class="text-2xl font-bold text-gray-800 tracking-tight">Create Account</h1>
            <p id="brand-sub" class="text-gray-500 text-sm mt-1">Maggadit Food Industries</p>
        </div>

        <hr class="my-4">

        <form method="POST" action="/mobile/register" id="register-form">
            <div class="mb-4">
                <label id="name-label" class="block text-gray-700 text-sm font-semibold mb-2">Full Name</label>
                <input type="text" name="name" id="name-input" required
                       class="input-field"
                       placeholder="Enter your full name">
            </div>

            <div class="mb-4">
                <label id="username-label" class="block text-gray-700 text-sm font-semibold mb-2">Username</label>
                <input type="text" name="username" id="username-input" required
                       class="input-field"
                       placeholder="Choose a username">
            </div>
            
            <div class="mb-4">
                <label id="email-label" class="block text-gray-700 text-sm font-semibold mb-2">Email Address</label>
                <input type="email" name="email" id="email-input" required
                       class="input-field"
                       placeholder="Enter your email">
            </div>

            <div class="mb-5">
                <label id="password-label" class="block text-gray-700 text-sm font-semibold mb-2">Password</label>
                <input type="password" name="password" id="password-input" required minlength="6"
                       class="input-field"
                       placeholder="Create a password">
            </div>

            <button type="submit" id="signup-btn" class="btn-signin w-full text-white text-base font-semibold transition">Sign Up</button>
        </form>

        <div class="text-center mt-6 text-sm text-gray-500">
            <span id="login-text">Already have an account?</span>
            <a href="/mobile/login" id="login-link" class="signup-link mr-1">Sign In</a>
        </div>
    </div>

    <!-- Toast الإشعار -->
    <div id="register-toast" class="toast-notification"></div>

    <script>
        const translations = {
            en: {
                brand_title: "Create Account",
                brand_sub: "Maggadit Food Industries",
                name_label: "Full Name",
                name_placeholder: "Enter your full name",
                username_label: "Username",
                username_placeholder: "Choose a username",
                email_label: "Email Address",
                email_placeholder: "Enter your email",
                password_label: "Password",
                password_placeholder: "Create a password",
                signup_btn: "Sign Up",
                login_text: "Already have an account?",
                login_link: "Sign In",
                lang_switch_text: "🇸🇦 العربية",
                dir: "ltr",
                text_align: "left",
                toast_loading: "⏳ Creating account...",
                toast_error: "❌ Error creating account. Try another username/email."
            },
            ar: {
                brand_title: "إنشاء حساب",
                brand_sub: "ماغاديت للصناعات الغذائية",
                name_label: "الاسم الكامل",
                name_placeholder: "أدخل اسمك الكامل",
                username_label: "اسم المستخدم",
                username_placeholder: "اختر اسم مستخدم",
                email_label: "البريد الإلكتروني",
                email_placeholder: "أدخل بريدك الإلكتروني",
                password_label: "كلمة المرور",
                password_placeholder: "أنشئ كلمة مرور",
                signup_btn: "إنشاء الحساب",
                login_text: "لديك حساب بالفعل؟",
                login_link: "تسجيل الدخول",
                lang_switch_text: "🇬🇧 English",
                dir: "rtl",
                text_align: "right",
                toast_loading: "⏳ جاري إنشاء الحساب...",
                toast_error: "❌ خطأ في إنشاء الحساب. تأكد من صحة البيانات."
            }
        };

        let currentLang = localStorage.getItem('lang') || 'en';

        function showToast(message, isError = false) {
            const toast = document.getElementById('register-toast');
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
            document.getElementById('name-label').innerText = t.name_label;
            document.getElementById('name-input').placeholder = t.name_placeholder;
            document.getElementById('username-label').innerText = t.username_label;
            document.getElementById('username-input').placeholder = t.username_placeholder;
            document.getElementById('email-label').innerText = t.email_label;
            document.getElementById('email-input').placeholder = t.email_placeholder;
            document.getElementById('password-label').innerText = t.password_label;
            document.getElementById('password-input').placeholder = t.password_placeholder;
            document.getElementById('signup-btn').innerText = t.signup_btn;
            document.getElementById('login-text').innerText = t.login_text;
            document.getElementById('login-link').innerText = t.login_link;
            document.getElementById('lang-switch').innerText = t.lang_switch_text;

            document.documentElement.setAttribute('dir', t.dir);
            document.documentElement.lang = lang === 'ar' ? 'ar' : 'en';
            document.body.style.textAlign = t.text_align;
            const card = document.querySelector('.login-card');
            if (card) card.style.textAlign = t.text_align;
        }

        document.getElementById('lang-switch').addEventListener('click', () => {
            const newLang = currentLang === 'en' ? 'ar' : 'en';
            setLanguage(newLang);
        });

        const form = document.getElementById('register-form');
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const t = translations[currentLang];
            showToast(t.toast_loading, false);
            
            const btn = document.getElementById('signup-btn');
            btn.disabled = true;
            btn.style.opacity = '0.7';

            try {
                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());
                
                const response = await fetch('/mobile/register', {
                    method: 'POST',
                    body: JSON.stringify(data),
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.token) {
                        localStorage.setItem('api_token', data.token);
                        localStorage.setItem('customer_token', data.token);
                    }
                    if (data.success && data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        window.location.href = '/customer/dashboard';
                    }
                } else {
                    // فشل التسجيل
                    const errorData = await response.json();
                    let errMsg = t.toast_error;
                    if(errorData.errors) {
                        errMsg = Object.values(errorData.errors)[0][0] || t.toast_error;
                    }
                    showToast(errMsg, true);
                    btn.disabled = false;
                    btn.style.opacity = '1';
                }
            } catch (error) {
                console.error("Register Error:", error);
                showToast(t.toast_error, true);
                btn.disabled = false;
                btn.style.opacity = '1';
            }
        });

        setLanguage(currentLang);
    </script>
</body>
</html>
