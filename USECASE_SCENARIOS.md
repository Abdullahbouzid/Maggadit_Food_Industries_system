# سيناريوهات عمل النظام — دليل المستخدم الكامل
## نظام ماغاديت للصناعات الغذائية
## التاريخ: الاثنين 15 أغسطس 2026

---

## الفهرس
1. سيناريوهات إدارة المخازن والبضائع
2. سيناريوهات تطبيق العملاء (Web + PWA)
3. سيناريوهات لوحة الإدارة
4. تدفقات المصادقة والهوية

---

## 1. إدارة المخازن والبضائع

### 1.1 إنشاء دفيئة إنتاجية جديدة

**السيناريو:** أمين المخازن يريد تسجيل دفيئة إنتاج جديدة منتجات غذائية.

**خطوات العملية:**
```
المشرف → لوحة التحكم → Data Definitions → Warehouse & Vehicles → Production Batches → "إضافة"
```

1. يفتح `GET /production-batches/create` → `ProductionBatchController::create()`
2. النظام يُنشئ رقم دفيئة أوتوماتيكي: `BATCH-20260815-001`
3. يضيف العناصر (Items) إلى الدفيئة — كل عنصر يربط بـ:
   - منتج (Item) — اختيار من القائمة المنسدلة
   - عبوة (Item Pack) — يحدد الكمية والـ barcode
   - الكمية الإجمالية (quantity)
4. يحدد:
   - تاريخ الإنتاج (production_date)
   - تاريخ انتهاء الصلاحية (expiry_date)
   - الحالة (status: active)
   - ملاحظات
5. يضغط "حفظ" → `POST /production-batches` → `ProductionBatchController::store()`
   - يُنشئ الجدول `production_batches`
   - يُنشئ `batch_items` لكل منتج (مع dispatched_quantity = 0)
   - كل ذلك في معاملة `DB::transaction`

**النتيجة:** تظهر الدفيئة في القائمة بحالتها "active".

---

### 1.2 توزيع دفيئة على مخزن

**السيناريو:** تم إنهاء إنتاج دفيئة، الآن نريد توزيعها على مخزن التوزيع الرئيسي.

**خطوات العملية:**
```
Production Batches → [الدفيئة] → زر "توزيع"
```

1. يفتح `GET /production-batches/{batch}/distribute` → `ProductionBatchController::distributeForm()`
   - يعرض جدول العناصر في الدفيئة مع الأرصدة المتبقية (remaining = quantity - dispatched)
   - قائمة منسدلة بالمخازن النشطة
2. يحدد:
   - المخزن الهدف (warehouse_id)
   - الكمية لكل عنصر (يجب أن تكون ≤ remaining_quantity)
3. يضغط "توزيع" → `POST /production-batches/{batch}/distribute` → `ProductionBatchController::distribute()`

**العمليات الداخلية:**
```php
DB::transaction(function () use ($validated, $productionBatch) {
    foreach ($validated['items'] as $itemData) {
        $batchItem = BatchItem::findOrFail($itemData['batch_item_id']);
        
        // 1. زيادة dispatched_quantity (تم توزيعه الآن)
        $batchItem->increment('dispatched_quantity', $itemData['quantity']);
        
        // 2. إنشاء/تحديث رصيد المخزن
        $stock = BatchItemStock::firstOrNew([
            'batch_item_id' => $batchItem->id,
            'warehouse_id' => $validated['warehouse_id'],
        ]);
        $stock->quantity += $itemData['quantity'];
        $stock->save();
        
        // 3. تسجيل الحركة في السجل
        BatchMovement::create([
            'batch_item_id' => $batchItem->id,
            'from_warehouse_id' => null,      // من الإنتاج (ليس من مخزن)
            'to_warehouse_id' => $validated['warehouse_id'],
            'movement_type' => 'in',          // نوع الحركة: وارد
            'quantity' => $itemData['quantity'],
            'movement_date' => now(),
            'notes' => 'توزيع من الدفعة ' . $productionBatch->batch_number,
        ]);
    }
});
```

**النتيجة:**
- يظهر الرصيد المحدث في صفحة `warehouse/show`
- يظهر الحركة في `warehouse-movements/index` بنوع "in"

---

### 1.3 عرض أرصدة المخزن

**السيناريو:** أمين المخازن يريد مراجعة كميات موجودة في مخزن معين.

**خطوات العملية:**
```
Warehouses → [اسم المخزن] → زر "عرض"
```

1. يفتح `GET /warehouses/{warehouse}` → `WarehouseController::show()`
2. النظام يجلب كل `BatchItemStock` المرتبط بـ هذا المخزن
3. يجمع الأرصدة حسب (item_id + item_pack_id) — كل صف يمثل منتج واحد
4. البيانات المعروضة لكل صف:
   - اسم المنتج (name_en)
   - الكود (code)
   - الفئة (category)
   - العلامة التجارية (brand)
   - إجمالي الكمية (total_quantity)
   - الدفيئات النشطة (batches) — مع رقم الدفيئة، التاريخ، الكمية
5. إحصاءات في الأسفل:
   - إجمالي المنتجات: 24
   - إجمالي الكمية: 1,250 وحدة
   - عدد الدفيئات: 5
6. مخطط أعمدة يوضح توزيع العبوات (percentage-based)

**ميزة إضافية — نقل منتجات مباشرة:**
- يوجد زر "نقل منتجات" في أعلى الصفحة
- يفتح نظام النقل المباشر من هذه الصفحة

---

### 1.4 نقل منتجات بين مخازن (من صفحة المخزن)

**السيناريو:** يوجد زبدة زيتية في المخزن أ، ونريد نقل جزء منها إلى المخزن ب.

**الخطوة 1: اختيار المنتجات:**
1. من صفحة `warehouse/show`، يضغط على "نقل منتجات"
2. يفتح `POST /warehouses/{warehouse}/transfer/store-selection-from-warehouse`
   - يرسل `selected_items` (مصفوفة batch_item_id)
   - النظام يتحقق أن العناصر تنتمي لهذا المخزن وله رصيد > 0
3. يحفظ في الجلسة:
   ```php
   session([
       'warehouse_transfer' => [
           'source_warehouse_id' => $warehouse->id,           // المخزن أ
           'selected_batch_item_ids' => [15, 22, 37],         // العناصر
       ]
   ]);
   ```
4. يعيد التوجيه إلى `GET /warehouses/{warehouse}/transfer/confirm-from-warehouse`

**الخطوة 2: تحديد الكميات والوجهة:**
1. يعرض جدول بالعناصر المختارة:
   - اسم المنتج، الكمية المتوفرة (available_quantity)
   - حقل إدخال الكمية لكل واحد
   - قائمة المخازن المتاحة (باستثناء المصدر)
2. يحدد:
   - المخزن الهدف (target_warehouse_id)
   - الكمية لكل عنصر (يجب أن تكون ≤ available_quantity)
   - تاريخ النقل، ملاحظات
3. يضغط "تأكيد النقل" → `POST /warehouses/{warehouse}/transfer/execute`

**العملية الداخلية للنقل (transferExecute):**
```php
DB::transaction(function () use ($warehouse, $targetWarehouseId, $selectedIds, $validated) {
    foreach ($selectedIds as $batchItemId) {
        $qty = $validated['quantities'][$batchItemId] ?? 0;
        if ($qty <= 0) continue;

        // 1. خصم من المصدر
        $fromStock = BatchItemStock::where('batch_item_id', $batchItemId)
            ->where('warehouse_id', $warehouse->id)->first();
        $fromStock->decrement('quantity', $qty);

        // 2. إضافة للهدف (firstOrNew)
        $toStock = BatchItemStock::firstOrNew([
            'batch_item_id' => $batchItemId,
            'warehouse_id' => $targetWarehouseId,
        ]);
        $toStock->quantity += $qty;
        $toStock->save();

        // 3. تسجيل الحركة
        BatchMovement::create([
            'batch_item_id' => $batchItemId,
            'from_warehouse_id' => $warehouse->id,     // المصدر
            'to_warehouse_id' => $targetWarehouseId,    // الهدف
            'movement_type' => 'transfer',
            'quantity' => $qty,
            'notes' => $validated['notes'],
        ]);
    }
});
session()->forget('warehouse_transfer');  // مسح الجلسة
```

**النتيجة:**
- ينقص الرصيد من المخزن أ
- يزداد الرصيد في المخزن ب
- يُسجل في `batch_movements` بنوع "transfer"
- يعاد إلى صفحة المخزن

---

### 1.5 نقل بين المخازن (من صفحة الدفيئة)

**السيناريو:** تم نقل دفيئة كاملة أو جزء منها بين مخازن مختلفة لحركة مخزنية.

**الخطوة 1: اختيار المصدر والعناصر:**
1. من صفحة الدفيئة → "نقل بين المخازن"
2. `GET /production-batches/{batch}/transfer` → `transferForm()`
   - يعرض قائمة بالمخازن كـ Radio Buttons
   - جدول العناصر في الدفيئة (مع أرصدة كل مخزن)
   - checkboxes لاختيار العناصر
3. يحدد:
   - المخزن المصدر
   - العناصر المراد نقلها
4. يضغط "التالي" → `POST /production-batches/{batch}/transfer`:
   ```php
   // Step 1 — حفظ في الجلسة
   session(['transfer_step1' => [
       'source_warehouse_id' => $request->source_warehouse_id,
       'transfer_date' => $request->transfer_date,
       'notes' => $request->notes,
   ]]);
   session(['transfer_step2' => [
       'selected_item_ids' => $request->selected_items,
   ]]);
   ```
   - يعيد للصفحة نفسها (أماكنه في الخطوة 2)

**الخطوة 2: تحديد الوجهة والكميات:**
1. يعرض:
   - المخزن الهدف (Radio buttons)
   - جدول بالعناصر المختارة مع available_quantity في المصدر
   - حقل كمية لكل عنصر
2. يحدد:
   - المخزن الهدف
   - الكميات
3. يضغط "تنفيذ النقل" → POST (Step 2):
   ```php
   DB::transaction(function () use ($batch, $sourceId, $targetId, $quantities) {
       foreach ($quantities as $batchItemId => $quantity) {
           $batchItem = $batch->batchItems->find($batchItemId);
           
           // خصم من المصدر
           $fromStock = BatchItemStock::where('batch_item_id', $batchItem->id)
               ->where('warehouse_id', $sourceId)->first();
           $fromStock->decrement('quantity', $quantity);

           // إضافة للهدف
           $toStock = BatchItemStock::firstOrNew([...]);
           $toStock->quantity += $quantity;
           $toStock->save();

           // تسجيل الحركة
           BatchMovement::create([
               'batch_item_id' => $batchItem->id,
               'from_warehouse_id' => $sourceId,
               'to_warehouse_id' => $targetId,
               'movement_type' => 'transfer',
               ...
           ]);
       }
   });
   session()->forget(['transfer_step1', 'transfer_step2']);
   ```

**الاختلاف بين النوعين:**
- **Warehouse Transfer** — يعمل على مستوى المنتج/الدفيئة بغض النظر عن الدفيئة الأصلية، ويأتي من صفحة المخزن
- **Production Batch Transfer** — يقتصر على عناصر دفيئة معينة، ويأتي من صفحة الدفيئة

---

### 1.6 استعلام عن الحركة بين المخازن

**السيناريو:** المدير يريد مراجعة جميع عمليات النقل التي تمت خلال الأسبوع الماضي.

**خطوات العملية:**
```
Dashboard → Data Definitions → Warehouse & Vehicles → Batch Movements
```

1. يفتح `GET /warehouse-movements` → `WarehouseMovementController::index()`
2. النظام يجلب كل `BatchMovement` مع eager loading:
   - `batchItem.item`، `fromWarehouse`، `toWarehouse`، `batchItem.batch`
3. يُجمع الحركات حسب المفتاح المركب:
   `movement_type | from_warehouse | to_warehouse | movement_date | batch_id`
4. يعرض جدولاً مجمعاً — كل صف يمثل مجموعة نقل:
   - رقم الدفيئة (batch_number)
   - المصدر → الوجهة (from_warehouse → to_warehouse)
   - التاريخ
   - عدد العناصر (items_count)
   - إجمالي الكمية (total_quantity)
5. **فلاتر متوفرة:**
   - movement_type (dropdown: in / transfer / expiry_return)
   - from_warehouse_id (dropdown)
   - to_warehouse_id (dropdown)
   - date_from / date_to
   - بحث نصي (notes، batch_number، name_en, name_ar, code)
6. **إحصاءات في الأعلى:**
   - إجمالي المجموعات: 25
   - وارد (in): 18
   - تحويل (transfer): 5
   - استرجاع صلاحية: 2
7. **تصدير إلى Excel:** زر "Export" يفتح `GET /warehouse-movements/export` → `MovementsExport`

---

### 1.7 إرجاع دفيئة منتهية الصلاحية

**السيناريو:** انتهت صلاحية بعض الدفيئات في المخزن، ونريد إرجاعها.

**خطوات العملية:**
```
Dashboard → Production Batches → "Expired Batches"
```

1. `GET /production-batches/expired` → `ProductionBatchController::expiredBatches()`
2. يجلب كل `BatchItemStock` حيث:
   - `batch.expiry_date < today`
   - `batch.status = active`
   - `stock.quantity > 0`
3. يعرض جدولاً بـ:
   - رقم الدفيئة + تاريخ الانتهاء
   - اسم المنتج + العبوة
   - المخزن + الكمية المتوفرة
4. يمكن اختيار دفيئات وإرفاقها بملاحظة إرجاع
5. `POST /production-batches/return-expired` → `returnExpired()`:
   - يغيّر حالة الدفيئة إلى `expired`
   - يدون حركة `movement_type = expiry_return`

---

## 2. تطبيق العملاء

### 2.1 تسجيل دخول العميل (Web)

**السيناريو:** عميل يفتح التطبيق على متصفح الهاتف ويريد تسجيل الدخول.

1. العميل يفتح `https://blue-beaver-923812.hostingersite.com/mobile/login`
2. يظهر `app_android/login.blade.php` — نموذج مع username + password
3. عند الإرسال → `POST /mobile/login`:
   ```php
   $response = Http::withoutVerifying()->post('https://blue-beaver-923812.hostingersite.com/api/customer/login', [
       'username' => $request->username,
       'password' => $request->password,
   ]);
   
   if ($response->successful()) {
       $remoteUser = $data['customer'];
       
       // 1. إنشاء/تحديث المستخدم محلياً
       $customer = Customer::updateOrCreate(['username' => $remoteUser['username']], [...]);
       
       // 2. تسجيل الجلسة
       Auth::guard('customer')->login($customer);
       Session::put('api_token', $data['token']);  // حفظ التوكن
       
       // 3. إعادة توجيه
       return redirect()->route('customer.dashboard');
   }
   ```
4. **آلية Fallback:** إذا فشل الـ API (offline) → يعرض رسالة خطأ

---

### 2.2 تصفح المنتجات والعثور على واحد

**السيناريو:** العميل يتصفح المنتجات ويبحث عن منتج معين.

1. من الـ Dashboard (`customer_dashboard.blade.php`):
   - بنرات إعلانية (Banners) — أعلى الصفحة
   - فئات (Categories) — شريحة أفقية
   - علامات تجارية (Brands) — شريحة أفقية
   - أفضل منتجات (Products) — شبكة 2×2

2. العميل يضغط على "فئة" → `GET /customer/categories/{id}`:
   - `CustomerAppController::categoriesShow($id)`
   - يحاول `Http::get(".../api/customer/categories/{$id}")`
   - إذا فشل → Fallback للـ Local DB: `Item::where('category_id', $id)`
   - يعرض `app_android/categories/show.blade.php`
   - كل منتج يحمل زر مفضلة ♡ (مُملوء إذا كان موجوداً في `CustomerFavorite`)

3. العميل يضغط على أحد المنتجات → `GET /customer/product/{id}`:
   - `CustomerAppController::productShow($id)`
   - API → `GET /api/customer/products/{id}`
   - Fallback → `Item::find($id)`
   - يعرض: صور، أسماء (عربي/إنجليزي)، عبوات (packs)، سعر الافتراضي
   - زر إضافة للسلة + زر مفضلة

---

### 2.3 إضافة منتج للمفضلة

**السيناريو:** العميل يريد حفظ منتج مفضل له.

1. العميل يكون على أي صفحة منتجات (categories/show، brands/show، products/index، product/show)
2. يضغط على أيقونة المفضلة (♡) للمنتج المطلوب
3. يرسل طلب AJAX:
   ```javascript
   fetch('{{ route("customer.favorite.toggle") }}', {
       method: 'POST',
       headers: {
           'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
           'Content-Type': 'application/json',
       },
       body: JSON.stringify({ item_id: productId })
   })
   .then(r => r.json())
   .then(data => {
       // data.is_favorite: true/false
       // يحدّث الأيقونة بصرياً
   });
   ```
4. الـ Backend (`toggleFavorite()`):
   ```php
   $favorite = $customer->favorites()->where('item_id', $itemId)->first();
   if ($favorite) {
       $favorite->delete();          // إزالة
       return json(['is_favorite' => false]);
   } else {
       $customer->favorites()->create(['item_id' => $itemId]);  // إضافة
       return json(['is_favorite' => true]);
   }
   ```
5. **ملاحظة:** المفضلة تُخزن **محلياً فقط** في `customer_favorites` table — لا تُرسل إلى الـ Hostinger API.

---

### 2.4 عرض صفحة المفضلة

**السيناريو:** العميل يفتح صفحة "المفضلة" لرؤية منتجاته المحفوظة.

1. `GET /customer/favorite` → `CustomerAppController::favorites()`
2. النظام:
   - يجلب `favoriteItemIds` من: `$customer->favorites()->pluck('item_id')`
   - **يحاول API أولاً:** `Http::get(".../api/customer/products")` ثم يفلترها بالـ favoriteItemIds
   - **Fallback:** `Item::whereIn('id', $favoriteItemIds)` — يجلبها محلياً
3. يعرض `app_android/favorite.blade.php`:
   - شبكة منتجات مفضلة مع صور، أسماء، أسعار
   - زر إزالة من المفضلة × لكل منتج
   - إذا كانت المفضلة فارغة → زر "تصفح المنتجات الآن"

---

### 2.5 تحديث بيانات العميل الشخصية

**السيناريو:** العميل يريد تغيير اسمه أو رفع صورة أفتار جديدة.

1. من الـ Bottom Nav → "المزيد" → "معلوماتي"
2. `GET /customer/my-info` → `CustomerAppController::myInfo()`
   - يعرض نموذج بـ: الاسم، البريد الإلكتروني، اسم المستخدم، حقل رفع الأفتار
3. العميل يغير البيانات ويضغط "حفظ" → `POST /customer/my-info`
4. `updateInfo()`:
   - إرسال البيانات للـ Hostinger API عبر `Http::withToken($token)->post(".../customer/profile/update")`
   - إذا كان هناك ملف أفتار → إرفاقه بالطلب
   - **مزامنة محلياً:** إذا نجح API → يحدث العميل في الـ Local DB
   - إذا فشل API → يعرض الأخطاء من الـ server validation

---

### 2.6 عرض الإشعارات

**السيناريو:** عميل جديد يفتتح التطبيق وله إشعارات.

1. من الـ Bottom Nav → "المزيد" → "الإشعارات"
2. `GET /customer/notifications` → `CustomerAppController::notificationsIndex()`
3. النظام:
   - يحاول API: `Http::withToken($token)->get(".../api/customer/notifications")`
   - يعرض الإشعارات مع: العنوان (subject_ar)، النص (body_ar)، الأيقونة، الحالة (مقروء/غير مقروء)
   - **Fallback:** جلب من `Notification` model المحلي بنفس الفلاتر (whereJsonContains('customers', 'all'))
4. كل إشعار يحمل أيقونة وهمتينها (⭐) لتثقيفه

---

### 2.7 عرض "نبذة عنا" (About Us)

**السيناريو:** عميل يريد معرفة المزيد عن الشركة.

1. من الـ Bottom Nav → "المزيد" → "نبذة عنا"
2. `GET /customer/about-us` → `CustomerAppController::aboutUsWeb()`
3. النظام:
   - إذا كان العميل مسجّلاً → يجلب `OrganizationAbout` المرتبط بـ `active_organization_id`
   - إذا غير مسجّل → يجلب أي `OrganizationAbout` نشطة أولاً
   - **Fallback:** `organization_about` model مباشرة
4. يعرض `app_android/more/about_us.blade.php`:
   - صورة الغلاف + شعار
   - العنوان، الوصف
   - الرؤية (vision) والمهمة (mission)
   - القيم (values) — كبطاقات
   - الإحصاءات (statistics) — كبطاقات أرقام (مثلاً: 15+ سنة خبرة، 500+ منتج)

---

### 2.8 تطبيق PWA الثابت (Static HTML/JS)

**السيناريو:** المستخدم يفتح التطبيق PWA من هاتفه.

1. المتصفح يفتح `https://blue-beaver-923812.hostingersite.com/customer-app/index.html`
2. صفحة تسجيل الدخول (`index.html`):
   - يدخل username + password
   - `API.login()` → `POST /api/customer/login` على الـ Hostinger
   - يحفظ التوكن في `localStorage['customer_token']`
   - يعيد توجيه إلى `dashboard.html`
3. الـ `dashboard.html`:
   - `API.getDashboard()` → `GET /api/customer/dashboard`
   - يعرض Carousel إعلاني (هارد كود — **ليس من الـ API**)
   - يعرض الفئات والعلامات والمنتجات
   - **ملاحظة:** هذه النسخة ما زالت في بداية التطوير — لا تدعم المفضلة أو التفاعل مع API التوكن

---

## 3. إدارة المحتوى عبر لوحة الإدارة

### 3.1 إدارة البنرات الإعلانية

**السيناريو:** المسوق يريد إضافة بنر إعلاني جديد للعرض الخاص.

1. `Dashboard → Customer App Properties → Banners → "إضافة"`
2. `BannerController::create()` → نموذج:
   - العنوان (title)
   - صورة البنر (رفع ملف)
   - الرابط (url) — زر النقر يفتحه
   - "إظهار في تطبيق العملاء" (show_in_customer_app — checkbox)
   - ترتيب العرض (order)
3. `BannerController::store()` → حفظ في `banners` table
4. يظهر البنر في:
   - **Web App:** `customer_dashboard.blade.php` — يُحمّل من API Dashboard endpoint
   - **PWA:** ليس مدمجاً بعد (الكروتيلار هارد كود)

---

### 3.2 إرسال إشعارات للعملاء

**السيناريو:** المدير يريد إرسال إشعار "بدء العرض الخاص" لجميع العملاء.

1. `Dashboard → Customer App Properties → Send Notification → "إضافة"`
2. `NotificationController::create()` → نموذج:
   - العنوان (subject_en/ar)
   - النص (body_en/ar)
   - صورة (اختياري)
   - القناة (channel) — all / push / inapp
   - المستلمون (customers) — JSON array (يمكن "all" أو [1, 5, 12] لمعرفات محددة)
   - أيقونة (icon_type) — notification / order / offer / system
   - جدولة الإرسال (scheduled_at)
3. `NotificationController::store()` → حفظ في `notifications`
   - الحقل `is_sent` يبدأ false
4. يجب تفعيل الإرسال يدوياً أو عبر scheduler → ضبط `is_sent = true`
5. العملاء يرونه في:
   - **Web App:** `notificationsIndex()` — يجلب من API أو Fallback local query
   - **PWA:** لم يتم التكامل بعد

---

### 3.3 إدارة الـ FAQ

**السيناريو:** إضافة سؤال جديد للأسئلة الشائعة.

1. `Dashboard → Customer App Properties → FAQ → "إضافة"`
2. `FaqController::create()` → نموذج بسيط:
   - الفئة (category) — مثلاً: "الدفع"، "التوصيل"
   - السؤال (question)
   - الإجابة (answer) — نص كامل
3. `FaqController::store()` → حفظ في `faqs`
4. يعرض في:
   - **Web App:** `faqIndex()` — يجلب من API أو Fallback `Faq::all()` → يُجمع حسب category → `more/faq.blade.php`

---

## 4. تدفقات المصادقة والهوية

### 4.1 تدفق مصادقة المشرف (Admin)

```
المشرف يفتح /admin
    ↓
Auth::guard('web') — يتحقق من جلسة Laravel
    ↓
إذا مسجل → /dashboard
إذا غير مسجل → /login (LoginController)
    ↓
بعد التسجيل → Session ثابت + Spatie Role
    ↓
Sidebar يعرض البطاقات حسب الصلاحيات (roles.permissions)
```

### 4.2 تدفق مصادقة العميل (Customer — Web)

```
العميل يفتح / (User-Agent = mobile)
    ↓
Redirect إلى /mobile/login
    ↓
العميل يدخل username + password
    ↓
POST /mobile/login
    ├── يرسل إلى Hostinger API (https://.../api/customer/login)
    │   ├── إذا نجح → يحصل على: token + customer data + organizations
    │   └── يحفظ: Session('api_token') + Local Customer record
    └─ يبدأ جلسة Auth::guard('customer')
    ↓
Redirect إلى /customer/dashboard (middleware: check.customer.session)
    ↓
CheckCustomerSession يتحقق من:
    ├── Auth::guard('customer')->check() OR
    └── Session::has('api_token')
    (أي أحدهما يكفي — مرن جداً للـ WebView)
```

### 4.3 تدفق مصادقة العميل (Customer — PWA/JS)

```
العميل يفتح customer-app/index.html
    ↓
التحقق من localStorage['customer_token']
    ├── موجود → redirect dashboard.html
    └── غير موجود → عرض نموذج الدخول
    ↓
API.login() → POST /api/customer/login (Bearer Token)
    ↓
يحفظ token في localStorage['customer_token']
    ↓
يعيد توجيه إلى dashboard.html
```

**ملحوظة أمنية:** PWA لا يمتلك Session Cookies — يعتمد كلياً على localStorage token، مما يعني عدم وجود حماية CSRF أو Session Hijacking protection.

---

## 5. جدول المصطلحات

| المصطلح | التعريف |
|---------|---------|
| Batch | دفيئة إنتاج (ProductionBatch) — مجموعة متماثلة من المنتج بنفس المواصفات |
| Batch Item | عنصر داخل الدفيئة — ربط بين Item و Pack و Batch مع الكمية |
| Batch Item Stock | رصيد كمية موجودة لعنصر الدفيئة في مخزن محدد |
| Movement | سجل عملية نقل/استلام لكمية من/إلى مخزن |
| Dispatch | كمية صُرفت من الدفيئة (تم توزيعها على مخازن) |
| Transfer | نقل بين مخزنين (from → to) |
| Expiry Return | إرجاع منتج منتهي الصلاحية |

---

*تم إعداد هذا الدليل بناءً على تحليل الكود المصدري. كل خطوة موثقة بملفات و line numbers في التقرير السابق.*