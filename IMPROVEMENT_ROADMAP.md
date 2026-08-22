# خطة تحسين واستكمال النظام
## نظام ماغاديت للصناعات الغذائية
## التاريخ: الاثنين 15 أغسطس 2026

---

## جدول المحتويات
1. [ملخص حالة النظام الحالية](#1-ملخص-حالة-النظام-الحالية)
2. [المشاكل والثغرات المكتشفة](#2-المشاكل-والثغرات-المكتشفة)
3. [الخطة الزمنية للتحسين (3 مراحل)](#3-الخطة-الزمنية-للتحسين-3-مراحل)
4. [تحسينات الأداء (Performance)](#4-تحسينات-الأداء-performance)
5. [توحيد طبقة API والـ Fallback](#5-توحيد-طبقة-api-والـ-fallback)
6. [تحسينات PWA الـ HTML/JS](#6-تحسينات-pwa-الـ-htmljs)
7. [تحسينات أمنية](#7-تحسينات-أمنية)
8. [تحسينات بنية الكود (Code Quality)](#8-تحسينات-بنية-الكود-code-quality)
9. [تحسينات تجربة المستخدم (UX)](#9-تحسينات-تجربة-المستخدم-ux)
10. [جداول التحسين المقترحة](#10-جداول-التحسين-المقترحة)

---

## 1. ملخص حالة النظام الحالية

| المكوّن | الحالة | الملاحظات |
|--------|:---:|---------|
| لوحة إدارة المخازن | ✅ مكتمل | Batch، Transfer، Movement، Expiry |
| توزيع الدفيئات | ✅ مكتمل | 3 مسارات مكررة (BatchDistribution, ProductionBatch::distribute, distributeStore) |
| نقل بين المخازن | ✅ مكتمل | 2 مسار: من صفحة المخزن، ومن صفحة الدفيئة |
| تطبيق العملاء (Blade) | ⚠️ مكتمل جزئياً | Dual API+Fallback، مفضلة محلياً فقط |
| تطبيق العملاء (PWA) | ❌ غير مكتمل | فقط login + dashboard، لا يدعم fav/notifications/offers |
| API Endpoints | ✅ مكتمل | CustomerApiController 676 سطر |
| NativePHP APK | ⚠️ إعداد غير مكتمل | start_url يشير للـ Hostinger، runtime=persistent |
| الاختبارات | ❌ غير كافية | فقط CustomerAboutTest واحد |
| أمان | ⚠️ معقّد | API بدون rate limiting، PWA بلا CSRF |
| بنية الكود | ⚠️ تضارب | دوال مكررة (parseJsonField، parseStatsField، normalizeImagePath) |

---

## 2. المشاكل والثغرات المكتشفة

### 2.1 تكرار الكود (Code Duplication) — خطيرة

**التكرار في `CustomerAppController` (899 سطر):**
- `parseJsonField()` مكرر **3 مرات** في:
  - `CustomerAppController` (سطر 848)
  - `CustomerApiController` (سطر 506)
  - منطق JSON المضمن في views

- `parseStatsField()` مكرر **3 مرات** بنفس الطريقة

- `normalizeImagePath()` — نطاق واسع لكنه:
  - لا يدعم بروتوكول `https://` في `str_contains` بدلاً من URL validation
  - يحتاج إلى `Http` facade بدلاً من استخدام `Str::isUrl()`

**التكرار في التوزيع:**
- `BatchDistributionController::store()` (84 سطر) — **ما لا يُستخدم** (لا يوجد route له)
- `ProductionBatchController::distribute()` (601 سطر) — النسخة الأساسية المستخدمة
- `ProductionBatchController::distributeStore()` (601 سطر) — نسخة مكررة غير مستخدمة

**التوحيد المقترح:**
```
✅ إنشاء Service Class: `app/Services/ImagePathService.php`
   → normalize($path, $modelType = null)
   
✅ إنشاء Trait: `app/Traits/JsonParserTrait.php`
   → parseJsonField($field), parseStatsField($field)
   
✅ حذف BatchDistributionController بالكامل (غير مستخدم)
   → أو دمجه في ProductionBatchController::distribute()
```

### 2.2 مشاكل الأداء (N+1 Queries)

| الملف:السطر | المشكلة | الحل |
|-------------|---------|-----|
| `WarehouseController::index():43` | `Warehouse::count()` ×2 — استعلامات منفصلة | استبدال بـ `$query->count()` أو استخدام `withCount` |
| `WarehouseController::show():99-101` | `BatchItemStock::where(...)` مع `with` — لكنه يُنفذ `filter()` في الذاكرة بدلاً من DB | استخدام `whereHas` بدلاً من `filter` |
| `WarehouseMovementController::index():53` | `$query->get()` — يجلب كل السجلات إلى الذاكرة ثم يجمعها | استخدام pagination + DB grouping |
| `WarehouseMovementController::index():58-64` | `groupBy` على collection — يجلب كل البيانات | استخدام `DB::raw GROUP BY` في الاستعلام الأصلي |
| `WarehouseMovementController::index():115` | `$query->with('batchItem.batch')->get()` — استعلام مكرر داخل closure | استبدال بإحصاءات مكررة أو cached counters |
| `ProductionBatchController::expiredBatches()` | لا يستخدم eager loading على `batchItem.item` | إضافة `->with(['batchItem.item', 'batchItem.pack', 'batchItem.batch', 'warehouse'])` |

**التأثير:** في قاعدة كبيرة، `warehouse-movements/index` قد يأخذ 5-10 ثوانٍ للتحميل.

### 2.3 مشاكل في PWA الـ HTML/JS — غير مكتمل

| المشكلة | الوصف |
|--------|-------|
| **عرض المنتج** | `app.js` يحوي `renderProductCard` لكنه يستخدم `product.image_url` — لكن `api.js` لا يُرجع هذا الحقل. API يرجع `image` بدلاً من `image_url` |
| **الفئات والعلامات** | `api.js` يرجع `logo` وليس `logo_url` — عدم توافق |
| **الصفحات المفقودة** | `categories.html`, `products.html`, `product-details.html`, `brands.html` — غير موجودة رغم أن `app.js` يحاول التنقل إليها |
| **المفضلة** | لا تدعم PWA — لا يوجد API endpoint للمفضلة، ولا يوجد UI |
| **الإشعارات** | لا يوجد `notifications.html` ولا API integration |
| **العروض/المبيعات** | `offers` لا يأتي من الـ API بشكل كامل (بيانات وهمية) |
| **السلة** | `cart.html` موجود لكنه فقط واجهة — لا backend، لا API، لا مزامنة |
| **المصادقة** | PWA يستخدم `localStorage` فقط — لا Session، لا CSRF Protection، لا حماية من XSS |
| **CORS** | PWA تستخدم `fetch` مباشرة — قد تحتاج CORS headers على الـ Hostinger |

### 2.4 مشاكل في API Design

| المشكلة | الوصف |
|--------|-------|
| **استجابة API غير موحدة** | أحياناً يرجع `categories`، أحياناً `data`، أحياناً الـ array الكامل |
| **عدم وجود pagination metadata** في استجابة productsIndex | يستخدم `$query->paginate(20)` لكنه لا يرسل `meta`/`links` |
| **is_favorite دائماً false** | في الـ API يرجع `is_favorite: false` ثابت — لا يتحقق من المفضلة |
| **عدم وجود endpoint للمفضلة** | لا `POST /api/customer/favorites` أو `GET /api/customer/favorites` |
| **عدم وجود endpoint للسلة** | لا يوجد API لإدارة السلة |
| **عدم وجود endpoint للطلبات** | لا يوجد نظام طلبات/مبيعات |
| **عدد كبير من `test` endpoints** | `testroot, testxyz, testzzz, testapianother, testinside` — يجب حذفها في Production |

### 2.5 مشاكل في مخطط القاعدة (Schema Issues)

| الملف:السطر | المشكلة | الوصف |
|-------------|---------|-------|
| `batch_movements migration:20` | `$table->string('movement_type')->change()` | استدعاء `->change()` على عمود غير موجود — سيظطلع error. يجب إما إزالته أو تعريف العمود أولاً |
| `batch_items migration:20` | `remaining_quantity` nullable لكن `BatchItem` يستخدم accessor لاحتسابه | تضارب — إما يُخزن في DB أو يُحسّب افتراضياً، لا كليهما |
| `customer_favorites migration:22` | لا يوجد FK على `item_id` | المبرر: "items قد تكون من API" — لكنه يقتلح إمكانية integrity |

### 2.6 مشاكل في الأمان

| المشكلة | الوصف |
|--------|-------|
| `CheckCustomerSession` | يسمح بالدخول إذا كان `api_token` موجود — حتى لو كان منتهي الصلاحية على الـ Hostinger |
| PWA `fetch` | لا يوجد CSRF Protection، لا يوجد Authorization Header retry logic |
| `Http::withoutVerifying()` | يستخدم في جميع استدعاءات API — يعني لا SSL certificate verification أبداً |
| لا يوجد Rate Limiting على API endpoints | |
| لا يوجد API Token validation على الـ Hostinger | |

---

## 3. الخطة الزمنية للتحسين (3 مراحل)

### المرحلة 1 — MVP (2-3 أسابيع)  
**الهدف:** إصلاح الأخطاء الحرجة، دمج الـ PWA، توحيد الـ API

| رقم | المهمة | الأولوية | الجهد |
|-----|--------|---------|-------|
| 1.1 | إصلاح `movement_type` migration — حذف `->change()` | عالية | ساعة |
| 1.2 | دمج `BatchDistributionController` الزائد في `ProductionBatchController::distribute()` | عالية | نصف يوم |
| 1.3 | إنشاء Service Trait (`ImageService`, `JsonParserTrait`) | عالية | نصف يوم |
| 1.4 | إنشاء `GET/POST /api/customer/favorites` endpoints | عالية | 2 أيام |
| 1.5 | ربط PWA `products.html` + `categories.html` + `brands.html` | متوسطة | 3 أيام |
| 1.6 | حذف `test*` endpoints من api.php | منخفضة | 10 دقائق |

**النتيجة المتوقعة:** نظام مستقر، PWA يدعم التصفح الأساسي، توحيد الكود.

---

### المرحلة 2 — تحسين الأداء والأمان (3-4 أسابيع)
**الهدف:** تحسين الأداء، الأمان، بنية الكود

| رقم | المهمة | الأولوية | الجهد |
|-----|--------|---------|-------|
| 2.1 | استبدال collection-based grouping بـ DB GROUP BY في WarehouseMovementController | عالية | 2 أيام |
| 2.2 | استبدال `Item::all()` بـ pagination في ProductsIndex fallback | عالية | نصف يوم |
| 2.3 | إضافة Cache للبيانات الثابتة (categories, brands, banners) | عالية | يوم |
| 2.4 | إضافة Rate Limiter للـ Customer API | متوسطة | نصف يوم |
| 2.5 | إضافة CORS headers للـ API | متوسطة | ساعة |
| 2.6 | تحويل `Http::withoutVerifying()` إلى `Http::withOptions(['verify' => env('APP_DEBUG')])` | عالية | نصف يوم |
| 2.7 | إزالة eager loading غير الضروري في `WarehouseMovementController::create/edit` | منخفضة | ساعة |
| 2.8 | إنشاء `NotificationService` لتجميع منطق notifications | متوسطة | يوم |

**النتيجة المتوقعة:** استجابة أسرع ×3-5، أمان أعلى، بنية نظيفة.

---

### المرحلة 3 — استكمال الميزات والـ NativePHP (4-6 أسابيع)
**الهدف:** استكمال PWA، بناء APK كامل، اختبارات شاملة

| رقم | المهمة | الأولوية | الجهد |
|-----|--------|---------|-------|
| 3.1 | إكمال PWA: notifications.html, product-details.html, cart.html, orders.html | عالية | 10 أيام |
| 3.2 | بناء APK كامل بـ NativePHP (start_url = local، session persistence) | عالية | أسبوعين |
| 3.3 | ربط NativePHP APK بالـ local Blade routes بدلاً من Hostinger | عالية | أسبوع |
| 3.4 | إنشاء نظام طلبات/مبيعات (Order Model + API) | متوسطة | أسبوع |
| 3.5 | إنشاء اختبارات Feature شاملة (Warehouse, Batch, Movement, Customer) | عالية | أسبوع |
| 3.6 | إضافة API endpoint للمفضلة (toggle) + مزامنته مع الـ Hostinger | متوسطة | 3 أيام |
| 3.7 | تحسين `remaining_quantity` — جعله accessor فقط، إزالة الحقل من DB | منخفضة | نصف يوم |

**النتيجة المتوقعة:** نظام كامل موحد، APK يعمل Offline+Online، تغطية اختبارات ≥80%.

---

## 4. تحسينات الأداء (Performance)

### 4.1 استعلامات N+1 — إصلاحات محددة

**🔴 `WarehouseMovementController::index()` — الحركة الأبطأ**
```php
// الحالي (بطيء جداً):
$movements = $query->orderBy('movement_date', 'desc')->get();  // يجلب كل السجلات
$grouped = $movements->groupBy(...);  // تجميع في الذاكرة
$getGroupCount = function ($type = null) use ($request) {
    $query = BatchMovement::query();  // استعلام منفصل كل مرة!
    $data = $query->with('batchItem.batch')->get();  // يجلب كل البيانات مرة ثانية!
    ...
};

// المقترح:
// 1. Pagination مباشرة في DB
// 2. إحصاءات محسوبة في استعلام واحد
$total = BatchMovement::where(...same filters...)->distinct('movement_type')...
```

**🔴 `WarehouseController::show()` — filter في الذاكرة:**
```php
// الحالي:
$stocks = BatchItemStock::where('warehouse_id', $warehouse->id)->with(...)->get();
$validStocks = $stocks->filter(...);  // ترشيح في الذاكرة!
if ($search) { $validStocks->filter(...) }  // ترشيح آخر في الذاكرة!

// المقترح:
$query = BatchItemStock::where('warehouse_id', $warehouse->id)
    ->whereHas('batchItem.item', ...)
    ->whereHas('batchItem.batch', ...);
if ($search) { $query->whereHas(...) }
```

### 4.2 الكاشينج (Caching)

**الاقتراح:** إنشاء Cache للبيانات التي تتغير نادراً:
```php
// في CustomerApiController أو CustomerAppController
Cache::remember('customer_app_categories', 3600, fn() => 
    Category::where('show_in_customer_app', true)->get()
);

Cache::remember('customer_dashboard_banners', 1800, fn() =>
    Banner::where('show_in_customer_app', true)->orderBy('order')->get()
);

Cache::remember('customer_app_static_data', 3600, fn() => [
    'categories' => Category::active()->get(),
    'brands' => Brand::active()->get(),
    'faqs' => Faq::all()->groupBy('category'),
]);
```

---

## 5. توحيد طبقة API والـ Fallback

### 5.1 المشكلة الحالية:
```php
// كل controller يكرر هذا النمط 20+ مرة:
try {
    $response = Http::withoutVerifying()->get("{$this->baseUrl}/customer/...");
    if ($response->successful()) {
        $data = $response->json();
        // استخراج البيانات من مفاتيح مختلفة (data, categories, products...)
        // fallback logic
    }
} catch (\Exception $e) {
    Log::error(...);
}
// fallback للـ local DB
```

### 5.2 الحل المقترح: Service + Response Normalizer

```php
// app/Services/CustomerApiService.php
class CustomerApiService 
{
    private function request(string $endpoint): array 
    {
        try {
            $response = Http::withToken(session('api_token'))
                ->timeout(8)
                ->get(env('CUSTOMER_API_URL') . $endpoint);
            
            if ($response->failed() || $response->status() === 401) {
                return ['success' => false, 'data' => null, 'error' => 'auth_failed'];
            }
            
            return ['success' => true, 'data' => $response->json()];
        } catch (\Exception $e) {
            return ['success' => false, 'data' => null, 'error' => $e->getMessage()];
        }
    }
    
    public function getDashboard(): array 
    {
        $result = $this->request('/customer/dashboard');
        return $result['success'] 
            ? $this->normalizeDashboard($result['data'])
            : $this->fallbackDashboard();
    }
}
```

### 5.3 توحيد استجابة API

**التوحيد المقترح:**
```json
{
    "success": true,
    "data": { ... },       // أو "collections": { ... } للكذاكرة
    "meta": {
        "source": "api|local|cached",
        "timestamp": "2026-08-15T11:30:00Z"
    }
}
```

---

## 6. تحسينات PWA الـ HTML/JS

### 6.1 صفحات PWA المطلوبة:

| الصفحة | الحالة | ما ينقص |
|--------|--------|--------|
| `index.html` (Login) | ✅ مكتمل | لا شيء |
| `dashboard.html` | ✅ مكتمل مع fallback | توصيل banners من API |
| `categories.html` | ❌ مفقود | `API.getCategories()` + `App.renderCategoryCard(list)` |
| `category-details.html` | ❌ مفقود | `API.getCategory(id)` + المنتجات |
| `products.html` | ❌ مفقود | `API.getProducts()` + `App.renderProductCard(list)` |
| `product-details.html` | ❌ مفقود | `API.getProduct(id)` — التفاصيل + packs |
| `brands.html` | ❌ مفقود | `API.getBrands()` |
| `brand-details.html` | ❌ مفقود | `API.getBrand(id)` + المنتجات |
| `notifications.html` | ❌ مفقود | `API` endpoint مطلوب أولاً |
| `cart.html` | ⚠️ UI فقط | Backend + API مطلوبان |
| `checkout.html` | ❌ مفقود | خطوة دفع غير موجودة |
| `profile.html` | ❌ مفقود | `API.getProfile()` + تحديث |
| `favorites.html` | ❌ مفقود | `API.getFavorites()` مطلوب أولاً |
| `orders.html` | ❌ مفقود | `API.getOrders()` مطلوب |

### 6.2 توحيد واجهة برمجة التطبيقات (API Consistency)

**المشكلة:** `api.js` يستخدم `image_url` و`logo_url` لكن `CustomerApiController` يرجع `image` و`logo`:

| الحقل في App.js | الحقل في API | الحالة |
|----------------|-------------|--------|
| `product.image_url` | `product.image` (المباشر) أو `product.main_image_url` | ❌ غير متطابق |
| `category.image_url` | `category.image` | ❌ غير متطابق |
| `brand.logo_url` | `brand.logo` | ❌ غير متطابق |

**الحل:** إما توحيد الأسماء في الـ API، أو إنشاء Transformer في `api.js`.

### 6.3 PWA Manifest

```json
// public/customer-app/manifest.json (مطلوب)
{
    "name": "ماغاديت للصناعات الغذائية",
    "short_name": "ماغاديت",
    "start_url": "/customer-app/dashboard.html",
    "display": "standalone",
    "theme_color": "#7c3aed",
    "background_color": "#ffffff",
    "icons": [ ... 192px, 512px ... ]
}
```

---

## 7. تحسينات أمنية

| المجال | التحسين المقترح |
|--------|-----------------|
| **HTTPS Verification** | استبدال `Http::withoutVerifying()` بوضعيّة: `Http::withOptions(['verify' => !app()->environment('local')])` |
| **CSRF Protection** | إضافة `@csrf` token إلى جميع نماذر Blade العامة؟ PWA تستخدم Bearer Token — لا حاجة للكوكيز |
| **Rate Limiting** | إضافة `ThrottleRequests` middleware على `/api/customer/*` (60/minute/ip) |
| **Session Security** | إضافة `Session::put('api_token_expires_at', now()->addHours(2))` — وتوقيع الجلسة |
| **Token Validation** | في `CheckCustomerSession` — إرسال طلب `/customer/profile` للتحقق من صلاحية التوكن على Hostinger |
| **CORS** | إضافة `HandleCors` middleware أو إعداد `fruitcake/laravel-cors` للـ API |
| **Input Validation** | جميع الـ API endpoints تستخدم `auth:api` — لكنها لا تتحقق من `organization_id` |

---

## 8. تحسينات بنية الكود (Code Quality)

### 8.1 نمط الأسماء والبروتوكولات

| المشكلة | الحل |
|--------|------|
| `WarehouseController::transferExecute()` يحتوي على منطق نقل | استخراج إلى `WarehouseTransferService::execute()` |
| `ProductionBatchController` (601 سطر) — Single Responsibility مكرّر | تقسيم: `BatchCrudController`, `BatchDistributionController` (separate), `BatchTransferController` |
| `CustomerAppController` (899 سطر) — كل شيء مركز | استخراج Services: `CustomerApiService`, `ImagePathService`, `CustomerAuthService` |
| وجود 4 نوافذ للمخازن المتداخلة (`transfer_confirm.blade.php` + `transfer_confirm_from_warehouse.blade.php` + `batches/transfer.blade.php`) | توحيدها باستخدام Component مشترك |

### 8.2 اختبارات (Tests)

**الاختبارات الحالية:** فقط `CustomerAboutTest.php` (94 سطر — يستخدم Schema::create مباشرة!)

**الاختبارات المطلوبة:**

| الفئة | الاختبارات المقترحة |
|-------|-------------------|
| **Warehouse** | CRUD، نقل بين المخازن (3 خطوات)، إحصاءات العرض |
| **ProductionBatch** | إنشاء، توزيع، نقل، إرجاع صلاحية، validation |
| **BatchMovement** | إنشاء movement، تصدير Excel، فلاتر |
| **CustomerAuth** | Login/Register Web+API، Session Token |
| **CustomerApp** | Dashboard API+Fallback، Categories، Brands، Products |
| **Favorites** | Toggle محلي + API Sync |
| **OrganizationAbout** | CRUD + About API + Fallback |
| **Notifications** | Send، Read/Unread، Star، Archive |

**الأداة المقترحة:** استخدام `phpunit.xml` الموجود — `php artisan test`

---

## 9. تحسينات تجربة المستخدم (UX)

### 9.1 لوحة الإدارة
- **Dashboard Sidebar** — يحتوي على 3 بطاقات disabled (`#`): Visit Types، Fields Group، Stock Statuses — يجب ربطها أو إخفاؤها
- **Page Loader** — لا يوجد Loading Spinner في العمليات الثقيلة (توزيع، نقل)
- **Error Handling** — `catch (\Exception $e)` في بعض الأماكن بدون رسالة واضحة للمستخدم

### 9.2 تطبيق العملاء
- **Empty States** — `cart.blade.php` و `favorite.blade.php` يعرضان زر "Continue Shopping" — جميل
- **RTL Support** — `dir="rtl"` في كل الـ HTML، لكن PWA تبدأ بـ `dir="rtl"` متى كان `lang="ar"`
- **Image Lazy Loading** — في `product/show.blade.php` توجد `loading="lazy"` — جيد. PWA لا يدعمه بعد
- **Form Validation** — في `login/register` لا توجد رسائل خطأ واضحة (تعتمد على `form-error` div)

### 9.3 Responsive Design
- الـ Admin Dashboard يستخدم Bootstrap 5 — Responsive جيد
- PWA يستخدم CSS مخصص — يدعم `viewport-fit=cover` و `@media` queries
- Blade views تستخدم TailwindCSS + inline styles — تحتاج توحيد

---

## 10. جداول التحسين المقترحة

### جدول الأولويات العليا (عالية — يجب إصلاحها أولاً)

| الرقم | المهمة | الملف/المكان | الوقت |
|-------|--------|-------------|-------|
| 1 | إصلاح migration `batch_movements` — إزالة `->change()` على `movement_type` | `2026_07_14_145942_create_batch_movements_table.php` | 30 دقيقة |
| 2 | دمج/حذف `BatchDistributionController` | `BatchDistributionController.php` | نصف يوم |
| 3 | دمج `parseJsonField`/`parseStatsField` في Service | `CustomerApiController.php:506`, `CustomerAppController.php:848` | نصف يوم |
| 4 | دمج `normalizeImagePath` في Service | `CustomerAppController.php:400`, `Item.php:100`, `OrganizationAbout.php:44` | نصف يوم |
| 5 | حذف `test*` endpoints من `api.php` | `routes/api.php:8-22, 28, 70` | 10 دقائق |
| 6 | إضافة Favorites API endpoints | `api.php`, `CustomerApiController.php` | 2 أيام |
| 7 | إصلاح `Item::all()` → pagination في ProductsIndex fallback | `CustomerAppController.php:299` | نصف يوم |

### جدول تحسينات الأداء (متوسطة)

| الرقم | المهمة | الملف | الوقت |
|-------|--------|------|-------|
| 1 | استبدال collection grouping بـ DB query في WarehouseMovementController | `WarehouseMovementController.php:58` | يوم |
| 2 | إضافة eager loading في expiredBatches | `ProductionBatchController.php:527` | 30 دقيقة |
| 3 | إضافة Cache للبيانات الثابتة | Controllers العامة | يوم |
| 4 | إضافة Rate Limiting للـ API | `app/Http/Kernel.php` | نصف يوم |

### جدول تحسينات PWA (طويلة المدى)

| الرقم | المهمة | الوقت |
|-------|--------|-------|
| 1 | بناء: categories.html, products.html, brands.html, product-details.html | 6 أيام |
| 2 | بناء: notifications.html, profile.html, favorites.html, orders.html | 4 أيام |
| 3 | ربط PWA بالـ API ببروتوكول موحد (image_url، logo_url) | يوم |
| 4 | إنشاء manifest.json + Service Worker للـ PWA | يوم |
| 5 | إضافة CSRF + Token Refresh للـ PWA | 2 أيام |

---

## الخلاصة والتوصيات

### أهم 3 إجراءات فورية:
1. **إصلاح migration خطأ `batch_movements`** — هذا خطأ syntax سيكسر `php artisan migrate` تماماً
2. **وضع Service لمعالجة الصور والـ JSON** — يزيل ~200 سطر مكرر
3. **حذف `test*` endpoints** — خطر أمني في Production

### أهم 3 تحسينات هيكلية:
1. **توحيد طبقة API** — أنشئ `CustomerApiService` واحد يدير كل الـ requests+caching+fallback
2. **استبدال collection-based queries** بـ DB-level grouping — تحسين الأداء
3. **إكمال PWA** أو إيقافها — الحالة الحالية نصف مكتملة وتسبب لبساً

### أهم 3 تحسينات تجربة:
1. **بناء الـ PWA بالكامل** — أو إعادة توجيهه إلى Blade routes فوراً
2. **مزامنة المفضلة مع الـ Hostinger API** — حالياً محلياً فقط
3. **بنية بناء APK واحدة** — NativePHP إعداده غير كامل (يشير للـ Hostinger بدلاً من local)

---

*تم إعداد هذه الخطة بناءً على تحليل شامل للكود المصدري. جميع الملفات والـ line numbers مستندة من الكود الفعلي.*
*الملف مرفق: `IMPROVEMENT_ROADMAP.md`*