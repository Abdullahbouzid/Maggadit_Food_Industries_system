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

            <!-- Data Definitions -->
            <li class="sidebar-item cursor-pointer" id="data-definitions-header">
                <a class="sidebar-link" href="javascript:void(0);">
                    <i class="align-middle" data-feather="database"></i>
                    <span class="align-middle">Data Definitions</span>
                </a>
            </li>

            <!-- Customer App Properties -->
            <li class="sidebar-item cursor-pointer" id="customer-app-header">
                <a class="sidebar-link" href="javascript:void(0);">
                    <i class="align-middle" data-feather="smartphone"></i>
                    <span class="align-middle">Customer App Properties</span>
                </a>
            </li>

            <!-- Permissions & Roles -->
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
            <li class="sidebar-item">
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

<!-- ========================================================== -->
<!--  بيانات التعريفات (Data Definitions)                         -->
<!-- ========================================================== -->
@php
    $definitions = [
        'master' => [
            'title' => 'master data definitions',
            'icon' => 'database',
            'items' => [
                ['label' => 'Organizations', 'icon' => 'users', 'route' => route('organizations.index'), 'disabled' => false],
                ['label' => 'Vendors', 'icon' => 'truck', 'route' => route('vendors.index'), 'disabled' => false],
                ['label' => 'Pack Statuses', 'icon' => 'package', 'route' => route('package-statuses.index'), 'disabled' => false],
                ['label' => 'Visit Types', 'icon' => 'map-pin', 'route' => '#', 'disabled' => false],
                ['label' => 'Fields Group', 'icon' => 'layers', 'route' => '#', 'disabled' => false],
                ['label' => 'Geographical Location Hierarchy', 'icon' => 'map', 'route' => route('geographical.index'), 'disabled' => false],
                ['label' => 'Sales Order Type', 'icon' => 'truck', 'route' => route('sales-order-types.index'), 'disabled' => false],
                ['label' => 'Stock Statuses', 'icon' => 'package', 'route' => '#', 'disabled' => false],
                ['label' => 'Item Model', 'icon' => 'box', 'route' => '#', 'disabled' => false],
                ['label' => 'Currencies', 'icon' => 'dollar-sign', 'route' => route('currencies.index'), 'disabled' => false],
                ['label' => 'Currency Rates', 'icon' => 'repeat', 'route' => '#', 'disabled' => false],
                ['label' => 'Banks', 'icon' => 'bank', 'route' => '#', 'disabled' => false],
                ['label' => 'Branches', 'icon' => 'building', 'route' => '#', 'disabled' => false],
                ['label' => 'Tax Type', 'icon' => 'percent', 'route' => '#', 'disabled' => false],
                ['label' => 'Reasons', 'icon' => 'alert-triangle', 'route' => '#', 'disabled' => false],
                ['label' => 'Reason Groups', 'icon' => 'list', 'route' => '#', 'disabled' => false],
            ]
        ],
        'staff' => [
            'title' => 'staff data definitions',
            'icon' => 'users',
            'items' => [
                ['label' => 'Devices', 'icon' => 'phone', 'route' => route('devices.index'), 'disabled' => false],
                ['label' => 'personal information', 'icon' => 'user', 'route' => '#', 'disabled' => false],
                ['label' => 'employment details', 'icon' => 'briefcase', 'route' => '#', 'disabled' => false],
                ['label' => 'attendance schema', 'icon' => 'calendar-check', 'route' => '#', 'disabled' => false],
            ]
        ],
        'customer' => [
            'title' => 'customer data definitions',
            'icon' => 'users',
            'items' => [
                ['label' => 'customer demographics', 'icon' => 'user-plus', 'route' => route('customers.index'), 'disabled' => false],
                ['label' => 'contact information', 'icon' => 'phone', 'route' => '#', 'disabled' => false],
                ['label' => 'loyalty & segments', 'icon' => 'award', 'route' => '#', 'disabled' => false],
            ]
        ],
        'products' => [
            'title' => 'products data definitions',
            'icon' => 'box',
            'items' => [
                ['label' => 'product attributes', 'icon' => 'sliders', 'route' => '#', 'disabled' => false],
                ['label' => 'pricing & tax rules', 'icon' => 'percent', 'route' => '#', 'disabled' => false],
                ['label' => 'Brands', 'icon' => 'tag', 'route' => route('brands.index'), 'disabled' => false],
                ['label' => 'Categories', 'icon' => 'layers', 'route' => route('categories.index'), 'disabled' => false],
                ['label' => 'Item Types', 'icon' => 'box', 'route' => route('item-types.index'), 'disabled' => false],
                ['label' => 'Units of Measure (UOMs)', 'icon' => 'arrows-alt', 'route' => route('uoms.index'), 'disabled' => false],
                ['label' => 'Pack Operations', 'icon' => 'shift', 'route' => route('pack-operations.index'), 'disabled' => false],
                ['label' => 'Pack Groups', 'icon' => 'layers', 'route' => route('pack-groups.index'), 'disabled' => false],
                ['label' => 'Items', 'icon' => 'box', 'route' => route('items.index'), 'disabled' => false],
                ['label' => 'Item Packs', 'icon' => 'package', 'route' => route('item-packs.index'), 'disabled' => false],
                ['label' => 'Item Images', 'icon' => 'image', 'route' => route('item-images.index'), 'disabled' => false],
                ['label' => 'Item Models', 'icon' => 'box', 'route' => route('item-models.index'), 'disabled' => false],
            ]
        ],
        'warehouse' => [
            'title' => 'warehouse & vehicles',
            'icon' => 'truck',
            'items' => [
                ['label' => 'states', 'icon' => 'map-pin', 'route' => route('states.index'), 'disabled' => false],
                ['label' => 'areas', 'icon' => 'map-pin', 'route' => route('areas.index'), 'disabled' => false],
                ['label' => 'warehouse types', 'icon' => 'warehouse', 'route' => route('warehouse-types.index'), 'disabled' => false],
                ['label' => 'warehouses', 'icon' => 'warehouse', 'route' => route('warehouses.index'), 'disabled' => false],
                ['label' => 'vehicle types', 'icon' => 'truck', 'route' => route('vehicle-types.index'), 'disabled' => false],
                ['label' => 'vehicles', 'icon' => 'truck', 'route' => route('vehicles.index'), 'disabled' => false],
            ]
        ],
        'mobile' => [
            'title' => 'mobile customization',
            'icon' => 'smartphone',
            'items' => [
                ['label' => 'UI layouts', 'icon' => 'layout', 'route' => '#', 'disabled' => false],
                ['label' => 'offline forms', 'icon' => 'save', 'route' => '#', 'disabled' => false],
                ['label' => 'push notification schemas', 'icon' => 'bell', 'route' => '#', 'disabled' => false],
            ]
        ],
        'display' => [
            'title' => 'display units',
            'icon' => 'monitor',
            'items' => [
                ['label' => 'screen layouts', 'icon' => 'layout', 'route' => '#', 'disabled' => false],
                ['label' => 'widget definitions', 'icon' => 'sliders', 'route' => '#', 'disabled' => false],
                ['label' => 'real-time dashboards', 'icon' => 'monitor', 'route' => '#', 'disabled' => false],
            ]
        ],
    ];
@endphp
<div id="definitions-data" style="display:none;" data-json='{!! json_encode($definitions) !!}'></div>

<!-- ========== عرض بطاقات Data Definitions ========== -->
<div id="definitions-card-view" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-75 d-none z-index-1050">
    <div class="container h-100 d-flex align-items-center justify-content-center">
        <div class="row w-100">
            <div id="main-categories-container" class="col-12">
                <div class="row g-4 justify-content-center" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 1.25rem; width: 100%;">
                    @foreach ($definitions as $key => $data)
                        <div class="col">
                            <a href="javascript:void(0);" class="card h-100 text-center main-category-card" data-target="{{ $key }}" style="min-height: 160px; background: rgba(255,255,255,0.08); backdrop-filter: blur(4px); border: 1px solid rgba(255,255,255,0.12); border-radius: 20px; transition: all 0.25s ease; cursor: pointer; text-decoration: none;">
                                <div class="card-body d-flex flex-column align-items-center py-4">
                                    <i data-feather="{{ $data['icon'] }}" class="mb-3 main-icon"></i>
                                    <h5 class="card-title">{{ $data['title'] }}</h5>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>

            <div id="subcategories-container" class="col-12 d-none">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <button id="back-to-main" class="btn btn-outline-light btn-sm">
                        <i data-feather="arrow-left"></i> Back
                    </button>
                    <h5 id="subcategory-title" class="mb-0" style="color: #e2e8f0;"></h5>
                </div>
                <div id="subcards-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 1rem;"></div>
            </div>
        </div>
    </div>
</div>

<!-- ========== عرض بطاقات Customer App Properties (مع إضافة Send Notification) ========== -->
<div id="customer-app-card-view" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-75 d-none z-index-1050">
    <div class="container h-100 d-flex align-items-center justify-content-center">
        <div class="row w-100">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <button id="close-customer-app" class="btn btn-outline-light btn-sm">
                        <i data-feather="x"></i> Close
                    </button>
                    <h5 class="mb-0" style="color: #e2e8f0;">Customer App Properties</h5>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1.5rem; justify-content: center;">
                    
                    <!-- 1. FAQ -->
                    <a href="{{ route('faqs.index') }}" class="card h-100 text-center subcard-link" style="min-height: 160px; background: rgba(255,255,255,0.08); backdrop-filter: blur(4px); border: 1px solid rgba(255,255,255,0.12); border-radius: 20px; transition: all 0.25s ease; cursor: pointer; text-decoration: none;">
                        <div class="card-body d-flex flex-column align-items-center py-4">
                            <i data-feather="help-circle" class="mb-3 sub-icon" style="font-size: 4rem; width: auto; height: 100%; color: #a5b4fc;"></i>
                            <small style="font-size: 1rem; font-weight: 600; color: #e2e8f0;">الأسئلة الشائعة</small>
                        </div>
                    </a>

                    <!-- 2. Surveys -->
                    <a href="{{ route('surveys.index') }}" class="card h-100 text-center subcard-link" style="min-height: 160px; background: rgba(255,255,255,0.08); backdrop-filter: blur(4px); border: 1px solid rgba(255,255,255,0.12); border-radius: 20px; transition: all 0.25s ease; cursor: pointer; text-decoration: none;">
                        <div class="card-body d-flex flex-column align-items-center py-4">
                            <i data-feather="clipboard" class="mb-3 sub-icon" style="font-size: 4rem; width: auto; height: 100%; color: #a5b4fc;"></i>
                            <small style="font-size: 1rem; font-weight: 600; color: #e2e8f0;">الاستبيانات</small>
                        </div>
                    </a>

                    <!-- 3. Banners -->
                    <a href="{{ route('banners.index') }}" class="card h-100 text-center subcard-link" style="min-height: 160px; background: rgba(255,255,255,0.08); backdrop-filter: blur(4px); border: 1px solid rgba(255,255,255,0.12); border-radius: 20px; transition: all 0.25s ease; cursor: pointer; text-decoration: none;">
                        <div class="card-body d-flex flex-column align-items-center py-4">
                            <i data-feather="image" class="mb-3 sub-icon" style="font-size: 4rem; width: auto; height: 100%; color: #a5b4fc;"></i>
                            <small style="font-size: 1rem; font-weight: 600; color: #e2e8f0;">Banners</small>
                        </div>
                    </a>

                    <!-- 4. Send Notification (جديد) -->
                    <a href="{{ route('notifications.index') }}" class="card h-100 text-center subcard-link" style="min-height: 160px; background: rgba(255,255,255,0.08); backdrop-filter: blur(4px); border: 1px solid rgba(255,255,255,0.12); border-radius: 20px; transition: all 0.25s ease; cursor: pointer; text-decoration: none;">
                        <div class="card-body d-flex flex-column align-items-center py-4">
                            <i data-feather="bell" class="mb-3 sub-icon" style="font-size: 4rem; width: auto; height: 100%; color: #a5b4fc;"></i>
                            <small style="font-size: 1rem; font-weight: 600; color: #e2e8f0;">إرسال إشعار</small>
                        </div>
                    </a>
                     <!-- 5. Organization About (جديد) -->
                    <a href="{{ route('organization-about.index') }}" class="card h-100 text-center subcard-link" style="min-height: 160px; background: rgba(255,255,255,0.08); backdrop-filter: blur(4px); border: 1px solid rgba(255,255,255,0.12); border-radius: 20px; transition: all 0.25s ease; cursor: pointer; text-decoration: none;">
                        <div class="card-body d-flex flex-column align-items-center py-4">
                            <i data-feather="info" class="mb-3 sub-icon" style="font-size: 4rem; width: auto; height: 100%; color: #a5b4fc;"></i>
                            <small style="font-size: 1rem; font-weight: 600; color: #e2e8f0;">نبذة المنظمات</small>
                        </div>
                    </a>

                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* ========== Sidebar Styles ========== */
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

.sidebar-header {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 700;
    color: #64748b;
    padding: 20px 16px 8px 16px;
    margin-top: 8px;
}

.sidebar-item {
    margin-bottom: 4px;
}

.sidebar-item.cursor-pointer {
    cursor: pointer;
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

/* Modals */
body.modal-open {
    overflow: hidden;
}

#definitions-card-view,
#customer-app-card-view {
    z-index: 9999 !important;
}

/* Cards */
.card.main-category-card,
.card.subcard-link {
    background: rgba(255,255,255,0.08);
    backdrop-filter: blur(4px);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 20px;
    transition: all 0.25s ease;
    cursor: pointer;
    height: 100%;
    min-height: 160px;
    text-decoration: none;
}

.card.main-category-card:hover,
.card.subcard-link:hover {
    transform: translateY(-6px) scale(1.02);
    background: rgba(255,255,255,0.15);
    box-shadow: 0 12px 30px rgba(99, 102, 241, 0.3);
}

.card.main-category-card .main-icon,
.card.subcard-link .sub-icon {
    font-size: 4rem;
    width: auto;
    height: 100%;
    color: #a5b4fc;
    margin-bottom: 0.75rem;
    filter: drop-shadow(0 4px 8px rgba(99,102,241,0.2));
    transition: all 0.3s ease;
}

.card.main-category-card:hover .main-icon,
.card.subcard-link:hover .sub-icon {
    filter: drop-shadow(0 0 20px rgba(99,102,241,0.6));
    transform: scale(1.05);
}

.card-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: #e2e8f0;
    margin-top: 0.25rem;
}

.subcard-link small {
    font-size: 0.8rem;
    color: #cbd5e1;
}

/* Responsive */
@media (max-width: 576px) {
    #main-categories-container .row {
        grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
        gap: 0.75rem;
    }
    #subcards-grid {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 0.75rem;
    }
    .card.main-category-card .main-icon,
    .card.subcard-link .sub-icon {
        font-size: 3rem;
    }
    .card.main-category-card {
        min-height: 120px;
    }
    .card.subcard-link {
        min-height: 110px;
    }
    .card-title {
        font-size: 0.75rem;
    }
    .subcard-link small {
        font-size: 0.7rem;
    }
}

@media (max-width: 400px) {
    #main-categories-container .row {
        grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
        gap: 0.5rem;
    }
    #subcards-grid {
        grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
        gap: 0.5rem;
    }
    .card.main-category-card .main-icon,
    .card.subcard-link .sub-icon {
        font-size: 2.5rem;
    }
    .card.main-category-card {
        min-height: 100px;
    }
    .card.subcard-link {
        min-height: 90px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // ========== Data Definitions Logic ==========
    const dataEl = document.getElementById('definitions-data');
    window.subcategoryData = dataEl ? JSON.parse(dataEl.dataset.json) : {};

    const definitionsView = document.getElementById('definitions-card-view');
    const mainContainer   = document.getElementById('main-categories-container');
    const subContainer    = document.getElementById('subcategories-container');
    const backBtn         = document.getElementById('back-to-main');
    const subTitle        = document.getElementById('subcategory-title');
    const subcardsGrid    = document.getElementById('subcards-grid');

    document.getElementById('data-definitions-header').addEventListener('click', function(e) {
        e.stopPropagation();
        document.getElementById('customer-app-card-view').classList.add('d-none');
        definitionsView.classList.remove('d-none');
        document.body.classList.add('modal-open');
        mainContainer.classList.remove('d-none');
        subContainer.classList.add('d-none');
        subcardsGrid.innerHTML = '';
        subTitle.textContent = '';
    });

    definitionsView.addEventListener('click', function(e) {
        if (e.target === definitionsView) {
            definitionsView.classList.add('d-none');
            document.body.classList.remove('modal-open');
        }
    });

    document.querySelectorAll('.main-category-card').forEach(card => {
        card.addEventListener('click', function() {
            const target = this.getAttribute('data-target');
            if (!target) return;

            const categoryData = window.subcategoryData[target] || {};
            const items = categoryData.items || [];

            subTitle.textContent = this.querySelector('.card-title').textContent;
            subcardsGrid.innerHTML = '';
            items.forEach(item => {
                const col = document.createElement('div');
                col.className = 'col';

                const link = document.createElement('a');
                if (!item.disabled && item.route && item.route !== '#') {
                    link.href = 'javascript:void(0);';
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        window.location.href = item.route;
                    });
                } else {
                    link.href = item.disabled || !item.route ? '#' : item.route;
                }

                const baseClasses = 'card h-100 text-center subcard-link';
                link.className = item.disabled ? `${baseClasses} disabled` : baseClasses;

                link.innerHTML = `
                    <div class="card-body d-flex flex-column align-items-center py-3">
                        <i data-feather="${item.icon}" class="mb-2 sub-icon"></i>
                        <small>${item.label}</small>
                    </div>
                `;

                if (item.disabled) {
                    link.style.cursor = 'not-allowed';
                    link.title = 'هذا العنصر غير مفعل حاليًا';
                }

                col.appendChild(link);
                subcardsGrid.appendChild(col);
            });

            if (typeof feather !== 'undefined') {
                feather.replace();
            }

            mainContainer.classList.add('d-none');
            subContainer.classList.remove('d-none');
        });
    });

    backBtn.addEventListener('click', function() {
        mainContainer.classList.remove('d-none');
        subContainer.classList.add('d-none');
        subcardsGrid.innerHTML = '';
        subTitle.textContent = '';
    });

    // ========== Customer App Properties Logic ==========
    const customerAppView = document.getElementById('customer-app-card-view');
    const closeCustomerAppBtn = document.getElementById('close-customer-app');

    document.getElementById('customer-app-header').addEventListener('click', function(e) {
        e.stopPropagation();
        definitionsView.classList.add('d-none');
        customerAppView.classList.remove('d-none');
        document.body.classList.add('modal-open');
    });

    customerAppView.addEventListener('click', function(e) {
        if (e.target === customerAppView) {
            customerAppView.classList.add('d-none');
            document.body.classList.remove('modal-open');
        }
    });

    closeCustomerAppBtn.addEventListener('click', function() {
        customerAppView.classList.add('d-none');
        document.body.classList.remove('modal-open');
    });

    document.querySelectorAll('#customer-app-card-view .card, #customer-app-card-view .container')
        .forEach(el => el.addEventListener('click', function(e) {
            e.stopPropagation();
        }));
});
</script>