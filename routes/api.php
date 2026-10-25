<?php

use App\Http\Controllers\Api\CustomerApiController;
use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('customer')->name('api.customer.')->group(function () {
    Route::post('/login', [CustomerAuthController::class, 'login']);
    Route::post('/register', [CustomerAuthController::class, 'register']);

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
    Route::get('/contact-info', [CustomerApiController::class, 'contactInfo']);
    Route::post('/contact-us', [CustomerApiController::class, 'contactUs']);

    // مسارات محمية (تتطلب توكن)
    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [CustomerAuthController::class, 'logout']);
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::post('/profile/update', [ProfileController::class, 'updateProfile']);

        Route::get('/dashboard', [CustomerApiController::class, 'dashboard']);
        Route::get('/financial-statement', [CustomerApiController::class, 'financialStatement']);
        Route::get('/orders', [CustomerApiController::class, 'ordersIndex']);
        Route::get('/orders/{id}', [CustomerApiController::class, 'orderDetails']);
        Route::get('/order-history', [CustomerApiController::class, 'orderHistory']);
        Route::get('/returns', [CustomerApiController::class, 'returnsIndex']);
        Route::get('/my-products', [CustomerApiController::class, 'myProducts']);
        Route::get('/orders/{id}/pdf', [CustomerApiController::class, 'downloadOrderPdf']);
        Route::get('/invoices/{id}/pdf', [CustomerApiController::class, 'downloadInvoicePdf']);
        Route::get('/orders/{id}/quotation-pdf', [CustomerApiController::class, 'downloadQuotationPdf']);
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

        // الفروع (branches) - للعميل
        Route::get('/branches', [CustomerApiController::class, 'branchesIndex']);
        Route::post('/branches', [CustomerApiController::class, 'branchesStore']);
        Route::put('/branches/{id}', [CustomerApiController::class, 'branchesUpdate']);
        Route::delete('/branches/{id}', [CustomerApiController::class, 'branchesDestroy']);
        Route::get('/favorites', [CustomerApiController::class, 'favorites']);
        Route::get('/favorites/ids', [CustomerApiController::class, 'favoriteIds']);
        Route::post('/favorites/toggle', [CustomerApiController::class, 'toggleFavorite']);
        Route::get('/invoices', [CustomerApiController::class, 'invoices']);
        Route::get('/invoices/{id}', [CustomerApiController::class, 'invoiceDetail'])->name('api.customer.invoice.detail');
        Route::get('/orders/{order}/quotation', [CustomerApiController::class, 'orderQuotation'])->name('api.customer.order.quotation');
        Route::post('/orders/{order}/quotation/respond', [CustomerApiController::class, 'respondToQuotation'])->name('api.customer.order.quotation.respond');
        Route::post('/checkout', [CustomerApiController::class, 'checkout'])->name('api.customer.checkout');
    });
});
