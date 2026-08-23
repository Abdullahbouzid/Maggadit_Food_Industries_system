# التقرير الشامل لواجهات نظام ماغاديت للصناعات الغذائية
## التاريخ: الاثنين 15 أغسطس 2026
## الإصدار: فترة تطوير كاملة — منشأط من الصفر حتى النظام الكامل

---

## 1. ملخص تنفيذي

### لمحة عامة عن المشروع
نظام إدارة شامل لشركة ماغاديت للصناعات الغذائية (Maggadit Food Industries)، مبني على **Laravel 13 / PHP 8.3** مع قاعدة بيانات MySQL. يدعم النظام ثلاثة طبقات رئيسية:

| الطبقة | الوصف |
|--------|-------|
| **لوحة تحكم الإدارة (Admin Dashboard)** | واجهة Laravel + Blade لإدارة كل شيء |
| **تطبيق العملاء على الويب (Web Customer App)** | نسخة مبنية على Blade تعمل عبر المتصفح |
| **تطبيق العملاء الهاتفي (Mobile APK / Pure HTML JS PWA)** | تطبيق NativePHP + ملفات HTML/JS ثابتة في `public/customer-app` |

### بنية المشروع
```
Maggadit_Food_Industries_system/
├── app/
│   ├── Http/
│   │   ├── Controllers/          (44 controller — 52 ملف)
│   │   │   ├── Admin/            (RolePermissionController)
│   │   │   ├── Api/              (CustomerApiController, CustomerAuthController, ProfileController)
│   │   │   ├── Auth/             (LoginController, RegisteredUserController, ...)
│   │   │   └── Customer/         (CustomerAppController — 899 سطر، نقله للـ Laravel 13)
│   │   └── Middleware/           (CheckCustomerSession, Localization)
│   ├── Models/                   (45 نموذج — 48 ملف)
│   ├── Exports/                  (24 Export — 1458 سطر إجمالاً)
│   └── Imports/                  (للـ Excel)
├── database/
│   └── migrations/               (50+ migration)
├── resources/views/
│   ├── app_android/              (32 view — تطبيق العملاء الهواتف)
│   │   ├── more/                 (10 صفحات: about_us, contact_us, faq, notifications, ...)
│   │   ├── partials/mobile/      (app_mobile, bottom_nav, header, scripts)
│   │   └── products/, categories/, brands/ (index + show)
│   ├── dashboard/                (لوحة التحكم الإدارية)
│   │   ├── data_definition/      (تعريفات البيانات — 7 فئات رئيسية)
│   │   │   ├── customer_app/     (banner, notification)
│   │   │   ├── data_product/     (brand, category, items, item_packs, ...)
│   │   │   ├── master/           (organizations, vendors, currencies, countries, cities)
│   │   │   ├── staff/            (device)
│   │   │   ├── w&v/              (warehouses, warehouse_types, batches, warehouse_movements, vehicles)
│   │   ├── organization_about/
│   │   ├── customers/
│   │   └── partials/             (sidebar, navbar, header, footer, script, layout)
│   ├── auth/                     (login, register, password reset)
│   ├── components/               (12 مكوّن UI)
│   ├── layouts/                  (app, guest, navigation)
│   ├── profile/
│   └── welcome.blade.php
├── routes/
│   ├── web.php                   (532 سطر — 60+ route)
│   └── api.php                   (73 سطر — Customer API routes)
├── public/
│   ├── customer-app/             (تطبيق PWA/HTML الثابت)
│   │   ├── index.html            (صفحة تسجيل الدخول)
│   │   ├── dashboard.html        (الصفحة الرئيسية للـ PWA)
│   │   ├── js/api.js             (عميل API Pure JavaScript)
│   │   ├── js/app.js             (منطق التطبيق العام)
│   │   └── css/app.css           (أنماط موحدة)
│   └── img/                      (شعارات، أفاتارات، إلخ)
├── config/
├── bootstrap/
├── tests/
│   └── Feature/CustomerAboutTest.php (اختبار Feature لصفحة "نبذة عنا")
└── composer.json / package.json
```

---

## 2. قاعدة البيانات الكاملة

### الجداول الأساسية (Core Tables — migrations الأصلية):

| الجدول | الهدف |
|--------|-------|
| `users` | مستخدمو لوحة الإدارة (مع Spatie Permission) |
| `organizations` | المنظمات (أب / بنك فروع) — دعم التسلسل الهرمي |
| `customers` | العملاء (مع Sanctum Token، active_organization_id) |
| `customer_organization` | جدول ربط many-to-many العملاء والمنظمات |
| `customer_favorites` | جدول ربط المفضلة للعملاء (مع item_id غير مرتبط FK) |
| `organizations_about` | نبذة المنظمة (title, description, vision, mission, values, statistics, صور) |
| `brands` | العلامات التجارية (name, logo, show_in_customer_app) |
| `categories` | الفئات (name, image, parent_id, show_in_customer_app) |
| `items` | المنتجات (43 حقلًا — من الكمية إلى is_power_sku، has_serial_number، ...) |
| `item_packs` | العبوات (uom, quantity, barcode, packing specs) |
| `item_images` | صور إضافية للمنتجات (sort_order) |
| `item_types` | أنواع المنتجات (مثلاً: منتجات، مواد خام) |
| `item_models` | موديلات المنتجات |
| `item_pack_pack_operation` | ربط many-to-many pack ↔ operation |
| `item_pack_pack_group` | ربط many-to-many pack ↔ group |
| `uoms` | وحدات القياس |
| `pack_operations` | عمليات التعبئة |
| `pack_groups` | مجموعات التعبئة |
| `warehouses` | المخازن (SoftDeletes، 27 حقلًا — من GPS إلى destinations/sources/employees كـ JSON) |
| `warehouse_types` | أنواع المخازن |
| `vehicles` / `vehicle_types` | المركبات والمصفوفات |
| `states` / `cities` / `areas` / `countries` | التسلسل الجغرافي (4 مستويات) |
| `banners` | بنرات الإعلانية (show_in_customer_app, order) |
| `notifications` | الإشعارات (channel, icon_type, customers as JSON, is_sent, scheduled_at) |
| `notification_reads` | جدول ربط: notifications ↔ customers (is_starred, is_archived, read_at) |
| `faqs` | الأسئلة الشائعة (category, question, answer) |
| `surveys` → `survey_questions` → `survey_responses` → `survey_answers` | نظام الاستبيانات الكامل |
| `vendors` | الموردين |
| `competitors` | المنافسون |
| `currencies` | العملات |
| `sales_order_types` | أنواع أوامر المبيعات |
| `package_statuses` | حالات التعبئة |
| `devices` | الأجهزة |
| `personal_access_tokens` | Sanctum |

### الجداول الجديدة (Batch/Warehouse Management):

| الجدول | الهدف | الحقول الرئيسية |
|--------|-------|-----------------|
| `production_batches` | الدفيئات الإنتاجية | `batch_number` (unique)، `production_date`، `expiry_date`، `status` (enum: active/expired/consumed)، SoftDeletes |
| `batch_items` | عناصر الدفيئة | `batch_id`↔production_batches، `item_id`↔items، `item_pack_id`↔item_packs، `quantity`، `dispatched_quantity`، `transferred_quantity`، `remaining_quantity` |
| `batch_item_stock` | أرصدة المنتجات بالمخازن | `batch_item_id`↔batch_items، `warehouse_id`↔warehouses، `quantity`، `reserved_quantity` (unique على batch_item+warehouse) |
| `batch_movements` | سجل الحركة | `batch_item_id`↔batch_items، `from_warehouse_id`↔warehouses (nullable)، `to_warehouse_id`↔warehouses، `movement_type` (string — in/transfer/expiry_return)، `quantity` (decimal 15,3)، `movement_date`، `notes` |

### نماذج البيانات الرئيسية (Eloquent Models):

**الخاصة بالمخازن والدفيئات:**
- `Warehouse` (59 سطر) — 27 fillable، casts لـ destinations/sources/employees كـ JSON array
- `WarehouseType` — نوع المخزن
- `ProductionBatch` (31 سطر) — SoftDeletes، hasMany batchItems
- `BatchItem` (71 سطر) — accessor `getRemainingQuantityAttribute()` (quantity - dispatched_quantity)
- `BatchItemStock` (30 سطر) — belongsTo batchItem + warehouse
- `BatchMovement` (42 سطر) — belongsTo batchItem + fromWarehouse + toWarehouse

**الخاصة بتطبيق العملاء:**
- `Customer` (102 سطر) — Authenticatable، SanctumHasApiTokens، hasMany favorites، belongsToMany organizations
- `CustomerFavorite` (15 سطر) — بسيط: customer_id + item_id
- `CustomerOrganization` — pivot للعملاء والمنظمات
- `Organization` (73 سطر) — HasRoles (Spatie)، hasOne about، hasMany warehouses/vehicles/customers
- `OrganizationAbout` (68 سطر) — يدعم values/statistics كـ JSON array مع accessors للصور
- `Banner`, `Notification`, `Faq`, `Category`, `Brand`, `Item`, `ItemPack`, `ItemImage`, `Survey` + أقرانها

**الخاصة بالمنتجات:**
- `Item` (115 سطر) — 43 fillable field، accessor للـ price/old_price/rate/main_image_url
- `ItemPack` (46 سطر) — belongsTo item+uom، manyToMany operations+groups
- `ItemType`, `ItemModel`, `Uom`, `PackOperation`, `PackGroup`

**الباقي:** `Country`، `State`، `City`، `Area`، `Vendor`، `Competitor`، `Currency`، `SalesOrderType`، `PackageStatus`، `Device`، `NotificationRead`

---

## 3. واجهات المسارات (Routes) — web.php (532 سطر)

### 3.1 مسارات تطبيق العملاء على الجوال (Mobile Customer App — /customer + /mobile)

**المسارات العامة (بدون مصادقة):**
- `GET /mobile/login` → `app_android.login`
- `GET /mobile/register` → `app_android.register`
- `POST /mobile/register` → إرسال للـ Hostinger API + إنشاء محلي + تسجيل الجلسة
- `POST /mobile/login` → إرسال للـ Hostinger API + updateOrCreate محلي + تسجيل الجلسة + حفظ api_token
- `GET /customer/logout` → تسجيل الخروج
- `GET /` → كشف User-Agent: Android/Mobile → `/mobile/login`، غيرها → `/admin`

**مجموعة `/customer` (prefix + name('customer.')):**
- `/categories`، `/categories/{id}` — مع Fallback لقاعدة بيانات محلية
- `/brands`، `/brands/{id}`
- `/products`، `/product/{id}`
- `/showoffers`
- `/change-organization` (View)، `/surveys` (View)
- `/proxy/*` — مسارات Proxy للاستبيانات والمنظمات (تستخدم session api_token للاتصال بالـ Hostinger)
- `/notifications`
- `/privacy-policy`
- `/about-us` → `aboutUsWeb()`
- `/contact-us`
- `/faq`

**المسارات المحمية بـ `check.customer.session` Middleware:**
- `/dashboard` → `dashboard()` — يجلب بنرات، فئات، علامات، منتجات من API مع Fallback محلي
- `/more` → صفحة "المزيد"
- `/offers` → صفحة العروض
- `/cart` → سلة التسوق (View فقط — لا backend)
- `/favorite` → `favorites()` — يجلب المفضلة من DB المحلي + API (يحاول تجديد البيانات)
- `/favorite/toggle` → `toggleFavorite()` — إضافة/إزالة منتج من المفضلة
- `/order-history`، `/order/{id}`، `/financial-statement`
- `/my-info` (GET + POST) → `myInfo()` + `updateInfo()` — تحديث البيانات على الـ Hostinger + محلياً

**آلية المصادقة (Dual Auth):**
- `CheckCustomerSession` middleware — يتحقق من `Auth::guard('customer')` أو `session('api_token')`
- الجلسة PHP الرسيمية (cookies) للجلسات العادية
- API Token محفوظ في `session('api_token')` للـ APK WebView

### 3.2 مسارات API (`routes/api.php` — 73 سطر):
```
/api/testroot, /api/testxyz, /api/testzzz, /api/testapianother, /api/testinside
/api/customer/login                          — Sanctum Token
/api/customer/register                      — إنشاء حساب جديد
/api/customer/categories                    — GET (public)
/api/customer/categories/{id}               — GET (public)
/api/customer/brands                       — GET (public)
/api/customer/brands/{id}                  — GET (public)
/api/customer/products                     — GET (public)
/api/customer/products/{id}                — GET (public)
/api/customer/offers                       — GET (public)
/api/customer/faq                          — GET (public)
/api/customer/privacy-policy               — GET (public)
/api/customer/contact-us                   — GET (public)
[auth:api middleware group]
/api/customer/logout
/api/customer/profile (GET/PUT + POST update)
/api/customer/dashboard
/api/customer/financial-statement
/api/customer/surveys
/api/customer/surveys/{id}
/api/customer/surveys/{id}/respond
/api/customer/notifications
/api/customer/notifications/check-new
/api/customer/fcm-token
/api/customer/notifications/{id}/read
/api/customer/notifications/{id}/toggle-read
/api/customer/notifications/{id}/toggle-star
/api/customer/notifications/{id}/toggle-archive
/api/customer/about-us
/api/customer/organizations
/api/customer/change-organization
```

### 3.3 مسارات لوحة الإدارة (Admin Dashboard — /admin أو بدون prefix)

**مصادقة المشرفين:**
- `GET/POST /login` → `LoginController` (Spatie أو Breeze)
- `GET /admin` → إعادة توجيه إلى login
- `POST /logout`

**لوحة التحكم الرئيسية:**
- `GET /dashboard` → `DashboardController::index()` — صفحة تعريفات البيانات على شكل بطاقات تفاعلية

**Data Definitions (Resource Routes + Export/Import):**
- `organizations` + export/import/template
- `customers` + export/import/template
- `vendors` + export/import
- `competitors` + export/import/template
- `package-statuses` + export/import/template
- `countries` (مع Geographical index)
- `cities`, `states`, `areas`
- `sales-order-types` + export/import/template
- `currencies` + export/import/template
- `brands` + export/import
- `categories` + export/import/template
- `faqs`
- `surveys` + toggle
- `item-types` + export/import/template
- `uoms` + export/filtered
- `pack-operations` + export/filtered
- `pack-groups` + export/filtered
- `items` + export/import/template
- `item-packs`, `item-images`, `item-models`
- `item-pack-pack-groups` (store/destroy فقط)
- `item-pack-pack-operations` (store/destroy فقط)
- `warehouse-types` + export/import
- `warehouses` + export/import/template + AJAX states/cities/areas
- **`warehouse-movements` (BatchMovement)** — resource + export + export-detail
- **`production-batches` (ProductionBatch)** — resource +:
  - `get-packs/{itemId}` — AJAX
  - `{batch}/distribute` — توزيع على المخازن
  - `{batch}/warehouse-stock/{warehouse}` — أرصدة المخزن
  - `{batch}/transfer` — نقل الدفيئة بين المخازن (نمط خطوتين)
  - `expired` — صفحة الدفيئات منتهية الصلاحية
  - `return-expired` — إرجاع منتهية الصلاحية
- `vehicles` + export/import/template
- `vehicle-types` + export/import/template
- `devices` + export/import/template
- `banners` + export
- `notifications` + resend + export
- `organization-about` (Resource — CRUD كامل للنبذة)
- `roles` + `permissions` (Spatie)

**Warehouse Transfer Routes (مضافة حديثاً):**
```php
Route::resource('warehouses', WarehouseController::class);
Route::prefix('warehouses')->group(function () {
    Route::get('/{warehouse}/transfer/select',   'warehouses.transfer.select');
    Route::post('/{warehouse}/transfer/select',  'warehouses.transfer.store-selection');
    Route::get('/{warehouse}/transfer/confirm',  'warehouses.transfer.confirm');
    Route::post('/{warehouse}/transfer/execute', 'warehouses.transfer.execute');
    Route::post('/{warehouse}/transfer/store-selection-from-warehouse', 'warehouses.transfer.store-selection-from-warehouse');
    Route::get('/{warehouse}/transfer/confirm-from-warehouse',          'warehouses.transfer.confirm-from-warehouse');
});
```

---

## 4. لوحة تحكم الإدارة (Admin Dashboard)

### التصميم العام:
- **Sidebar** (`sidebar.blade.php` — 591 سطر) — تصميم داكن (gradient 145° #0f172a → #0a0f1c)
- **شريط جانبي تفاعلي** — يعرض "Data Definitions" و "Customer App Properties" كبطاقات floating modal مع بحث وتصفية ذكية
- **Navbar** (`navbar.blade.php`) — إشعارات، رسائل، قائمة مستخدم

### بطاقات التعريفات (Data Definitions):
7 فئات رئيسية، كل فئة تفتح شبكة بطاقات فرعية:

| الفئة | العناصر |
|-------|---------|
| **master (Master Data)** | Organizations, Vendors, Pack Statuses, Geographical, Sales Order Type, Currencies, ..., Items, UOMs, Packs... |
| **staff** | Devices, Personal Info, Employment, Attendance |
| **customer** | Customer demographics, Contact, Loyalty |
| **products** | Product Attributes, Pricing, Brands, Categories, Items, Item Models... |
| **warehouse & vehicles** | States, Areas, Warehouse Types, Warehouses, Vehicle Types, Vehicles, Production Batches, Batch Movements |
| **mobile (Customer App)** | UI Layouts, Offline Forms, Push Notifications |
| **display** | Screen Layouts, Widgets, Real-time Dashboards |

### صفحة العناصر (Items):
- `index` — جدول منقسم بالفئات، مع إحصاءات (إجمالي/نشط/غير نشط/مع صورة)
- `create` — نموذج شامل مع: نوع المنتج، فئة، علامة، موديل، مورد، UOM، عبوات (quantity, barcode, dimensions, pack operations/groups)، صور إضافية
- `show` — عرض مفصل للمنتج والعبوات والصور

### صفحة المخازن (Warehouses):
- `index` — جدول مع فلاتر
- `create/edit` — نموذج شامل مع: نوع المخزن، المنظمة، GPS، عنوان، هاتف، fax، barcode، destinations/sources/employees (كـ comma-separated)
- `show` — **صفحة مركزية مهمة** (470 سطر):
  - جدول أرصدة المنتجات مجمعة حسب (item_id + item_pack_id)
  - كل صف: اسم المنتج، الكود، الفئة، العلامة، عدد الدفيئات النشطة، الكمية الإجمالية
  - إحصاءات الدفيئات (عددها، تواريخ انتهائها)
  - **زر "نقل منتجات"** — يفتح نظام النقل (3 خطوات)
  - بحث بالمنتج (name_en/name_ar/code/barcode)
  - إحصاءات العبوات على شكل نسب مئوية (pie chart-ready)

### نظام نقل المخازن (Warehouse Transfer):
- **transfer_select** — جدول المنتجات المتاحة في المخزن المصدر مع فلاتر (category, brand, search). كل صف = batch_item مع available_quantity
- **transferConfirm / transferConfirmFromWarehouse** — نافذة تحديد: المخزن الهدف، الكميات لكل منتج، تاريخ النقل، ملاحظات
- **transferExecute** — في المعاملة (DB::transaction): خصم من المصدر، إضافة للهدف، تسجيل في batch_movements. يمسح الجلسة بعد الإكمال
- البيانات محفوظة في الجلسة (`session('warehouse_transfer')`) بين الخطوات

### نظام الدفيئات الإنتاذية (Batches):
**ProductionBatchController (600 سطر) — 7 واجهات:**

| الواجهة | الوظيفة |
|--------|---------|
| `index` | قائمة الدفيئات مع فلاتر (status، search) وإحصاءات (total/active/expired) |
| `create` | نموذج إنشاء دفيئة — ينشئ رقم دفيئة أوتوماتيكي (BATCH-YYYYMMDD-NNN)، يضيف عناصر |
| `store` | حفظ الدفيئة في معاملة — يجمع العناصر المكررة حسب (item_id, item_pack_id) |
| `show` | عرض الدفيئة — جدول العناصر مع الكميات (quantity, dispatched, transferred, remaining) |
| `edit/update` | تعديل — مع الحفاظ على dispatched_quantity |
| `distribute` | توزيع الدفيئة على مخزن — يرسل الكميات إلى batch_item_stock ويدونها كـ movement_type = 'in' |
| `transfer` | نقل الدفيئة بين مخازن (2 خطوات: اختيار المصدر+العناصر ← تحديد الهدف+الكميات)، باستخدام session('transfer_step1', 'transfer_step2') |
| `expired` | صفحة مراجعة الدفيئات المنتهية الصلاحية في كل المخازن |
| `returnExpired` | إرجاع الدفيئات المنتهية |

**BatchDistributionController (84 سطر):**
- `create($batchId)` — واجهة توزيع
- `store($batchId)` — يوزع الكميات على مخزن (updateOrCreate stock, تسجيل movement = 'in')
- `distributeStore/distribute` في ProductionBatchController — نسخة محسّنة

### نظام حركة المخازن (Warehouse Movement):
**WarehouseMovementController (261 سطر) — CRUD كامل على BatchMovement:**

| الواجهة | الوظيفة |
|--------|---------|
| `index` | قائمة الحركات — تجميع حسب (type, from, to, date, batch_id)، فلاتر متقدمة، إحصاءات (in/transfer/expiry_return)، ترقيم صفحات |
| `create` | نموذج يدوي لإضافة حركة (batch_item، المصدر، الوجهة، type، quantity، date، notes) |
| `store` | حفظ الحركة |
| `show` | عرض تفاصيل الحركة مع جميع المنتجات في المجموعة |
| `edit/update` | تعديل الحركة |
| `destroy` | حذف |
| `export` | تصدير كل الحركات (الفلاتر) إلى Excel |
| `exportDetail` | تصدير تفاصيل مجموعة واحدة إلى Excel |

**أنواع الحركة:** `in` (وارد/توزيع)، `transfer` (نقل بين المخازن)، `expiry_return` (استرجاع صلاحية منتهية)

**نماذج التصدير (Exports):**
- `MovementsExport` (124 سطر) — تصدير الحركات مع الفلاتر + WithMapping + WithStyles
- `MovementDetailExport` (82 سطر) — تفاصيل مجموعة واحدة

### "نبذة عنا" (Organization About):
- **OrganizationAboutController (131 سطر)** — CRUD كامل
- `OrganizationAbout` model (68 سطر) — يدعم: title، description، vision، mission، values (JSON array)، statistics (JSON array)، image/cover_image مع accessors
- واجهة `organization_about/index/create/edit/show` — نموذج شامل مع رفع صور، إدخال values/statistics ديناميكي
- **اختبار Feature:** `CustomerAboutTest.php` (94 سطر) — يختبر عرض النبذة النشطة بشكل صحيح

### باقي واجهات الإدارة:
- **Customer App Properties:** Banners (CRUD)، Notifications (CRUD + resend + export، مع channel + customers JSON + icon_type)
- **FAQs:** CRUD ببساطة (category, question, answer)
- **Surveys:** CRUD كامل (Survey → Questions → Responses → Answers) مع toggle
- **Roles & Permissions:** Spatie integration — `RolePermissionController`

---

## 5. تطبيق العملاء (Customer App)

### 5.1 التطبيق الهاتفي الثابت (PWA / HTML JS — `public/customer-app/`)

يدعم نوعين من الوصول:

#### نموذج 1: PWA (Progressive Web App) — Static HTML/JS
| الملف | الوظيفة |
|-------|---------|
| `index.html` | صفحة تسجيل الدخول — دعم لغتين (عربي/إنجليزي)، إعداد عنوان الخادم، مصادقة عبر API |
| `dashboard.html` | الصفحة الرئيسية — Carousel إعلاني (هارد كود)، فئات، علامات، منتجات |
| `js/api.js` (206 سطر) | **عميل API أساسي** — إدارة التوكن (localStorage)، طلبات HTTP، endpoint لكل شيء (login, logout, getDashboard, getCategories, getBrands, getProducts, getProduct) |
| `js/app.js` (342 سطر) | منطق عام — Toast، Loading، Auth Guard، Navigation، عرض البطاقات |
| `css/app.css` | أنماط موحدة — دعم dark mode، RTL، responsive |

**API.js يدعم:**
```javascript
API.login(username, password) → حفظ التوكن + بيانات العميل
API.logout() → حذف التوكن
API.getDashboard() → بنرات + فئات + علامات + منتجات
API.getCategories(), API.getCategory(id), API.getCategoryProducts(id, page)
API.getBrands(), API.getBrand(id), API.getBrandProducts(id, page)
API.getProducts(page, params), API.getProduct(id)
```

**لا يزال PWA غير مكتمل — فقط login + dashboard مبنيان. باقي الصفحات (categories.html, products.html, product.html) مش بعد موجودة.**

#### نموذج 2: Blade Views (Laravel) — `resources/views/app_android/`

يدعم نظام مزدوج (API + Fallback للـ Local DB):

| الصفحة | الوظيفة |
|--------|--------|
| `app_android/login.blade.php` (374) | تسجيل الدخول — يرسل للـ Hostinger API، يحفظ الـ token + يزيلحق المحلي |
| `app_android/register.blade.php` (331) | تسجيل جديد — يرسل للـ Hostinger API |
| `app_android/customer_dashboard.blade.php` (328) | الصفحة الرئيسية — بنرات، فئات، علامات، منتجات مع زر مفضلة لكل منتح |
| `app_android/categories/index.blade.php` | قائمة الفئات |
| `app_android/categories/show.blade.php` (127) | فئة محددة + المنتجات فيها + مفضلة |
| `app_android/brands/index.blade.php` (51) | قائمة العلامات |
| `app_android/brands/show.blade.php` (126) | علامة محددة + منتجاتها + مفضلة |
| `app_android/products/index.blade.php` (125) | جميع المنتجات مع مفضلة |
| `app_android/product/show.blade.php` (100) | تفاصيل منتج واحد — صور، عبوات، سعر، مفضلة، إضافة للسلة |
| `app_android/favorite.blade.php` (232) | صفحة المفضلة — يحاول API أولاً، fallback للـ local DB |
| `app_android/offers.blade.php` (66) + `offers/index.blade.php` (79) | العروض |
| `app_android/cart.blade.php` (315) | سلة التسوق — واجهة فقط (لا backend) |
| `app_android/more.blade.php` (459) | صفحة "المزيد" — 12 زراً للانتقال |
| **المجلد `more/` (10 صفحات):** | about_us (268)، change_organization (184)، contact_us (130)، faq (184)، financial_statement (187)، my_info (340)، notifications (495)، order_details (209)، order_history (281)، privacy_policy (123)، surveys (516) |

**الـ Partials (Mobile):**
- `partials/mobile/app_mobile.blade.php` — التصميم العام (header + content + bottom_nav)
- `partials/mobile/bottom_nav.blade.php` — شريط تنقل سفلي (5 أزرار: home, offers, cart, favorite, more)
- `partials/mobile/header.blade.php` — شريط علوي مع إشعارات
- `partials/mobile/scripts.blade.php` — سكريبتات مساعدة

### 5.2 الـ Customer Service Layer (Blade Controllers)

`CustomerAppController` (899 سطر) — هذا هو القلب النابض لتطبيق العملاء على الويب/Blade.

**آلية العمل:**
1. كل صفحة تحاول جلب البيانات من الـ **Hostinger API** أولاً (عبر `Http::withoutVerifying()->get("{$this->baseUrl}/customer/...")`)
2. إذا فشل الـ API (استثناء أو غير ناجح أو فارغ)، ينتقل إلى **Fallback محلي** — جلب البيانات من قاعدة بيانات Laravel المحلية
3. الدوال المساعدة: `parseData()`, `applyImagePath()`, `normalizeImagePath()` — توحيد مسارات الصور بين API والـ local

**نقاط تنبيه مهمة:**
- `normalizeImagePath()` — يعالج 5 حالات: URL كامل، `storage/`، `uploads/`، بحث افتراضي — مع Fallback للـ Hostinger URL
- `base URL` ثابت على `https://blue-beaver-923812.hostingersite.com`
- يتم حفظ التوكن في الجلسة (`session('api_token')`) بعد الدخول
- نظام المفضلة يعمل **محلياً فقط** (DB) — `toggleFavorite()` لا يرسل للـ API

**المسارات المتوفرة:**
- `dashboard()` → `customer_dashboard.blade.php` + `favoriteItemIds` من local favorites
- `categoriesIndex/show($id)` — API + Fallback
- `brandsIndex/show($id)` — API + Fallback
- `productsIndex()` — API + Fallback
- `productShow($id)` — API + Fallback
- `offersIndex()` — API + Fallback (بيانات افتراضية فارغة)
- `myInfo()` / `updateInfo()` — تحديث على Hostinger + محلي
- `faqIndex()` — API + Fallback (Faq::all)
- `notificationsIndex()` — API + Fallback (Notification model مع readNotifications pivot)
- `favorites()` — يحاول API /products ثم يفلتر بالـ favoriteItemIds
- `toggleFavorite()` — محلي فقط
- `more()` — الصفحة الرئيسية للـ "المزيد"
- `aboutUsWeb()` — API أو local OrganizationAbout (نشطة)
- `aboutUs()` — نفسه لكن كـ JSON (للـ API)

---

## 6. نظام المخازن والإدارة الكاملة — Architecture Deep Dive

### 6.1 النماذج والعلاقات (Data Model Relationships):

```
ProductionBatch (1) ──hasMany──> BatchItem (N)
    │                                  │
    │                                  ├── belongsTo ──> Item
    │                                  ├── belongsTo ──> ItemPack
    │                                  ├── hasMany   ──> BatchItemStock (N)
    │                                  └── hasMany   ──> BatchMovement (N)
    │
Warehouse (1) ──hasMany──> BatchItemStock
    │
    └── (عبر BatchItemStock) ←── (batch_item_id + warehouse_id → quantity)
```

**BatchMovement:**
- `movement_type` = `in` → توزيع من الإنتاج (from_warehouse_id = null)
- `movement_type` = `transfer` → نقل بين مخازن
- `movement_type` = `expiry_return` → إرجاع صلاحية منتهية

### 6.2 سير عمل النقل (Transfer Workflow):

**من صفحة المخزن (Warehouse → Show):**
```
[Show Warehouse] → زر "نقل منتجات" 
    ↓
POST /warehouses/{warehouse}/transfer/store-selection-from-warehouse
    ↓ (يحفظ selected_batch_item_ids في الجلسة)
GET /warehouses/{warehouse}/transfer/confirm-from-warehouse
    ↓ (يعرض: المخازن المتاحة، كل منتج مع available_quantity، إدخال الكميات)
POST /warehouses/{warehouse}/transfer/execute
    ↓ (DB::transaction — خصم من المصدر، إضافة للهدف، تسجيل BatchMovement)
[Back to Warehouse Show] ✅
```

**من صفحة الدفيئة (Batch → Transfer):**
```
[Batch Show] → "نقل بين المخازن"
    ↓
GET /production-batches/{batch}/transfer (الخطوة 1: اختيار المصدر + العناصر)
POST /production-batches/{batch}/transfer (يحفظ step1 + step2 في الجلسة)
    ↓
GET /production-batches/{batch}/transfer (الخطوة 2: اختيار الهدف + الكميات)
POST /production-batches/{batch}/transfer (DB::transaction — التنفيذ الكامل)
[Back to Batch Show] ✅
```

### 6.3 سير عمل التوزيع (Distribution Workflow):

**Batch → Distribute:**
```
[Batch Show] → زر "توزيع"
    ↓
GET /production-batches/{batch}/distribute (BatchDistributionController::create)
    ↓ (اختيار مخزن، كميات لكل عنصر)
POST /production-batches/{batch}/distribute
    ↓ (DB::transaction — increment dispatched_quantity، updateOrCreate stock، BatchMovement type=in)
[Back to Batch Show] ✅
```

هناك **طريقتان موحدتان** للتوزيع:
1. `BatchDistributionController::store($batchId)` (85 سطر) — إصدار أقدم/بسيط
2. `ProductionBatchController::distribute(Request, $batch)` (600 سطر) — النسخة الأساسية المرتبطة بـ resource controller
3. `ProductionBatchController::distributeStore(Request, $productionBatch)` — نسخة ثالثة (قد تكون deprecated)

---

## 7. النظام ثلاثي المستويات (Hybrid Architecture)

### الطبقة 1: Admin Dashboard (Laravel Blade)
- مسارات `/dashboard`، `/login` (middleware `auth`)
- يدير البيانات المحلياً بالكامل
- Spatie Permissions للوصول

### الطبقة 2: Customer Web App (Laravel Blade + API Proxy)
- مسارات `/mobile/*`, `/customer/*`
- يجلب منتجات، فئات، علامات، بنرات، إشعارات، استبيانات، نبذة، ملف تعريف من **Hostinger API**
- مع Fallback إلى قاعدة بيانات Local
- المفضلة تعمل محلياً فقط
- المصادقة: يرسل بيانات الدخول إلى الـ Hostinger، ويخلق حساباً محلياً بنفس البيانات + يحفظ التوكن

### الطبقة 3: Customer PWA (Static HTML + JS)
- ملفات استاتيك في `public/customer-app/`
- Pure JavaScript (API.js + App.js) — لا يعتمد على Laravel
- يتواصل مباشرة مع `https://blue-beaver-923312.hostingersite.com/api`
- مستوى أمان Session غير مدمج (يعتمد على localStorage token فقط)

---

## 8. نظام الصور والوسائط (Media Handling)

**المنهجية الموحدة في `normalizeImagePath()` والـ accessors:**
- إذا كان المسار URL كامل (http/https) → يُستخدم كما هو
- إذا بدأ بـ `storage/` → `asset('storage/...')` محلياً أو Hostinger/storage
- إذا بدأ بـ `uploads/` → `asset('uploads/...')` محلياً أو Hostinger/uploads
- للمنتجات/الفئات/العلامات → Fallback إلى Hostinger/uploads
- للـ OrganizationAbout → `asset('uploads/organization_about/...')`

**الملفات المرفوعة:**
- `public/uploads/items/`
- `public/uploads/categories/`
- `public/uploads/brands/`
- `public/uploads/customers/`
- `public/uploads/organization_about/`
- `public/storage/` (رابط Symbolic)

---

## 9. التوحيد بين Web و API و PWA

| الميزة | Admin (Blade) | Customer Web (Blade) | Customer PWA (Static JS) | Customer API |
|--------|:---:|:---:|:---:|:---:|
| CRUD منتجات | ✅ | ❌ (عرض فقط) | ❌ (عرض فقط) | ✅ (JSON) |
| CRUD مخازن | ✅ | ❌ | ❌ | ❌ |
| CRUD دفيئات | ✅ | ❌ | ❌ | ❌ |
| نقل بين المخازن | ✅ | ❌ | ❌ | ❌ |
| حركة البضائع | ✅ | ❌ | ❌ | ❌ |
| توزيع دفيئة | ✅ | ❌ | ❌ | ❌ |
| عرض المنتجات | ✅ | ✅ (Fallback) | ✅ | ✅ |
| عرض الفئات | ✅ | ✅ (Fallback) | ✅ | ✅ |
| عرض العلامات | ✅ | ✅ (Fallback) | ✅ | ✅ |
| المفضلة | ❌ | ✅ (محلياً) | ❌ | ❌ (TODO) |
| البنرات | ✅ | ✅ (Fallback) | ✅ | ✅ |
| الاستبيانات | ✅ | ❌ (Proxy) | ❌ (Proxy) | ✅ |
| الإشعارات | ✅ | ✅ | ❌ | ✅ |
| الملف الشخصي | ✅ | ✅ (API+Local) | ❌ | ✅ |
| نبذة عنا | ✅ (CRUD) | ✅ (API+Fallback) | ❌ | ✅ |

---

## 10. التغييرات غير الملتزمة حالياً (Pending Changes)

هناك تعديلات غير ملتزمة في هذه الملفات:

| الملف | النوع |
|-------|-------|
| `app/Http/Controllers/WarehouseController.php` | تحديث نظام النقل (449 سطر تغيير) + transferStoreSelectionFromWarehouse + transferConfirmFromWarehouse |
| `resources/views/dashboard/data_definition/w&v/warehouse/show.blade.php` | تحسينات على واجهة المخزن (57 سطر) |
| `resources/views/dashboard/data_definition/w&v/warehouse/transfer_confirm.blade.php` | تحديث واجهة تأكيد النقل (234 سطر) |
| `resources/views/dashboard/data_definition/w&v/warehouse/transfer_select.blade.php` | تحديث واجهة اختيار المنتجات (278 سطر) |
| `resources/views/dashboard/data_definition/w&v/warehouse/transfer_confirm_from_warehouse.blade.php` | ملف جديد (untracked) |
| `routes/web.php` | إضافة 2 مسار جديد للنقل من صفحة المخزن، تحديث transfer route لاستخدام {productionBatch} |

---

## 11. ملخص الكميات البرمجية

| الفئة | العدد | المجموع الكلي (خطوط) |
|-------|-------|---------------------|
| Controllers | 44 ملف | ~7000+ سطر |
| Models | 45 ملف | ~1500 سطر |
| Exports | 24 ملف | 1458 سطر |
| Blade Views | 97 ملف | ~25000+ سطر |
| Routes (web + api) | 2 ملف | ~600 سطر |
| Migrations | 50+ ملف | ~1000 سطر |

---

*تم إعداد هذا التقرير بناءً على استعراض كامل للكود المصدري. جميع البيانات مستندة من الملفات الفعلية في المستودع.*