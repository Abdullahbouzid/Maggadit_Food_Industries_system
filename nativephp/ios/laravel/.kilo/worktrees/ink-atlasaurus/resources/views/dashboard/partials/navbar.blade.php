<nav class="nova-topnav">
    <div class="nova-topnav-left">
        <button class="nova-icon-btn nova-mobile-menu-btn" type="button" id="novaMobileMenu" aria-label="{{ __('messages.navbar.open_menu') }}">
            <i class="fas fa-bars"></i>
        </button>

        <a class="nova-brand" href="{{ route('dashboard') }}">
            <div class="nova-brand-logo">M</div>
            <span class="nova-brand-name">Maggadit<span>.</span></span>
        </a>
    </div>

    <div class="nova-search">
        <i class="fas fa-search"></i>
        <input type="search" placeholder="{{ __('messages.navbar.search_placeholder') }}" aria-label="{{ __('messages.navbar.search_placeholder') }}">
        <kbd>⌘K</kbd>
    </div>

    <div class="nova-topnav-right">
        <button class="nova-icon-btn" type="button" id="novaQuickAdd" aria-label="{{ __('messages.navbar.quick_add') }}" title="{{ __('messages.navbar.quick_add') }}">
            <i class="fas fa-plus"></i>
        </button>

        <button class="nova-icon-btn" type="button" aria-label="{{ __('messages.navbar.notifications') }}" title="{{ __('messages.navbar.notifications') }}">
            <i class="fas fa-bell"></i>
            <span class="nova-badge">3</span>
        </button>
        <span class="nova-badge" style="background: #22c55e; color: white; padding: 2px 7px; border-radius: 50%; font-size: 0.65rem; font-weight: 700; min-width: 16px; text-align: center;">0</span>
        <span style="font-size: 0.7rem; font-weight: 600; color: #22c55e; margin-inline-end: 0.25rem; align-self: center;">{{ __('messages.navbar.translation_status') }}</span>

        <button class="nova-theme-toggle" type="button" data-theme-toggle aria-label="{{ __('messages.navbar.toggle_theme') }}" title="{{ __('messages.navbar.toggle_theme') }}">
            <i class="fas fa-sun"></i>
            <i class="fas fa-moon"></i>
        </button>

        <a href="/lang/{{ app()->getLocale() == 'ar' ? 'en' : 'ar' }}" class="nova-btn nova-btn-sm" style="margin-inline-start: 0.5rem;" title="{{ __('messages.navbar.toggle_language') }}">
            @if(app()->getLocale() == 'ar')
                🇸🇦 العربية
            @else
                🇬🇧 English
            @endif
        </a>

        <div class="nova-divider"></div>

        <div class="nova-user-menu">
            <button class="nova-user-trigger" type="button" id="novaUserBtn" aria-haspopup="true">
                <div class="nova-user-avatar">{{ strtoupper(substr(Auth::user()->name ?? '', 0, 1)) }}</div>
                <div class="nova-user-info">
                    <span class="nova-user-name">{{ Auth::user()->name ?? __('messages.navbar.user') }}</span>
                    <span class="nova-user-role">{{ __('messages.navbar.administrator') }}</span>
                </div>
                <i class="fas fa-chevron-down nova-user-arrow"></i>
            </button>
            <div class="nova-user-dropdown" id="novaUserDropdown">
                <div class="nova-dropdown-header">
                    <div class="nova-user-avatar nova-user-avatar-lg">{{ strtoupper(substr(Auth::user()->name ?? '', 0, 1)) }}</div>
                    <div>
                        <div class="nova-dropdown-name">{{ Auth::user()->name ?? __('messages.navbar.user') }}</div>
                        <div class="nova-dropdown-email">{{ Auth::user()->email ?? 'user@maggadit.com' }}</div>
                    </div>
                </div>
                <div class="nova-dropdown-divider"></div>
                <a href="{{ route('profile.edit') }}" class="nova-user-dropdown-item">
                    <i class="fas fa-user"></i> <span>{{ __('messages.navbar.my_profile') }}</span>
                </a>
           
        
                <div class="nova-dropdown-divider"></div>
                <a href="#" onclick="event.preventDefault(); document.getElementById('nova-logout-form').submit();" class="nova-user-dropdown-item nova-text-danger">
                    <i class="fas fa-sign-out-alt"></i> <span>{{ __('messages.navbar.log_out') }}</span>
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Quick Add Dropdown -->
<div class="nova-quickadd-menu" id="novaQuickAddMenu">
    <div class="nova-quickadd-header">{{ __('messages.navbar.quick_add') }}</div>
    <a href="{{ route('items.create') }}" class="nova-quickadd-item">
        <i class="fas fa-box"></i> <span>{{ __('messages.navbar.new_product') }}</span>
    </a>
    <a href="{{ route('customers.create') }}" class="nova-quickadd-item">
        <i class="fas fa-user-plus"></i> <span>{{ __('messages.navbar.new_customer') }}</span>
    </a>
    <a href="{{ route('offers.create') }}" class="nova-quickadd-item">
        <i class="fas fa-tag"></i> <span>{{ __('messages.navbar.new_offer') }}</span>
    </a>
    <a href="{{ route('invoices.create') }}" class="nova-quickadd-item">
        <i class="fas fa-file-invoice"></i> <span>{{ __('messages.navbar.new_invoice') }}</span>
    </a>

    <a href="{{ route('warehouses.create') }}" class="nova-quickadd-item">
        <i class="fas fa-warehouse"></i> <span>{{ __('messages.navbar.new_warehouse') }}</span>
    </a>
</div>

<!-- Sidebar Overlay (for mobile) -->
<div class="nova-sidebar-overlay"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const userBtn = document.getElementById('novaUserBtn');
    const userDropdown = document.getElementById('novaUserDropdown');

    function closeAllDropdowns() {
        if (userDropdown) userDropdown.classList.remove('show');
        const qa = document.getElementById('novaQuickAddMenu');
        if (qa) qa.classList.remove('show');
    }

    if (userBtn && userDropdown) {
        userBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const isShowing = userDropdown.classList.contains('show');
            closeAllDropdowns();
            if (!isShowing) userDropdown.classList.add('show');
        });
    }

    const quickAdd = document.getElementById('novaQuickAdd');
    const quickAddMenu = document.getElementById('novaQuickAddMenu');

    if (quickAdd && quickAddMenu) {
        quickAdd.addEventListener('click', function(e) {
            e.stopPropagation();
            const isShowing = quickAddMenu.classList.contains('show');
            closeAllDropdowns();
            if (!isShowing) {
                const rect = quickAdd.getBoundingClientRect();
                quickAddMenu.style.top = (rect.bottom + 8) + 'px';
                quickAddMenu.style.right = (window.innerWidth - rect.right) + 'px';
                quickAddMenu.classList.add('show');
            }
        });
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.nova-user-menu') && !e.target.closest('#novaQuickAdd') && !e.target.closest('.nova-quickadd-menu')) {
            closeAllDropdowns();
        }
    });
});
</script>
