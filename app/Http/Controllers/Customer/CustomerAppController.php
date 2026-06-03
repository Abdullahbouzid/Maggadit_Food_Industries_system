<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Item;        // استخدم نموذج Item بدلاً من Product
use App\Models\Offer;
use Illuminate\Http\Request;

class CustomerAppController extends Controller
{
    /**
     * عرض لوحة التحكم الرئيسية (Dashboard)
     */
    public function dashboard()
    {
        $categories = Category::where('show_in_customer_app', true)
                              ->limit(6)
                              ->get();

        $brands = Brand::where('show_in_customer_app', true)
                       ->limit(5)
                       ->get();

        // جلب أحدث 4 منتجات نشطة (is_inactive = false)
        $products = Item::with(['category', 'brand'])
                        ->where('is_inactive', false)
                        ->latest()
                        ->limit(4)
                        ->get();

        // جلب العروض النشطة (يمكنك تعديل حسب هيكل Offers لديك)
        // $offers = Offer::where('is_active', true)
        //                ->where('expiry_date', '>', now())
        //                ->limit(2)
        //                ->get();

        return view('app_android.customer_dashboard', compact('categories', 'brands', 'products', 
        // 'offers'
        ));
    }

    /**
     * عرض قائمة الفئات
     */
    public function categoriesIndex()
    {
        $categories = Category::where('show_in_customer_app', true)->paginate(20);
        return view('app_android.categories.index', compact('categories'));
    }

    /**
     * عرض فئة محددة مع منتجاتها (Items)
     */
    public function categoriesShow($id)
    {
        $category = Category::findOrFail($id);
        $items = Item::where('category_id', $id)
                     ->where('is_inactive', false)
                     ->with(['category', 'brand', 'packs'])
                     ->paginate(12);
        return view('app_android.categories.show', compact('category', 'items'));
    }

    /**
     * عرض قائمة الماركات
     */
    public function brandsIndex()
    {
        $brands = Brand::where('show_in_customer_app', true)->paginate(20);
        return view('app_android.brands.index', compact('brands'));
    }

    /**
     * عرض ماركة محددة مع منتجاتها (Items)
     */
    public function brandsShow($id)
    {
        $brand = Brand::findOrFail($id);
        $items = Item::where('brand_id', $id)
                     ->where('is_inactive', false)
                     ->with(['category', 'brand', 'packs'])
                     ->paginate(12);
        return view('app_android.brands.show', compact('brand', 'items'));
    }

    /**
     * عرض قائمة جميع المنتجات (Items)
     */
    public function productsIndex()
    {
        $items = Item::where('is_inactive', false)
                     ->with(['category', 'brand', 'packs'])
                     ->paginate(12);
        return view('app_android.products.index', compact('items'));
    }

    /**
     * عرض قائمة العروض (إذا كان لديك نموذج Offer)
     */
    // public function offersIndex()
    // {
    //     $offers = Offer::where('is_active', true)
    //                    ->where('expiry_date', '>', now())
    //                    ->paginate(10);
    //     return view('app_android.offers.index', compact('offers'));
    // }
}