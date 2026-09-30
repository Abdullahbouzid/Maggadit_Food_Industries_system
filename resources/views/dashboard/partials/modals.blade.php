<!-- Data Definitions Modal -->
<div class="nova-modal" id="data-definitions-modal" aria-hidden="true" role="dialog" aria-labelledby="data-definitions-title">
    <div class="nova-modal-backdrop" data-close-modal></div>
    <div class="nova-modal-dialog" role="document">
        <div class="nova-modal-header">
            <h2 class="nova-modal-title" id="data-definitions-title">
                <i class="fas fa-database"></i> التعريفات
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
      {"label": "{{ __('messages.modals.organizations') }}", "icon": "fas fa-building", "route": "{{ route('organizations.index') }}"},
      {"label": "{{ __('messages.modals.vendors') }}", "icon": "fas fa-handshake", "route": "{{ route('vendors.index') }}"},
      {"label": "{{ __('messages.modals.pack_statuses') }}", "icon": "fas fa-box", "route": "{{ route('package-statuses.index') }}"},
      {"label": "{{ __('messages.modals.visit_types') }}", "icon": "fas fa-tag", "route": "javascript:void(0);", "disabled": true},
      {"label": "{{ __('messages.modals.fields_group') }}", "icon": "fas fa-layer-group", "route": "javascript:void(0);", "disabled": true},
      {"label": "{{ __('messages.modals.geographical_hierarchy') }}", "icon": "fas fa-globe", "route": "{{ route('geographical.index') }}"},
      {"label": "{{ __('messages.modals.sales_order_type') }}", "icon": "fas fa-file-alt", "route": "{{ route('sales-order-types.index') }}"},
      {"label": "{{ __('messages.modals.stock_statuses') }}", "icon": "fas fa-boxes", "route": "javascript:void(0);", "disabled": true},
      {"label": "{{ __('messages.modals.item_model') }}", "icon": "fas fa-cube", "route": "{{ route('item-models.index') }}"},
      {"label": "{{ __('messages.modals.currencies') }}", "icon": "fas fa-money-bill-wave", "route": "{{ route('currencies.index') }}"},
      {"label": "{{ __('messages.modals.currency_rates') }}", "icon": "fas fa-exchange-alt", "route": "javascript:void(0);", "disabled": true},
      {"label": "{{ __('messages.modals.banks') }}", "icon": "fas fa-university", "route": "javascript:void(0);", "disabled": true},
      {"label": "{{ __('messages.modals.branches') }}", "icon": "fas fa-building", "route": "javascript:void(0);", "disabled": true},
      {"label": "{{ __('messages.modals.tax_type') }}", "icon": "fas fa-receipt", "route": "javascript:void(0);", "disabled": true},
      {"label": "{{ __('messages.modals.reasons') }}", "icon": "fas fa-comment-alt", "route": "javascript:void(0);", "disabled": true},
      {"label": "{{ __('messages.modals.reason_groups') }}", "icon": "fas fa-comments", "route": "javascript:void(0);", "disabled": true}
    ]
  },
  "staff": {
    "items": [
      {"label": "{{ __('messages.modals.devices') }}", "icon": "fas fa-mobile-alt", "route": "{{ route('devices.index') }}"},
      {"label": "{{ __('messages.modals.personal_info') }}", "icon": "fas fa-user", "route": "javascript:void(0);", "disabled": true},
      {"label": "{{ __('messages.modals.employment_details') }}", "icon": "fas fa-briefcase", "route": "javascript:void(0);", "disabled": true},
      {"label": "{{ __('messages.modals.attendance_schema') }}", "icon": "fas fa-calendar-check", "route": "javascript:void(0);", "disabled": true}
    ]
  },
  "customer": {
    "items": [
      {"label": "{{ __('messages.modals.customer_demographics') }}", "icon": "fas fa-users", "route": "javascript:void(0);", "disabled": true},
      {"label": "{{ __('messages.modals.contact_information') }}", "icon": "fas fa-address-book", "route": "{{ route('contact.info') }}"},
      {"label": "{{ __('messages.modals.loyalty_segments') }}", "icon": "fas fa-gem", "route": "javascript:void(0);", "disabled": true},
      {"label": "{{ __('messages.modals.organization_about') }}", "icon": "fas fa-info-circle", "route": "{{ route('organization-about.index') }}"}
    ]
  },
  "products": {
    "items": [
      {"label": "{{ __('messages.modals.product_attributes') }}", "icon": "fas fa-cog", "route": "javascript:void(0);", "disabled": true},
      {"label": "{{ __('messages.modals.pricing_tax_rules') }}", "icon": "fas fa-dollar-sign", "route": "javascript:void(0);", "disabled": true},
      {"label": "{{ __('messages.navbar.brands') }}", "icon": "fas fa-trademark", "route": "{{ route('brands.index') }}"},
      {"label": "{{ __('messages.navbar.categories') }}", "icon": "fas fa-layer-group", "route": "{{ route('categories.index') }}"},
      {"label": "{{ __('messages.modals.item_types') }}", "icon": "fas fa-tags", "route": "{{ route('item-types.index') }}"},
      {"label": "{{ __('messages.modals.uoms') }}", "icon": "fas fa-balance-scale", "route": "{{ route('uoms.index') }}"},
      {"label": "{{ __('messages.modals.pack_operations') }}", "icon": "fas fa-cogs", "route": "{{ route('pack-operations.index') }}"},
      {"label": "{{ __('messages.modals.pack_groups') }}", "icon": "fas fa-layer-group", "route": "{{ route('pack-groups.index') }}"},
      {"label": "{{ __('messages.modals.pack_types') }}", "icon": "fas fa-layer-group", "route": "{{ route('pack-types.index') }}"},
      {"label": "{{ __('messages.modals.pack_capacities') }}", "icon": "fas fa-cube", "route": "{{ route('pack-capacities.index') }}"},
      {"label": "{{ __('messages.dashboard.items') }}", "icon": "fas fa-box", "route": "{{ route('items.index') }}"},
      {"label": "{{ __('messages.dashboard.item_packs') }}", "icon": "fas fa-boxes", "route": "{{ route('item-packs.index') }}"},
      {"label": "{{ __('messages.modals.item_images') }}", "icon": "fas fa-image", "route": "{{ route('item-images.index') }}"},
      {"label": "{{ __('messages.modals.item_models') }}", "icon": "fas fa-cube", "route": "{{ route('item-models.index') }}"}
    ]
  },
  "warehouse": {
    "items": [
      {"label": "{{ __('messages.dashboard.warehouses') }}", "icon": "fas fa-warehouse", "route": "{{ route('warehouses.index') }}"},
      {"label": "{{ __('messages.modals.warehouse_types') }}", "icon": "fas fa-warehouse", "route": "{{ route('warehouse-types.index') }}"},
      {"label": "{{ __('messages.modals.areas') }}", "icon": "fas fa-map", "route": "{{ route('areas.index') }}"},
      {"label": "{{ __('messages.modals.batches') }}", "icon": "fas fa-box", "route": "{{ route('production-batches.index') }}"},
      {"label": "{{ __('messages.modals.warehouse_movements') }}", "icon": "fas fa-exchange-alt", "route": "{{ route('warehouse-movements.index') }}"},
      {"label": "{{ __('messages.modals.vehicles') }}", "icon": "fas fa-truck", "route": "{{ route('vehicles.index') }}"},
      {"label": "{{ __('messages.modals.vehicle_types') }}", "icon": "fas fa-car", "route": "{{ route('vehicle-types.index') }}"},
      {"label": "{{ __('messages.dashboard.states') }}", "icon": "fas fa-map-pin", "route": "{{ route('states.index') }}"}
    ]
  },
  "mobile": {
    "items": [
      {"label": "{{ __('messages.modals.mobile_app') }}", "icon": "fas fa-mobile-alt", "route": "javascript:void(0);", "disabled": true},
      {"label": "{{ __('messages.modals.mobile_customization') }}", "icon": "fas fa-palette", "route": "javascript:void(0);", "disabled": true}
    ]
  },
  "display": {
    "items": [
      {"label": "{{ __('messages.modals.display_units') }}", "icon": "fas fa-monitor", "route": "javascript:void(0);", "disabled": true},
      {"label": "{{ __('messages.modals.dashboard_widgets') }}", "icon": "fas fa-th-large", "route": "javascript:void(0);", "disabled": true}
    ]
  }
}' style="display: none;"></div>
            <div class="nova-modal-grid">
                <!-- Master Data -->
                <div class="nova-modal-card" data-target="master">
                    <div class="nova-modal-card-icon"><i class="fas fa-database"></i></div>
                    <div class="nova-modal-card-title">بيانات أساسية</div>
                    <div class="nova-modal-card-arrow"><i class="fas fa-chevron-right"></i></div>
                </div>
                <!-- Staff -->
                <div class="nova-modal-card" data-target="staff">
                    <div class="nova-modal-card-icon"><i class="fas fa-users"></i></div>
                    <div class="nova-modal-card-title">الموظفون</div>
                    <div class="nova-modal-card-arrow"><i class="fas fa-chevron-right"></i></div>
                </div>
                <!-- Customer -->
                <div class="nova-modal-card" data-target="customer">
                    <div class="nova-modal-card-icon"><i class="fas fa-user"></i></div>
                    <div class="nova-modal-card-title">العملاء</div>
                    <div class="nova-modal-card-arrow"><i class="fas fa-chevron-right"></i></div>
                </div>
                <!-- Products -->
                <div class="nova-modal-card" data-target="products">
                    <div class="nova-modal-card-icon"><i class="fas fa-box"></i></div>
                    <div class="nova-modal-card-title">المنتجات</div>
                    <div class="nova-modal-card-arrow"><i class="fas fa-chevron-right"></i></div>
                </div>
                <!-- Warehouse & Vehicles -->
                <div class="nova-modal-card" data-target="warehouse">
                    <div class="nova-modal-card-icon"><i class="fas fa-truck"></i></div>
                    <div class="nova-modal-card-title">{{ __('messages.modals.warehouse_vehicles') }}</div>
                    <div class="nova-modal-card-arrow"><i class="fas fa-chevron-right"></i></div>
                </div>
                <!-- Mobile Customization -->
                <div class="nova-modal-card" data-target="mobile">
                    <div class="nova-modal-card-icon"><i class="fas fa-mobile-alt"></i></div>
                    <div class="nova-modal-card-title">{{ __('messages.modals.mobile_customization') }}</div>
                    <div class="nova-modal-card-arrow"><i class="fas fa-chevron-right"></i></div>
                </div>
                <!-- Display Units -->
                <div class="nova-modal-card" data-target="display">
                    <div class="nova-modal-card-icon"><i class="fas fa-monitor"></i></div>
                    <div class="nova-modal-card-title">{{ __('messages.modals.display_units') }}</div>
                    <div class="nova-modal-card-arrow"><i class="fas fa-chevron-right"></i></div>
                </div>
            </div>
            <div id="subcategories-container" class="nova-subcategories" style="display: none;">
                <div class="nova-subcategories-header">
                    <button class="nova-subcategories-back" id="back-to-main">
                        <i class="fas fa-arrow-left"></i> رجوع
                    </button>
                    <h4 class="nova-subcategories-title" id="subcategory-title"></h4>
                </div>
                <div id="subcards-grid" class="nova-modal-grid"></div>
            </div>
        </div>
    </div>
</div>

<!-- Customer App Modal -->
<div class="nova-modal" id="customer-app-modal" aria-hidden="true" role="dialog" aria-labelledby="customer-app-title">
    <div class="nova-modal-backdrop" data-close-modal></div>
    <div class="nova-modal-dialog" role="document">
        <div class="nova-modal-header">
            <h2 class="nova-modal-title" id="customer-app-title">
                <i class="fas fa-mobile-alt"></i> تطبيق العميل
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
                    <div class="nova-modal-card-title">البنرات</div>
                </a>
                <a href="{{ route('notifications.index') }}" class="nova-modal-card">
                    <div class="nova-modal-card-icon"><i class="fas fa-bell"></i></div>
                    <div class="nova-modal-card-title">رسل إشعارات</div>
                </a>
                <a href="{{ route('offers.index') }}" class="nova-modal-card">
                    <div class="nova-modal-card-icon"><i class="fas fa-tag"></i></div>
                    <div class="nova-modal-card-title">العروض</div>
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