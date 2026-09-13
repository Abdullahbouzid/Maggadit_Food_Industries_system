<nav class="nova-topnav">
    <div class="nova-topnav-left">
        <button class="nova-icon-btn nova-mobile-menu-btn" type="button" id="novaMobileMenu" aria-label="Open menu">
            <i class="fas fa-bars"></i>
        </button>

        <a class="nova-brand" href="{{ route('dashboard') }}">
            <div class="nova-brand-logo">M</div>
            <span class="nova-brand-name">Maggadit<span>.</span></span>
        </a>
    </div>

    <div class="nova-search">
        <i class="fas fa-search"></i>
        <input type="search" placeholder="Search products, customers, invoices..." aria-label="Search">
        <kbd>⌘K</kbd>
    </div>

    <div class="nova-topnav-right">
        <button class="nova-icon-btn" type="button" id="novaQuickAdd" aria-label="Quick add" title="Quick add">
            <i class="fas fa-plus"></i>
        </button>

        <button class="nova-icon-btn" type="button" aria-label="Notifications" title="Notifications">
            <i class="fas fa-bell"></i>
            <span class="nova-badge">3</span>
        </button>

        <button class="nova-theme-toggle" type="button" data-theme-toggle aria-label="Toggle theme" title="Toggle theme">
            <i class="fas fa-sun"></i>
            <i class="fas fa-moon"></i>
        </button>

        <div class="nova-divider"></div>

        <div class="nova-user-menu">
            <button class="nova-user-trigger" type="button" id="novaUserBtn" aria-haspopup="true">
                <div class="nova-user-avatar">{{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}</div>
                <div class="nova-user-info">
                    <span class="nova-user-name">{{ Auth::user()->name ?? 'User' }}</span>
                    <span class="nova-user-role">Administrator</span>
                </div>
                <i class="fas fa-chevron-down nova-user-arrow"></i>
            </button>
            <div class="nova-user-dropdown" id="novaUserDropdown">
                <div class="nova-dropdown-header">
                    <div class="nova-user-avatar nova-user-avatar-lg">{{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}</div>
                    <div>
                        <div class="nova-dropdown-name">{{ Auth::user()->name ?? 'User' }}</div>
                        <div class="nova-dropdown-email">{{ Auth::user()->email ?? 'user@maggadit.com' }}</div>
                    </div>
                </div>
                <div class="nova-dropdown-divider"></div>
                <a href="{{ route('profile.show') }}" class="nova-user-dropdown-item">
                    <i class="fas fa-user"></i> <span>My Profile</span>
                </a>
                <a href="#" class="nova-user-dropdown-item">
                    <i class="fas fa-cog"></i> <span>Settings</span>
                </a>
                <a href="#" class="nova-user-dropdown-item">
                    <i class="fas fa-life-ring"></i> <span>Help & Support</span>
                </a>
                <div class="nova-dropdown-divider"></div>
                <a href="#" onclick="event.preventDefault(); document.getElementById('nova-logout-form').submit();" class="nova-user-dropdown-item nova-text-danger">
                    <i class="fas fa-sign-out-alt"></i> <span>Log out</span>
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Quick Add Dropdown -->
<div class="nova-quickadd-menu" id="novaQuickAddMenu">
    <div class="nova-quickadd-header">Quick Add</div>
    <a href="{{ route('items.create') }}" class="nova-quickadd-item">
        <i class="fas fa-box"></i> <span>New Product</span>
    </a>
    <a href="{{ route('customers.create') }}" class="nova-quickadd-item">
        <i class="fas fa-user-plus"></i> <span>New Customer</span>
    </a>
    <a href="{{ route('offers.create') }}" class="nova-quickadd-item">
        <i class="fas fa-tag"></i> <span>New Offer</span>
    </a>
    <a href="{{ route('invoices.create') }}" class="nova-quickadd-item">
        <i class="fas fa-file-invoice"></i> <span>New Invoice</span>
    </a>
    <a href="{{ route('pack-definitions.create') }}" class="nova-quickadd-item">
        <i class="fas fa-box-open"></i> <span>New Pack Definition</span>
    </a>
    <a href="{{ route('warehouses.create') }}" class="nova-quickadd-item">
        <i class="fas fa-warehouse"></i> <span>New Warehouse</span>
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
