<nav id="sidebar" class="sidebar js-sidebar">
    <div class="sidebar-content custom-scroll">
        <a class="sidebar-brand" href="{{ route('dashboard') }}">
            <img src="{{ asset('img/logo/logo.png') }}" alt="Logo" style="width: 50%; display: block; margin: 0 auto;">
        </a>

        <ul class="sidebar-nav">
            <!-- Dashboard -->
            <li class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('dashboard') }}">
                    <i class="align-middle" data-feather="sliders"></i>
                    <span class="align-middle">Dashboard</span>
                </a>
            </li>

            <li class="sidebar-header">Data Definitions</li>

            @php
                $isMasterActive = request()->routeIs('organizations.*') || request()->routeIs('package-statuses.*');
                $isStaffActive = false;
                $isMahachinActive = false;
                $isCustomerActive = false;
                $isProductsActive = false;
                $isDynamicFormsActive = false;
                $isWarehouseActive = false;
                $isMobileActive = false;
                $isDisplayUnitsActive = false;
            @endphp

            <li class="sidebar-item">
                <a data-bs-toggle="collapse" href="#dataDefinitionsMenu" 
                   class="sidebar-link {{ request()->routeIs('organizations.*') || request()->routeIs('package-statuses.*') ? '' : 'collapsed' }}" 
                   aria-expanded="{{ request()->routeIs('organizations.*') || request()->routeIs('package-statuses.*') ? 'true' : 'false' }}">
                    <i class="align-middle" data-feather="database"></i>
                    <span class="align-middle">Data Definitions</span>
                    <i class="chevron-icon align-middle" data-feather="chevron-down"></i>
                </a>

                <!-- تم إضافة data-bs-parent="#dataDefinitionsMenu" هنا -->
                <ul id="dataDefinitionsMenu" class="sidebar-dropdown list-unstyled collapse {{ request()->routeIs('organizations.*') || request()->routeIs('package-statuses.*') ? 'show' : '' }}" data-bs-parent="#sidebar">
                    
                    <!-- master data definitions -->
                    <li class="sidebar-item">
                        <a data-bs-toggle="collapse" href="#masterSubmenu" 
                           class="sidebar-link {{ request()->routeIs('organizations.*') || request()->routeIs('package-statuses.*') ? '' : 'collapsed' }}" 
                           aria-expanded="{{ request()->routeIs('organizations.*') || request()->routeIs('package-statuses.*') ? 'true' : 'false' }}"
                           data-bs-parent="#dataDefinitionsMenu">
                            master data definitions
                            <i class="chevron-icon align-middle" data-feather="chevron-down"></i>
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
                        <a data-bs-toggle="collapse" href="#staffSubmenu" class="sidebar-link collapsed" data-bs-parent="#dataDefinitionsMenu">
                            staff data definitions
                            <i class="chevron-icon align-middle" data-feather="chevron-down"></i>
                        </a>
                        <ul id="staffSubmenu" class="sidebar-dropdown list-unstyled collapse">
                            <li class="sidebar-item"><a class="sidebar-link" href="{{ route('devices.index') }}">Devices</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="#">personal information</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="#">employment details</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="#">attendance schema</a></li>
                        </ul>
                    </li>

                  

                    <!-- customer data definitions -->
                    <li class="sidebar-item">
                        <a data-bs-toggle="collapse" href="#customerSubmenu" class="sidebar-link collapsed" data-bs-parent="#dataDefinitionsMenu">
                            customer data definitions
                            <i class="chevron-icon align-middle" data-feather="chevron-down"></i>
                        </a>
                        <ul id="customerSubmenu" class="sidebar-dropdown list-unstyled collapse">
                            <li class="sidebar-item"><a class="sidebar-link" href="#">customer demographics</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="#">contact information</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="#">loyalty & segments</a></li>
                        </ul>
                    </li>

                    <!-- products data definitions -->
                    <li class="sidebar-item">
                        <a data-bs-toggle="collapse" href="#productsSubmenu" class="sidebar-link collapsed" data-bs-parent="#dataDefinitionsMenu">
                            products data definitions
                            <i class="chevron-icon align-middle" data-feather="chevron-down"></i>
                        </a>
                        <ul id="productsSubmenu" class="sidebar-dropdown list-unstyled collapse">
                            <li class="sidebar-item"><a class="sidebar-link" href="#">product attributes</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="#">pricing & tax rules</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="{{ route('brands.index') }}">Brands</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="{{ route('categories.index') }}">Categories</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="{{ route('item-types.index') }}">Item Types</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="{{ route('uoms.index') }}">Units of Measure (UOMs)</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="{{ route('pack-operations.index') }}">Pack Operations</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="{{ route('pack-groups.index') }}">Pack Groups</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="{{ route('items.index') }}">Items</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="{{ route('item-packs.index') }}">Item Packs</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="{{ route('item-images.index') }}">Item Images</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="{{ route('item-models.index') }}">Item Models</a></li>   
                        </ul>
                    </li>

                 

                    <!-- warehouse & vehicles -->
                    <li class="sidebar-item">
                        <a data-bs-toggle="collapse" href="#warehouseSubmenu" class="sidebar-link collapsed" data-bs-parent="#dataDefinitionsMenu">
                            warehouse & vehicles
                            <i class="chevron-icon align-middle" data-feather="chevron-down"></i>
                        </a>
                        <ul id="warehouseSubmenu" class="sidebar-dropdown list-unstyled collapse">
                            <li class="sidebar-item"><a class="sidebar-link" href="{{ route('states.index') }}">states</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="{{ route('areas.index') }}">areas</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="{{ route('warehouse-types.index') }}">warehouse types</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="{{ route('warehouses.index') }}">warehouses</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="{{ route('vehicle-types.index') }}">vehicle types</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="{{ route('vehicles.index') }}">vehicles</a></li>
                        </ul>
                    </li>

                    <!-- mobile customization -->
                    <li class="sidebar-item">
                        <a data-bs-toggle="collapse" href="#mobileSubmenu" class="sidebar-link collapsed" data-bs-parent="#dataDefinitionsMenu">
                            mobile customization
                            <i class="chevron-icon align-middle" data-feather="chevron-down"></i>
                        </a>
                        <ul id="mobileSubmenu" class="sidebar-dropdown list-unstyled collapse">
                            <li class="sidebar-item"><a class="sidebar-link" href="#">UI layouts</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="#">offline forms</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="#">push notification schemas</a></li>
                        </ul>
                    </li>

                    <!-- display units -->
                    <li class="sidebar-item">
                        <a data-bs-toggle="collapse" href="#displayUnitsSubmenu" class="sidebar-link collapsed" data-bs-parent="#dataDefinitionsMenu">
                            display units
                            <i class="chevron-icon align-middle" data-feather="chevron-down"></i>
                        </a>
                        <ul id="displayUnitsSubmenu" class="sidebar-dropdown list-unstyled collapse">
                            <li class="sidebar-item"><a class="sidebar-link" href="#">screen layouts</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="#">widget definitions</a></li>
                            <li class="sidebar-item"><a class="sidebar-link" href="#">real-time dashboards</a></li>
                        </ul>
                    </li>
                </ul>
            </li>

            <li class="sidebar-header">Application Content</li>
            <li class="sidebar-item {{ request()->routeIs('faqs.*') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('faqs.index') }}">
                    <i class="align-middle" data-feather="help-circle"></i>
                    <span class="align-middle">الأسئلة الشائعة (FAQ)</span>
                </a>
            </li>
            <li class="sidebar-item {{ request()->routeIs('surveys.*') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('surveys.index') }}">
                    <i class="align-middle" data-feather="clipboard"></i>
                    <span class="align-middle">الاستبيانات</span>
                </a>
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
            <li class="sidebar-item {{ request()->routeIs('logout') ? 'active' : '' }}">
                <a class="sidebar-link" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="align-middle" data-feather="log-out"></i>
                    <span class="align-middle">sign out</span>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </li>
        </ul>


        
    </div>
</nav>

<style>
/* ========== تحسين شامل للـ Sidebar مع الحفاظ على الكلاسات ========== */
.sidebar {
    background: linear-gradient(145deg, #0f172a 0%, #0a0f1c 100%);
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
    border-right: none;
    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
}

.sidebar-content {
    padding: 0 12px;
}

.sidebar-brand {
    display: flex;
    justify-content: center;
    padding: 20px 0;
    margin-bottom: 10px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
}

.sidebar-brand img {
    transition: transform 0.2s;
}
.sidebar-brand img:hover {
    transform: scale(1.02);
}

/* عناوين الأقسام */
.sidebar-header {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 700;
    color: #64748b;
    padding: 20px 16px 8px 16px;
    margin-top: 8px;
}

/* عناصر القائمة الرئيسية */
.sidebar-item {
    margin-bottom: 4px;
}

.sidebar-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 16px;
    border-radius: 12px;
    color: #94a3b8;
    font-weight: 500;
    font-size: 0.9rem;
    transition: all 0.2s ease;
    text-decoration: none;
    position: relative;
}

.sidebar-link i {
    width: 20px;
    height: 20px;
    color: #64748b;
    transition: all 0.2s;
}

.sidebar-link .chevron-icon {
    margin-left: auto;
    width: 16px;
    height: 16px;
    transition: transform 0.25s ease;
}

.sidebar-link:hover {
    background: rgba(99, 102, 241, 0.15);
    color: #e2e8f0;
    transform: translateX(4px);
}

.sidebar-link:hover i {
    color: #818cf8;
}

.sidebar-item.active > .sidebar-link {
    background: linear-gradient(135deg, rgba(99,102,241,0.2), rgba(139,92,246,0.1));
    color: white;
    border-left: 3px solid #6366f1;
    border-radius: 12px 8px 8px 12px;
}

.sidebar-item.active > .sidebar-link i {
    color: #a5b4fc;
}

/* القوائم المنسدلة (dropdown) */
.sidebar-dropdown {
    margin-left: 24px;
    padding-left: 12px;
    border-left: 1px solid rgba(255,255,255,0.08);
}

.sidebar-dropdown .sidebar-link {
    padding: 8px 16px;
    font-size: 0.85rem;
}

/* عناصر المستوى الثاني والثالث */
.sidebar-dropdown .sidebar-dropdown {
    margin-left: 20px;
    border-left-color: rgba(255,255,255,0.05);
}

.sidebar-dropdown .sidebar-header {
    font-size: 0.65rem;
    padding: 12px 0 4px 0;
    margin-top: 8px;
    color: #475569;
}

/* حالة collapse */
.collapse:not(.show) {
    display: none;
}

/* transition سلس عند الفتح/الغلق */
.collapsing {
    transition: height 0.2s ease;
}

/* شريط التمرير المخصص */
.custom-scroll {
    overflow-y: auto !important;
    height: 100vh;
    scrollbar-width: thin;
}
.custom-scroll::-webkit-scrollbar {
    width: 5px;
}
.custom-scroll::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.05);
    border-radius: 10px;
}
.custom-scroll::-webkit-scrollbar-thumb {
    background: #6366f1;
    border-radius: 10px;
}
.custom-scroll::-webkit-scrollbar-thumb:hover {
    background: #818cf8;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // تطبيق سلوك الـ Accordion على القوائم الرئيسية داخل #dataDefinitionsMenu
    const menuContainer = document.getElementById('dataDefinitionsMenu');
    if (menuContainer) {
        // استهداف جميع عناصر التبديل المباشرة (الأولى) فقط
        const mainToggles = menuContainer.querySelectorAll(':scope > li > a[data-bs-toggle="collapse"]');
        
        mainToggles.forEach(toggle => {
            // تعيين الـ parent لضمان إغلاق الآخرين
            toggle.setAttribute('data-bs-parent', '#dataDefinitionsMenu');
            
            // إدارة أيقونة السهم باستخدام أحداث Bootstrap
            const targetId = toggle.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                // عند بدء الفتح
                targetElement.addEventListener('show.bs.collapse', function() {
                    const chevron = toggle.querySelector('.chevron-icon');
                    if (chevron) chevron.style.transform = 'rotate(180deg)';
                });
                // عند بدء الإغلاق
                targetElement.addEventListener('hide.bs.collapse', function() {
                    const chevron = toggle.querySelector('.chevron-icon');
                    if (chevron) chevron.style.transform = 'rotate(0deg)';
                });
                // ضبط الحالة الأولية
                if (targetElement.classList.contains('show')) {
                    const chevron = toggle.querySelector('.chevron-icon');
                    if (chevron) chevron.style.transform = 'rotate(180deg)';
                }
            }
        });
    }

    // معالجة الأسهم للعنصر الرئيسي "Data Definitions" نفسه
    const mainToggle = document.querySelector('[data-bs-toggle="collapse"][href="#dataDefinitionsMenu"]');
    if (mainToggle) {
        const mainTarget = document.querySelector('#dataDefinitionsMenu');
        if (mainTarget) {
            mainTarget.addEventListener('show.bs.collapse', function() {
                const chevron = mainToggle.querySelector('.chevron-icon');
                if (chevron) chevron.style.transform = 'rotate(180deg)';
            });
            mainTarget.addEventListener('hide.bs.collapse', function() {
                const chevron = mainToggle.querySelector('.chevron-icon');
                if (chevron) chevron.style.transform = 'rotate(0deg)';
            });
            if (mainTarget.classList.contains('show')) {
                const chevron = mainToggle.querySelector('.chevron-icon');
                if (chevron) chevron.style.transform = 'rotate(180deg)';
            }
        }
    }
});
</script>