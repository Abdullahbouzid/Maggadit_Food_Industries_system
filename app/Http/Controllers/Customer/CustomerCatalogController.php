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

        if ($categories->isEmpty()) {
            $remoteData = $this->fetchRemoteData('customer/categories');
            if ($remoteData && isset($remoteData['categories'])) {
                $categories = collect($remoteData['categories'])->map(function ($cat) {
                    $catObj = (object) $cat;
                    $catObj->name = $catObj->name ?? 'تصنيف';
                    $catObj->products_count = $catObj->products_count ?? 0;
                    $catObj->image = isset($catObj->image) ? $this->imagePathService->normalize($catObj->image) : null;
                    return $catObj;
                });
            }
        }

        return view('app_android.categories.index', compact('categories'));
    }

    public function categoriesShow($id)
    {
        $category = Category::where('show_in_customer_app', true)
            ->with(['items' => function ($q) {
                $q->where('is_inactive', false);
            }])
            ->find($id);

        if (!$category) {
            $remoteData = $this->fetchRemoteData("customer/categories/{$id}");
            if ($remoteData && isset($remoteData['category'])) {
                $c = $remoteData['category'];
                $category = (object) [
                    'id' => $c['id'],
                    'name' => $c['name'] ?? 'تصنيف',
                    'image' => isset($c['image']) ? $this->imagePathService->normalize($c['image']) : null,
                    'products_count' => count($c['items'] ?? []),
                ];
                $items = collect($c['items'] ?? [])->map(function ($item) {
                    $itemObj = (object) $item;
                    $itemObj->image = isset($itemObj->image) ? $this->imagePathService->normalize($itemObj->image) : null;
                    return $this->normalizeItem($itemObj);
                });
                $customer = $this->getCustomer(request());
                $favoriteItemIds = $customer ? $customer->favorites()->pluck('item_id')->toArray() : [];
                return view('app_android.categories.show', compact('category', 'items', 'favoriteItemIds'));
            }
            abort(404);
        }

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

        if ($brands->isEmpty()) {
            $remoteData = $this->fetchRemoteData('customer/brands');
            if ($remoteData && isset($remoteData['brands'])) {
                $brands = collect($remoteData['brands'])->map(function ($brand) {
                    $brandObj = (object) $brand;
                    $brandObj->name = $brandObj->name ?? 'ماركة';
                    $brandObj->products_count = $brandObj->products_count ?? 0;
                    $brandObj->logo = isset($brandObj->logo) ? $this->imagePathService->normalize($brandObj->logo) : null;
                    return $brandObj;
                });
            }
        }

        return view('app_android.brands.index', compact('brands'));
    }

    public function brandsShow($id)
    {
        $brand = Brand::with(['items' => function ($q) {
            $q->where('is_inactive', false);
        }])->find($id);

        if (!$brand) {
            $remoteData = $this->fetchRemoteData("customer/brands/{$id}");
            if ($remoteData && isset($remoteData['brand'])) {
                $b = $remoteData['brand'];
                $brand = (object) [
                    'id' => $b['id'],
                    'name' => $b['name'] ?? 'ماركة',
                    'logo' => isset($b['logo']) ? $this->imagePathService->normalize($b['logo']) : null,
                    'products_count' => count($b['items'] ?? []),
                ];
                $items = collect($b['items'] ?? [])->map(function ($item) {
                    $itemObj = (object) $item;
                    $itemObj->image = isset($itemObj->image) ? $this->imagePathService->normalize($itemObj->image) : null;
                    return $this->normalizeItem($itemObj);
                });
                $customer = Auth::guard('customer')->user();
                $favoriteItemIds = $customer ? $customer->favorites()->pluck('item_id')->toArray() : [];
                return view('app_android.brands.show', compact('brand', 'items', 'favoriteItemIds'));
            }
            abort(404);
        }

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

        if ($items->isEmpty()) {
            $params = array_filter(['search' => $search, 'category_id' => $categoryId, 'brand_id' => $brandId]);
            $remoteData = $this->fetchRemoteData('customer/products', null, $params);
            if ($remoteData && isset($remoteData['products'])) {
                $rawList = isset($remoteData['products']['data']) ? $remoteData['products']['data'] : $remoteData['products'];
                $items = collect($rawList)->map(function ($item) {
                    $itemObj = (object) $item;
                    $itemObj->name = $itemObj->name ?? ($itemObj->name_ar ?? 'منتج');
                    $itemObj->image = isset($itemObj->image) ? $this->imagePathService->normalize($itemObj->image) : null;
                    $itemObj->price = (float) ($itemObj->price ?? 0);
                    $itemObj->old_price = isset($itemObj->old_price) ? (float) $itemObj->old_price : null;
                    return $itemObj;
                });
            }
        }

        $categories = Category::where('show_in_customer_app', true)->get();
        if ($categories->isEmpty()) {
            $catData = $this->fetchRemoteData('customer/categories');
            if ($catData && isset($catData['categories'])) {
                $categories = collect($catData['categories'])->map(fn($c) => (object)$c);
            }
        }

        $brands = Brand::where('show_in_customer_app', true)->get();
        if ($brands->isEmpty()) {
            $brandData = $this->fetchRemoteData('customer/brands');
            if ($brandData && isset($brandData['brands'])) {
                $brands = collect($brandData['brands'])->map(fn($b) => (object)$b);
            }
        }

        $customer = Auth::guard('customer')->user();
        $favoriteItemIds = $customer ? $customer->favorites()->pluck('item_id')->toArray() : [];

        return view('app_android.products.index', compact('items', 'favoriteItemIds', 'categories', 'brands'));
    }

    public function productShow(Request $request, $id)
    {
        $item = Item::where('is_inactive', false)
            ->with(['category', 'brand', 'packs', 'images'])
            ->find($id);

        if (!$item) {
            $remoteData = $this->fetchRemoteData("customer/products/{$id}");
            if ($remoteData && isset($remoteData['product'])) {
                $p = $remoteData['product'];
                $product = (object) [
                    'id' => $p['id'],
                    'name' => $p['name'] ?? 'منتج',
                    'description' => $p['description'] ?? '',
                    'image' => isset($p['images'][0]) ? $this->imagePathService->normalize($p['images'][0]) : null,
                    'price' => (float) ($p['packs'][0]['price'] ?? 0),
                    'old_price' => null,
                    'packs' => $p['packs'] ?? [],
                    'images' => array_map(fn($img) => $this->imagePathService->normalize($img), $p['images'] ?? []),
                ];
                $customer = $this->getCustomer($request);
                $favoriteItemIds = $customer ? $customer->favorites()->pluck('item_id')->toArray() : [];
                return view('app_android.product.show', compact('product', 'favoriteItemIds'));
            }
            abort(404);
        }

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

        if ($offers->isEmpty()) {
            $remoteData = $this->fetchRemoteData('customer/offers');
            if ($remoteData && isset($remoteData['offers'])) {
                $rawOffers = isset($remoteData['offers']['data']) ? $remoteData['offers']['data'] : $remoteData['offers'];
                $offers = collect($rawOffers)->map(function ($offer) {
                    $o = (object) $offer;
                    $o->name = $o->title ?? ($o->name ?? 'عرض خاص');
                    $o->image = isset($o->image) ? $this->imagePathService->normalize($o->image) : null;
                    $o->old_price = (float) ($o->old_price ?? 0);
                    $o->new_price = (float) ($o->new_price ?? 0);
                    return $o;
                });
            }
        }

        return view('app_android.offers.index', compact('offers'));
    }
}
