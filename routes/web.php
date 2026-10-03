<?php

use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CompetitorController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\Customer\CustomerCatalogController;
use App\Http\Controllers\Customer\CustomerDashboardController;
use App\Http\Controllers\Customer\CustomerOrderController;
use App\Http\Controllers\Customer\CustomerReturnController;
use App\Http\Controllers\Customer\CustomerStatementController;
use App\Http\Controllers\Customer\CustomerPagesController;
use App\Http\Controllers\Customer\CustomerProfileController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ItemImageController;
use App\Http\Controllers\ItemModelController;
use App\Http\Controllers\ItemPackController;
use App\Http\Controllers\ItemPackPackGroupController;
use App\Http\Controllers\ItemPackPackOperationController;
use App\Http\Controllers\ItemTypeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Offer\OfferController;
use App\Http\Controllers\OrganizationAboutController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PackageStatusController;
use App\Http\Controllers\PackGroupController;
use App\Http\Controllers\PackOperationController;
use App\Http\Controllers\ProductionBatchController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SalesOrderTypeController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\UomController;
use App\Http\Controllers\PackTypeController;
use App\Http\Controllers\Settings\PrivacyPolicyController;
use App\Http\Controllers\PackCapacityController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehicleTypeController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WarehouseMovementController;
use App\Http\Controllers\WarehouseTypeController;
use App\Models\Customer;
use App\Models\Offer;
use App\Models\PrivacyPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes (Browser)
|--------------------------------------------------------------------------
| هذه المسارات تخدم متصفح الويب فقط (سطح المكتب أو الموبايل عبر المتصفح).
| وهي تختلف تماماً عن مسارات API المخصصة لتطبيق NativePHP.
| مسارات API موجودة في ملف routes/api.php
*/

// ========== الجزء الأول: تطبيق العميل (الويب) ==========
// جميع مسارات العميل تبدأ بـ /customer وتستخدم جلسات PHP العادية

Route::get('/mobile/login', function () {
    return view('app_android.login');
})->name('mobile.login');

Route::get('/mobile/register', function () {
    return view('app_android.register');
})->name('mobile.register');

Route::post('/mobile/register', function (Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'username' => 'required|string|unique:customers',
        'email' => 'required|email|unique:customers',
        'password' => 'required|string|min:6',
    ]);

    // إرسال طلب التسجيل للاستضافة الحقيقية
    try {
        $response = Http::withoutVerifying()->post('https://blue-beaver-923812.hostingersite.com/api/customer/register', [
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password,
            'password_confirmation' => $request->password, // مهم لتجاوز التحقق confirmed في الاستضافة
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $remoteUser = $data['customer'];

            // إنشاء المستخدم محلياً في الـ APK لمزامنة الجلسة
            $customer = Customer::create([
                'name' => $remoteUser['name'],
                'username' => $remoteUser['username'],
                'email' => $remoteUser['email'],
                'password' => Hash::make($request->password),
            ]);

            Auth::guard('customer')->login($customer);
            $request->session()->regenerate();
            $request->session()->put('api_token', $data['token']); // حفظ التوكن للطلبات القادمة
            $request->session()->put('customer_id', $customer->id); // ربط الجلسة بالعميل المحلي

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'redirect' => route('customer.dashboard', [], false), 'token' => $data['token']]);
            }

            return redirect()->route('customer.dashboard');
        }

        // في حال وجود أخطاء تحقق من السيرفر
        $errors = $response->json('errors') ?? ['error' => ['فشل التسجيل في السيرفر']];
        if ($request->expectsJson()) {
            return response()->json(['errors' => $errors], 422);
        }

        return back()->withErrors($errors);

    } catch (Exception $e) {
        // خطأ في الاتصال
        if ($request->expectsJson()) {
            return response()->json(['errors' => ['error' => ['فشل الاتصال بالسيرفر']]], 422);
        }

        return back()->withErrors(['error' => 'فشل الاتصال بالسيرفر']);
    }
})->name('mobile.register.submit');

Route::post('/mobile/login', function (Request $request) {
    $request->validate([
        'username' => 'required',
        'password' => 'required',
    ]);

    // إرسال طلب الدخول للاستضافة الحقيقية
    try {
        $response = Http::withoutVerifying()->post('https://blue-beaver-923812.hostingersite.com/api/customer/login', [
            'username' => $request->username,
            'password' => $request->password,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $remoteUser = $data['customer'];

            // إنشاء أو تحديث المستخدم محلياً في الـ APK لمزامنة الجلسة
            $customer = Customer::updateOrCreate(
                ['username' => $remoteUser['username']],
                [
                    'name' => $remoteUser['name'],
                    'email' => $remoteUser['email'],
                    'password' => Hash::make($request->password),
                ]
            );

            Auth::guard('customer')->login($customer);
            $request->session()->regenerate();
            $request->session()->put('api_token', $data['token']); // حفظ التوكن للطلبات القادمة
            $request->session()->put('customer_id', $customer->id); // ربط الجلسة بالعميل المحلي

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'redirect' => route('customer.dashboard', [], false), 'token' => $data['token']]);
            }

            return redirect()->route('customer.dashboard');
        }
    } catch (Exception $e) {
        // خطأ في الاتصال
    }

    if ($request->expectsJson()) {
        return response()->json(['message' => 'فشل الاتصال بالسيرفر أو بيانات خاطئة'], 422);
    }

    return back()->withErrors(['username' => 'بيانات الدخول غير صحيحة أو السيرفر غير متاح.']);
})->name('mobile.login.submit');

Route::get('/customer/logout', function (Request $request) {
    Auth::guard('customer')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('mobile.login');
})->name('customer.logout');

// الصفحة الرئيسية توجه بناءً على الجهاز (ويب أو تطبيق)
Route::get('/', function (Request $request) {
    $userAgent = strtolower($request->header('User-Agent'));

    // التحقق مما إذا كان الطلب قادماً من تطبيق الموبايل (APK) أو متصفح هاتف
    if (str_contains($userAgent, 'android') || str_contains($userAgent, 'mobile') || str_contains($userAgent, 'wv')) {
        return redirect()->route('mobile.login');
    }

    // إذا كان من متصفح الويب العادي (سطح المكتب)
    return redirect('/admin');
});

// مجموعة مسارات العميل (تستخدم middleware للتحقق من الجلسة)
Route::prefix('customer')->name('customer.')->group(function () {

    // مسارات عامة (لا تحتاج جلسة) - لعرض البيانات للمستخدم غير المسجل
    Route::get('/categories', [CustomerCatalogController::class, 'categoriesIndex'])->name('categories.index');
    Route::get('/categories/{id}', [CustomerCatalogController::class, 'categoriesShow'])->name('categories.show');
    Route::get('/brands', [CustomerCatalogController::class, 'brandsIndex'])->name('brands');
    Route::get('/brands/{id}', [CustomerCatalogController::class, 'brandsShow'])->name('brands.show');
    Route::get('/product/{id}', [CustomerCatalogController::class, 'productShow'])->name('product.show');
    Route::get('/products', [CustomerCatalogController::class, 'productsIndex'])->name('products');
    Route::get('/showoffers', [CustomerCatalogController::class, 'offersIndex'])->name('offer');

    // ===================================================================
    // مسارات APK العامة التي لا تعرض أي بيانات عميل
    // ===================================================================
    Route::get('/change-organization', function () {
        return view('app_android.more.change_organization');
    })->name('change_organization');

    // ===================================================================
    // مسارات Proxy للـ APK - تستخدم توكن الجلسة للاتصال بالسيرفر الخارجي
    // هذه المسارات تحل محل استدعاء API المباشر من المتصفح بالتوكن
    // ===================================================================
    Route::prefix('proxy')->name('proxy.')->group(function () {
        $baseUrl = 'https://blue-beaver-923812.hostingersite.com/api';

        // جلب قائمة الاستبيانات
        Route::get('/surveys', function () use ($baseUrl) {
            $token = session('api_token');
            if (! $token) {
                return response()->json(['success' => false, 'message' => 'غير مصرح'], 401);
            }
            try {
                $response = Http::withToken($token)->withoutVerifying()
                    ->get("{$baseUrl}/customer/surveys");

                return response()->json($response->json(), $response->status());
            } catch (Exception $e) {
                return response()->json(['success' => false, 'message' => 'فشل الاتصال بالخادم'], 503);
            }
        })->name('surveys.index');

        // جلب تفاصيل استبيان
        Route::get('/surveys/{id}', function ($id) use ($baseUrl) {
            $token = session('api_token');
            if (! $token) {
                return response()->json(['success' => false, 'message' => 'غير مصرح'], 401);
            }
            try {
                $response = Http::withToken($token)->withoutVerifying()
                    ->get("{$baseUrl}/customer/surveys/{$id}");

                return response()->json($response->json(), $response->status());
            } catch (Exception $e) {
                return response()->json(['success' => false, 'message' => 'فشل الاتصال بالخادم'], 503);
            }
        })->name('surveys.show');

        // إرسال إجابات استبيان
        Route::post('/surveys/{id}/respond', function (Request $request, $id) use ($baseUrl) {
            $token = session('api_token');
            if (! $token) {
                return response()->json(['success' => false, 'message' => 'غير مصرح'], 401);
            }
            try {
                $response = Http::withToken($token)->withoutVerifying()
                    ->post("{$baseUrl}/customer/surveys/{$id}/respond", $request->all());

                return response()->json($response->json(), $response->status());
            } catch (Exception $e) {
                return response()->json(['success' => false, 'message' => 'فشل الاتصال بالخادم'], 503);
            }
        })->name('surveys.respond');

        // جلب قائمة المنظمات
        Route::get('/organizations', function () use ($baseUrl) {
            $token = session('api_token');
            if (! $token) {
                return response()->json(['success' => false, 'message' => 'غير مصرح'], 401);
            }
            try {
                $response = Http::withToken($token)->withoutVerifying()
                    ->get("{$baseUrl}/customer/organizations");

                return response()->json($response->json(), $response->status());
            } catch (Exception $e) {
                return response()->json(['success' => false, 'message' => 'فشل الاتصال بالخادم'], 503);
            }
        })->name('organizations.index');

        // تبديل المنظمة
        Route::post('/change-organization', function (Request $request) use ($baseUrl) {
            $token = session('api_token');
            if (! $token) {
                return response()->json(['success' => false, 'message' => 'غير مصرح'], 401);
            }
            try {
                $response = Http::withToken($token)->withoutVerifying()
                    ->post("{$baseUrl}/customer/change-organization", $request->all());

                return response()->json($response->json(), $response->status());
            } catch (Exception $e) {
                return response()->json(['success' => false, 'message' => 'فشل الاتصال بالخادم'], 503);
            }
        })->name('organizations.switch');

        // Proxy للمنتجات والعروض والماركات والتصنيفات والمفضلة
        Route::get('/categories', function () use ($baseUrl) {
            try {
                $response = Http::withoutVerifying()->get("{$baseUrl}/customer/categories");
                return response()->json($response->json(), $response->status());
            } catch (Exception $e) {
                return response()->json(['success' => false, 'message' => 'فشل الاتصال'], 503);
            }
        })->name('categories');

        Route::get('/brands', function () use ($baseUrl) {
            try {
                $response = Http::withoutVerifying()->get("{$baseUrl}/customer/brands");
                return response()->json($response->json(), $response->status());
            } catch (Exception $e) {
                return response()->json(['success' => false, 'message' => 'فشل الاتصال'], 503);
            }
        })->name('brands');

        Route::get('/products', function (Request $request) use ($baseUrl) {
            try {
                $response = Http::withoutVerifying()->get("{$baseUrl}/customer/products", $request->all());
                return response()->json($response->json(), $response->status());
            } catch (Exception $e) {
                return response()->json(['success' => false, 'message' => 'فشل الاتصال'], 503);
            }
        })->name('products');

        Route::get('/offers', function () use ($baseUrl) {
            try {
                $response = Http::withoutVerifying()->get("{$baseUrl}/customer/offers");
                return response()->json($response->json(), $response->status());
            } catch (Exception $e) {
                return response()->json(['success' => false, 'message' => 'فشل الاتصال'], 503);
            }
        })->name('offers');

        Route::get('/favorites', function () use ($baseUrl) {
            $token = session('api_token') ?: request()->bearerToken();
            if (!$token) {
                return response()->json(['success' => false, 'favorites' => []]);
            }
            try {
                $response = Http::withToken($token)->withoutVerifying()->get("{$baseUrl}/customer/favorites");
                return response()->json($response->json(), $response->status());
            } catch (Exception $e) {
                return response()->json(['success' => false, 'favorites' => []], 503);
            }
        })->name('favorites');
        Route::post('/favorites/toggle', function (Request $request) use ($baseUrl) {
            $token = session('api_token') ?: request()->bearerToken();
            if (!$token) {
                return response()->json(['success' => false, 'message' => 'غير مصرح'], 401);
            }
            try {
                $response = Http::withToken($token)->withoutVerifying()->post("{$baseUrl}/customer/favorites/toggle", $request->all());

                return response()->json($response->json(), $response->status());
            } catch (Exception $e) {
                return response()->json(['success' => false, 'message' => 'فشل الاتصال'], 503);
            }
        })->name('favorites.toggle');

        Route::get('/faqs', function () use ($baseUrl) {
            try {
                $response = Http::withoutVerifying()->get("{$baseUrl}/customer/faq");
                return response()->json($response->json(), $response->status());
            } catch (Exception $e) {
                return response()->json(['success' => false, 'message' => 'فشل الاتصال'], 503);
            }
        })->name('faqs');

        Route::get('/notifications', function () use ($baseUrl) {
            $token = session('api_token') ?: request()->bearerToken();
            if (!$token) {
                return response()->json(['success' => false, 'message' => 'غير مصرح'], 401);
            }
            try {
                $response = Http::withToken($token)->withoutVerifying()->get("{$baseUrl}/customer/notifications");
                return response()->json($response->json(), $response->status());
            } catch (Exception $e) {
                return response()->json(['success' => false, 'message' => 'فشل الاتصال'], 503);
            }
        })->name('notifications');

        Route::get('/financial-statement', function () use ($baseUrl) {
            $token = session('api_token') ?: request()->bearerToken();
            if (!$token) {
                return response()->json(['success' => false, 'message' => 'غير مصرح'], 401);
            }
            try {
                $response = Http::withToken($token)->withoutVerifying()->get("{$baseUrl}/customer/financial-statement");
                return response()->json($response->json(), $response->status());
            } catch (Exception $e) {
                return response()->json(['success' => false, 'message' => 'فشل الاتصال'], 503);
            }
        })->name('financial_statement');

        Route::get('/orders', function () use ($baseUrl) {
            $token = session('api_token') ?: request()->bearerToken();
            if (!$token) {
                return response()->json(['success' => false, 'message' => 'غير مصرح'], 401);
            }
            try {
                $response = Http::withToken($token)->withoutVerifying()->get("{$baseUrl}/customer/orders");
                return response()->json($response->json(), $response->status());
            } catch (Exception $e) {
                return response()->json(['success' => false, 'message' => 'فشل الاتصال'], 503);
            }
        })->name('orders');

        Route::get('/order/{id}', function ($id) use ($baseUrl) {
            $token = session('api_token') ?: request()->bearerToken();
            if (!$token) {
                return response()->json(['success' => false, 'message' => 'غير مصرح'], 401);
            }
            try {
                $response = Http::withToken($token)->withoutVerifying()->get("{$baseUrl}/customer/orders/{$id}");
                return response()->json($response->json(), $response->status());
            } catch (Exception $e) {
                return response()->json(['success' => false, 'message' => 'فشل الاتصال'], 503);
            }
        })->name('order_details');

        Route::get('/returns', function () use ($baseUrl) {
            $token = session('api_token') ?: request()->bearerToken();
            if (!$token) {
                return response()->json(['success' => false, 'message' => 'غير مصرح'], 401);
            }
            try {
                $response = Http::withToken($token)->withoutVerifying()->get("{$baseUrl}/customer/returns");
                return response()->json($response->json(), $response->status());
            } catch (Exception $e) {
                return response()->json(['success' => false, 'message' => 'فشل الاتصال'], 503);
            }
        })->name('returns');

        Route::get('/my-products', function () use ($baseUrl) {
            $token = session('api_token') ?: request()->bearerToken();
            if (!$token) {
                return response()->json(['success' => false, 'message' => 'غير مصرح'], 401);
            }
            try {
                $response = Http::withToken($token)->withoutVerifying()->get("{$baseUrl}/customer/my-products");
                return response()->json($response->json(), $response->status());
            } catch (Exception $e) {
                return response()->json(['success' => false, 'message' => 'فشل الاتصال'], 503);
            }
        })->name('my_products');

        Route::get('/dashboard', function () use ($baseUrl) {
            $token = session('api_token') ?: request()->bearerToken();
            if (!$token) {
                return response()->json(['success' => false, 'message' => 'غير مصرح'], 401);
            }
            try {
                $response = Http::withToken($token)->withoutVerifying()->get("{$baseUrl}/customer/dashboard");
                return response()->json($response->json(), $response->status());
            } catch (Exception $e) {
                return response()->json(['success' => false, 'message' => 'فشل الاتصال'], 503);
            }
        })->name('dashboard');
    });
    Route::get('/privacy-policy', function () {
        $policy = PrivacyPolicy::where('type', 'privacy_policy')
            ->where('is_active', true)
            ->latest()
            ->first();

        return view('app_android.more.privacy_policy', compact('policy'));
    })->name('privacy');
    Route::get('/terms-of-service', function () {
        $policy = PrivacyPolicy::where('type', 'terms_of_service')
            ->where('is_active', true)
            ->latest()
            ->first();

        return view('app_android.more.privacy_policy', compact('policy'));
})->name('terms');
    Route::get('/contact-us', function () {
        return view('app_android.more.contact_us');
    })->name('contact');
    Route::post('/contact-us', [CustomerPagesController::class, 'contactUs'])->name('contact.submit');
    Route::get('/contact-info', [CustomerPagesController::class, 'contactInfo'])->name('contact.info');
    Route::get('/about', [CustomerPagesController::class, 'aboutUsWeb'])->name('about');
    Route::get('/faq', [CustomerPagesController::class, 'faqIndex'])->name('faq');

    // مسارات محمية (تتطلب تسجيل دخول بجلسة PHP)
    Route::middleware('check.customer.session')->group(function () {
        Route::get('/dashboard', [CustomerDashboardController::class, 'dashboard'])->name('dashboard');

        Route::get('/notifications', [CustomerProfileController::class, 'notificationsIndex'])->name('notifications');

        // محرّك الإشعارات داخل التطبيق: يعمل بجلسة العميل لأن تطبيق العميل
        // لا يملك Sanctum token (يعمل داخل WebView على 127.0.0.1).
        Route::post('/notifications/check-new', [CustomerProfileController::class, 'notificationsCheckNew'])->name('notifications.check_new');
        Route::post('/notifications/{id}/read', [CustomerProfileController::class, 'notificationMarkRead'])->name('notifications.read');
        Route::post('/notifications/{id}/toggle-read', [CustomerProfileController::class, 'notificationToggleRead'])->name('notifications.toggle_read');
        Route::post('/notifications/{id}/toggle-star', [CustomerProfileController::class, 'notificationToggleStar'])->name('notifications.toggle_star');
        Route::post('/notifications/{id}/toggle-archive', [CustomerProfileController::class, 'notificationToggleArchive'])->name('notifications.toggle_archive');

        Route::get('/surveys', function () {
            return view('app_android.more.surveys');
        })->name('surveys');

        Route::get('/more', [CustomerPagesController::class, 'more'])->name('more');
        Route::get('/offers', function () {
            $offers = \App\Models\Offer::with(['item.packs.packCapacity.uom', 'itemPack.packCapacity.uom'])
                ->active()
                ->get()
                ->map(function ($offer) {
                    $item = $offer->item;
                    $defaultPack = $item ? $item->packs->first() : null;
                    $offerPack = $offer->itemPack;

                    // uom hangs off the pack capacity, not the pack itself.
                    $offerUom = $offerPack?->packCapacity?->uom;
                    $defaultUom = $defaultPack?->packCapacity?->uom;

                    $data = [
                        'id' => $offer->id,
                        'title' => $offer->title,
                        'description' => $offer->description,
                        'item_id' => $offer->item_id,
                        'item_pack_id' => $offer->item_pack_id,
                        'name' => $item ? ($item->name_ar ?: $item->name_en) : 'منتج محذوف',
                        'pack_type' => $offerUom
                            ? ($offerUom->name_ar ?: $offerUom->name_en)
                            : ($defaultUom ? ($defaultUom->name_ar ?: $defaultUom->name_en) : ''),
                        'image' => $offer->display_image ?? ($item ? $item->main_image_url : null),
                        'old_price' => (float) $offer->old_price,
                        'new_price' => (float) $offer->new_price,
                        'discount_percentage' => (float) $offer->discount_percentage,
                        'start_date' => $offer->start_date?->format('Y-m-d H:i'),
                        'end_date' => $offer->end_date?->format('Y-m-d H:i'),
                    ];

                    return (object) $data;
                });

            return view('app_android.offers.index', compact('offers'));
        })->name('offers');
        Route::get('/cart', function () {
            return view('app_android.cart');
        })->name('cart');
        Route::post('/checkout', [CustomerOrderController::class, 'checkout'])->name('checkout');
        Route::get('/favorite', [CustomerProfileController::class, 'favorites'])->name('favorite');
        Route::post('/favorite/toggle', [CustomerProfileController::class, 'toggleFavorite'])->name('favorite.toggle');
        Route::get('/financial-statement', [CustomerStatementController::class, 'index'])->name('financial_statement');
        Route::get('/returns', [CustomerReturnController::class, 'index'])->name('returns');
        Route::get('/returns/create/{invoiceId}', [CustomerReturnController::class, 'create'])->name('returns.create');
        Route::post('/returns/{invoiceId}', [CustomerReturnController::class, 'store'])->name('returns.store');
        Route::get('/my-products', [CustomerReturnController::class, 'products'])->name('my_products');
        Route::get('/order-history', [CustomerOrderController::class, 'orderHistory'])->name('order_history');
        Route::get('/order/{id}', [CustomerOrderController::class, 'orderDetails'])->name('order_details');
        Route::post('/order/{id}/quotation/respond', [CustomerOrderController::class, 'respondToQuotation'])->name('order.quotation.respond');
        Route::get('/order/{id}/pdf', [CustomerOrderController::class, 'downloadOrderPdf'])->name('order_pdf');
        Route::get('/order/{id}/invoice-pdf', [CustomerOrderController::class, 'downloadPdf'])->name('order_invoice_pdf');
        Route::get('/order/{id}/quotation-pdf', [CustomerOrderController::class, 'downloadQuotationPdf'])->name('order_quotation_pdf');
        Route::get('/my-info', [CustomerProfileController::class, 'myInfo'])->name('my_info');
        Route::post('/my-info', [CustomerProfileController::class, 'updateInfo'])->name('my_info.update');
    });
});

Route::get('/offers/get-item-price', [\App\Http\Controllers\Offer\OfferController::class, 'getItemPrice'])->name('offers.get_item_price');

// ========== الجزء الثاني: لوحة تحكم الأدمن (Admin) ==========
// تستخدم Auth العادي (gurad web) الخاص بجدول users

Route::get('/admin', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::get('/lang/{locale}', [\App\Http\Controllers\LanguageController::class, 'switch'])->name('lang.switch');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [LoginController::class, 'logout_submit'])->name('logout');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // الأدوار والصلاحيات
    Route::resource('roles', RolePermissionController::class)->except(['show']);
    Route::get('permissions', [RolePermissionController::class, 'permissionsIndex'])->name('permissions.index');
    Route::get('permissions/create', [RolePermissionController::class, 'createPermission'])->name('permissions.create');
    Route::post('permissions', [RolePermissionController::class, 'storePermission'])->name('permissions.store');
    Route::get('permissions/{permission}/edit', [RolePermissionController::class, 'editPermission'])->name('permissions.edit');
    Route::put('permissions/{permission}', [RolePermissionController::class, 'updatePermission'])->name('permissions.update');
    Route::delete('permissions/{permission}', [RolePermissionController::class, 'destroyPermission'])->name('permissions.destroy');

    // المنظمات وغيرها من الموارد (كلها كما هي)
    Route::get('organizations/export', [OrganizationController::class, 'export'])->name('organizations.export');
    Route::get('organizations/template', [OrganizationController::class, 'downloadTemplate'])->name('organizations.template');
    Route::post('organizations/import', [OrganizationController::class, 'import'])->name('organizations.import');
    Route::resource('organizations', OrganizationController::class);

    // ========== أضف هنا مسارات نبذة المنظمة ==========
    Route::resource('organization-about', OrganizationAboutController::class);

    Route::get('customers/export', [CustomerController::class, 'export'])->name('customers.export');
    Route::post('customers/import', [CustomerController::class, 'import'])->name('customers.import');
    Route::get('customers/template', [CustomerController::class, 'downloadTemplate'])->name('customers.template');
    Route::resource('customers', CustomerController::class);

    Route::get('package-statuses/export', [PackageStatusController::class, 'export'])->name('package-statuses.export');
    Route::get('package-statuses/template', [PackageStatusController::class, 'downloadTemplate'])->name('package-statuses.template');
    Route::post('package-statuses/import', [PackageStatusController::class, 'import'])->name('package-statuses.import');
    Route::resource('package-statuses', PackageStatusController::class);

    Route::resource('vendors', VendorController::class);
    Route::get('vendors/export', [VendorController::class, 'export'])->name('vendors.export');
    Route::get('vendors/template', [VendorController::class, 'downloadTemplate'])->name('vendors.template');
    Route::post('vendors/import', [VendorController::class, 'import'])->name('vendors.import');

    Route::resource('competitors', CompetitorController::class);
    Route::get('competitors/export/data', [CompetitorController::class, 'export'])->name('competitors.export');
    Route::post('competitors/import', [CompetitorController::class, 'import'])->name('competitors.import');
    Route::get('competitors/template/download', [CompetitorController::class, 'downloadTemplate'])->name('competitors.template');

    // المواقع الجغرافية
    Route::prefix('geographical')->name('geographical.')->group(function () {
        Route::get('/', [CountryController::class, 'index'])->name('index');
    });
    Route::resource('countries', CountryController::class)->except(['index']);
    Route::resource('cities', CityController::class);
    Route::resource('sales-order-types', SalesOrderTypeController::class);
    Route::get('sales-order-types/export/data', [SalesOrderTypeController::class, 'export'])->name('sales-order-types.export');
    Route::post('sales-order-types/import', [SalesOrderTypeController::class, 'import'])->name('sales-order-types.import');
    Route::get('sales-order-types/template/download', [SalesOrderTypeController::class, 'downloadTemplate'])->name('sales-order-types.template');

    Route::resource('currencies', CurrencyController::class);
    Route::get('currencies/export/data', [CurrencyController::class, 'export'])->name('currencies.export');
    Route::post('currencies/import', [CurrencyController::class, 'import'])->name('currencies.import');
    Route::get('currencies/template/download', [CurrencyController::class, 'downloadTemplate'])->name('currencies.template');

    Route::resource('brands', BrandController::class);
    Route::get('brands/export/data', [BrandController::class, 'export'])->name('brands.export');
    Route::post('brands/import', [BrandController::class, 'import'])->name('brands.import');

    Route::resource('categories', CategoryController::class);
    Route::get('categories/export', [CategoryController::class, 'export'])->name('categories.export');
    Route::get('categories/template', [CategoryController::class, 'downloadTemplate'])->name('categories.template');
    Route::post('categories/import', [CategoryController::class, 'import'])->name('categories.import');

    Route::resource('faqs', FaqController::class);

    // الاستبيانات
    Route::resource('surveys', SurveyController::class);
    Route::patch('surveys/{survey}/toggle', [SurveyController::class, 'toggle'])->name('surveys.toggle');

    Route::resource('item-types', ItemTypeController::class);
    Route::get('item-types/export', [ItemTypeController::class, 'export'])->name('item-types.export');
    Route::get('item-types/template', [ItemTypeController::class, 'downloadTemplate'])->name('item-types.template');
    Route::post('item-types/import', [ItemTypeController::class, 'import'])->name('item-types.import');

    Route::resource('uoms', UomController::class);
    Route::get('uoms/export/filtered', [UomController::class, 'export'])->name('uoms.export');
    Route::post('uoms/import', [UomController::class, 'import'])->name('uoms.import');
    Route::get('uoms/download-template', [UomController::class, 'downloadTemplate'])->name('uoms.template');

    Route::resource('pack-operations', PackOperationController::class);
    Route::get('pack-operations/export/filtered', [PackOperationController::class, 'export'])->name('pack-operations.export');
    Route::post('pack-operations/import', [PackOperationController::class, 'import'])->name('pack-operations.import');
    Route::get('pack-operations/download-template', [PackOperationController::class, 'downloadTemplate'])->name('pack-operations.template');

    Route::resource('pack-groups', PackGroupController::class);
    Route::get('pack-groups/export/filtered', [PackGroupController::class, 'export'])->name('pack-groups.export');
    Route::post('pack-groups/import', [PackGroupController::class, 'import'])->name('pack-groups.import');
    Route::get('pack-groups/download-template', [PackGroupController::class, 'downloadTemplate'])->name('pack-groups.template');

    Route::resource('pack-types', PackTypeController::class)->except(['show']);
    Route::resource('pack-capacities', PackCapacityController::class)->except(['show']);

    Route::resource('items', ItemController::class);
    Route::get('items/export/data', [ItemController::class, 'export'])->name('items.export');
    Route::post('items/import', [ItemController::class, 'import'])->name('items.import');
    Route::get('items/template/download', [ItemController::class, 'downloadTemplate'])->name('items.template');

    Route::resource('item-packs', ItemPackController::class);
    Route::resource('item-images', ItemImageController::class);
    Route::resource('item-models', ItemModelController::class);
    Route::get('item-models/export', [ItemModelController::class, 'export'])->name('item-models.export');
    Route::get('item-models/template', [ItemModelController::class, 'downloadTemplate'])->name('item-models.template');
    Route::post('item-models/import', [ItemModelController::class, 'import'])->name('item-models.import');
    Route::resource('item-pack-pack-groups', ItemPackPackGroupController::class)->only(['store', 'destroy']);
    Route::resource('item-pack-pack-operations', ItemPackPackOperationController::class)->only(['store', 'destroy']);

    Route::get('warehouse-types/export', [WarehouseTypeController::class, 'export'])->name('warehouse-types.export');
    Route::get('warehouse-types/template', [WarehouseTypeController::class, 'downloadTemplate'])->name('warehouse-types.template');
    Route::post('warehouse-types/import', [WarehouseTypeController::class, 'import'])->name('warehouse-types.import');
    Route::resource('warehouse-types', WarehouseTypeController::class);

    Route::get('states/export', [StateController::class, 'export'])->name('states.export');
    Route::get('states/template', [StateController::class, 'downloadTemplate'])->name('states.template');
    Route::post('states/import', [StateController::class, 'import'])->name('states.import');
    Route::resource('states', StateController::class);

    Route::get('areas/export', [AreaController::class, 'export'])->name('areas.export');
    Route::get('areas/template', [AreaController::class, 'downloadTemplate'])->name('areas.template');
    Route::post('areas/import', [AreaController::class, 'import'])->name('areas.import');
    Route::resource('areas', AreaController::class);

    Route::get('warehouses/export', [WarehouseController::class, 'export'])->name('warehouses.export');
    Route::get('warehouses/template', [WarehouseController::class, 'downloadTemplate'])->name('warehouses.template');
    Route::post('warehouses/import', [WarehouseController::class, 'import'])->name('warehouses.import');
    Route::get('warehouses/states/{countryId}', [WarehouseController::class, 'getStatesByCountry'])->name('warehouses.states');
    Route::get('warehouses/cities/{stateId}', [WarehouseController::class, 'getCitiesByState'])->name('warehouses.cities');
    Route::get('/warehouses/areas-by-state/{stateId}', [WarehouseController::class, 'getAreasByState']);
    Route::resource('warehouses', WarehouseController::class);
    Route::prefix('warehouses')->group(function () {
        Route::get('/{warehouse}/transfer/select', [WarehouseController::class, 'transferSelect'])->name('warehouses.transfer.select');
        Route::post('/{warehouse}/transfer/select', [WarehouseController::class, 'transferStoreSelection'])->name('warehouses.transfer.store-selection');
        Route::get('/{warehouse}/transfer/confirm', [WarehouseController::class, 'transferConfirm'])->name('warehouses.transfer.confirm');
        Route::post('/{warehouse}/transfer/execute', [WarehouseController::class, 'transferExecute'])->name('warehouses.transfer.execute');

        // Warehouse transfer from warehouse show page
        Route::post('/{warehouse}/transfer/store-selection-from-warehouse', [WarehouseController::class, 'transferStoreSelectionFromWarehouse'])->name('warehouses.transfer.store-selection-from-warehouse');
        Route::get('/{warehouse}/transfer/store-selection-from-warehouse', function() {
            return redirect()->back()->with('error', 'يرجى تحديد المنتجات ثم النقر على زر النقل.');
        });
        Route::get('/{warehouse}/transfer/confirm-from-warehouse', [WarehouseController::class, 'transferConfirmFromWarehouse'])->name('warehouses.transfer.confirm-from-warehouse');
    });

    Route::get('warehouse-movements/export', [WarehouseMovementController::class, 'export'])->name('warehouse-movements.export');
    Route::get('warehouse-movements/{warehouse_movement}/export-detail', [WarehouseMovementController::class, 'exportDetail'])->name('warehouse-movements.export-detail');
    Route::get('warehouse-movements/available-items/{warehouse}', [WarehouseMovementController::class, 'getAvailableItems'])->name('warehouse-movements.available-items');
    Route::resource('warehouse-movements', WarehouseMovementController::class);

    Route::resource('production-batches', ProductionBatchController::class);
    Route::get('production-batches/get-packs/{itemId}', [ProductionBatchController::class, 'getPacks'])->name('production-batches.get-packs');
    Route::get('production-batches/{productionBatch}/distribute', [ProductionBatchController::class, 'distributeForm'])->name('production-batches.distribute');
    Route::post('production-batches/{productionBatch}/distribute', [ProductionBatchController::class, 'distribute'])->name('production-batches.distribute.store');
    Route::get('production-batches/{batch}/warehouse-stock/{warehouse}', [ProductionBatchController::class, 'getWarehouseStock'])->name('production-batches.warehouse-stock');

    // نقل الكميات بين المخازن
    Route::get('production-batches/{productionBatch}/transfer', [ProductionBatchController::class, 'transferForm'])->name('production-batches.transfer');
    Route::post('production-batches/{productionBatch}/transfer', [ProductionBatchController::class, 'transferStore'])->name('production-batches.transfer.store');

    // الباتشات منتهية الصلاحية
    Route::get('production-batches/expired', [ProductionBatchController::class, 'expiredBatches'])->name('production-batches.expired');
    Route::post('production-batches/return-expired', [ProductionBatchController::class, 'returnExpired'])->name('production-batches.return-expired');

    Route::get('vehicle-types/export', [VehicleTypeController::class, 'export'])->name('vehicle-types.export');
    Route::get('vehicle-types/template', [VehicleTypeController::class, 'downloadTemplate'])->name('vehicle-types.template');
    Route::post('vehicle-types/import', [VehicleTypeController::class, 'import'])->name('vehicle-types.import');
    Route::resource('vehicle-types', VehicleTypeController::class);

    Route::resource('vehicles', VehicleController::class);
    Route::get('vehicles/export/excel', [VehicleController::class, 'export'])->name('vehicles.export');
    Route::post('vehicles/import', [VehicleController::class, 'import'])->name('vehicles.import');
    Route::get('vehicles/template/download', [VehicleController::class, 'downloadTemplate'])->name('vehicles.template');

    Route::get('devices/export', [DeviceController::class, 'export'])->name('devices.export');
    Route::get('devices/template', [DeviceController::class, 'downloadTemplate'])->name('devices.template');
    Route::post('devices/import', [DeviceController::class, 'import'])->name('devices.import');
    Route::resource('devices', DeviceController::class);

    Route::resource('banners', BannerController::class);
    Route::get('banners/export', [BannerController::class, 'export'])->name('banners.export');

    Route::resource('invoices', InvoiceController::class);
    Route::get('invoices/get-warehouses/{itemId}', [InvoiceController::class, 'getWarehousesForItem'])->name('invoices.warehouses');
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');

    // دورة الفاتورة: موافقة / رفض / تسجيل دفعة (عبر المحفظة أو نقدًا إلخ)
    Route::post('invoices/{invoice}/approve', [InvoiceController::class, 'approve'])->name('invoices.approve');
    Route::post('invoices/{invoice}/reject', [InvoiceController::class, 'reject'])->name('invoices.reject');
    Route::post('invoices/{invoice}/payments', [InvoiceController::class, 'recordPayment'])->name('invoices.payments');

    Route::resource('orders', OrderController::class);
    Route::get('/warehouse/{warehouse}/products', [OrderController::class, 'warehouseProducts'])->name('orders.warehouse.products');
    Route::get('/price-list/{priceList}/price', [OrderController::class, 'priceListPrice'])->name('orders.price-list.price');
    Route::post('/orders/{order}/send-new-quote', [OrderController::class, 'sendNewPriceQuote'])->name('orders.send-new-quote');
    Route::post('/orders/{order}/transition', [OrderController::class, 'transitionStatus'])->name('orders.transition');
    Route::get('/orders/{order}/pdf', [OrderController::class, 'downloadOrderPdf'])->name('orders.pdf');
    Route::get('/orders/{order}/quotation/pdf', [OrderController::class, 'downloadQuotationPdf'])->name('orders.quotation.pdf');

    Route::resource('offers', OfferController::class);

    Route::resource('price-lists', \App\Http\Controllers\Admin\PriceListController::class);
    Route::post('price-lists/{priceList}/toggle-status', [\App\Http\Controllers\Admin\PriceListController::class, 'toggleStatus'])->name('price-lists.toggle-status');
    Route::post('price-lists/{priceList}/versions', [\App\Http\Controllers\Admin\PriceListController::class, 'createVersion'])->name('price-lists.versions.store');
    Route::get('price-lists/{priceList}/versions/{version}/edit', [\App\Http\Controllers\Admin\PriceListController::class, 'editVersion'])->name('price-lists.versions.edit');
    Route::put('price-lists/{priceList}/versions/{version}', [\App\Http\Controllers\Admin\PriceListController::class, 'updateVersion'])->name('price-lists.versions.update');
    Route::delete('price-lists/{priceList}/versions/{version}', [\App\Http\Controllers\Admin\PriceListController::class, 'destroyVersion'])->name('price-lists.versions.destroy');
    Route::get('price-lists/customers', [\App\Http\Controllers\Admin\PriceListController::class, 'customers'])->name('price-lists.customers');
    Route::get('customers/{customer}/returns/{invoice}/create', [\App\Http\Controllers\Admin\CustomerReturnRecordController::class, 'create'])->name('customers.returns.create');
    Route::post('customers/{customer}/returns/{invoice}', [\App\Http\Controllers\Admin\CustomerReturnRecordController::class, 'store'])->name('customers.returns.store');
    Route::post('customers/{customer}/price-list', [\App\Http\Controllers\Admin\PriceListController::class, 'updateCustomerPriceList'])->name('customers.price-list.update');

    // The controller has no create()/edit() actions.
    Route::resource('contact-messages', ContactMessageController::class)->except(['create', 'edit']);
    Route::post('contact-messages/{contactMessage}/forward', [ContactMessageController::class, 'forward'])->name('contact-messages.forward');
    Route::post('contact-messages/{contactMessage}/close', [ContactMessageController::class, 'close'])->name('contact-messages.close');
    Route::post('contact-messages/{contactMessage}/status', [ContactMessageController::class, 'updateStatus'])->name('contact-messages.status');
    Route::post('contact-messages/{contactMessage}/importance', [ContactMessageController::class, 'updateImportance'])->name('contact-messages.importance');

    Route::get('contact-info', [CustomerPagesController::class, 'contactInfoWeb'])->name('contact.info');

    Route::resource('notifications', NotificationController::class);
    Route::post('notifications/{notification}/resend', [NotificationController::class, 'resend'])->name('notifications.resend');
    Route::get('notifications/export', [NotificationController::class, 'export'])->name('notifications.export');

    Route::get('/deliveries', [\App\Http\Controllers\Admin\DeliveryController::class, 'index'])->name('deliveries.index');
    Route::get('/returns', [\App\Http\Controllers\Admin\ReturnController::class, 'index'])->name('returns.index');
    Route::get('/inventory', [\App\Http\Controllers\Admin\InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/movements', [\App\Http\Controllers\Admin\InventoryController::class, 'movements'])->name('inventory.movements');
    Route::get('/inventory/alerts', [\App\Http\Controllers\Admin\InventoryController::class, 'alerts'])->name('inventory.alerts');
    Route::post('/inventory/alerts/notify', [\App\Http\Controllers\Admin\InventoryController::class, 'notifyAlerts'])->name('inventory.alerts.notify');
    Route::get('/inventory/transfers', [\App\Http\Controllers\Admin\WarehouseTransferController::class, 'index'])->name('inventory.transfers');
    Route::post('/inventory/transfers', [\App\Http\Controllers\Admin\WarehouseTransferController::class, 'store'])->name('inventory.transfers.store');
    Route::get('/wallets', [\App\Http\Controllers\Admin\WalletController::class, 'index'])->name('wallets.index');
    Route::get('/settings/payment-deadlines', [\App\Http\Controllers\Admin\PaymentDeadlineController::class, 'index'])->name('payment-deadlines.index');

    Route::resource('privacy-policies', PrivacyPolicyController::class);

    Route::prefix('deliveries')->name('deliveries.')->group(function () {
        Route::get('create', [\App\Http\Controllers\Admin\DeliveryController::class, 'create'])->name('create');
        Route::get('{delivery}', [\App\Http\Controllers\Admin\DeliveryController::class, 'show'])->name('show');
        Route::post('{delivery}/advance-stage', [\App\Http\Controllers\Admin\DeliveryController::class, 'advanceStage'])->name('advance-stage');
    });

    Route::prefix('settings/cancellation-deadlines')->name('cancellation-deadlines.')->group(function () {
        $controller = \App\Http\Controllers\Admin\CancellationDeadlineController::class;

        Route::get('/', [$controller, 'index'])->name('index');
        Route::post('/', [$controller, 'store'])->name('store');
        Route::put('{cancellationDeadline}', [$controller, 'update'])->name('update');
        Route::post('{cancellationDeadline}/toggle', [$controller, 'toggle'])->name('toggle');
        Route::delete('{cancellationDeadline}', [$controller, 'destroy'])->name('destroy');
    });

    Route::post('inventory/adjust', [\App\Http\Controllers\Admin\InventoryController::class, 'adjust'])->name('inventory.adjust');

    Route::prefix('wallets')->name('wallets.')->group(function () {
        Route::post('/', [\App\Http\Controllers\Admin\WalletController::class, 'store'])->name('store');
        Route::get('{wallet}', [\App\Http\Controllers\Admin\WalletController::class, 'show'])->name('show');
        Route::post('{wallet}/deposit', [\App\Http\Controllers\Admin\WalletController::class, 'deposit'])->name('deposit');
    });

    Route::prefix('settings/payment-deadlines')->name('payment-deadlines.')->group(function () {
        Route::get('create', [\App\Http\Controllers\Admin\PaymentDeadlineController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\PaymentDeadlineController::class, 'store'])->name('store');
        Route::get('{paymentDeadline}/edit', [\App\Http\Controllers\Admin\PaymentDeadlineController::class, 'edit'])->name('edit');
        Route::put('{paymentDeadline}', [\App\Http\Controllers\Admin\PaymentDeadlineController::class, 'update'])->name('update');
        Route::delete('{paymentDeadline}', [\App\Http\Controllers\Admin\PaymentDeadlineController::class, 'destroy'])->name('destroy');
        Route::post('{paymentDeadline}/toggle', [\App\Http\Controllers\Admin\PaymentDeadlineController::class, 'toggle'])->name('toggle');
    });

    Route::middleware('guest')->group(function () {
        Route::get('register', [\App\Http\Controllers\Auth\RegisteredUserController::class, 'create'])->name('register');
        Route::post('register', [\App\Http\Controllers\Auth\RegisteredUserController::class, 'store']);

        Route::get('forgot-password', [\App\Http\Controllers\Auth\PasswordResetLinkController::class, 'create'])->name('password.request');
        Route::post('forgot-password', [\App\Http\Controllers\Auth\PasswordResetLinkController::class, 'store'])->name('password.email');

        Route::get('reset-password/{token}', [\App\Http\Controllers\Auth\NewPasswordController::class, 'create'])->name('password.reset');
        Route::post('reset-password', [\App\Http\Controllers\Auth\NewPasswordController::class, 'store'])->name('password.store');
    });

    Route::middleware('auth')->group(function () {
        Route::get('verify-email', [\App\Http\Controllers\Auth\EmailVerificationPromptController::class])->name('verification.notice');
        Route::get('verify-email/{id}/{hash}', [\App\Http\Controllers\Auth\VerifyEmailController::class])->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
        Route::post('email/verification-notification', [\App\Http\Controllers\Auth\EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1')->name('verification.send');
        Route::get('confirm-password', [\App\Http\Controllers\Auth\ConfirmablePasswordController::class, 'show'])->name('password.confirm');
        Route::post('confirm-password', [\App\Http\Controllers\Auth\ConfirmablePasswordController::class, 'store']);
        Route::put('password', [\App\Http\Controllers\Auth\PasswordController::class, 'update'])->name('password.update');
    });
});
