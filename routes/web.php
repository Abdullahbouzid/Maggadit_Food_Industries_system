<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PackageStatusController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\CompetitorController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\SalesOrderTypeController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Customer\CustomerAppController;
use Illuminate\Http\Request;

// ========== Customer App Login ==========
Route::get('/mobile/login', function () {
    return view('app_android.login');
})->name('mobile.login');

Route::post('/mobile/login', function (Request $request) {
    $request->validate([
        'username' => 'required',
        'password' => 'required',
    ]);
    session(['logged_in' => true, 'username' => $request->username]);
    return redirect()->route('customer.dashboard');
})->name('mobile.login.submit');

Route::get('/logout', function () {
    session()->forget('logged_in');
    return redirect()->route('mobile.login');
})->name('logout');

Route::get('/', function () {
    return redirect()->route('mobile.login');
});

// ========== Customer App Routes (Mobile) ==========
Route::prefix('customer')->name('customer.')->group(function () {

    // Public routes (no session required)
    Route::get('/categories', [CustomerAppController::class, 'categoriesIndex'])->name('categories.index');
    Route::get('/categories/{id}', [CustomerAppController::class, 'categoriesShow'])->name('categories.show');

    Route::get('/brands', [CustomerAppController::class, 'brandsIndex'])->name('brands');
    Route::get('/brands/{id}', [CustomerAppController::class, 'brandsShow'])->name('brands.show');

    Route::get('/products', [CustomerAppController::class, 'productsIndex'])->name('products');
    Route::get('/showoffers', [CustomerAppController::class, 'offersIndex'])->name('offer');

    // المسارات المحمية (تتطلب جلسة)
    Route::middleware('check.customer.session')->group(function () {
        Route::get('/dashboard', [CustomerAppController::class, 'dashboard'])->name('dashboard');

        // باقي المسارات كما هي (more, cart, favorite, change-organization ...)
        Route::get('/more', function () {
            return view('app_android.more');
        })->name('more');

        Route::get('/offers', function () {
            return view('app_android.offers');
        })->name('offers');

        Route::get('/cart', function () {
            return view('app_android.cart');
        })->name('cart');

        Route::get('/favorite', function () {
            return view('app_android.favorite');
        })->name('favorite');

        Route::get('/change-organization', function () {
            return view('app_android.more.change_organization');
        })->name('change_organization');

        Route::get('/financial-statement', function () {
            return view('app_android.more.financial_statement');
        })->name('financial_statement');

        Route::get('/change-password', function () {
            return view('app_android.more.change_password');
        })->name('change_password');

        Route::get('/order-history', function () {
            return view('app_android.more.order_history');
        })->name('order_history');

        Route::get('/order/{id}', function ($id) {
            return view('app_android.more.order_details', ['orderId' => $id]);
        })->name('order_details');

        Route::get('/my-info', function () {
            return view('app_android.more.my_info');
        })->name('my_info');

        Route::get('/surveys', function () {
            return view('app_android.more.surveys');
        })->name('surveys');

        Route::get('/notifications', function () {
            return view('app_android.more.notifications');
        })->name('notifications');

        Route::get('/faq', function () {
            return view('app_android.more.faq');
        })->name('faq');

        Route::get('/privacy-policy', function () {
            return view('app_android.more.privacy_policy');
        })->name('privacy');

        Route::get('/about-us', function () {
            return view('app_android.more.about_us');
        })->name('about');

        Route::get('/contact-us', function () {
            return view('app_android.more.contact_us');
        })->name('contact');
    });
});

// ========== Admin Dashboard Routes (Laravel Auth) ==========
Route::get('/admin', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');

    // Roles & Permissions
    Route::resource('roles', \App\Http\Controllers\Admin\RolePermissionController::class)->except(['show']);
    Route::get('permissions', [\App\Http\Controllers\Admin\RolePermissionController::class, 'permissionsIndex'])->name('permissions.index');
    Route::get('permissions/create', [\App\Http\Controllers\Admin\RolePermissionController::class, 'createPermission'])->name('permissions.create');
    Route::post('permissions', [\App\Http\Controllers\Admin\RolePermissionController::class, 'storePermission'])->name('permissions.store');
    Route::get('permissions/{permission}/edit', [\App\Http\Controllers\Admin\RolePermissionController::class, 'editPermission'])->name('permissions.edit');
    Route::put('permissions/{permission}', [\App\Http\Controllers\Admin\RolePermissionController::class, 'updatePermission'])->name('permissions.update');
    Route::delete('permissions/{permission}', [\App\Http\Controllers\Admin\RolePermissionController::class, 'destroyPermission'])->name('permissions.destroy');

    // Organizations
    Route::get('organizations/export', [OrganizationController::class, 'export'])->name('organizations.export');
    Route::get('organizations/template', [OrganizationController::class, 'downloadTemplate'])->name('organizations.template');
    Route::post('organizations/import', [OrganizationController::class, 'import'])->name('organizations.import');
    Route::resource('organizations', OrganizationController::class);

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

    // Geographical
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

    // Brands (Admin)
    Route::resource('brands', BrandController::class);
    Route::get('brands/export/data', [BrandController::class, 'export'])->name('brands.export');
    Route::post('brands/import', [BrandController::class, 'import'])->name('brands.import');

    // Categories (Admin)
    Route::resource('categories', CategoryController::class);
    Route::get('categories/export', [CategoryController::class, 'export'])->name('categories.export');
});