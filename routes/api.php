<?php

use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\Api\CustomerApiController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/testroot', function () {
    return response()->json(['test' => 'Root API working']);
});

Route::get('/testxyz', function () {
    return response()->json(['test' => 'Test XYZ API working']);
});

Route::get('/testzzz', function () {
    return response()->json(['test' => 'Test ZZZ API working']);
});

Route::post('/testapianother', function () {
    return response()->json(['test' => 'API working']);
});

Route::prefix('customer')->name('api.customer.')->group(function () {
    // مسارات عامة
    Route::post('/login', [CustomerAuthController::class, 'login']);
    Route::post('/register', [CustomerAuthController::class, 'register']);
    Route::post('/testapi3', function () {
        return response()->json(['test' => 'API working']);
    });

    Route::get('/categories', [CustomerApiController::class, 'categoriesIndex']);
    Route::get('/categories/{id}', [CustomerApiController::class, 'categoriesShow']);
    Route::get('/brands', [CustomerApiController::class, 'brandsIndex']);
    Route::get('/brands/{id}', [CustomerApiController::class, 'brandsShow']);
    Route::get('/products', [CustomerApiController::class, 'productsIndex']);
    Route::get('/products/{id}', [CustomerApiController::class, 'productsShow']);
    Route::get('/offers', [CustomerApiController::class, 'offersIndex']);
    
    // مسارات عامة (بدون توكن)
    Route::get('/faq', [CustomerApiController::class, 'faq']);
    Route::get('/privacy-policy', [CustomerApiController::class, 'privacyPolicy']);
    Route::get('/contact-us', [CustomerApiController::class, 'contactUs']);
    
    // مسارات محمية (تتطلب توكن)
    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [CustomerAuthController::class, 'logout']);
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::post('/profile/update', [ProfileController::class, 'updateProfile']);
      
        Route::get('/dashboard', [CustomerApiController::class, 'dashboard']);
        Route::get('/financial-statement', [CustomerApiController::class, 'financialStatement']);
        Route::get('/surveys', [CustomerApiController::class, 'surveys']);
        Route::get('/surveys/{id}', [CustomerApiController::class, 'getSurvey']);
        Route::post('/surveys/{id}/respond', [CustomerApiController::class, 'submitSurvey']);
        Route::get('/notifications', [CustomerApiController::class, 'notifications']);
        Route::post('/notifications/check-new', [CustomerApiController::class, 'checkNewNotifications']);
        Route::post('/fcm-token', [CustomerApiController::class, 'updateFcmToken']);
        Route::post('/notifications/{id}/read', [CustomerApiController::class, 'markNotificationRead']);
        Route::post('/notifications/{id}/toggle-read', [CustomerApiController::class, 'toggleNotificationRead']);
        Route::post('/notifications/{id}/toggle-star', [CustomerApiController::class, 'toggleNotificationStar']);
        Route::post('/notifications/{id}/toggle-archive', [CustomerApiController::class, 'toggleNotificationArchive']);
        Route::get('/about-us', [CustomerApiController::class, 'aboutUs']);
        Route::get('/organizations', [CustomerApiController::class, 'organizationsList']);
        Route::post('/change-organization', [CustomerApiController::class, 'switchOrganization']);
    });

    // Add test route inside customer group
    Route::get('/testinside', function () {
        return response()->json(['test' => 'Inside group API working']);
    });
});