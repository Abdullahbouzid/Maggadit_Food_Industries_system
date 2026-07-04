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

            <!-- Data Definitions Header (click to show cards) -->
            <li class="sidebar-header cursor-pointer" id="data-definitions-header" data-bs-toggle="tooltip" data-bs-placement="right" title="تعريفات البيانات">
                <i data-feather="database"></i>
                <span class="align-middle">Data Definitions</span>
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
                ['label' => 'customer demographics', 'icon' => 'user-plus', 'route' => '#', 'disabled' => false],
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

<!-- Dedicated Card View (hidden by default, shown when clicking Data Definitions header) -->
<div id="definitions-card-view" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-75 d-none z-index-1050">
    <div class="container h-100 d-flex align-items-center justify-content-center">
        <div class="row w-100">
            <!-- Main Categories Cards (shown initially) -->
            <div id="main-categories-container" class="col-12">
                <div class="row g-4 justify-content-center">
                    @foreach ($definitions as $key => $data)
                        <div class="col-md-2 col-sm-4">
                            <a href="javascript:void(0);" class="card h-100 text-center main-category-card" data-target="{{ $key }}">
                                <div class="card-body d-flex flex-column align-items-center py-4">
                                    <i data-feather="{{ $data['icon'] }}" class="mb-3" style="font-size:2.5rem;"></i>
                                    <h5 class="card-title">{{ $data['title'] }}</h5>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Subcategory Cards (hidden initially) -->
            <div id="subcategories-container" class="col-12 d-none">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <button id="back-to-main" class="btn btn-outline-light btn-sm">
                        <i data-feather="arrow-left"></i> Back
                    </button>
                    <h5 id="subcategory-title" class="mb-0"></h5>
                </div>
                <div class="row g-3" id="subcards-grid">
                    <!-- Subcards will be injected here via JS -->
                </div>
            </div>
        </div>
    </div>
</div>

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

/* элементы уровня второго и третьего */
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

/* состояние коллапса */
.collapse:not(.show) {
    display: none;
}

/* плавное переход при открытии/закрытии */
.collapsing {
    transition: height 0.2s ease;
}

/* кастомный ползунок прокрутки */
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

/* предотвращение прокрутки фона при открытом модальном окне */
body.modal-open {
    overflow: hidden;
}

/* Убедиться, что вид карточек определений находится поверх всего */
#definitions-card-view {
    z-index: 9999 !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Feather icons replacement
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // Load definitions data from hidden div
    const dataEl = document.getElementById('definitions-data');
    window.subcategoryData = dataEl ? JSON.parse(dataEl.dataset.json) : {};

    const definitionsView = document.getElementById('definitions-card-view');
    const mainContainer   = document.getElementById('main-categories-container');
    const subContainer    = document.getElementById('subcategories-container');
    const backBtn         = document.getElementById('back-to-main');
    const subTitle        = document.getElementById('subcategory-title');
    const subcardsGrid    = document.getElementById('subcards-grid');

    // Show definitions view when clicking the header
    document.getElementById('data-definitions-header').addEventListener('click', function() {
        definitionsView.classList.remove('d-none');
        document.body.classList.add('modal-open'); // prevent background scroll
        // Reset to main view
        mainContainer.classList.remove('d-none');
        subContainer.classList.add('d-none');
        subcardsGrid.innerHTML = '';
        subTitle.textContent = '';
    });

    // Hide definitions view when clicking outside the content (optional)
    definitionsView.addEventListener('click', function(e) {
        if (e.target === definitionsView) {
            definitionsView.classList.add('d-none');
            document.body.classList.remove('modal-open');
        }
    });

    // Handle main category card clicks
    document.querySelectorAll('.main-category-card').forEach(card => {
        card.addEventListener('click', function() {
            console.log('Main category card clicked');
            const target = this.getAttribute('data-target');
            if (!target) return;

            const subcategoryData = window.subcategoryData || {};
            const categoryData = subcategoryData[target] || {};
            const data = categoryData.items || [];
            console.log('Target:', target, 'Data length:', data.length);

            subTitle.textContent = this.querySelector('.card-title').textContent;
            // Build sub‑cards
            subcardsGrid.innerHTML = ''; // clear previous
            data.forEach(item => {
                const col = document.createElement('div');
                col.className = 'col-md-2 col-sm-4';

                const link = document.createElement('a');

                // For enabled items with actual routes, use explicit navigation
                // For disabled items or items with '#' route, stay in place
                if (!item.disabled && item.route && item.route !== '#') {
                    link.href = 'javascript:void(0);';
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        console.log('Subcard link clicked, navigating to:', item.route);
                        window.location.href = item.route;
                    });
                } else {
                    link.href = item.disabled || !item.route ? '#' : item.route;
                }

                // Build class name without trailing spaces
                const baseClasses = 'card h-100 text-center subcard-link';
                link.className = item.disabled ? `${baseClasses} disabled` : baseClasses;

                link.innerHTML = `
                    <div class="card-body d-flex flex-column align-items-center py-3">
                        <i data-feather="${item.icon}" class="mb-2" style="font-size:1.75rem;"></i>
                        <small>${item.label}</small>
                    </div>
                `;
                // Disable interaction for disabled items
                if (item.disabled) {
                    link.style.cursor = 'not-allowed';
                    link.title = 'هذا العنصر غير مفعل حاليًا';
                }

                col.appendChild(link);
                subcardsGrid.appendChild(col);
            });

            // Re‑render Feather icons for newly added icons
            if (typeof feather !== 'undefined') {
                feather.replace();
            }

            // Switch view: hide main categories, show sub‑categories
            mainContainer.classList.add('d-none');
            subContainer.classList.remove('d-none');
        });
    });

    // Back button
    backBtn.addEventListener('click', function() {
        mainContainer.classList.remove('d-none');
        subContainer.classList.add('d-none');
        subcardsGrid.innerHTML = '';
        subTitle.textContent = '';
        // Keep modal-open class; only removed when backdrop clicked
    });
});
</script>