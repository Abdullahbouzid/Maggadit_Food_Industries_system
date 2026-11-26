# تطبيق عملاء ماغديت — Maggadit Customer App

تطبيق Flutter كامل لواجهة العميل، متصل بـ Laravel API عبر `routes/api.php` (بادئة `/api/customer`).
الواجهة عربية بالكامل (RTL) وتتبع الهوية البصرية المستخدمة في `resources/views/app_android`
والنماذج الجاهزة في `design/mockups/customer_app/`.

---

## المحتويات

| المجلد | الوصف |
| --- | --- |
| `lib/core/` | الإعدادات (`config.dart`)، عميل Dio (`api_client.dart`)، التخزين (`session_store.dart`)، التنسيقات (`formatters.dart`)، فتح الملفات (`file_opener.dart`) |
| `lib/models/` | نماذج البيانات ومحلّلات JSON المطابقة لاستجابات الـ API |
| `lib/services/` | خدمات الكتالوج والطلبات والاشتراكات والإشعارات و Firebase |
| `lib/state/` | متحكّمات `provider` (الجلسة، السلة، المفضلة، الإشعارات) وحاوية التبعيات |
| `lib/ui/screens/` | 29 شاشة (القائمة أدناه) |
| `lib/ui/widgets/` | مكوّنات مشتركة: بطاقات، هيكل الصفحة، عناصر النموذج، حالات الفراغ والخطأ |
| `test/` | اختبارات النماذج والسلة والتنسيقات (16 اختباراً) |

## الشاشات

1. البداية (Splash)
2. تسجيل الدخول — مع إمكانية تعديل عنوان الخادم
3. إنشاء حساب
4. الهيكل الرئيسي وشريط التنقل السفلي (5 تبويبات)
5. الرئيسية (بانرات، تصنيفات، ماركات، منتجات، عروض)
6. المنتجات — بحث، تصفية بالتصنيف/الماركة، تحميل صفحات متتابع
7. تفاصيل المنتج — معرض صور، اختيار العبوة، المفضلة، الإضافة للسلة
8. التصنيفات والماركات (`TaxonomyScreen`)
9. العروض
10. المفضلة
11. السلة
12. إتمام الطلب
13. طلباتي
14. تفاصيل الطلب — خطوات، جدول زمني، عرض السعر (موافقة/رفض)، تنزيل PDF
15. الإشعارات — قراءة/تمييز/أرشفة
16. المحفظة وكشف الحساب
17. الفواتير
18. تفاصيل الفاتورة + تنزيل PDF
19. المرتجعات
20. الاستبيانات
21. تعبئة الاستبيان
22. الأسئلة الشائعة
23. من نحن
24. تواصل معنا (اتصال/واتساب/بريد)
25. سياسة الخصوصية
26. الملف الشخصي (تعديل البيانات وكلمة المرور)
27. المنظمات/الفروع (تبديل المنظمة النشطة)
28. المزيد
29. منتجاتي — الأصناف المشتراة مع الكميات والدفعات

## التشغيل

```bash
cd mobile/customer_app
flutter pub get

# محاكي Android (10.0.2.2 = 127.0.0.1 على الجهاز المضيف)
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api

# جهاز حقيقي على نفس الشبكة
flutter run --dart-define=API_BASE_URL=http://192.168.1.20:8000/api

# الإنتاج
flutter run --dart-define=API_BASE_URL=https://app.maggadit.ly/api
```

على iOS Simulator استخدم `http://127.0.0.1:8000/api`.

### بناء APK

```bash
flutter build apk --release --dart-define=API_BASE_URL=https://app.maggadit.ly/api
# أو ملف تعريفات جاهز:
cp firebase.env.example.json firebase.env.json   # ثم عبّئ القيم
flutter build apk --release --dart-define-from-file=firebase.env.json
```

> `android:usesCleartextTraffic="true"` مُفعّل للتطوير على HTTP. أزله عند الانتقال إلى HTTPS فقط.

## ربط الإشعارات (Firebase Cloud Messaging)

الخادم يرسل الإشعارات عبر `App\Services\Customer\FcmNotificationService` (HTTP v1):

- القناة على أندرويد: `maggadit_channel` ويجب أن تطابق `AppConfig.androidChannelId`.
- حزمة `data` تتضمن `type`, `order_id`, `click_action`, `timestamp`.
- التطبيق ينشئ قناة محلية بأهمية `Importance.max` ثم يعرض إشعاراً منبثقاً في المقدمة (foreground)،
  ويوجّه الضغط على الإشعار إلى الشاشة المناسبة (الطلب/الفاتورة/الإشعارات).

### إعداد التطبيق

**الطريقة الأولى (بدون ملفات أصلية):** مرّر قيم Firebase عبر `--dart-define`:

```bash
flutter run \
  --dart-define=API_BASE_URL=https://app.maggadit.ly/api \
  --dart-define=FIREBASE_API_KEY=... \
  --dart-define=FIREBASE_APP_ID=1:...:android:... \
  --dart-define=FIREBASE_SENDER_ID=... \
  --dart-define=FIREBASE_PROJECT_ID=maggaditfoodsystem
```

**الطريقة الثانية (native):** ضع `android/app/google-services.json`
(و`ios/Runner/GoogleService-Info.plist` لـ iOS). يوجد قالب جاهز في
`android/app/google-services.json.example`. إضافة Gradle لـ `com.google.gms.google-services`
مشروطة بوجود الملف، فلن يتعطّل البناء بدونه.

### على الخادم

```
FCM_PROJECT_ID=maggaditfoodsystem
FCM_CLIENT_EMAIL=firebase-adminsdk-...@maggaditfoodsystem.iam.gserviceaccount.com
FCM_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----\n"
```

(انظر `config/fcm.php`.)

## نقاط الـ API المستخدمة

| الشاشة | المسار |
| --- | --- |
| الدخول/الخروج/التسجيل | `POST /customer/login`, `/customer/logout`, `/customer/register` |
| الملف الشخصي | `GET /customer/profile`, `POST /customer/profile/update` |
| الرئيسية | `GET /customer/dashboard` |
| المنتجات | `GET /customer/products`, `/customer/products/{id}` |
| التصنيفات/الماركات | `GET /customer/categories`, `/customer/brands` |
| العروض | `GET /customer/offers` |
| المفضلة | `GET /customer/favorites`, `/favorites/ids`, `POST /favorites/toggle` |
| الطلبات | `POST /customer/checkout`, `GET /customer/orders`, `/orders/{id}`, `/order-history` |
| عرض السعر | `GET /customer/orders/{order}/quotation`, `POST .../quotation/respond` |
| المحفظة | `GET /customer/financial-statement` |
| الفواتير | `GET /customer/invoices`, `/invoices/{id}`, `/invoices/{id}/pdf` |
| المرتجعات | `GET /customer/returns` |
| الاستبيانات | `GET /customer/surveys`, `/surveys/{id}`, `POST /surveys/{id}/respond` |
| الإشعارات | `GET /customer/notifications`, `POST /check-new`, `POST /{id}/toggle-read` … |
| المحتوى | `GET /customer/faq`, `/about-us`, `/contact-info`, `/privacy-policy`, `POST /contact-us` |
| المنظمات | `GET /customer/organizations`, `POST /customer/change-organization`, `/branches` |

## الاختبارات والتحقق

```bash
flutter analyze          # 0 أخطاء
flutter test             # 16 اختباراً
flutter build web        # تحقّق من الترجمة الكاملة
```

`flutter build apk` يحتاج Android SDK (غير متوفّر في بيئة التطوير الحالية).
