<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;    // افتراض وجود موديل Product
use App\Models\Offer;      // افتراض وجود موديل Offer
use Illuminate\Http\Request;

class CustomerAppController extends Controller
{
    /**
     * عرض لوحة التحكم الرئيسية (Dashboard)
     */
    public function dashboard()
    {
        // جلب الفعاليات التي تظهر في تطبيق الزبون فقط
        $categories = Category::where('show_in_customer_app', true)
                              ->limit(6)  // نفس عدد العناصر الثابتة سابقاً
                              ->get();

        // جلب الماركات التي تظهر في تطبيق الزبون
        $brands = Brand::where('show_in_customer_app', true)
                       ->limit(5)
                       ->get();

        // جلب أفضل المنتجات (مثال: أحدث 4 منتجات)
        // $products = Product::latest()->limit(4)->get();

        // جلب العروض الفعالة (مثال: تاريخ انتهاء أكبر من اليوم)
        // $offers = Offer::where('expiry_date', '>', now())->limit(2)->get();

        return view('app_android.customer_dashboard', compact('categories', 'brands',
        //  'products', 'offers'
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
     * عرض فئة محددة مع منتجاتها
     */
    public function categoriesShow($id)
    {
        $category = Category::with('products')->findOrFail($id);
        return view('app_android.categories.show', compact('category'));
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
     * عرض ماركة محددة مع منتجاتها
     */
    public function brandsShow($id)
    {
        $brand = Brand::with('products')->findOrFail($id);
        return view('app_android.brands.show', compact('brand'));
    }

    /**
     * عرض قائمة المنتجات
     */
    // public function productsIndex()
    // {
    //     $products = Product::paginate(12);
    //     return view('app_android.products.index', compact('products'));
    // }

    /**
     * عرض قائمة العروض
     */
    // public function offersIndex()
    // {
    //     $offers = Offer::where('is_active', true)->paginate(10);
    //     return view('app_android.offers.index', compact('offers'));
    // }
}