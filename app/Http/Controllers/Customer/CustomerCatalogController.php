<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use App\Models\Offer;
use App\Models\PriceListVersion;
use App\Models\PriceListVersionItem;
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
                $q->where('is_inactive', false)->with(['packs.packType', 'packs.packCapacity.uom']);
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
                $customer = $this->getCustomer(request());
                $favoriteItemIds = $customer ? $customer->favorites()->pluck('item_id')->toArray() : [];
                
                $items = collect($c['items'] ?? [])->map(function ($item) use ($customer, $favoriteItemIds) {
                    $itemObj = (object) $item;
                    $itemObj->name = $itemObj->name_ar ?? $itemObj->name_en ?? $itemObj->name ?? 'منتج';
                    $itemObj->image = isset($itemObj->image) ? $this->imagePathService->normalize($itemObj->image) : null;
                    
                    // Add packs from API response
                    $packs = [];
                    if (isset($itemObj->packs) && is_array($itemObj->packs) && count($itemObj->packs) > 0) {
                        foreach ($itemObj->packs as $pack) {
                            $packs[] = [
                                'id' => $pack['id'] ?? null,
                                'name' => $pack['name'] ?? ($pack['display_name'] ?? 'الحبة'),
                                'display_name' => $pack['display_name'] ?? null,
                                'uom_name' => $pack['uom_name'] ?? '',
                                'uom_symbol' => $pack['uom_symbol'] ?? '',
                                'quantity' => (float) ($pack['quantity'] ?? $pack['quantity_in_pack'] ?? 1),
                                'uom_id' => $pack['uom_id'] ?? null,
                                'price' => (float) ($pack['price'] ?? 0),
                                'barcode' => $pack['barcode'] ?? null,
                                'pack_type' => $pack['pack_type'] ?? 'قطعة',
                                'capacity' => $pack['capacity'] ?? '',
                                'is_default' => $pack['is_default'] ?? false,
                            ];
                        }
                    } else {
                        $packs = [[
                            'id' => null,
                            'name' => 'الحبة',
                            'uom_name' => '',
                            'quantity' => 1,
                            'uom_id' => null,
                            'price' => (float) ($itemObj->price ?? 0),
                            'barcode' => null,
                            'pack_type' => 'قطعة',
                            'capacity' => '',
                        ]];
                    }
                    $itemObj->packs = $packs;
                    $itemObj->favorite = in_array($itemObj->id, $favoriteItemIds ?? []);
                    
                    return $itemObj;
                });
                
                return view('app_android.categories.show', compact('category', 'items', 'favoriteItemIds'));
            }
            abort(404);
        }

        $category = $this->applyImagePath($category, 'image');
        $category->products_count = $category->items->count();

        $customer = $this->getCustomer(request());
        $favoriteItemIds = $customer ? $customer->favorites()->pluck('item_id')->toArray() : [];

        $items = $category->items->map(function ($item) use ($customer, $favoriteItemIds) {
            $item = $this->applyImagePath($item, 'image');

            $packs = $item->packs->map(function ($pack) use ($item, $customer) {
                $capacity = $pack->packCapacity;
                $uom = $capacity?->uom;
                $uomName = $uom ? ($uom->name_ar ?? $uom->name_en) : '';
                $packName = $pack->display_name ?? ($uomName ?: ('عبوة ' . (float) $pack->quantity_in_pack));

                return [
                    'id' => $pack->id,
                    'name' => $packName,
                    'uom_name' => $uomName,
                    'quantity' => (float) $pack->quantity_in_pack,
                    'uom_id' => $capacity?->uom_id,
                    'price' => $this->getItemPrice($item, $customer, $pack->id),
                    'barcode' => $pack->barcode,
                ];
            })->toArray();

            if (empty($packs)) {
                $packs = [[
                    'id' => null,
                    'name' => 'الحبة',
                    'uom_name' => '',
                    'quantity' => 1,
                    'uom_id' => null,
                    'price' => $this->getItemPrice($item, $customer),
                    'barcode' => null,
                ]];
            }

            return [
                'id' => $item->id,
                'name' => $item->name_ar ?? $item->name_en ?? 'منتج',
                'name_ar' => $item->name_ar,
                'name_en' => $item->name_en,
                'image' => $item->image ?? $item->main_image_url,
                'category_id' => $item->category_id,
                'brand_id' => $item->brand_id,
                'packs' => $packs,
                'favorite' => in_array($item->id, $favoriteItemIds),
            ];
        });

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
            $q->where('is_inactive', false)->with(['packs.packType', 'packs.packCapacity.uom']);
        }])->find($id);

        $customer = $this->getCustomer(request());
        $favoriteItemIds = $customer ? $customer->favorites()->pluck('item_id')->toArray() : [];

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
                $items = collect($b['items'] ?? [])->map(function ($item) use ($customer, $favoriteItemIds) {
                    $itemObj = (object) $item;
                    $itemObj->name = $itemObj->name_ar ?? $itemObj->name_en ?? $itemObj->name ?? 'منتج';
                    $itemObj->image = isset($itemObj->image) ? $this->imagePathService->normalize($itemObj->image) : null;
                    
                    // Add packs from API response
                    $packs = [];
                    if (isset($itemObj->packs) && is_array($itemObj->packs) && count($itemObj->packs) > 0) {
                        foreach ($itemObj->packs as $pack) {
                            $packs[] = [
                                'id' => $pack['id'] ?? null,
                                'name' => $pack['name'] ?? ($pack['display_name'] ?? 'الحبة'),
                                'display_name' => $pack['display_name'] ?? null,
                                'uom_name' => $pack['uom_name'] ?? '',
                                'uom_symbol' => $pack['uom_symbol'] ?? '',
                                'quantity' => (float) ($pack['quantity'] ?? $pack['quantity_in_pack'] ?? 1),
                                'uom_id' => $pack['uom_id'] ?? null,
                                'price' => (float) ($pack['price'] ?? 0),
                                'barcode' => $pack['barcode'] ?? null,
                                'pack_type' => $pack['pack_type'] ?? 'قطعة',
                                'capacity' => $pack['capacity'] ?? '',
                                'is_default' => $pack['is_default'] ?? false,
                            ];
                        }
                    } else {
                        $packs = [[
                            'id' => null,
                            'name' => 'الحبة',
                            'uom_name' => '',
                            'quantity' => 1,
                            'uom_id' => null,
                            'price' => (float) ($itemObj->price ?? 0),
                            'barcode' => null,
                            'pack_type' => 'قطعة',
                            'capacity' => '',
                        ]];
                    }
                    $itemObj->packs = $packs;
                    $itemObj->favorite = in_array($itemObj->id, $favoriteItemIds ?? []);
                    
                    return $itemObj;
                });
                return view('app_android.brands.show', compact('brand', 'items', 'favoriteItemIds'));
            }
            abort(404);
        }

        $brand = $this->applyImagePath($brand, 'logo');
        $brand->products_count = $brand->items->count();

        $items = $brand->items->map(function ($item) use ($customer, $favoriteItemIds) {
            $item = $this->applyImagePath($item, 'image');

            $packs = $item->packs->map(function ($pack) use ($item, $customer) {
                $capacity = $pack->packCapacity;
                $uom = $capacity?->uom;
                $uomName = $uom ? ($uom->name_ar ?? $uom->name_en) : '';
                $packName = $pack->display_name ?? ($uomName ?: ('عبوة ' . (float) $pack->quantity_in_pack));

                return [
                    'id' => $pack->id,
                    'name' => $packName,
                    'uom_name' => $uomName,
                    'quantity' => (float) $pack->quantity_in_pack,
                    'uom_id' => $capacity?->uom_id,
                    'price' => $this->getItemPrice($item, $customer, $pack->id),
                    'barcode' => $pack->barcode,
                ];
            })->toArray();

            if (empty($packs)) {
                $packs = [[
                    'id' => null,
                    'name' => 'الحبة',
                    'uom_name' => '',
                    'quantity' => 1,
                    'uom_id' => null,
                    'price' => $this->getItemPrice($item, $customer),
                    'barcode' => null,
                ]];
            }

            return [
                'id' => $item->id,
                'name' => $item->name_ar ?? $item->name_en ?? 'منتج',
                'name_ar' => $item->name_ar,
                'name_en' => $item->name_en,
                'image' => $item->image ?? $item->main_image_url,
                'category_id' => $item->category_id,
                'brand_id' => $item->brand_id,
                'packs' => $packs,
                'favorite' => in_array($item->id, $favoriteItemIds),
            ];
        });

        return view('app_android.brands.show', compact('brand', 'items', 'favoriteItemIds'));
    }

    public function productsIndex(Request $request)
    {
        $search = $request->query('search');
        $categoryId = $request->query('category_id');
        $brandId = $request->query('brand_id');

        $query = Item::where('is_inactive', false)
            ->with(['category', 'brand', 'packs.packType', 'packs.packCapacity.uom']);

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

        $customer = $this->getCustomer($request);
        $favoriteItemIds = $customer ? $customer->favorites()->pluck('item_id')->toArray() : [];

        $items = $query->get()->map(function ($item) use ($customer, $favoriteItemIds) {
            $item = $this->applyImagePath($item, 'image');

            $packs = $item->packs->map(function ($pack) use ($item, $customer) {
                $packType = $pack->packType?->name ?? 'قطعة';
                $capacity = $pack->packCapacity;
                $capacityText = $capacity ? "{$capacity->value} " . ($capacity->uom?->uom_symbol ?? '') : '';
                
                $packName = "{$packType}" . ($capacityText ? " - سعة {$capacityText}" : '');

                return [
                    'id' => $pack->id,
                    'name' => $packName,
                    'uom_name' => $capacityText,
                    'quantity' => (float) $pack->quantity_in_pack,
                    'uom_id' => $capacity?->uom_id,
                    'price' => $this->getItemPrice($item, $customer, $pack->id),
                    'barcode' => $pack->barcode,
                    'pack_type' => $packType,
                    'capacity' => $capacityText,
                ];
            })->toArray();

            if (empty($packs)) {
                $packs = [[
                    'id' => null,
                    'name' => 'الحبة',
                    'uom_name' => '',
                    'quantity' => 1,
                    'uom_id' => null,
                    'price' => $this->getItemPrice($item, $customer),
                    'barcode' => null,
                ]];
            }

            return [
                'id' => $item->id,
                'name' => $item->name_ar ?? $item->name_en ?? 'منتج',
                'name_ar' => $item->name_ar,
                'name_en' => $item->name_en,
                'image' => $item->image ?? $item->main_image_url,
                'category_id' => $item->category_id,
                'brand_id' => $item->brand_id,
                'packs' => $packs,
                'favorite' => in_array($item->id, $favoriteItemIds),
            ];
        });

        if ($items->isEmpty()) {
            $params = array_filter(['search' => $search, 'category_id' => $categoryId, 'brand_id' => $brandId]);
            $remoteData = $this->fetchRemoteData('customer/products', null, $params);
            if ($remoteData && isset($remoteData['products'])) {
                $rawList = isset($remoteData['products']['data']) ? $remoteData['products']['data'] : $remoteData['products'];
                $items = collect($rawList)->map(function ($item) use ($customer, $favoriteItemIds) {
                    $itemObj = (object) $item;
                    $itemObj->name = $itemObj->name ?? ($itemObj->name_ar ?? 'منتج');
                    $itemObj->image = isset($itemObj->image) ? $this->imagePathService->normalize($itemObj->image) : null;
                    $itemObj->price = (float) ($itemObj->price ?? 0);
                    $itemObj->old_price = isset($itemObj->old_price) ? (float) $itemObj->old_price : null;
                    
                    // Add packs array (view expects this structure)
                    $packPrice = $itemObj->price;
                    if (isset($itemObj->packs) && is_array($itemObj->packs) && count($itemObj->packs) > 0) {
                        $itemObj->packs = array_map(function ($pack) use ($packPrice) {
                            return [
                                'id' => $pack['id'] ?? null,
                                'name' => $pack['name'] ?? ($pack['display_name'] ?? 'الحبة'),
                                'display_name' => $pack['display_name'] ?? null,
                                'uom_name' => $pack['uom_name'] ?? '',
                                'uom_symbol' => $pack['uom_symbol'] ?? '',
                                'quantity' => (float) ($pack['quantity'] ?? $pack['quantity_in_pack'] ?? 1),
                                'uom_id' => $pack['uom_id'] ?? null,
                                'price' => (float) ($pack['price'] ?? $packPrice),
                                'barcode' => $pack['barcode'] ?? null,
                                'pack_type' => $pack['pack_type'] ?? 'قطعة',
                                'capacity' => $pack['capacity'] ?? '',
                                'is_default' => $pack['is_default'] ?? false,
                            ];
                        }, $itemObj->packs);
                    } else {
                        // Default single pack
                        $itemObj->packs = [[
                            'id' => null,
                            'name' => 'الحبة',
                            'uom_name' => '',
                            'quantity' => 1,
                            'uom_id' => null,
                            'price' => $packPrice,
                            'barcode' => null,
                            'pack_type' => 'قطعة',
                            'capacity' => '',
                        ]];
                    }
                    
                    // Add favorite boolean
                    $itemObj->favorite = in_array($itemObj->id, $favoriteItemIds ?? []);
                    
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

        return view('app_android.products.index', compact('items', 'favoriteItemIds', 'categories', 'brands'));
    }

    public function productShow(Request $request, $id)
    {
        $item = Item::where('is_inactive', false)
            ->with(['category', 'brand', 'packs.packType', 'packs.packCapacity.uom', 'images'])
            ->find($id);

        if (!$item) {
            $remoteData = $this->fetchRemoteData("customer/products/{$id}");
            if ($remoteData && isset($remoteData['product'])) {
                $p = $remoteData['product'];
                
                // Transform packs from API to match view expectations
                $packs = [];
                if (isset($p['packs']) && is_array($p['packs']) && count($p['packs']) > 0) {
                    foreach ($p['packs'] as $pack) {
                        $packs[] = [
                            'id' => $pack['id'] ?? null,
                            'name' => $pack['name'] ?? ($pack['display_name'] ?? 'الحبة'),
                            'display_name' => $pack['display_name'] ?? null,
                            'uom_name' => $pack['uom_name'] ?? $pack['uom_symbol'] ?? '',
                            'uom_symbol' => $pack['uom_symbol'] ?? '',
                            'quantity' => (float) ($pack['quantity'] ?? $pack['quantity_in_pack'] ?? 1),
                            'uom_id' => $pack['uom_id'] ?? null,
                            'price' => (float) ($pack['price'] ?? 0),
                            'barcode' => $pack['barcode'] ?? null,
                            'pack_type' => $pack['pack_type'] ?? 'قطعة',
                            'capacity' => $pack['capacity'] ?? '',
                            'is_default' => $pack['is_default'] ?? false,
                        ];
                    }
                }
                
                $product = (object) [
                    'id' => $p['id'],
                    'name' => $p['name'] ?? 'منتج',
                    'description' => $p['description'] ?? '',
                    'image' => isset($p['images'][0]) ? $this->imagePathService->normalize($p['images'][0]) : null,
                    'price' => (float) ($p['packs'][0]['price'] ?? 0),
                    'old_price' => null,
                    'packs' => $packs,
                    'images' => array_map(fn($img) => $this->imagePathService->normalize($img), $p['images'] ?? []),
                ];
                $customer = $this->getCustomer($request);
                $favoriteItemIds = $customer ? $customer->favorites()->pluck('item_id')->toArray() : [];
                return view('app_android.product.show', compact('product', 'favoriteItemIds'));
            }
            abort(404);
        }

        $product = $this->normalizeItem($item);
        $product->description = $item->definition;
        
        $customer = $this->getCustomer($request);
        $product->price = $this->getItemPrice($item, $customer);
        $product->old_price = null;

        // Ensure image is set - try main_image_url first, then first image from relationship
        $product->image = $item->main_image_url;
        if (empty($product->image) && $item->images->isNotEmpty()) {
            $firstImage = $item->images->first();
            $product->image = $this->imagePathService->normalize($firstImage->image_path);
        }
        
        // Also populate images array for gallery - use normalized paths
        $product->images = $item->images->map(fn($img) => $this->imagePathService->normalize($img->image_path))->all();

        $packs = $item->packs->map(function ($pack) use ($item, $customer) {
            $packPrice = $this->getItemPrice($item, $customer, $pack->id);
            $capacity = $pack->packCapacity;
            $uom = $capacity?->uom;
            $uomName = $uom ? ($uom->name_ar ?? $uom->name_en) : '';
            $packName = $pack->display_name ?? ($uomName ?: ('عبوة ' . (float) $pack->quantity_in_pack));

            return [
                'id' => $pack->id,
                'name' => $packName,
                'uom_name' => $uomName,
                'price' => $packPrice,
                'quantity' => (float) $pack->quantity_in_pack,
                'is_default' => $pack->is_default_pack,
            ];
        });

        $product->packs = $packs;

        $customer = $this->getCustomer($request);
        $favoriteItemIds = $customer ? $customer->favorites()->pluck('item_id')->toArray() : [];

        return view('app_android.product.show', compact('product', 'favoriteItemIds'));
    }

    public function offersIndex()
    {
        $offers = Offer::with(['item', 'itemPack.packType', 'itemPack.packCapacity.uom'])
            ->active()
            ->get()
            ->map(function ($offer) {
                $item = $offer->item;
                $defaultPack = $item ? $item->packs->first() : null;
                $offerPack = $offer->itemPack;

                $packUomName = function ($pack) {
                    if (!$pack) {
                        return '';
                    }
                    $uom = $pack->packCapacity?->uom;
                    if ($uom) {
                        return $uom->name_ar ?? $uom->name_en ?? '';
                    }
                    return $pack->packType?->name ?? '';
                };

                return (object) [
                    'id' => $offer->id,
                    'title' => $offer->title,
                    'description' => $offer->description,
                    'item_id' => $offer->item_id,
                    'item_pack_id' => $offer->item_pack_id,
                    'name' => $item ? ($item->name_ar ?: $item->name_en) : 'منتج محذوف',
                    'pack_type' => $packUomName($offerPack) ?: $packUomName($defaultPack),
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
