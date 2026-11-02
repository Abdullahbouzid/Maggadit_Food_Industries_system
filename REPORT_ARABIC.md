# تقرير التعديلات على نظام ماغاديت للصناعات الغذائية

## التاريخ
2 نوفمبر 2026

---

## 1. إصلاح مشكلة عرض الصورة الشخصية في تطبيق APK

### المشكلة
روابط صور العملاء كانت تُنشأ باستخدام الدالة `asset()` في لارافيل، والتي تولد روابط بناءً على `APP_URL` (الإعداد الافتراضي `http://127.0.0.1:8000`). بما أن تطبيق APK يعمل على الهاتف المحمول، فإن `127.0.0.1` تعني الجهاز نفسه وليس جهاز التطوير، مما أدى إلى عدم عرض الصور.

### الحل
تعديل المصادر لاستخدام accessor `$customer->avatar_url` الموجود في نموذج `Customer.php` (السطر 82-93)، والذي يعيد الرابط الصحيح على دومين الاستضافة `https://blue-beaver-923812.hostingersite.com/uploads/customers/`.

### الملفات المعدلة
| الملف | السطر | التعديل |
|-------|-------|----------|
| `resources/views/app_android/more/my_info.blade.php` | 20, 87 | استبدال `asset('uploads/customers/...')` بـ `$customer->avatar_url` |
| `resources/views/app_android/more.blade.php` | 20 | نفس التعديل أعلاه |

---

## 2. نظام الإشعارات الخارجية (Firebase Cloud Messaging)

### الغرض
إضافة دعم الإشعارات الفورية (Push Notifications) لتطبيق الجوال، مع إمكانية إرسال إشعارات من لوحة التحكم للمستخدمين específicos أو لجميع العملاء أو لفروع محددة.

### الملفات الجديدة
| الملف | الغرض |
|-------|-------|
| `app/Services/Customer/FcmNotificationService.php` | خدمة الإرسال عبر FCM تدعم: إرسال لعميل واحد، إرسال للجميع، إرسال لفروع محددة، وتقسيم الرموز (حتي 1000 توكن لكل طلب) |
| `app/Http/Controllers/Customer/NotificationController.php` | المتحكم الذي يدير نقاط النهاية (API endpoints) للإشعارات |

### نقاط النهاية (API Routes)
| المسار | الطريقة | الغرض |
|-------|---------|-------|
| `POST /customer/notification/subscribe` | POST | تسجيل توكن FCM للعميل عند تسجيل الدخول |
| `POST /admin/notifications/send/customer` | POST | إرسال إشعار لعميل محدد |
| `POST /admin/notifications/send/all` | POST | إرسال إشعار لجميع العملاء |
| `POST /admin/notifications/send/branches` | POST | إرسال إشعار لفروع/منظمات محددة |

### الملفات المعدلة للإشعارات
| الملف | التعديل |
|-------|----------|
| `config/nativephp.php` | تفعيل `push_notifications` في قسم الصلاحيات |
| `config/services.php` | إضافة إعدادات FCM (server_key, sender_id) |
| `routes/web.php` | إضافة routes للإشعارات تحت prefix `admin/notifications` |
| `resources/views/app_android/login.blade.php` | إضافة دالة `registerPushNotifications()` لطلب الإذن وتسجيل التوكن بعد تسجيل الدخول |
| `resources/views/dashboard/partials/sidebar.blade.php` | إضافة رابط "إشعارات التطبيق" في قائمة التنقل |

---

## 3. لوحة إدارة الإشعارات (Admin Dashboard)

### الملف الجديد
- `resources/views/dashboard/notifications/index.blade.php` - واجهة كاملة تحتوي على:
  - إحصائيات: إجمالي الإشعارات المرسلة، عدد العملاء المشتركين، عدد أجهزة التسجيل
  - نموذج إرسال إشعار جديد مع خيارات:
    - عميل محدد (بإدخال معرف العميل)
    - جميع العملاء المشتركين
    - فروع محددة (اختيار متعدد)
  - حقول: عنوان الإشعار، نص الإشعار
  - أزرار: إرسال، إلغاء

---

## 4. بناء ملف APK

### الملفات الناتجة
| الملف | المسار | الحجم | النوع |
|-------|--------|-------|-------|
| `maggadit-release2026.apk` | `apk-output/` | 143 ميجابايت | release (موقع) |

### معلومات التوقيع
- ملف الـ keystore: `maggadit-release.keystore`
- الalias: `maggadit`
- كلمة السر: `maggadit2026`

---

## 5. ملخص所有 الملفات المتغيرة والجديدة

### الملفات الجديدة
```
app/Services/Customer/FcmNotificationService.php
app/Http/Controllers/Customer/NotificationController.php
resources/views/dashboard/notifications/index.blade.php
REPORT_ARABIC.md
```

### الملفات المعدلة
```
config/nativephp.php
config/services.php
routes/web.php
resources/views/app_android/login.blade.php
resources/views/app_android/more/my_info.blade.php
resources/views/app_android/more.blade.php
resources/views/dashboard/partials/sidebar.blade.php
```

---

## 6. تحذير هام قبل الرفع إلى GitHub

### ملفات APK بحجم كبير
يتم تجاهل ملفات APK في `.gitignore` (تمت إضافة `*.apk` و `apk-output/`)، لتجنب رفض GitHub لملفات تزيد عن 100 ميجابايت.

### إعداد FCM
يجب وضع مفتاح خادم Firebase (FCM_SERVER_KEY) في ملف `.env` قبل التشغيل الإنتاجي، 그렇지 لن تعمل الإشعارات.

---

## 7. أوامر التحقق
```bash
# فحص بناء PHP لجميع الملفات المعدلة
php -l resources/views/app_android/more/my_info.blade.php
php -l resources/views/app_android/more.blade.php
php -l resources/views/dashboard/notifications/index.blade.php
php -l app/Services/Customer/FcmNotificationService.php
php -l app/Http/Controllers/Customer/NotificationController.php
php -l routes/web.php
php -l config/nativephp.php
php -l config/services.php
php -l resources/views/app_android/login.blade.php
```

---