<?php

use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\Api\CustomerApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Customer Mobile App
|--------------------------------------------------------------------------
|
| هذه المسارات مخصصة لتطبيق العميل (الموبايل) وتستخدم Sanctum
| للتوثيق عبر التوكن.
|
*/

// ========== مسارات عامة (بدون توثيق) ==========
Route::prefix('customer')->name('api.customer.')->group(function () {

    // تسجيل الدخول
    Route::post('/login', [CustomerAuthController::class, 'login'])->name('login');

    // مسارات عامة يمكن الوصول إليها بدون تسجيل دخول
    Route::get('/categories', [CustomerApiController::class, 'categoriesIndex'])->name('categories.index');
    Route::get('/categories/{id}', [CustomerApiController::class, 'categoriesShow'])->name('categories.show');
    Route::get('/brands', [CustomerApiController::class, 'brandsIndex'])->name('brands.index');
    Route::get('/brands/{id}', [CustomerApiController::class, 'brandsShow'])->name('brands.show');
    Route::get('/products', [CustomerApiController::class, 'productsIndex'])->name('products.index');
    Route::get('/products/{id}', [CustomerApiController::class, 'productsShow'])->name('products.show');
});

// ========== مسارات محمية (تتطلب توثيق) ==========
Route::prefix('customer')->name('api.customer.')->middleware('auth:sanctum')->group(function () {

    // تسجيل الخروج
    Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');

    // الملف الشخصي
    Route::get('/profile', [CustomerAuthController::class, 'profile'])->name('profile');

    // لوحة التحكم
    Route::get('/dashboard', [CustomerApiController::class, 'dashboard'])->name('dashboard');
});
