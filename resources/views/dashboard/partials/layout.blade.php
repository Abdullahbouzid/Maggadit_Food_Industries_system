@include('dashboard.partials.header')

<body class="nova-app">
    <div class="nova-shell">
        <!-- Sidebar Navigation -->
        @include('dashboard.partials.sidebar')

        <!-- Main Content Area -->
        <div class="nova-content-wrapper">
            @include('dashboard.partials.navbar')

            <main class="nova-main">
                @yield('content')
            

    <!-- Modals for Data Definitions & Customer App Properties -->
    @include('dashboard.partials.modals')

    {{-- ===== Nova Collapse / Tooltip shim =====
         المشروع لا يحمّل Bootstrap، لكن بعض القوالب استخدمت سماته (data-bs-*).
         هذا المعالج يوفّر السلوك المطلوب عبر نظام Nova:
           .nova-collapse + كلاس show   (CSS: public/css/nova.css)
           [data-nova-tooltip]
         مبني على event delegation فيعمل مع المحتوى الذي يُحقن لاحقًا. --}}
    <script>
    (function () {
        'use strict';

        // ===== Collapse =====
        document.addEventListener('click', function (event) {
            var trigger = event.target.closest('[data-bs-toggle="collapse"], [data-nova-toggle="collapse"]');
            if (!trigger) return;

            var selector = trigger.dataset.bsTarget || trigger.getAttribute('data-bs-target')
                        || trigger.dataset.novaTarget || trigger.getAttribute('data-nova-target');
            if (!selector) return;

            var target = document.querySelector(selector);
            if (!target) return;

            event.preventDefault();

            var willShow = !target.classList.contains('show');
            target.classList.toggle('show', willShow);
            trigger.setAttribute('aria-expanded', willShow ? 'true' : 'false');
        });

        // ===== Tooltip =====
        // Bootstrap tooltips غير متوفرة، لذلك نعرض النص في طبقة صغيرة.
        var tip = null;

        function hideTip() {
            if (tip) { tip.remove(); tip = null; }
        }

        document.addEventListener('mouseover', function (event) {
            var target = event.target.closest('[data-bs-toggle="tooltip"], [data-nova-tooltip]');
            if (!target) return;

            var text = target.getAttribute('data-nova-tooltip-text')
                    || target.getAttribute('title');
            if (!text) return;

            // حفظ النص الأصلي ثم منع ظهور تلميح المتصفح الافتراضي
            var original = target.getAttribute('title');
            if (original) {
                target.setAttribute('data-nova-tooltip-text', original);
                target.removeAttribute('title');
            }

            hideTip();
            tip = document.createElement('div');
            tip.className = 'nova-tooltip';
            tip.textContent = text;
            tip.style.cssText = 'position:absolute;z-index:9999;padding:.4rem .6rem;'
                + 'background:#1f2937;color:#fff;border-radius:.4rem;font-size:.75rem;'
                + 'max-width:16rem;box-shadow:0 4px 12px rgba(0,0,0,.18);pointer-events:none;';

            document.body.appendChild(tip);

            var rect = target.getBoundingClientRect();
            tip.style.left = (window.scrollX + rect.left) + 'px';
            tip.style.top  = (window.scrollY + rect.top - tip.offsetHeight - 6) + 'px';
        });

        document.addEventListener('mouseout', function (event) {
            var target = event.target.closest('[data-bs-toggle="tooltip"], [data-nova-tooltip]');
            if (!target) return;

            hideTip();

            // إعادة title الأصلي حتى يظل متاحًا لقارئات الشاشة
            var saved = target.getAttribute('data-nova-tooltip-text');
            if (saved) {
                target.setAttribute('title', saved);
                target.removeAttribute('data-nova-tooltip-text');
            }
        });

        window.addEventListener('scroll', hideTip, { passive: true });
        window.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') hideTip();
        });
    })();
    </script>

    @stack('scripts')
</body>

</html>
