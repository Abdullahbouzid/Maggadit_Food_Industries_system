<?php

use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\Api\CustomerApiController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/testroot', function () {
    return response()->json(['test' => 'Root API working']);
});

Route::prefix('customer')->name('api.customer.')->group(function () {
    // مسارات عامة
    Route::post('/login', [CustomerAuthController::class, 'login']);
    Route::post('/register', [CustomerAuthController::class, 'register']);
    Route::post('/testapi2', function () {
        return response()->json(['test' => 'API working']);
    });

    Route::get('/categories', [CustomerApiController::class, 'categoriesIndex']);
    Route::get('/categories/{id}', [CustomerApiController::class, 'categoriesShow']);
    Route::get('/brands', [CustomerApiController::class, 'brandsIndex']);
    Route::get('/brands/{id}', [CustomerApiController::class, 'brandsShow']);
    Route::get('/products', [CustomerApiController::class, 'productsIndex']);
    Route::get('/products/{id}', [CustomerApiController::class, 'productsShow']);
    Route::get('/offers', [CustomerApiController::class, 'offersIndex']);

    // مسارات محمية (تتطلب توكن)
    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [CustomerAuthController::class, 'logout']);
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::post('/change-password', [ProfileController::class, 'changePassword']);
        Route::get('/dashboard', [CustomerApiController::class, 'dashboard']);
        Route::get('/financial-statement', [CustomerApiController::class, 'financialStatement']);
        Route::get('/surveys', [CustomerApiController::class, 'surveys']);
        Route::post('/surveys/{id}/respond', [CustomerApiController::class, 'submitSurvey']);
        Route::get('/notifications', [CustomerApiController::class, 'notifications']);
        Route::post('/notifications/{id}/read', [CustomerApiController::class, 'markNotificationRead']);
        Route::get('/faq', [CustomerApiController::class, 'faq']);
        Route::get('/privacy-policy', [CustomerApiController::class, 'privacyPolicy']);
        Route::get('/about-us', [CustomerApiController::class, 'aboutUs']);
        Route::get('/contact-us', [CustomerApiController::class, 'contactUs']);
        Route::get('/organizations', [CustomerApiController::class, 'organizationsList']);
        Route::post('/change-organization', [CustomerApiController::class, 'switchOrganization']);
    });
});