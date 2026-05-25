<nav id="sidebar" class="sidebar js-sidebar">
    <div class="sidebar-content custom-scroll">
        <a class="sidebar-brand" href="{{ route('dashboard') }}">
            <img src="{{ asset('img/logo/logo.png') }}" alt="Logo" style="width: 50%; justify-content: center; justify-items: center; display: block; margin-left: auto; margin-right: auto;">
        </a>

        <ul class="sidebar-nav">
            <!-- Dashboard (always check route name) -->
            <li class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('dashboard') }}">
                    <i class="align-middle" data-feather="sliders"></i> 
                    <span class="align-middle">Dashboard</span>
                </a>
            </li>

            <li class="sidebar-header">Data Definitions</li>

            <!-- Data Definitions Menu with submenus -->
            @php
                $isMasterActive = request()->routeIs('organizations.*') || request()->routeIs('package-statuses.*');
                $isStaffActive = false; // add conditions later
                $isMahachinActive = false;
                $isCustomerActive = false;
                $isProductsActive = false;
                $isDynamicFormsActive = false;
                $isWarehouseActive = false;
                $isMobileActive = false;
                $isDisplayUnitsActive = false;
            @endphp

            <li class="sidebar-item">
                <a data-bs-toggle="collapse" href="#dataDefinitionsMenu" class="sidebar-link {{ request()->routeIs('organizations.*') || request()->routeIs('package-statuses.*') ? 'collapsed' : 'collapsed' }}" 
                   aria-expanded="{{ request()->routeIs('organizations.*') || request()->routeIs('package-statuses.*') ? 'true' : 'false' }}">
                    <i class="align-middle" data-feather="database"></i> 
                    <span class="align-middle">Data Definitions</span>
                </a>

                <ul id="dataDefinitionsMenu" class="sidebar-dropdown list-unstyled collapse {{ request()->routeIs('organizations.*') || request()->routeIs('package-statuses.*') ? 'show' : '' }}">
                    
                    <!-- master data definitions -->
                    <li class="sidebar-item">
                        <a data-bs-toggle="collapse" href="#masterSubmenu" class="sidebar-link {{ request()->routeIs('organizations.*') || request()->routeIs('package-statuses.*') ? '' : 'collapsed' }}" 
                           aria-expanded="{{ request()->routeIs('organizations.*') || request()->routeIs('package-statuses.*') ? 'true' : 'false' }}">
                            master data definitions
                        </a>
                        <ul id="masterSubmenu" class="sidebar-dropdown list-unstyled collapse {{ request()->routeIs('organizations.*') || request()->routeIs('package-statuses.*') ? 'show' : '' }}">
                            <li class="sidebar-header">General</li>
                            
                            <li class="sidebar-item {{ request()->routeIs('organizations.*') ? 'active' : '' }}">
                                <a class="sidebar-link" href="{{ route('organizations.index') }}">Organizations</a>
                            </li>
                            <li class="sidebar-item {{ request()->routeIs('vendors.*') ? 'active' : '' }}">
                                <a class="sidebar-link" href="{{ route('vendors.index') }}">Vendors</a>
                            </li>
                            <li class="sidebar-item {{ request()->routeIs('package-statuses.*') ? 'active' : '' }}">
                                <a class="sidebar-link" href="{{ route('package-statuses.index') }}">Pack Statuses</a>
                            </li>
                            <li class="sidebar-item"><a class="sidebar-link" href="{{ route('competitors.index') }}">Competitors</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="#">Visit Types</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="#">Fields Group</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="{{ route('geographical.index') }}">Geographical Location Hierarchy</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="{{ route('sales-order-types.index') }}">Sales Order Type</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="#">Stock Statuses</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="#">Item Model</a></li>

                            <li class="sidebar-header">Finance</li>
                            <li class="sidebar-item"><a class="sidebar-link" href="{{ route('currencies.index') }}">Currencies</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="#">Currency Rates</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="#">Banks</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="#">Branches</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="#">Tax Type</a></li>

                            <li class="sidebar-header">Reasons Definition</li>
                            <li class="sidebar-item"><a class="sidebar-link" href="#">Reasons</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="#">Reason Groups</a></li>
                        </ul>
                    </li>

                    <!-- staff data definitions -->
                    <li class="sidebar-item">
                        <a data-bs-toggle="collapse" href="#staffSubmenu" class="sidebar-link collapsed">
                            staff data definitions
                        </a>
                        <ul id="staffSubmenu" class="sidebar-dropdown list-unstyled collapse">
                            <li class="sidebar-item"><a class="sidebar-link" href="#">personal information</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="#">employment details</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="#">attendance schema</a></li>
                        </ul>
                    </li>

                    <!-- mahachin data definitions -->
                    <li class="sidebar-item">
                        <a data-bs-toggle="collapse" href="#mahachinSubmenu" class="sidebar-link collapsed">
                            mahachin data definitions
                        </a>
                        <ul id="mahachinSubmenu" class="sidebar-dropdown list-unstyled collapse">
                            <li class="sidebar-item"><a class="sidebar-link" href="#">machine profiles</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="#">production parameters</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="#">maintenance records</a></li>
                        </ul>
                    </li>

                    <!-- customer data definitions -->
                    <li class="sidebar-item">
                        <a data-bs-toggle="collapse" href="#customerSubmenu" class="sidebar-link collapsed">
                            customer data definitions
                        </a>
                        <ul id="customerSubmenu" class="sidebar-dropdown list-unstyled collapse">
                            <li class="sidebar-item"><a class="sidebar-link" href="#">customer demographics</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="#">contact information</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="#">loyalty & segments</a></li>
                        </ul>
                    </li>

                    <!-- products data definitions -->
                    <li class="sidebar-item">
                        <a data-bs-toggle="collapse" href="#productsSubmenu" class="sidebar-link collapsed">
                            products data definitions
                        </a>
                        <ul id="productsSubmenu" class="sidebar-dropdown list-unstyled collapse">
                            <li class="sidebar-item"><a class="sidebar-link" href="#">product attributes</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="#">pricing & tax rules</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="{{ route('brands.index') }}">Brands</a></li>
                        </ul>
                    </li>

                    <!-- dynamic forms -->
                    <li class="sidebar-item">
                        <a data-bs-toggle="collapse" href="#dynamicFormsSubmenu" class="sidebar-link collapsed">
                            dynamic forms
                        </a>
                        <ul id="dynamicFormsSubmenu" class="sidebar-dropdown list-unstyled collapse">
                            <li class="sidebar-item"><a class="sidebar-link" href="#">form builder schema</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="#">submission templates</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="#">validation rules</a></li>
                        </ul>
                    </li>

                    <!-- warehouse & vehicles -->
                    <li class="sidebar-item">
                        <a data-bs-toggle="collapse" href="#warehouseSubmenu" class="sidebar-link collapsed">
                            warehouse & vehicles
                        </a>
                        <ul id="warehouseSubmenu" class="sidebar-dropdown list-unstyled collapse">
                            <li class="sidebar-item"><a class="sidebar-link" href="#">storage zones</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="#">vehicle fleet data</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="#">logistics routes</a></li>
                        </ul>
                    </li>

                    <!-- mobile customization -->
                    <li class="sidebar-item">
                        <a data-bs-toggle="collapse" href="#mobileSubmenu" class="sidebar-link collapsed">
                            mobile customization
                        </a>
                        <ul id="mobileSubmenu" class="sidebar-dropdown list-unstyled collapse">
                            <li class="sidebar-item"><a class="sidebar-link" href="#">UI layouts</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="#">offline forms</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="#">push notification schemas</a></li>
                        </ul>
                    </li>

                    <!-- display units -->
                    <li class="sidebar-item">
                        <a data-bs-toggle="collapse" href="#displayUnitsSubmenu" class="sidebar-link collapsed">
                            display units
                        </a>
                        <ul id="displayUnitsSubmenu" class="sidebar-dropdown list-unstyled collapse">
                            <li class="sidebar-item"><a class="sidebar-link" href="#">screen layouts</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="#">widget definitions</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="#">real-time dashboards</a></li>
                        </ul>
                    </li>
                </ul>
            </li>

            <li class="sidebar-header">Permissions & Roles</li>
            <li class="sidebar-item {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('roles.index') }}">
                        <i class="align-middle" data-feather="users"></i> 
                    <span class="align-middle">Roles</span>
                </a>
            </li>
            <li class="sidebar-item {{ request()->routeIs('permissions.*') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('permissions.index') }}">
                    <i class="align-middle" data-feather="key"></i>
                    <span class="align-middle">Permissions</span>
                </a>
            </li>
        </ul>
    </div>
</nav>

<style>
/* Custom Scrollbar for Sidebar */
.custom-scroll {
    overflow-y: auto !important;
    height: 100vh;
    height: calc(100vh - 0px);
}
.custom-scroll::-webkit-scrollbar {
    width: 6px;
}
.custom-scroll::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scroll::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
}
.custom-scroll::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.4);
}
</style>