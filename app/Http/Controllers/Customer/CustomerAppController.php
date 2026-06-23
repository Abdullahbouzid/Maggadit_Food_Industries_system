<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CustomerAppController extends Controller
{
    private $baseUrl = 'https://blue-beaver-923812.hostingersite.com/api';

    /**
     * عرض لوحة التحكم الرئيسية (Dashboard)
     */
    public function dashboard()
    {
        try {
            $response = Http::withoutVerifying()->get("{$this->baseUrl}/customer/dashboard");
            if ($response->successful()) {
                $data = $response->json();
                $categories = json_decode(json_encode($data['categories']));
                $brands = json_decode(json_encode($data['brands']));
                $products = json_decode(json_encode($data['products']));
                return view('app_android.customer_dashboard', compact('categories', 'brands', 'products'));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Dashboard API Error: " . $e->getMessage());
        }

        return view('app_android.customer_dashboard', [
            'categories' => collect(),
            'brands' => collect(),
            'products' => collect(),
        ]);
    }

    /**
     * عرض قائمة الفئات
     */
    public function categoriesIndex()
    {
        $response = Http::get("{$this->baseUrl}/customer/categories");
        $categories = $response->successful() ? json_decode(json_encode($response->json()['categories'])) : collect();
        return view('app_android.categories.index', compact('categories'));
    }

    /**
     * عرض فئة محددة مع منتجاتها
     */
    public function categoriesShow($id)
    {
        $response = Http::get("{$this->baseUrl}/customer/categories/{$id}");
        if ($response->successful()) {
            $data = $response->json();
            $category = json_decode(json_encode($data['category']));
            $items = json_decode(json_encode($category->items ?? []));
            return view('app_android.categories.show', compact('category', 'items'));
        }
        abort(404);
    }

    /**
     * عرض قائمة الماركات
     */
    public function brandsIndex()
    {
        $response = Http::get("{$this->baseUrl}/customer/brands");
        $brands = $response->successful() ? json_decode(json_encode($response->json()['brands'])) : collect();
        return view('app_android.brands.index', compact('brands'));
    }

    /**
     * عرض ماركة محددة مع منتجاتها
     */
    public function brandsShow($id)
    {
        $response = Http::get("{$this->baseUrl}/customer/brands/{$id}");
        if ($response->successful()) {
            $data = $response->json();
            $brand = json_decode(json_encode($data['brand']));
            $items = json_decode(json_encode($brand->items ?? []));
            return view('app_android.brands.show', compact('brand', 'items'));
        }
        abort(404);
    }

    /**
     * عرض قائمة جميع المنتجات
     */
    public function productsIndex()
    {
        $response = Http::get("{$this->baseUrl}/customer/products");
        $items = $response->successful() ? json_decode(json_encode($response->json()['products']['data'])) : collect();
        return view('app_android.products.index', compact('items'));
    }
}
