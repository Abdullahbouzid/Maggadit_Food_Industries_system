<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PackageStatusController;
use App\Http\Controllers\VendorController;



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
});

// require __DIR__.'/auth.php'; // تم إيقاف هذا السطر لتجنب التعارض مع مسارات تسجيل الدخول المخصصة
