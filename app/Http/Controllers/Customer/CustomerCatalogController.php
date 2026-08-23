<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use App\Models\Offer;
use App\Services\Customer\ImagePathService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CustomerCatalogController extends Controller
{
    use CustomerAppHelpers;

    public function __construct(ImagePathService $imagePathService)
    {
        $this->imagePathService = $imagePathService;
    }

    public function categoriesIndex()
    {
        $categories = Category::where('show_in_customer_app', true)
            ->get()
            ->map(function ($category) {
                $category->products_count = $category->items()->where('is_inactive', false)->count();

                return $this->applyImagePath($category, 'image');
            });

        return view('app_android.categories.index', compact('categories'));
    }

    public function categoriesShow($id)
    {
        $category = Category::where('show_in_customer_app', true)
            ->with(['items' => function ($q) {
                $q->where('is_inactive', false);
            }])
            ->findOrFail($id);

        $category = $this->applyImagePath($category, 'image');
        $category->products_count = $category->items->count();

        $items = $category->items->map(function ($item) {
            $item = $this->applyImagePath($item, 'image');
            return $this->normalizeItem($item);
        });

        $customer = $this->getCustomer(request());
        $favoriteItemIds = $customer ? $customer->favorites()->pluck('item_id')->toArray() : [];

        return view('app_android.categories.show', compact('category', 'items', 'favoriteItemIds'));
    }

    public function brandsIndex()
    {
        $brands = Brand::where('show_in_customer_app', true)
            ->get()
            ->map(function ($brand) {
                $brand->products_count = $brand->items()->where('is_inactive', false)->count();

                return $this->applyImagePath($brand, 'logo');
            });

        return view('app_android.brands.index', compact('brands'));
    }

    public function brandsShow($id)
    {
        $brand = Brand::with(['items' => function ($q) {
            $q->where('is_inactive', false);
        }])->findOrFail($id);

        $brand = $this->applyImagePath($brand, 'logo');
        $brand->products_count = $brand->items->count();

        $items = $brand->items->map(function ($item) {
            $item = $this->applyImagePath($item, 'image');
            return $this->normalizeItem($item);
        });

        $customer = Auth::guard('customer')->user();
        $favoriteItemIds = $customer ? $customer->favorites()->pluck('item_id')->toArray() : [];

        return view('app_android.brands.show', compact('brand', 'items', 'favoriteItemIds'));
    }

    public function productsIndex(Request $request)
    {
        $search = $request->query('search');
        $categoryId = $request->query('category_id');
        $brandId = $request->query('brand_id');

        $query = Item::where('is_inactive', false)
            ->with(['category', 'brand', 'packs']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($brandId) {
            $query->where('brand_id', $brandId);
        }

        $items = $query->get()->map(function ($item) {
            $item = $this->applyImagePath($item, 'image');
            return $this->normalizeItem($item);
        });

        $categories = Category::where('show_in_customer_app', true)->get();
        $brands = Brand::where('show_in_customer_app', true)->get();

        $customer = Auth::guard('customer')->user();
        $favoriteItemIds = $customer ? $customer->favorites()->pluck('item_id')->toArray() : [];

        return view('app_android.products.index', compact('items', 'favoriteItemIds', 'categories', 'brands'));
    }

    public function productShow(Request $request, $id)
    {
        $item = Item::where('is_inactive', false)
            ->with(['category', 'brand', 'packs', 'images'])
            ->findOrFail($id);

        $product = $this->normalizeItem($item);
        $product->image = $item->main_image_url;
        $product->description = $item->definition;
        $product->price = $item->price;
        $product->old_price = null;

        $packs = $item->packs->map(function ($pack) {
            return [
                'id' => $pack->id,
                'name' => $pack->name,
                'price' => $pack->price,
                'quantity' => $pack->quantity,
                'is_default' => $pack->is_default_pack,
            ];
        });

        $images = $item->images->pluck('image_url');

        $product->packs = $packs;
        $product->images = $images;

        $customer = $this->getCustomer($request);
        $favoriteItemIds = $customer ? $customer->favorites()->pluck('item_id')->toArray() : [];

        return view('app_android.product.show', compact('product', 'favoriteItemIds'));
    }

    public function offersIndex()
    {
        $offers = Offer::with(['item', 'itemPack.uom'])
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            })
            ->get()
            ->map(function ($offer) {
                $item = $offer->item;
                $defaultPack = $item ? $item->packs->first() : null;
                $offerPack = $offer->itemPack;

                return (object) [
                    'id' => $offer->id,
                    'title' => $offer->title,
                    'description' => $offer->description,
                    'item_id' => $offer->item_id,
                    'item_pack_id' => $offer->item_pack_id,
                    'name' => $item ? ($item->name_ar ?: $item->name_en) : 'منتج محذوف',
                    'pack_type' => $offerPack ? ($offerPack->uom->name_ar ?? $offerPack->uom->name_en ?? '') : ($defaultPack ? ($defaultPack->uom->name_ar ?? $defaultPack->uom->name_en ?? '') : ''),
                    'image' => $offer->display_image ?? ($item ? $item->main_image_url : null),
                    'old_price' => (float) $offer->old_price,
                    'new_price' => (float) $offer->new_price,
                    'discount_percentage' => (float) $offer->discount_percentage,
                    'start_date' => $offer->start_date?->format('Y-m-d H:i'),
                    'end_date' => $offer->end_date?->format('Y-m-d H:i'),
                ];
            });

        return view('app_android.offers.index', compact('offers'));
    }
}
