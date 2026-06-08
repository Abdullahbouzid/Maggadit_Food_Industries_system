<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Item;
use Illuminate\Http\Request;

class CustomerApiController extends Controller
{
    /**
     * بيانات لوحة التحكم الرئيسية
     * GET /api/customer/dashboard
     */
    public function dashboard()
    {
        $categories = Category::where('show_in_customer_app', true)
                              ->limit(6)
                              ->get()
                              ->map(function ($category) {
                                  return $this->formatCategory($category);
                              });

        $brands = Brand::where('show_in_customer_app', true)
                       ->limit(5)
                       ->get()
                       ->map(function ($brand) {
                           return $this->formatBrand($brand);
                       });

        $products = Item::with(['category', 'brand', 'packs'])
                        ->where('is_inactive', false)
                        ->latest()
                        ->limit(4)
                        ->get()
                        ->map(function ($item) {
                            return $this->formatProduct($item);
                        });

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories,
                'brands' => $brands,
                'products' => $products,
            ],
        ]);
    }

    /**
     * قائمة الفئات
     * GET /api/customer/categories
     */
    public function categoriesIndex()
    {
        $categories = Category::where('show_in_customer_app', true)
                              ->paginate(20);

        $categories->getCollection()->transform(function ($category) {
            return $this->formatCategory($category);
        });

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * فئة محددة مع منتجاتها
     * GET /api/customer/categories/{id}
     */
    public function categoriesShow($id)
    {
        $category = Category::findOrFail($id);

        $items = Item::where('category_id', $id)
                     ->where('is_inactive', false)
                     ->with(['category', 'brand', 'packs'])
                     ->paginate(12);

        $items->getCollection()->transform(function ($item) {
            return $this->formatProduct($item);
        });

        return response()->json([
            'success' => true,
            'data' => [
                'category' => $this->formatCategory($category),
                'items' => $items,
            ],
        ]);
    }

    /**
     * قائمة الماركات
     * GET /api/customer/brands
     */
    public function brandsIndex()
    {
        $brands = Brand::where('show_in_customer_app', true)
                       ->paginate(20);

        $brands->getCollection()->transform(function ($brand) {
            return $this->formatBrand($brand);
        });

        return response()->json([
            'success' => true,
            'data' => $brands,
        ]);
    }

    /**
     * ماركة محددة مع منتجاتها
     * GET /api/customer/brands/{id}
     */
    public function brandsShow($id)
    {
        $brand = Brand::findOrFail($id);

        $items = Item::where('brand_id', $id)
                     ->where('is_inactive', false)
                     ->with(['category', 'brand', 'packs'])
                     ->paginate(12);

        $items->getCollection()->transform(function ($item) {
            return $this->formatProduct($item);
        });

        return response()->json([
            'success' => true,
            'data' => [
                'brand' => $this->formatBrand($brand),
                'items' => $items,
            ],
        ]);
    }

    /**
     * قائمة جميع المنتجات
     * GET /api/customer/products
     */
    public function productsIndex(Request $request)
    {
        $query = Item::where('is_inactive', false)
                     ->with(['category', 'brand', 'packs']);

        // بحث اختياري
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name_en', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // فلترة حسب الفئة
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // فلترة حسب الماركة
        if ($request->has('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        $items = $query->paginate(12);

        $items->getCollection()->transform(function ($item) {
            return $this->formatProduct($item);
        });

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    /**
     * تفاصيل منتج واحد
     * GET /api/customer/products/{id}
     */
    public function productsShow($id)
    {
        $item = Item::with(['category', 'brand', 'packs.uom', 'images', 'itemType'])
                    ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->formatProductDetailed($item),
        ]);
    }

    // ========== Helper Methods ==========

    private function formatCategory($category)
    {
        return [
            'id' => $category->id,
            'code' => $category->code ?? null,
            'name' => $category->name,
            'division' => $category->division ?? null,
            'image_url' => $category->image ? url('uploads/' . $category->image) : null,
        ];
    }

    private function formatBrand($brand)
    {
        return [
            'id' => $brand->id,
            'name' => $brand->name,
            'logo_url' => $brand->logo ? url('uploads/' . $brand->logo) : null,
        ];
    }

    private function formatProduct($item)
    {
        return [
            'id' => $item->id,
            'code' => $item->code,
            'name_en' => $item->name_en,
            'name_ar' => $item->name_ar,
            'image_url' => $item->image ? url('uploads/' . $item->image) : null,
            'price' => $item->price,
            'old_price' => $item->old_price,
            'rate' => $item->rate,
            'is_new' => $item->is_new,
            'category' => $item->category ? [
                'id' => $item->category->id,
                'name' => $item->category->name,
            ] : null,
            'brand' => $item->brand ? [
                'id' => $item->brand->id,
                'name' => $item->brand->name,
            ] : null,
        ];
    }

    private function formatProductDetailed($item)
    {
        $data = $this->formatProduct($item);

        $data['definition'] = $item->definition;
        $data['item_type'] = $item->itemType ? [
            'id' => $item->itemType->id,
            'name' => $item->itemType->name ?? null,
        ] : null;

        $data['packs'] = $item->packs->map(function ($pack) {
            return [
                'id' => $pack->id,
                'barcode' => $pack->barcode,
                'quantity' => $pack->quantity,
                'weight' => $pack->weight,
                'is_default_pack' => $pack->is_default_pack,
                'uom' => $pack->uom ? [
                    'id' => $pack->uom->id,
                    'name' => $pack->uom->name ?? null,
                ] : null,
            ];
        });

        $data['images'] = $item->images->map(function ($img) {
            return [
                'id' => $img->id,
                'url' => url('uploads/' . $img->image_path),
                'alt_text' => $img->alt_text,
            ];
        });

        return $data;
    }
}
