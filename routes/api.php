<?php

use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\Api\CustomerApiController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes for Customer App (Android - NativePHP)
|--------------------------------------------------------------------------
|
| هنا يتم استخدام guard 'api' والذي تم إعداده في config/auth.php
| ليعمل عبر Sanctum مع نموذج Customer.
|
*/

Route::prefix('customer')->name('api.customer.')->group(function () {

    // ========== المسارات العامة (لا تحتاج توكن) ==========
    Route::post('/login', [CustomerAuthController::class, 'login']);
    Route::post('/register', [CustomerAuthController::class, 'register']); // اختياري

    // تصفح عام بدون تسجيل دخول
    Route::get('/categories', [CustomerApiController::class, 'categoriesIndex']);
    Route::get('/categories/{id}', [CustomerApiController::class, 'categoriesShow']);
    Route::get('/brands', [CustomerApiController::class, 'brandsIndex']);
    Route::get('/brands/{id}', [CustomerApiController::class, 'brandsShow']);
    Route::get('/products', [CustomerApiController::class, 'productsIndex']);
    Route::get('/products/{id}', [CustomerApiController::class, 'productsShow']);
    Route::get('/offers', [CustomerApiController::class, 'offersIndex']);

    // ========== المسارات المحمية (تتطلب توكن صالح) ==========
    Route::middleware('auth:api')->group(function () {
        // المصادقة والحساب
        Route::post('/logout', [CustomerAuthController::class, 'logout']);
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::post('/change-password', [ProfileController::class, 'changePassword']);

        // لوحة التحكم
        Route::get('/dashboard', [CustomerApiController::class, 'dashboard']);

        // ==========================================================
        // السلة، المفضلة، الطلبات (معلقة حالياً لحين توفر النماذج)
        // ==========================================================
        // Route::get('/cart', [CartController::class, 'index']);
        // Route::post('/cart/add', [CartController::class, 'addItem']);
        // Route::put('/cart/update/{id}', [CartController::class, 'updateItem']);
        // Route::delete('/cart/remove/{id}', [CartController::class, 'removeItem']);
        // Route::delete('/cart/clear', [CartController::class, 'clearCart']);

        // Route::get('/favorites', [FavoriteController::class, 'index']);
        // Route::post('/favorites/add/{productId}', [FavoriteController::class, 'add']);
        // Route::delete('/favorites/remove/{productId}', [FavoriteController::class, 'remove']);

        // Route::get('/orders', [OrderController::class, 'history']);
        // Route::get('/orders/{id}', [OrderController::class, 'show']);
        // Route::post('/orders', [OrderController::class, 'store']);

        // الميزات الأخرى
        Route::get('/financial-statement', [CustomerApiController::class, 'financialStatement']);
        Route::get('/surveys', [CustomerApiController::class, 'surveys']);
        Route::post('/surveys/{id}/respond', [CustomerApiController::class, 'submitSurvey']);
        Route::get('/notifications', [CustomerApiController::class, 'notifications']);
        Route::post('/notifications/{id}/read', [CustomerApiController::class, 'markNotificationRead']);
        Route::get('/faq', [CustomerApiController::class, 'faq']);
        Route::get('/privacy-policy', [CustomerApiController::class, 'privacyPolicy']);
        Route::get('/about-us', [CustomerApiController::class, 'aboutUs']);
        Route::post('/contact-us', [CustomerApiController::class, 'contactUs']);

        // تغيير المؤسسة (المنظمة)
        Route::get('/organizations', [CustomerApiController::class, 'organizationsList']);
        Route::post('/change-organization', [CustomerApiController::class, 'switchOrganization']);
    });
});