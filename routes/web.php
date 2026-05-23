<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PackageStatusController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\CompetitorController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\SalesOrderTypeController;
use App\Http\Controllers\CurrencyController;
use Illuminate\Http\Request;

// صفحة تسجيل الدخول (mobile)
Route::get('/', function () {
    return view('app_android.login');
})->name('mobile.login');

Route::get('/customer/dashboard', function () {
    if (!session('logged_in')) return redirect()->route('mobile.login');
    return view('app_android.customer_dashboard');
})->name('customer.dashboard');

Route::get('/customer/more', function () {
    if (!session('logged_in')) return redirect()->route('mobile.login');
    return view('app_android.more');
})->name('customer.more');

Route::get('/customer/offers', function () {
    return view('app_android.offers');
})->name('customer.offers');

Route::get('/categories', function () {
    return view('app_android.categories.index');
})->name('categories.index');

Route::get('/categories/{id}', function ($id) {
    return view('app_android.categories.show', ['categoryId' => $id]);
})->name('categories.show');

Route::get('/customer/brands', fn() => view('app_android.brands.index'))->name('customer.brands');
Route::get('/customer/brands/{id}', fn($id) => view('app_android.brands.show', ['brandId' => $id]))->name('customer.brands.show');

Route::get('/customer/products', function () {
    return view('app_android.products.index');
})->name('customer.products');

Route::get('/customer/showoffers', function () {
    return view('app_android.offers.index');
})->name('customer.offer');



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

Route::get('/customer/offers', function () {
    if (!session('logged_in')) return redirect()->route('mobile.login');
    return view('app_android.offers');
})->name('customer.offers');

Route::get('/customer/cart', function () {
    if (!session('logged_in')) return redirect()->route('mobile.login');
    return view('app_android.cart');
})->name('customer.cart');

Route::get('/customer/favorite', function () {
    if (!session('logged_in')) return redirect()->route('mobile.login');
    return view('app_android.favorite');
})->name('customer.favorite');


Route::get('/admin', function () {
    return redirect()->route('login');
});

// Routes for guests (غير مسجل الدخول)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Routes for authenticated users (بعد تسجيل الدخول)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');

    // ====================
    // ROLES & PERMISSIONS MANAGEMENT (بدون prefix)
    // ====================
    Route::resource('roles', \App\Http\Controllers\Admin\RolePermissionController::class)->except(['show']);
    
    Route::get('permissions', [\App\Http\Controllers\Admin\RolePermissionController::class, 'permissionsIndex'])->name('permissions.index');
    Route::get('permissions/create', [\App\Http\Controllers\Admin\RolePermissionController::class, 'createPermission'])->name('permissions.create');
    Route::post('permissions', [\App\Http\Controllers\Admin\RolePermissionController::class, 'storePermission'])->name('permissions.store');
    Route::get('permissions/{permission}/edit', [\App\Http\Controllers\Admin\RolePermissionController::class, 'editPermission'])->name('permissions.edit');
    Route::put('permissions/{permission}', [\App\Http\Controllers\Admin\RolePermissionController::class, 'updatePermission'])->name('permissions.update');
    Route::delete('permissions/{permission}', [\App\Http\Controllers\Admin\RolePermissionController::class, 'destroyPermission'])->name('permissions.destroy');

    // Custom routes for organizations
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

    // Geographical routes
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

});