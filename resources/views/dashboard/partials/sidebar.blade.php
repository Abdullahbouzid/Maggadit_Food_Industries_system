<aside class="nova-sidebar" id="novaSidebar" aria-label="Main navigation">
    <div class="nova-sidebar-content">
        <!-- Brand Logo -->
        <div class="nova-sidebar-brand">
            <a href="{{ route('dashboard') }}" class="nova-brand">
                <div class="nova-brand-logo">M</div>
                <span class="nova-brand-name">Maggadit<span>.</span></span>
            </a>
        </div>

        <!-- Dashboard -->
        <div class="nova-sidebar-section">
            <a href="{{ route('dashboard') }}" class="nova-sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
        </div>

        <!-- Data Definitions -->
        <div class="nova-sidebar-section">
            <a href="javascript:void(0);" class="nova-sidebar-link" id="novaDataDefinitionsBtn" data-modal="data-definitions-modal">
                <i class="fas fa-database"></i>
                <span>Data Definitions</span>
            </a>
        </div>

        <!-- Customer App Properties -->
        <div class="nova-sidebar-section">
            <a href="javascript:void(0);" class="nova-sidebar-link" id="novaCustomerAppBtn" data-modal="customer-app-modal">
                <i class="fas fa-mobile-alt"></i>
                <span>Customer App Properties</span>
            </a>
        </div>

        <!-- Permissions & Roles -->
        <div class="nova-sidebar-section">
            <div class="nova-sidebar-title">Permissions & Roles</div>
            <a href="{{ route('roles.index') }}" class="nova-sidebar-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                <i class="fas fa-user-shield"></i>
                <span>Roles</span>
            </a>
            <a href="{{ route('invoices.index') }}" class="nova-sidebar-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                <i class="fas fa-file-invoice"></i>
                <span>الفواتير</span>
            </a>
        </div>

        <!-- Inventory & Products -->
        <div class="nova-sidebar-section">
            <div class="nova-sidebar-title">المخزون و المنتجات</div>
            <a href="{{ route('warehouses.index') }}" class="nova-sidebar-link {{ request()->routeIs('warehouses.*') ? 'active' : '' }}">
                <i class="fas fa-warehouse"></i>
                <span>المخزون</span>
            </a>
            <a href="{{ route('items.index') }}" class="nova-sidebar-link {{ request()->routeIs('items.*') ? 'active' : '' }}">
                <i class="fas fa-box"></i>
                <span>ال products</span>
            </a>
            <a href="{{ route('item-packs.index') }}" class="nova-sidebar-link {{ request()->routeIs('item-packs.*') ? 'active' : '' }}">
                <i class="fas fa-boxes"></i>
                <span> بكجات المنتج</span>
            </a>
            <a href="{{ route('uoms.index') }}" class="nova-sidebar-link {{ request()->routeIs('uoms.*') ? 'active' : '' }}">
                <i class="fas fa-balance-scale"></i>
                <span>وحدة القياس</span>
            </a>
            <a href="{{ route('pack-types.index') }}" class="nova-sidebar-link {{ request()->routeIs('pack-types.*') ? 'active' : '' }}">
                <i class="fas fa-layer-group"></i>
                <span> أنواع الbach</span>
            </a>
            <a href="{{ route('pack-capacities.index') }}" class="nova-sidebar-link {{ request()->routeIs('pack-capacities.*') ? 'active' : '' }}">
                <i class="fas fa-cube"></i>
                <span>Pack Capacities</span>
            </a>
            <a href="{{ route('pack-definitions.index') }}" class="nova-sidebar-link {{ request()->routeIs('pack-definitions.*') ? 'active' : '' }}">
                <i class="fas fa-box-open"></i>
                <span>Pack Definitions</span>
            </a>
            <a href="{{ route('price-lists.index') }}" class="nova-sidebar-link {{ request()->routeIs('price-lists.*') ? 'active' : '' }}">
                <i class="fas fa-dollar-sign"></i>
                <span>قوائم الأسعار</span>
            </a>
        </div>

        <!-- System -->
        <div class="nova-sidebar-section">
            <a href="{{ route('permissions.index') }}" class="nova-sidebar-link {{ request()->routeIs('permissions.*') ? 'active' : '' }}">
                <i class="fas fa-key"></i>
                <span>Permissions</span>
            </a>
            <a href="#" onclick="event.preventDefault(); document.getElementById('nova-logout-form').submit();" class="nova-sidebar-link nova-text-danger">
                <i class="fas fa-sign-out-alt"></i>
                <span>Sign out</span>
            </a>
        </div>
    </div>

    <!-- Sidebar Footer with Collapse Button -->
    <div class="nova-sidebar-footer">
        <button class="nova-sidebar-collapse" type="button" id="novaSidebarCollapse" aria-label="Collapse sidebar" title="Collapse">
            <i class="fas fa-chevron-left"></i>
        </button>
    </div>
</aside>