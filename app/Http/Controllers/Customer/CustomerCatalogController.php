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
use Illuminate\Support\Facades\Http;
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
        $categories = Category::where('show_in_customer_app', true)->get()->map(function ($category) {
            $category->products_count = $category->items()->where('is_inactive', false)->count();

            return $this->applyImagePath($category, 'image');
        });

        return view('app_android.categories.index', compact('categories'));
    }

    public function categoriesShow($id)
    {
        $category = null;
        $items = collect();

        try {
            $response = Http::withoutVerifying()->timeout(3)->get("{$this->baseUrl}/customer/categories/{$id}");
            if ($response->successful()) {
                $data = $response->json();
                $categoryData = $data['category'] ?? $data;

                $category = (object) $categoryData;
                $category = $this->applyImagePath($category, 'image');

                if (isset($data['products'])) {
                    $items = $this->parseData($data['products']);
                } elseif (isset($data['items'])) {
                    $items = $this->parseData($data['items']);
                }

                if (is_array($category->items ?? null)) {
                    $category->items = collect($category->items);
                }
            }
        } catch (\Exception $e) {
            Log::error('Category Show API Error: '.$e->getMessage());
        }

        if (! $category) {
            $category = Category::find($id);
            if (! $category) {
                abort(404, 'الفئة غير موجودة');
            }
            $category = $this->applyImagePath($category, 'image');
        }

        $category->products_count = is_object($category->items ?? null) && method_exists($category->items, 'count') ? $category->items->count() : (is_array($category->items ?? null) || $category->items instanceof \Countable ? count($category->items) : ($category->products_count ?? 0));

        if ($items->isEmpty()) {
            $items = Item::where('category_id', $id)->get()->map(function ($item) {
                return $this->applyImagePath($item, 'image');
            });
        }

        $customer = $this->getCustomer(request());
        $favoriteItemIds = $customer ? $customer->favorites()->pluck('item_id')->toArray() : [];

        return view('app_android.categories.show', compact('category', 'items', 'favoriteItemIds'));
    }

    public function brandsIndex()
    {
        $brands = Brand::where('show_in_customer_app', true)->get()->map(function ($brand) {
            $brand->products_count = $brand->items()->where('is_inactive', false)->count();

            return $this->applyImagePath($brand, 'logo');
        });

        return view('app_android.brands.index', compact('brands'));
    }

    public function brandsShow($id)
    {
        $brand = null;
        $items = collect();

        try {
            $response = Http::withoutVerifying()->timeout(3)->get("{$this->baseUrl}/customer/brands/{$id}");
            if ($response->successful()) {
                $data = $response->json();
                $brandData = $data['brand'] ?? $data;

                $brand = (object) $brandData;
                $brand = $this->applyImagePath($brand, 'logo');

                if (isset($data['products'])) {
                    $items = $this->parseData($data['products']);
                } elseif (isset($data['items'])) {
                    $items = $this->parseData($data['items']);
                }

                if (is_array($brand->items ?? null)) {
                    $brand->items = collect($brand->items);
                }
            }
        } catch (\Exception $e) {
            Log::error('Brand Show API Error: '.$e->getMessage());
        }

        if (! $brand) {
            $brand = Brand::find($id);
            if (! $brand) {
                abort(404, 'الماركة غير موجودة');
            }
            $brand = $this->applyImagePath($brand, 'logo');
        }

        $brand->products_count = is_object($brand->items ?? null) && method_exists($brand->items, 'count') ? $brand->items->count() : (is_array($brand->items ?? null) || $brand->items instanceof \Countable ? count($brand->items) : ($brand->products_count ?? 0));

        if ($items->isEmpty()) {
            $items = Item::where('brand_id', $id)->get()->map(function ($item) {
                return $this->applyImagePath($item, 'image');
            });
        }

        $customer = Auth::guard('customer')->user();
        $favoriteItemIds = $customer ? $customer->favorites()->pluck('item_id')->toArray() : [];

        return view('app_android.brands.show', compact('brand', 'items', 'favoriteItemIds'));
    }

    public function productsIndex(Request $request)
    {
        $search = $request->query('search');
        $categoryId = $request->query('category_id');
        $brandId = $request->query('brand_id');

        $categories = Category::where('show_in_customer_app', true)->get();
        $brands = Brand::where('show_in_customer_app', true)->get();

        try {
            $url = "{$this->baseUrl}/customer/products";
            $queryParams = [];
            if ($search) {
                $queryParams['search'] = $search;
            }
            if ($categoryId) {
                $queryParams['category_id'] = $categoryId;
            }
            if ($brandId) {
                $queryParams['brand_id'] = $brandId;
            }

            $response = Http::withoutVerifying()->timeout(3)->get($url, $queryParams);
            if ($response->successful()) {
                $data = $response->json();
                $rawData = $data['products'] ?? ($data['data'] ?? $data);

                if (isset($data['success']) && !isset($data['data']) && !isset($data['products'])) {
                    $rawData = [];
                }

                if (is_array($rawData) && isset($rawData['data']) && is_array($rawData['data'])) {
                    $rawData = $rawData['data'];
                }

                $items = $this->parseData(is_array($rawData) ? $rawData : []);

                if ($items->isNotEmpty()) {
                    $customer = Auth::guard('customer')->user();
                    $favoriteItemIds = $customer ? $customer->favorites()->pluck('item_id')->toArray() : [];
                    return view('app_android.products.index', compact('items', 'favoriteItemIds'));
                }
            }
        } catch (\Exception $e) {
            Log::error('Products API Error: '.$e->getMessage());
        }

        $items = Item::all()->map(function ($item) {
            return $this->applyImagePath($item, 'image');
        });

        $customer = Auth::guard('customer')->user();
        $favoriteItemIds = $customer ? $customer->favorites()->pluck('item_id')->toArray() : [];

        return view('app_android.products.index', compact('items', 'favoriteItemIds', 'categories', 'brands'));
    }

    public function productShow(Request $request, $id)
    {
        $product = null;
        try {
            $response = Http::withoutVerifying()->timeout(3)->get("{$this->baseUrl}/customer/products/{$id}");
            if ($response->successful()) {
                $data = $response->json();
                $productData = $data['product'] ?? $data;
                $product = (object) $productData;
                $product = $this->applyImagePath($product, 'image');
            }
        } catch (\Exception $e) {
            Log::error('Product Show API Error: '.$e->getMessage());
        }

        if (! $product) {
            $product = Item::find($id);
            if ($product) {
                $product = $this->applyImagePath($product, 'image');
            } else {
                abort(404, 'المنتج غير موجود');
            }
        }

        $customer = $this->getCustomer($request);
        $favoriteItemIds = $customer ? $customer->favorites()->pluck('item_id')->toArray() : [];

        return view('app_android.product.show', compact('product', 'favoriteItemIds'));
    }

    public function offersIndex()
    {
        $offers = collect();

        try {
            $response = Http::withoutVerifying()->timeout(3)->get("{$this->baseUrl}/customer/offers");
            if ($response->successful()) {
                $data = $response->json();
                $rawData = $data['offers'] ?? ($data['data'] ?? $data);

                if (isset($data['success']) && !isset($data['data']) && !isset($data['offers'])) {
                    $rawData = [];
                }

                if (is_array($rawData) && isset($rawData['data']) && is_array($rawData['data'])) {
                    $rawData = $rawData['data'];
                }

                $offers = $this->parseData(is_array($rawData) ? $rawData : []);

                if ($offers->isNotEmpty()) {
                    return view('app_android.offers.index', compact('offers'));
                }
            }
        } catch (\Exception $e) {
            Log::error("Offers API Error: " . $e->getMessage());
        }

        try {
            if (class_exists(\App\Models\Offer::class)) {
                $offers = \App\Models\Offer::with(['item', 'itemPack.uom'])->active()->get()->map(function ($offer) {
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
            }
        } catch (\Exception $e) {
            Log::warning('Local Offers fetch failed: ' . $e->getMessage());
        }

        return view('app_android.offers.index', compact('offers'));
    }
}
