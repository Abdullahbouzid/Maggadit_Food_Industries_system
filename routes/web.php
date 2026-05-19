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






Route::get('/', function () {
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



    // Custom routes for organizations must be defined before the resource route
    Route::get('organizations/export', [OrganizationController::class, 'export'])->name('organizations.export');
    Route::get('organizations/template', [OrganizationController::class, 'downloadTemplate'])->name('organizations.template');
    Route::post('organizations/import', [OrganizationController::class, 'import'])->name('organizations.import');
    // Resource routes for organizations
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


Route::resource('employees', EmployeeController::class);
});

// require __DIR__.'/auth.php'; // تم إيقاف هذا السطر لتجنب التعارض مع مسارات تسجيل الدخول المخصصة
