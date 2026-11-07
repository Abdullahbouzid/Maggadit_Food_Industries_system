<aside class="nova-sidebar" id="novaSidebar" aria-label="Main navigation">
    <div class="nova-sidebar-content">
        <!-- Brand Logo -->
        <div class="nova-sidebar-brand">
            <a href="{{ route('dashboard') }}" class="nova-brand" style="justify-content: center;">
                <div class="nova-brand-logo">
                <img src="{{ asset('img/icons/icon.png') }}" alt="Maggadit" style="height: 60px; width: 50px;">
            </div>
                  </a>
        </div>

        <br><br>
        <!-- Dashboard -->
        <div class="nova-sidebar-section">
            <a href="{{ route('dashboard') }}" class="nova-sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-th-large"></i>
                <span>{{ __('messages.dashboard.sidebar.dashboard') }}</span>
            </a>
        </div>

        <!-- Data Definitions -->
        <div class="nova-sidebar-section">
            <a href="javascript:void(0);" class="nova-sidebar-link" id="novaDataDefinitionsBtn" data-modal="data-definitions-modal">
                <i class="fas fa-database"></i>
                <span>{{ __('messages.dashboard.sidebar.data_definitions') }}</span>
            </a>
        </div>

        <!-- Customer App Properties -->
        <div class="nova-sidebar-section">
            <a href="javascript:void(0);" class="nova-sidebar-link" id="novaCustomerAppBtn" data-modal="customer-app-modal">
                <i class="fas fa-mobile-alt"></i>
                <span>{{ __('messages.dashboard.sidebar.customer_app_properties') }}</span>
            </a>
        </div>

        <!-- Permissions & Roles -->
        <div class="nova-sidebar-section">
            <div class="nova-sidebar-title">{{ __('messages.dashboard.sidebar.permissions_roles') }}</div>
            <a href="{{ route('roles.index') }}" class="nova-sidebar-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                <i class="fas fa-user-shield"></i>
                <span>{{ __('messages.dashboard.sidebar.roles') ?? 'Roles' }}</span>
            </a>
        </div>

        <!-- Inventory & Products -->
        <div class="nova-sidebar-section">
            <div class="nova-sidebar-title">{{ __('messages.dashboard.sidebar.inventory_products') }}</div>
            <a href="{{ route('warehouses.index') }}" class="nova-sidebar-link {{ request()->routeIs('warehouses.*') ? 'active' : '' }}">
                <i class="fas fa-warehouse"></i>
                <span>{{ __('messages.dashboard.sidebar.warehouses') ?? 'المخزون' }}</span>
            </a>
            <a href="{{ route('items.index') }}" class="nova-sidebar-link {{ request()->routeIs('items.*') ? 'active' : '' }}">
                <i class="fas fa-box"></i>
                <span>{{ __('messages.dashboard.sidebar.items') ?? 'المنتجات' }}</span>
            </a>
            <a href="{{ route('item-packs.index') }}" class="nova-sidebar-link {{ request()->routeIs('item-packs.*') ? 'active' : '' }}">
                <i class="fas fa-boxes"></i>
                <span>{{ __('messages.dashboard.sidebar.item_packs') ?? 'بكجات المنتج' }}</span>
            </a>
            <a href="{{ route('uoms.index') }}" class="nova-sidebar-link {{ request()->routeIs('uoms.*') ? 'active' : '' }}">
                <i class="fas fa-balance-scale"></i>
                <span>{{ __('messages.dashboard.sidebar.uom') ?? 'وحدة القياس' }}</span>
            </a>
            <a href="{{ route('pack-types.index') }}" class="nova-sidebar-link {{ request()->routeIs('pack-types.*') ? 'active' : '' }}">
                <i class="fas fa-layer-group"></i>
                <span>{{ __('messages.dashboard.sidebar.pack_types') ?? 'أنواع الباك' }}</span>
            </a>
            <a href="{{ route('price-lists.index') }}" class="nova-sidebar-link {{ request()->routeIs('price-lists.*') ? 'active' : '' }}">
                <i class="fas fa-dollar-sign"></i>
                <span>{{ __('messages.dashboard.sidebar.price_lists') ?? 'قوائم الأسعار' }}</span>
            </a>
        </div>

        <!-- Orders & Sales -->
        <div class="nova-sidebar-section">
            <div class="nova-sidebar-title">{{ __('messages.dashboard.sidebar.sales') }}</div>
            <a href="{{ route('orders.index') }}" class="nova-sidebar-link {{ request()->routeIs('orders.*') ? 'active' : '' }}">
                <i class="fas fa-shopping-cart"></i>
                <span>{{ __('messages.dashboard.sidebar.orders') }}</span>
            </a>
            <a href="{{ route('invoices.index') }}" class="nova-sidebar-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>{{ __('messages.dashboard.sidebar.invoices') }}</span>
            </a>
            {{-- TODO: Add quotations.index route --}}
            <a href="#" class="nova-sidebar-link">
                <i class="fas fa-file-contract"></i>
                <span>{{ __('messages.dashboard.sidebar.quotations') }}</span>
            </a>
            <a href="{{ route('deliveries.index') }}" class="nova-sidebar-link {{ request()->routeIs('deliveries.*') ? 'active' : '' }}">
                <i class="fas fa-truck"></i>
                <span>{{ __('messages.dashboard.sidebar.deliveries') }}</span>
            </a>
            <a href="{{ route('returns.index') }}" class="nova-sidebar-link {{ request()->routeIs('returns.*') ? 'active' : '' }}">
                <i class="fas fa-undo"></i>
                <span>{{ __('messages.dashboard.sidebar.returns') }}</span>
            </a>
        </div>

        <!-- Customers & Sales -->
        <div class="nova-sidebar-section">
            <div class="nova-sidebar-title">{{ __('messages.dashboard.sidebar.customers_sales') }}</div>
            <a href="{{ route('customers.index') }}" class="nova-sidebar-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <span>{{ __('messages.dashboard.sidebar.customers') }}</span>
            </a>
            <a href="{{ route('branches.index') }}" class="nova-sidebar-link {{ request()->routeIs('branches.*') ? 'active' : '' }}">
                <i class="fas fa-store"></i>
                <span>{{ __('messages.dashboard.sidebar.branches') ?? 'الفروع' }}</span>
            </a>
            {{-- TODO: Add payments route --}}
            <a href="#" class="nova-sidebar-link">
                <i class="fas fa-money-bill-wave"></i>
                <span>{{ __('messages.dashboard.sidebar.payments') }}</span>
            </a>
        </div>

        <!-- Inventory Management -->
        <div class="nova-sidebar-section">
            <div class="nova-sidebar-title">{{ __('messages.dashboard.sidebar.inventory_section') }}</div>
            <a href="{{ route('inventory.index') }}" class="nova-sidebar-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                <i class="fas fa-boxes"></i>
                <span>{{ __('messages.dashboard.sidebar.inventory') }}</span>
            </a>
            <a href="{{ route('inventory.alerts') }}" class="nova-sidebar-link {{ request()->routeIs('inventory.alerts') ? 'active' : '' }}">
                <i class="fas fa-triangle-exclamation"></i>
                <span>{{ __('messages.inventory_page.alerts_title') }}</span>
            </a>
            <a href="{{ route('inventory.movements') }}" class="nova-sidebar-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                <i class="fas fa-exchange-alt"></i>
                <span>{{ __('messages.dashboard.sidebar.inventory_movements') }}</span>
            </a>
        </div>

        <!-- Finance -->
        <div class="nova-sidebar-section">
            <div class="nova-sidebar-title">{{ __('messages.dashboard.sidebar.finance') }}</div>
            <a href="{{ route('wallets.index') }}" class="nova-sidebar-link {{ request()->routeIs('wallets.*') ? 'active' : '' }}">
                <i class="fas fa-wallet"></i>
                <span>{{ __('messages.dashboard.sidebar.wallets') }}</span>
            </a>
        </div>

        <!-- Settings -->
        <div class="nova-sidebar-section">
            <div class="nova-sidebar-title">{{ __('messages.dashboard.sidebar.settings') }}</div>
            <a href="{{ route('payment-deadlines.index') }}" class="nova-sidebar-link {{ request()->routeIs('payment-deadlines.*') ? 'active' : '' }}">
                <i class="fas fa-clock"></i>
                <span>{{ __('messages.dashboard.sidebar.payment_deadlines') }}</span>
            </a>
            <a href="{{ route('cancellation-deadlines.index') }}" class="nova-sidebar-link {{ request()->routeIs('cancellation-deadlines.*') ? 'active' : '' }}">
                <i class="fas fa-clock-rotate-left"></i>
                <span>{{ __('messages.cancellation_deadline.title') }}</span>
            </a>
        </div>

        <!-- System -->
        <div class="nova-sidebar-section">
            <a href="{{ route('permissions.index') }}" class="nova-sidebar-link {{ request()->routeIs('permissions.*') ? 'active' : '' }}">
                <i class="fas fa-key"></i>
                <span>{{ __('messages.dashboard.sidebar.permissions') ?? 'الصلاحيات' }}</span>
            </a>
            <a href="#" onclick="event.preventDefault(); document.getElementById('nova-logout-form').submit();" class="nova-sidebar-link nova-text-danger">
                <i class="fas fa-sign-out-alt"></i>
                <span>{{ __('messages.dashboard.sidebar.sign_out') }}</span>
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
