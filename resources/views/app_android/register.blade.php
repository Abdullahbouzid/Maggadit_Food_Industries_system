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

                // إرسال الطلب إلى مسار التسجيل في الويب
                const response = await fetch('/mobile/register', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    const json = await response.json();
                    if (json.success) {
                        // التخزين المؤقت للتوكن إذا أردت (مع ملاحظة أن المسار الويب قد لا يعيد توكن API)
                        // لكنه سنوجه المستخدم إلى لوحة التحكم
                        window.location.href = '/customer/dashboard';
                    } else {
                        showToast(json.message || t.toast_error, true);
                        btn.disabled = false;
                        btn.style.opacity = '1';
                    }
                } else {
                    // معالجة أخطاء التحقق (مثل 422)
                    const errorData = await response.json();
                    let errMsg = t.toast_error;
                    if (errorData.errors) {
                        const firstError = Object.values(errorData.errors)[0][0];
                        errMsg = firstError || t.toast_error;
                    } else if (errorData.message) {
                        errMsg = errorData.message;
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