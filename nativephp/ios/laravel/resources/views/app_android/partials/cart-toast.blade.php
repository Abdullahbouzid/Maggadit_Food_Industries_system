<!-- ============================================================
     CART TOAST NOTIFICATION — إشعار علوي احترافي بلون #9333ea
     ============================================================ -->
<div id="cart-toast"
     class="cart-toast"
     role="status"
     aria-live="polite"
     dir="rtl">

    <div class="cart-toast__inner">

        <!-- الأيقونة -->
        <div class="cart-toast__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <polyline class="cart-toast__check" points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>

        <!-- المحتوى -->
        <div class="cart-toast__body">
            <h4 class="cart-toast__title">تمت الإضافة بنجاح</h4>
            <p class="cart-toast__msg">تم إضافة المنتج إلى السلة</p>
        </div>

        <!-- زر الإغلاق -->
        <button type="button"
                class="cart-toast__close"
                id="cart-toast-close"
                aria-label="إغلاق">
            <i class="fas fa-times"></i>
        </button>

        <!-- شريط التقدم -->
        <div class="cart-toast__progress"></div>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
    /* ============================================================
       CART TOAST NOTIFICATION — اللون الرئيسي #9333ea (بنفسجي)
       ============================================================ */

    /* متغيّرات اللون — غيّر من هنا فقط */
    :root {
        --toast-brand-50 : #faf5ff;
        --toast-brand-400: #a855f7;
        --toast-brand-500: #9333ea;   /* اللون الرئيسي */
        --toast-brand-600: #7e22ce;
        --toast-brand-700: #6b21a8;
    }

    .cart-toast {
        position: fixed;
        top: 16px;
        left: 50%;
        z-index: 999999;
        width: calc(100% - 28px);
        max-width: 430px;
        transform: translateX(-50%) translateY(-140%);
        opacity: 0;
        pointer-events: none;
        transition:
            transform .55s cubic-bezier(.34, 1.56, .64, 1),
            opacity   .3s  ease;
        direction: rtl;
        font-family: inherit;
        box-sizing: border-box;
    }

    .cart-toast.is-visible {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
        pointer-events: auto;
    }

    /* ---------- البطاقة الداخلية ---------- */
    .cart-toast__inner {
        position: relative;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 14px 15px;
        border-radius: 18px;
        background: linear-gradient(135deg, #9333ea 0%, #6b21a8 100%);
        box-shadow:
            0 20px 40px -12px rgba(147, 51, 234, .55),
            0 8px 20px -8px rgba(0, 0, 0, .35),
            inset 0 1px 0 rgba(255, 255, 255, .25);
        color: #ffffff;
        overflow: hidden;
    }

    /* لمعة علوية */
    .cart-toast__inner::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,.55), transparent);
        pointer-events: none;
    }

    /* ---------- الأيقونة ---------- */
    .cart-toast__icon {
        position: relative;
        flex-shrink: 0;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .22);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: inset 0 0 0 1px rgba(255,255,255,.3);
    }

    /* حلقة نبض حول الأيقونة */
    .cart-toast__icon::after {
        content: '';
        position: absolute;
        inset: -2px;
        border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, .6);
        opacity: 0;
    }
    .cart-toast.is-visible .cart-toast__icon::after {
        animation: cart-toast-ring 1.6s ease-out .35s 2;
    }
    @keyframes cart-toast-ring {
        0%   { transform: scale(.85); opacity: .85; }
        100% { transform: scale(1.55); opacity: 0; }
    }

    .cart-toast__icon svg {
        width: 22px;
        height: 22px;
        color: #ffffff;
        filter: drop-shadow(0 1px 1px rgba(0,0,0,.15));
    }

    .cart-toast__check {
        stroke-dasharray: 32;
        stroke-dashoffset: 32;
    }
    .cart-toast.is-visible .cart-toast__check {
        animation: cart-toast-check .5s cubic-bezier(.65, 0, .45, 1) .25s forwards;
    }
    @keyframes cart-toast-check {
        to { stroke-dashoffset: 0; }
    }

    /* ---------- المحتوى ---------- */
    .cart-toast__body {
        flex: 1;
        min-width: 0;
        text-align: right;
    }

    .cart-toast__title {
        margin: 0 0 2px;
        font-size: 14.5px;
        font-weight: 800;
        letter-spacing: -.2px;
        color: #ffffff;
        line-height: 1.3;
    }

    .cart-toast__msg {
        margin: 0;
        font-size: 12.5px;
        font-weight: 500;
        color: rgba(255, 255, 255, .92);
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ---------- زر الإغلاق ---------- */
    .cart-toast__close {
        flex-shrink: 0;
        width: 30px;
        height: 30px;
        border: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, .18);
        color: #ffffff;
        font-size: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background-color .2s ease, transform .2s ease;
        -webkit-tap-highlight-color: transparent;
    }
    .cart-toast__close:hover {
        background: rgba(255, 255, 255, .32);
        transform: rotate(90deg);
    }
    .cart-toast__close:active {
        transform: rotate(90deg) scale(.9);
    }

    /* ---------- شريط التقدم ---------- */
    .cart-toast__progress {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 3px;
        background: rgba(255, 255, 255, .22);
        overflow: hidden;
    }
    .cart-toast__progress::after {
        content: '';
        position: absolute;
        inset: 0;
        background: #ffffff;
        transform-origin: right center;
        transform: scaleX(0);
    }
    .cart-toast.is-visible .cart-toast__progress::after {
        animation: cart-toast-progress 3s linear forwards;
    }
    @keyframes cart-toast-progress {
        0%   { transform: scaleX(1); }
        100% { transform: scaleX(0); }
    }

    /* ============================================================
       📱 Landscape — ضبط الأبعاد للوضع الأفقي
       ============================================================ */
    @media (orientation: landscape) and (max-height: 500px) {
        .cart-toast {
            top: 10px;
            max-width: 560px;
            width: calc(100% - 40px);
        }
        .cart-toast__inner {
            padding: 9px 14px 12px;
            border-radius: 14px;
            gap: 10px;
        }
        .cart-toast__icon {
            width: 36px;
            height: 36px;
        }
        .cart-toast__icon svg { width: 18px; height: 18px; }
        .cart-toast__title { font-size: 13px; }
        .cart-toast__msg   { font-size: 11.5px; -webkit-line-clamp: 1; }
        .cart-toast__close { width: 26px; height: 26px; font-size: 11px; }
    }

    /* 📱 شاشات ضيقة جدًا (≤360px) */
    @media (max-width: 360px) {
        .cart-toast { width: calc(100% - 20px); }
        .cart-toast__inner { padding: 12px 12px 14px; gap: 10px; }
        .cart-toast__icon { width: 40px; height: 40px; }
        .cart-toast__title { font-size: 13.5px; }
        .cart-toast__msg   { font-size: 12px; }
    }

    /* ♿ تقليل الحركة */
    @media (prefers-reduced-motion: reduce) {
        .cart-toast, .cart-toast * {
            animation-duration: .001ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: .001ms !important;
        }
    }
</style>

<script>
    // ============================================================
    // CART TOAST — JavaScript
    // ============================================================
    let cartToastTimer = null;

    function showCartToast(message, title = 'تمت الإضافة بنجاح') {
        const toast  = document.getElementById('cart-toast');
        if (!toast) return;

        const titleEl = toast.querySelector('.cart-toast__title');
        const msgEl   = toast.querySelector('.cart-toast__msg');

        if (titleEl) titleEl.textContent = title;
        if (msgEl)   msgEl.textContent   = message;

        // إعادة ضبط الأنيميشن
        toast.classList.remove('is-visible');
        void toast.offsetWidth; // force reflow
        toast.classList.add('is-visible');

        clearTimeout(cartToastTimer);
        cartToastTimer = setTimeout(hideCartToast, 3000);
    }

    function hideCartToast() {
        const toast = document.getElementById('cart-toast');
        if (!toast) return;
        clearTimeout(cartToastTimer);
        toast.classList.remove('is-visible');
    }

    // أزرار الإغلاق
    document.getElementById('cart-toast-close')?.addEventListener('click', hideCartToast);

    // إغلاق بـ Escape
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') hideCartToast();
    });

    // إيقاف الإغلاق التلقائي عند المرور
    const cartToastEl = document.getElementById('cart-toast');
    if (cartToastEl) {
        cartToastEl.addEventListener('mouseenter', () => clearTimeout(cartToastTimer));
        cartToastEl.addEventListener('mouseleave', () => {
            cartToastTimer = setTimeout(hideCartToast, 2000);
        });
    }
</script>