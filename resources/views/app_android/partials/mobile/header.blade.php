<style>
        .bottom-nav { position: fixed; bottom: 0; left: 0; right: 0; background: white; border-top: 1px solid #e2e8f0; padding: 8px 16px; display: flex; justify-content: space-around; z-index: 9999 !important; }
        .nav-item { display: flex; flex-direction: column; align-items: center; color: #94a3b8; transition: 0.2s; font-size: 12px; }
        .nav-item i { font-size: 22px; margin-bottom: 4px; }
        .nav-item.active { color: #9879b6; }
        .section-title { font-size: 18px; font-weight: 700; color: #1e293b; }
        .more-link { color: #9879b6; font-weight: 500; font-size: 14px; }
        .brand-chip { background: white; padding: 8px 16px; border-radius: 40px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; font-weight: 500; white-space: nowrap; }
        .category-card { background: white; border-radius: 16px; padding: 12px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #eef2f6; }
        .product-card { background: white; border-radius: 20px; padding: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid #eef2f6; }
        .heart-icon { cursor: pointer; transition: 0.2s; }
        .heart-icon.liked { color: #ef4444; }
        .lang-switch { background: white; padding: 6px 12px; border-radius: 40px; font-size: 0.75rem; font-weight: 600; color: #9879b6; border: 1px solid #e2e8f0; cursor: pointer; }
        
        /* BOTTOM NAVIGATION PREMIUM */
        .bottom-nav-premium { position: fixed; bottom: 16px; left: 16px; right: 16px; background: rgba(255, 255, 255, 0.96); backdrop-filter: blur(20px); border-radius: 40px; box-shadow: 0 8px 28px rgba(0, 0, 0, 0.12), 0 2px 4px rgba(0, 0, 0, 0.02); display: flex; justify-content: space-around; align-items: center; padding: 8px 12px; z-index: 9999 !important; border: 1px solid rgba(152, 121, 182, 0.2); transition: all 0.3s ease; }
        .nav-item-premium { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; cursor: pointer; transition: all 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.1); color: #94a3b8; position: relative; flex: 1; padding: 8px 0; border-radius: 32px; }
        .nav-icon { width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; border-radius: 28px; transition: all 0.25s ease; position: relative; }
        .nav-item-premium i { font-size: 22px; transition: transform 0.2s ease, color 0.2s; }
        .nav-text { font-size: 11px; font-weight: 500; letter-spacing: 0.3px; transition: all 0.2s; opacity: 0.8; }
        .nav-indicator { position: absolute; bottom: -4px; left: 50%; transform: translateX(-50%) scaleX(0); width: 24px; height: 3px; background: #9879b6; border-radius: 4px; transition: transform 0.25s ease; }
        .nav-item-premium.active { color: #9879b6; transform: translateY(-4px); }
        .nav-item-premium.active .nav-icon { background: rgba(152, 121, 182, 0.12); transform: scale(0.98); }
        .nav-item-premium.active i { transform: scale(1.1); text-shadow: 0 2px 6px rgba(152, 121, 182, 0.3); }
        .nav-item-premium.active .nav-text { font-weight: 600; opacity: 1; }
        .nav-item-premium.active .nav-indicator { transform: translateX(-50%) scaleX(1); }
        .nav-item-premium:not(.active):hover { color: #9879b6; transform: translateY(-2px); }
        .nav-item-premium:not(.active):hover .nav-icon { background: rgba(152, 121, 182, 0.06); }
        [dir="rtl"] .bottom-nav-premium { direction: rtl; }
        @supports (padding-bottom: env(safe-area-inset-bottom)) {
            .bottom-nav-premium { bottom: calc(16px + env(safe-area-inset-bottom)); }
        }
        
        /* ========== إضافات الهيدر الجديدة ========== */
        .header-icon-btn { transition: all 0.2s ease; cursor: pointer; }
        .header-icon-btn:hover { transform: scale(1.05); background: rgba(152, 121, 182, 0.1); }
        .header-icon-btn:active { transform: scale(0.95); }
        .notification-badge { transition: transform 0.2s ease; }
        .notification-badge.active { animation: shake 0.5s ease-in-out; }
        @keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-2px); } 75% { transform: translateX(2px); } }
        @keyframes notifShake {
            0%,100% { transform: rotate(0deg); }
            15%     { transform: rotate(12deg); }
            30%     { transform: rotate(-10deg); }
            45%     { transform: rotate(8deg); }
            60%     { transform: rotate(-6deg); }
            75%     { transform: rotate(4deg); }
        }
        .notif-shake { animation: notifShake 0.6s ease-in-out; }
        
        /* ========== مؤشر السحب للتحديث ========== */
        .ptr-indicator { position: fixed; top: 0; left: 0; right: 0; text-align: center; padding: 10px; background: #9879b6; color: white; font-size: 12px; transform: translateY(-100%); transition: transform 0.2s ease; z-index: 50; border-radius: 0 0 20px 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        body.ptr-refreshing .ptr-indicator { transform: translateY(0); }
        #toast-container { position: fixed; top: 80px; left: 16px; right: 16px; z-index: 9999; pointer-events: none; display: flex; flex-direction: column; align-items: center; gap: 8px; }
        .sticky { z-index: 15; }
        .toast-item { transition: transform 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1), opacity 0.3s; pointer-events: auto; cursor: grab; user-select: none; }
        .toast-item:active { cursor: grabbing; }
        .toast-dismissed-left { transform: translateX(-150%) !important; opacity: 0 !important; }
        .toast-dismissed-right { transform: translateX(150%) !important; opacity: 0 !important; }
</style>
<!-- مؤشر السحب للتحديث (يظهر في الأعلى) -->
<div id="ptr-indicator" class="ptr-indicator">
    <i class="fas fa-arrow-down mr-1"></i> اسحب لتحديث الصفحة
</div>

<!-- Header with location, language switch, search, and notifications -->
<div class="sticky top-0 bg-white/90 backdrop-blur-sm z-10 border-b border-gray-100 shadow-sm" style="border-radius: 20px 20px; padding-top: env(safe-area-inset-top);">
    <div class="flex justify-between items-center px-5 py-4">
        <!-- الشعار -->
        <div class="flex items-center gap-2">
            <img src="{{ asset('img/logo/logo.ico') }}" alt="Logo" class="w-12 h-12 rounded-full shadow-sm">
            @if(app()->getLocale() == 'ar')
                <span class="text-xl font-bold text-purple-600 hidden sm:inline">ماجديت</span>
            @else
                <span class="text-xl font-bold text-purple-600 hidden sm:inline">Maggadit</span>
            @endif
        </div>

        <!-- مجموعة الأزرار الجانبية -->
        <div class="flex items-center gap-2">
            <!-- زر البحث -->
            <div id="search-toggle" class="header-icon-btn lang-switch !p-2 !rounded-full">
                <i class="fas fa-search text-purple-600"></i>
            </div>

            <!-- زر الإشعارات مع عداد -->
            <div id="notification-bell" class="header-icon-btn lang-switch !p-2 !rounded-full relative" onclick="window.location.href='{{ route('customer.notifications', [], false) }}'">
                <i class="far fa-bell text-purple-600 text-lg"></i>
                <span id="notif-badge" class="notification-badge absolute -top-1 -right-1 bg-red-500 text-white text-[10px] rounded-full w-4 h-4 flex items-center justify-center {{ (isset($unreadCount) && $unreadCount > 0) ? '' : 'hidden' }}">{{ $unreadCount ?? 0 }}</span>
            </div>

            <!-- زر تبديل اللغة -->
            <div id="lang-switch" class="lang-switch">🇱🇾 العربية</div>
        </div>
    </div>

    <!-- شريط البحث المنبثق -->
    <div id="search-bar" class="px-5 pb-4 hidden transition-all duration-300">
        <div class="relative">
            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
            <input type="text" id="search-input" placeholder="ابحث عن منتجات، ماركات، عروض..." 
                   class="w-full pr-10 pl-10 py-2 border border-gray-200 rounded-full focus:border-purple-400 focus:ring-2 focus:ring-purple-200 transition text-sm bg-gray-50">
            <button id="close-search" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-purple-600 transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="search-suggestions" class="mt-2 bg-white rounded-xl shadow-md hidden"></div>
    </div>
</div>

<!-- حاوية الـ Toast في الأعلى -->
<div id="toast-container"></div>

<script>
    // ========== 1. نظام Toast الإشعارات (في الأعلى) ==========
    function showToast(message, type = 'info', duration = 3000) {
        const container = document.getElementById('toast-container');
        if (!container) return;
        
        const toast = document.createElement('div');
        let bgColor = '#9879b6'; // default purple
        if (type === 'success') bgColor = '#10b981';
        else if (type === 'error') bgColor = '#ef4444';
        else if (type === 'warning') bgColor = '#f59e0b';
        
        toast.className = 'toast-item flex items-center justify-between gap-3';
        toast.style.cssText = `
            background: ${bgColor};
            color: white;
            padding: 12px 20px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
            backdrop-filter: blur(4px);
            transform: translateY(-20px);
            opacity: 0;
            transition: transform 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1), opacity 0.3s;
            text-align: right;
            font-family: 'Inter', sans-serif;
            width: auto;
            max-width: 90%;
            touch-action: pan-y;
        `;
        toast.innerHTML = `<span class="flex-1">${message}</span><div class="w-1 h-8 bg-white/30 rounded-full"></div>`;
        container.appendChild(toast);
        
        // Swipe to dismiss logic
        let startX = 0;
        let currentX = 0;
        let isDragging = false;
        
        toast.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            isDragging = true;
            toast.style.transition = 'none';
        }, {passive: true});
        
        toast.addEventListener('touchmove', (e) => {
            if (!isDragging) return;
            currentX = e.touches[0].clientX - startX;
            toast.style.transform = `translateX(${currentX}px)`;
            toast.style.opacity = 1 - Math.abs(currentX) / 200;
        }, {passive: true});
        
        toast.addEventListener('touchend', (e) => {
            if (!isDragging) return;
            isDragging = false;
            toast.style.transition = 'transform 0.3s, opacity 0.3s';
            if (currentX > 80) {
                toast.classList.add('toast-dismissed-right');
                setTimeout(() => toast.remove(), 300);
            } else if (currentX < -80) {
                toast.classList.add('toast-dismissed-left');
                setTimeout(() => toast.remove(), 300);
            } else {
                toast.style.transform = 'translateY(0)';
                toast.style.opacity = '1';
            }
        });

        requestAnimationFrame(() => {
            toast.style.transform = 'translateY(0)';
            toast.style.opacity = '1';
        });
        
        if (duration > 0) {
            setTimeout(() => {
                if(toast.parentElement) {
                    toast.style.transform = 'translateY(-20px)';
                    toast.style.opacity = '0';
                    setTimeout(() => { if(toast.parentElement) toast.remove(); }, 300);
                }
            }, duration);
        }
    }

    // ========== 2. شارة الإشعارات (تُدار من قِبل محرك الـ Polling في scripts.blade.php) ==========
    // لا نحتاج لـ fetch هنا — المحرك الذكي في scripts.blade.php يتولى ذلك تلقائياً.
    // الدالة updateNotificationBadge مُعرَّفة في scripts.blade.php ومتاحة عالمياً.

    // ========== 3. البحث (Search Bar) ==========
    const searchToggle = document.getElementById('search-toggle');
    const searchBar = document.getElementById('search-bar');
    const closeSearchBtn = document.getElementById('close-search');
    const searchInput = document.getElementById('search-input');
    const suggestionsDiv = document.getElementById('search-suggestions');

    if (searchToggle && searchBar) {
        searchToggle.addEventListener('click', () => {
            searchBar.classList.toggle('hidden');
            if (!searchBar.classList.contains('hidden')) {
                searchInput.focus();
            }
        });
        if (closeSearchBtn) {
            closeSearchBtn.addEventListener('click', () => {
                searchBar.classList.add('hidden');
                searchInput.value = '';
                suggestionsDiv.classList.add('hidden');
            });
        }
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.trim().toLowerCase();
            if (query.length < 2) {
                suggestionsDiv.classList.add('hidden');
                return;
            }
            const demoProducts = ['حذاء رياضي', 'تي شيرت', 'ساعة ابل', 'سماعات', 'عطر'];
            const filtered = demoProducts.filter(p => p.includes(query));
            if (filtered.length > 0) {
                suggestionsDiv.innerHTML = filtered.map(p => `<div class="p-2 hover:bg-gray-100 cursor-pointer rounded-lg transition">${p}</div>`).join('');
                suggestionsDiv.classList.remove('hidden');
                suggestionsDiv.querySelectorAll('div').forEach(div => {
                    div.addEventListener('click', () => {
                        searchInput.value = div.innerText;
                        suggestionsDiv.classList.add('hidden');
                        showToast(`🔍 جاري البحث عن "${div.innerText}"`, 'info');
                    });
                });
            } else {
                suggestionsDiv.classList.add('hidden');
            }
        });
        document.addEventListener('click', (e) => {
            if (!searchBar.contains(e.target) && !searchToggle.contains(e.target)) {
                suggestionsDiv.classList.add('hidden');
            }
        });
    }

    // ========== 4. تحديث نص placeholder حسب اللغة ==========
    function updateHeaderLang() {
        const isAr = document.documentElement.getAttribute('dir') === 'rtl';
        if (searchInput) {
            searchInput.placeholder = isAr ? 'ابحث عن منتجات، ماركات، عروض...' : 'Search for products, brands, offers...';
        }
    }
    updateHeaderLang();
    window.addEventListener('languageChanged', updateHeaderLang);

    // ========== 5. سحب لتحديث (Pull to Refresh) مع مؤشر علوي ==========
    const ptrIndicator = document.getElementById('ptr-indicator');
    let startY = 0;
    let pulling = false;
    let refreshThreshold = 80; // المسافة المطلوبة للسحب
    let refreshing = false;
    let pullDistance = 0;
    
    function onTouchStart(e) {
        if (window.scrollY === 0 && !refreshing) {
            startY = e.touches[0].clientY;
            pulling = true;
            if (ptrIndicator) {
                ptrIndicator.style.transform = 'translateY(-100%)';
                ptrIndicator.innerHTML = '<i class="fas fa-arrow-down mr-1"></i> اسحب لتحديث';
            }
        }
    }
    
    function onTouchMove(e) {
        if (!pulling || refreshing) return;
        const currentY = e.touches[0].clientY;
        const diff = currentY - startY;
        if (diff > 0) {
            e.preventDefault();
            pullDistance = diff;
            if (ptrIndicator) {
                if (diff >= refreshThreshold) {
                    ptrIndicator.style.transform = 'translateY(0)';
                    ptrIndicator.innerHTML = '<i class="fas fa-sync-alt fa-spin mr-1"></i> حرر للتحديث';
                } else {
                    const percent = diff / refreshThreshold;
                    const translateY = -100 + (percent * 100);
                    ptrIndicator.style.transform = `translateY(${translateY}%)`;
                    ptrIndicator.innerHTML = '<i class="fas fa-arrow-down mr-1"></i> اسحب لتحديث';
                }
            }
        }
    }
    
    function onTouchEnd() {
        if (!pulling || refreshing) {
            pulling = false;
            return;
        }
        if (pullDistance >= refreshThreshold && !refreshing) {
            refreshing = true;
            if (ptrIndicator) {
                ptrIndicator.style.transform = 'translateY(0)';
                ptrIndicator.innerHTML = '<i class="fas fa-sync-alt fa-spin mr-1"></i> جاري التحديث...';
            }
            showToast('🔄 جاري تحديث الصفحة...', 'info', 1500);
            setTimeout(() => {
                location.reload();
            }, 200);
        } else {
            if (ptrIndicator) ptrIndicator.style.transform = 'translateY(-100%)';
        }
        pulling = false;
        pullDistance = 0;
    }
    
    // ========== 6. تحديث عداد السلة ==========
    function updateCartCount() {
        const cartItems = JSON.parse(localStorage.getItem('maggadit_cart')) || [];
        const totalCount = cartItems.reduce((sum, item) => sum + (item.quantity || 1), 0);
        const badge = document.getElementById('cart-count-badge');
        if (badge) {
            if (totalCount > 0) {
                badge.innerText = totalCount > 99 ? '99+' : totalCount;
                badge.classList.remove('hidden');
                badge.classList.add('flex');
            } else {
                badge.classList.add('hidden');
                badge.classList.remove('flex');
            }
        }
    }
    document.addEventListener('DOMContentLoaded', updateCartCount);

    document.body.addEventListener('touchstart', onTouchStart, { passive: false });
    document.body.addEventListener('touchmove', onTouchMove, { passive: false });
    document.body.addEventListener('touchend', onTouchEnd);
</script>
