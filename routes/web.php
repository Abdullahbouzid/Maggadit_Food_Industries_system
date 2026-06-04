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
use App\Http\Controllers\ItemTypeController;
use App\Http\Controllers\UomController;
use App\Http\Controllers\PackOperationController;
use App\Http\Controllers\PackGroupController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ItemPackController;
use App\Http\Controllers\ItemImageController;
use App\Http\Controllers\ItemModelController;
use App\Http\Controllers\ItemPackPackGroupController;
use App\Http\Controllers\ItemPackPackOperationController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\WarehouseTypeController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\VehicleTypeController;
use App\Http\Controllers\VehicleController;


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
    Route::post('/logout', [LoginController::class, 'logout_submit'])->name('logout_submit');

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
    Route::get('categories/template', [CategoryController::class, 'downloadTemplate'])->name('categories.template');
    Route::post('categories/import', [CategoryController::class, 'import'])->name('categories.import');

    // Item Types (Admin)
    Route::resource('item-types', ItemTypeController::class);
    Route::get('item-types/export', [ItemTypeController::class, 'export'])->name('item-types.export');
    Route::get('item-types/template', [ItemTypeController::class, 'downloadTemplate'])->name('item-types.template');
    Route::post('item-types/import  ', [ItemTypeController::class, 'import'])->name('item-types.import');

    // UOMs (Admin)

    Route::resource('uoms', UomController::class);
    Route::get('uoms/export/filtered', [UomController::class, 'export'])->name('uoms.export');
    Route::post('uoms/import', [UomController::class, 'import'])->name('uoms.import');
    Route::get('uoms/download-template', [UomController::class, 'downloadTemplate'])->name('uoms.template');

    // Pack Operations (Admin)
        Route::resource('pack-operations', PackOperationController::class);
    Route::get('pack-operations/export/filtered', [PackOperationController::class, 'export'])->name('pack-operations.export');
    Route::post('pack-operations/import', [PackOperationController::class, 'import'])->name('pack-operations.import');
    Route::get('pack-operations/download-template', [PackOperationController::class, 'downloadTemplate'])->name('pack-operations.template');

    // Pack Groups (Admin)
    Route::resource('pack-groups', PackGroupController::class);
    Route::get('pack-groups/export/filtered', [PackGroupController::class, 'export'])->name('pack-groups.export');
    Route::post('pack-groups/import', [PackGroupController::class, 'import'])->name('pack-groups.import');
    Route:: get('pack-groups/download-template', [PackGroupController::class, 'downloadTemplate'])->name('pack-groups.template');

    // Items & Item Packs
    Route::resource('items', ItemController::class);
    Route::get('items/export/data', [ItemController::class, 'export'])->name('items.export');
    Route::post('items/import', [ItemController::class, 'import '])->name('items.import');
    Route::get('items/template/download', [ItemController::class, 'downloadTemplate'])->name('items.template');

    // Item Packs
    Route::resource('item-packs', ItemPackController::class);

    // Item Images & Models
    Route::resource('item-images', ItemImageController::class);

    // Item Models
    Route::resource('item-models', ItemModelController::class);

    // Item Pack - Pack Group & Pack Operation Relations
    Route::resource('item-pack-pack-groups', ItemPackPackGroupController::class)->only(['store', 'destroy']);
    // تم إضافة مسارات إدارة العلاقات بين ItemPack و PackOperation
    Route::resource('item-pack-pack-operations', ItemPackPackOperationController::class)->only(['store', 'destroy']);

    Route::post('item-pack-pack-groups', [ItemPackPackGroupController::class, 'store'])->name('item-pack-pack-groups.store');
    Route::delete('item-pack-pack-groups/{itemPackId}/{packGroupId}', [ItemPackPackGroupController::class, 'destroy'])->name('item-pack-pack-groups.destroy');

    Route::post('item-pack-pack-operations', [ItemPackPackOperationController::class, 'store'])->name('item-pack-pack-operations.store');
    Route::delete('item-pack-pack-operations/{itemPackId}/{packOperationId}', [ItemPackPackOperationController::class, 'destroy'])->name('item-pack-pack-operations.destroy');      


    // Warehouse Types
    Route::get('warehouse-types/export', [WarehouseTypeController::class, 'export'])->name('warehouse-types.export');
    Route::get('warehouse-types/template', [WarehouseTypeController::class, 'downloadTemplate'])->name('warehouse-types.template');
    Route::post('warehouse-types/import', [WarehouseTypeController::class, 'import'])->name('warehouse-types.import');
    Route::resource('warehouse-types', WarehouseTypeController::class);


    // States
    Route::get('states/export', [StateController::class, 'export'])->name('states.export');
    Route::get('states/template', [StateController::class, 'downloadTemplate'])->name('states.template');
    Route::post('states/import', [StateController::class, 'import'])->name('states.import');
    Route::resource('states', StateController::class);
    // Areas
    Route::get('areas/export', [AreaController::class, 'export'])->name('areas.export');
    Route::get('areas/template', [AreaController::class, 'downloadTemplate'])->name('areas.template');
    Route::post('areas/import', [AreaController::class, 'import'])->name('areas.import');
    Route::resource('areas', AreaController::class);

    // Warehouses
    Route::get('warehouses/export', [WarehouseController::class, 'export'])->name('warehouses.export');
    Route::get('warehouses/template', [WarehouseController::class, 'downloadTemplate'])->name('warehouses.template');
    Route::post('warehouses/import', [WarehouseController::class, 'import'])->name('warehouses.import');
    Route::get('warehouses/states/{countryId}', [WarehouseController::class, 'getStatesByCountry'])->name('warehouses.states');
    Route::get('warehouses/cities/{stateId}', [WarehouseController::class, 'getCitiesByState'])->name('warehouses.cities');
    
    Route::get('/warehouses/areas-by-state/{stateId}', [WarehouseController::class, 'getAreasByState']);
    Route::resource('warehouses', WarehouseController::class);

    // Vehicle Types
    Route::get('vehicle-types/export', [VehicleTypeController::class, 'export'])->name('vehicle-types.export');
    Route::get('vehicle-types/template', [VehicleTypeController::class, 'downloadTemplate'])->name('vehicle-types.template');
    Route::post('vehicle-types/import', [VehicleTypeController::class, 'import'])->name('vehicle-types.import');
    Route::resource('vehicle-types', VehicleTypeController::class);

    // Vehicles
    Route::resource('vehicles', VehicleController::class);
    Route::get('vehicles/export/excel', [VehicleController::class, 'export'])->name('vehicles.export');
    Route::post('vehicles/import', [VehicleController::class, 'import'])->name('vehicles.import');
    Route::get('vehicles/template/download', [VehicleController::class, 'downloadTemplate'])->name('vehicles.template');
});