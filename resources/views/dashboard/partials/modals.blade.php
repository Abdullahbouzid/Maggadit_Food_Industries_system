<!-- Data Definitions Modal -->
<div class="nova-modal" id="data-definitions-modal" aria-hidden="true" role="dialog" aria-labelledby="data-definitions-title">
    <div class="nova-modal-backdrop" data-close-modal></div>
    <div class="nova-modal-dialog" role="document">
        <div class="nova-modal-header">
            <h2 class="nova-modal-title" id="data-definitions-title">
                <i class="fas fa-database"></i> Data Definitions
            </h2>
            <button type="button" class="nova-modal-close" data-close-modal aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="nova-modal-body">
            <!-- Hidden JSON data for subcategory routes -->
            <div id="definitions-data" data-json='{
  "master": {
    "items": [
      {"label": "Organizations", "icon": "fas fa-building", "route": "{{ route('organizations.index') }}"},
      {"label": "Vendors", "icon": "fas fa-handshake", "route": "{{ route('vendors.index') }}"},
      {"label": "Pack Statuses", "icon": "fas fa-box", "route": "{{ route('package-statuses.index') }}"},
      {"label": "Visit Types", "icon": "fas fa-tag", "route": "javascript:void(0);", "disabled": true},
      {"label": "Fields Group", "icon": "fas fa-layer-group", "route": "javascript:void(0);", "disabled": true},
      {"label": "Geographical Location Hierarchy", "icon": "fas fa-globe", "route": "{{ route('geographical.index') }}"},
      {"label": "Sales Order Type", "icon": "fas fa-file-alt", "route": "{{ route('sales-order-types.index') }}"},
      {"label": "Stock Statuses", "icon": "fas fa-boxes", "route": "javascript:void(0);", "disabled": true},
      {"label": "Item Model", "icon": "fas fa-cube", "route": "{{ route('item-models.index') }}"},
      {"label": "Currencies", "icon": "fas fa-money-bill-wave", "route": "{{ route('currencies.index') }}"},
      {"label": "Currency Rates", "icon": "fas fa-exchange-alt", "route": "javascript:void(0);", "disabled": true},
      {"label": "Banks", "icon": "fas fa-university", "route": "javascript:void(0);", "disabled": true},
      {"label": "Branches", "icon": "fas fa-building", "route": "javascript:void(0);", "disabled": true},
      {"label": "Tax Type", "icon": "fas fa-receipt", "route": "javascript:void(0);", "disabled": true},
      {"label": "Reasons", "icon": "fas fa-comment-alt", "route": "javascript:void(0);", "disabled": true},
      {"label": "Reason Groups", "icon": "fas fa-comments", "route": "javascript:void(0);", "disabled": true}
    ]
  },
  "staff": {
    "items": [
      {"label": "Devices", "icon": "fas fa-mobile-alt", "route": "{{ route('devices.index') }}"},
      {"label": "Personal Information", "icon": "fas fa-user", "route": "javascript:void(0);", "disabled": true},
      {"label": "Employment Details", "icon": "fas fa-briefcase", "route": "javascript:void(0);", "disabled": true},
      {"label": "Attendance Schema", "icon": "fas fa-calendar-check", "route": "javascript:void(0);", "disabled": true}
    ]
  },
  "customer": {
    "items": [
      {"label": "Customer Demographics", "icon": "fas fa-users", "route": "javascript:void(0);", "disabled": true},
      {"label": "Contact Information", "icon": "fas fa-address-book", "route": "{{ route('contact.info') }}"},
      {"label": "Loyalty & Segments", "icon": "fas fa-gem", "route": "javascript:void(0);", "disabled": true},
      {"label": "Organization About", "icon": "fas fa-info-circle", "route": "{{ route('organization-about.index') }}"}
    ]
  },
  "products": {
    "items": [
      {"label": "Product Attributes", "icon": "fas fa-cog", "route": "javascript:void(0);", "disabled": true},
      {"label": "Pricing & Tax Rules", "icon": "fas fa-dollar-sign", "route": "javascript:void(0);", "disabled": true},
      {"label": "Brands", "icon": "fas fa-trademark", "route": "{{ route('brands.index') }}"},
      {"label": "Categories", "icon": "fas fa-layer-group", "route": "{{ route('categories.index') }}"},
      {"label": "Item Types", "icon": "fas fa-tags", "route": "{{ route('item-types.index') }}"},
      {"label": "Units of Measure (UOMs)", "icon": "fas fa-balance-scale", "route": "{{ route('uoms.index') }}"},
      {"label": "Pack Operations", "icon": "fas fa-cogs", "route": "{{ route('pack-operations.index') }}"},
      {"label": "Pack Groups", "icon": "fas fa-layer-group", "route": "{{ route('pack-groups.index') }}"},
      {"label": "Pack Types", "icon": "fas fa-layer-group", "route": "{{ route('pack-types.index') }}"},
      {"label": "Pack Capacities", "icon": "fas fa-cube", "route": "{{ route('pack-capacities.index') }}"},
      {"label": "Pack Definitions", "icon": "fas fa-box-open", "route": "{{ route('pack-definitions.index') }}"},
      {"label": "Items", "icon": "fas fa-box", "route": "{{ route('items.index') }}"},
      {"label": "Item Packs", "icon": "fas fa-boxes", "route": "{{ route('item-packs.index') }}"},
      {"label": "Item Images", "icon": "fas fa-image", "route": "{{ route('item-images.index') }}"},
      {"label": "Item Models", "icon": "fas fa-cube", "route": "{{ route('item-models.index') }}"}
    ]
  },
  "warehouse": {
    "items": [
      {"label": "Warehouses", "icon": "fas fa-warehouse", "route": "{{ route('warehouses.index') }}"},
      {"label": "Warehouse Types", "icon": "fas fa-warehouse", "route": "{{ route('warehouse-types.index') }}"},
      {"label": "Areas", "icon": "fas fa-map", "route": "{{ route('areas.index') }}"},
      {"label": "Batches", "icon": "fas fa-box", "route": "{{ route('production-batches.index') }}"},
      {"label": "Warehouse Movements", "icon": "fas fa-exchange-alt", "route": "{{ route('warehouse-movements.index') }}"},
      {"label": "Vehicles", "icon": "fas fa-truck", "route": "{{ route('vehicles.index') }}"},
      {"label": "Vehicle Types", "icon": "fas fa-car", "route": "{{ route('vehicle-types.index') }}"},
      {"label": "States", "icon": "fas fa-map-pin", "route": "{{ route('states.index') }}"}
    ]
  },
  "mobile": {
    "items": [
      {"label": "Mobile App", "icon": "fas fa-mobile-alt", "route": "javascript:void(0);", "disabled": true},
      {"label": "Mobile Customization", "icon": "fas fa-palette", "route": "javascript:void(0);", "disabled": true}
    ]
  },
  "display": {
    "items": [
      {"label": "Display Units", "icon": "fas fa-monitor", "route": "javascript:void(0);", "disabled": true},
      {"label": "Dashboard Widgets", "icon": "fas fa-th-large", "route": "javascript:void(0);", "disabled": true}
    ]
  }
}' style="display: none;"></div>
            <div class="nova-modal-grid">
                <!-- Master Data Definitions -->
                <div class="nova-modal-card" data-target="master">
                    <div class="nova-modal-card-icon"><i class="fas fa-database"></i></div>
                    <div class="nova-modal-card-title">Master Data Definitions</div>
                    <div class="nova-modal-card-arrow"><i class="fas fa-chevron-right"></i></div>
                </div>
                <!-- Staff Data Definitions -->
                <div class="nova-modal-card" data-target="staff">
                    <div class="nova-modal-card-icon"><i class="fas fa-users"></i></div>
                    <div class="nova-modal-card-title">Staff Data Definitions</div>
                    <div class="nova-modal-card-arrow"><i class="fas fa-chevron-right"></i></div>
                </div>
                <!-- Customer Data Definitions -->
                <div class="nova-modal-card" data-target="customer">
                    <div class="nova-modal-card-icon"><i class="fas fa-user"></i></div>
                    <div class="nova-modal-card-title">Customer Data Definitions</div>
                    <div class="nova-modal-card-arrow"><i class="fas fa-chevron-right"></i></div>
                </div>
                <!-- Products Data Definitions -->
                <div class="nova-modal-card" data-target="products">
                    <div class="nova-modal-card-icon"><i class="fas fa-box"></i></div>
                    <div class="nova-modal-card-title">Products Data Definitions</div>
                    <div class="nova-modal-card-arrow"><i class="fas fa-chevron-right"></i></div>
                </div>
                <!-- Warehouse & Vehicles -->
                <div class="nova-modal-card" data-target="warehouse">
                    <div class="nova-modal-card-icon"><i class="fas fa-truck"></i></div>
                    <div class="nova-modal-card-title">Warehouse & Vehicles</div>
                    <div class="nova-modal-card-arrow"><i class="fas fa-chevron-right"></i></div>
                </div>
                <!-- Mobile Customization -->
                <div class="nova-modal-card" data-target="mobile">
                    <div class="nova-modal-card-icon"><i class="fas fa-mobile-alt"></i></div>
                    <div class="nova-modal-card-title">Mobile Customization</div>
                    <div class="nova-modal-card-arrow"><i class="fas fa-chevron-right"></i></div>
                </div>
                <!-- Display Units -->
                <div class="nova-modal-card" data-target="display">
                    <div class="nova-modal-card-icon"><i class="fas fa-monitor"></i></div>
                    <div class="nova-modal-card-title">Display Units</div>
                    <div class="nova-modal-card-arrow"><i class="fas fa-chevron-right"></i></div>
                </div>
            </div>
            <div id="subcategories-container" class="nova-subcategories" style="display: none;">
                <div class="nova-subcategories-header">
                    <button class="nova-subcategories-back" id="back-to-main">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
                    <h4 class="nova-subcategories-title" id="subcategory-title"></h4>
                </div>
                <div id="subcards-grid" class="nova-modal-grid"></div>
            </div>
        </div>
    </div>
</div>

<!-- Customer App Properties Modal -->
<div class="nova-modal" id="customer-app-modal" aria-hidden="true" role="dialog" aria-labelledby="customer-app-title">
    <div class="nova-modal-backdrop" data-close-modal></div>
    <div class="nova-modal-dialog" role="document">
        <div class="nova-modal-header">
            <h2 class="nova-modal-title" id="customer-app-title">
                <i class="fas fa-mobile-alt"></i> Customer App Properties
            </h2>
            <button type="button" class="nova-modal-close" data-close-modal aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="nova-modal-body">
            <div class="nova-modal-grid">
                <a href="{{ route('faqs.index') }}" class="nova-modal-card">
                    <div class="nova-modal-card-icon"><i class="fas fa-question-circle"></i></div>
                    <div class="nova-modal-card-title">الأسئلة الشائعة</div>
                </a>
                <a href="{{ route('surveys.index') }}" class="nova-modal-card">
                    <div class="nova-modal-card-icon"><i class="fas fa-clipboard-list"></i></div>
                    <div class="nova-modal-card-title">الاستبيانات</div>
                </a>
                <a href="{{ route('banners.index') }}" class="nova-modal-card">
                    <div class="nova-modal-card-icon"><i class="fas fa-image"></i></div>
                    <div class="nova-modal-card-title">Banners</div>
                </a>
                <a href="{{ route('notifications.index') }}" class="nova-modal-card">
                    <div class="nova-modal-card-icon"><i class="fas fa-bell"></i></div>
                    <div class="nova-modal-card-title">رسل إشعارات</div>
                </a>
                <a href="{{ route('offers.index') }}" class="nova-modal-card">
                    <div class="nova-modal-card-icon"><i class="fas fa-tag"></i></div>
                    <div class="nova-modal-card-title">الOffers</div>
                </a>
                <a href="{{ route('contact-messages.index') }}" class="nova-modal-card">
                    <div class="nova-modal-card-icon"><i class="fas fa-headset"></i></div>
                    <div class="nova-modal-card-title">خدمة العملاء</div>
                </a>
                <a href="{{ route('organization-about.index') }}" class="nova-modal-card">
                    <div class="nova-modal-card-icon"><i class="fas fa-info-circle"></i></div>
                    <div class="nova-modal-card-title">نبذة المنظمات</div>
                </a>
                <a href="{{ route('price-lists.index') }}" class="nova-modal-card">
                    <div class="nova-modal-card-icon"><i class="fas fa-dollar-sign"></i></div>
                    <div class="nova-modal-card-title">قوائم الأسعار</div>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Logout Form -->
<form id="nova-logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>