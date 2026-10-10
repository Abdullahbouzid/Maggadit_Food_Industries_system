<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use App\Models\Offer;
use App\Services\Customer\ImagePathService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class CustomerDashboardController extends Controller
{
    use CustomerAppHelpers;

    public function __construct(ImagePathService $imagePathService)
    {
        $this->imagePathService = $imagePathService;
    }

    public function dashboard()
    {
        $banners = Banner::where('show_in_customer_app', true)
            ->orderBy('order', 'asc')
            ->get()
            ->map(function ($banner) {
                return $this->applyImagePath($banner, 'image');
            });

        if ($banners->isEmpty()) {
            $banners = Banner::orderBy('order', 'asc')->get()->map(function ($banner) {
                return $this->applyImagePath($banner, 'image');
            });
        }

        $categories = Category::where('show_in_customer_app', true)
            ->get()
            ->map(function ($category) {
                return $this->applyImagePath($category, 'image');
            });

        if ($categories->isEmpty()) {
            $categories = Category::all()->map(function ($category) {
                return $this->applyImagePath($category, 'image');
            });
        }
        if ($categories->isEmpty()) {
            $catData = $this->fetchRemoteData('customer/categories');
            if ($catData && isset($catData['categories'])) {
                $categories = collect($catData['categories'])->map(function ($cat) {
                    $c = (object) $cat;
                    $c->image = isset($c->image) ? $this->imagePathService->normalize($c->image) : null;
                    return $c;
                });
            }
        }

        $brands = Brand::where('show_in_customer_app', true)
            ->get()
            ->map(function ($brand) {
                return $this->applyImagePath($brand, 'logo');
            });

        if ($brands->isEmpty()) {
            $brands = Brand::all()->map(function ($brand) {
                return $this->applyImagePath($brand, 'logo');
            });
        }
        if ($brands->isEmpty()) {
            $brandData = $this->fetchRemoteData('customer/brands');
            if ($brandData && isset($brandData['brands'])) {
                $brands = collect($brandData['brands'])->map(function ($brand) {
                    $b = (object) $brand;
                    $b->logo = isset($b->logo) ? $this->imagePathService->normalize($b->logo) : null;
                    return $b;
                });
            }
        }

        $customer = $this->getCustomer(request());
        $favoriteItemIds = $customer ? $customer->favorites()->pluck('item_id')->toArray() : [];

        // If local DB is empty, fetch favorites from external API (for APK)
        if (empty($favoriteItemIds) && $customer) {
            $favoriteItemIds = $this->getRemoteFavoriteIds(request());
        }

        $products = Item::where('is_inactive', false)
            ->with(['category', 'brand', 'packs.packType', 'packs.packCapacity.uom', 'images'])
            ->limit(10)
            ->get();

        $activeOffers = Offer::whereIn('item_id', $products->pluck('id'))
            ->where('is_active', true)
            ->get()
            ->keyBy('item_id');

        $productCards = collect();
        foreach ($products as $item) {
            if ($item instanceof Item) {
                $item = $this->normalizeItem($item);
                $itemImage = $item->main_image_url ?: ($item->image ? $this->imagePathService->normalize($item->image) : null);

                // Remote API returns packs as an array, local DB returns relationship
                $packs = $item->packs ?? collect();
                if (! $packs instanceof \Illuminate\Support\Collection) {
                    $packs = collect($packs);
                }

                $offer = $activeOffers[$item->id] ?? null;

                if ($packs->isEmpty()) {
                    $priceInfo = $this->getItemPriceRecord($item, $customer);
                    $productCards->push([
                        'id' => $item->id,
                        'pack_id' => null,
                        'name' => $item->name,
                        'image' => $itemImage,
                        'price' => $offer ? (float) $offer->new_price : $priceInfo['price'],
                        'old_price' => $offer ? (float) $offer->old_price : null,
                        'pack_name' => '',
                        'uom_name' => '',
                        'pack_quantity' => 1,
                        'favorite' => in_array($item->id, $favoriteItemIds),
                    ]);
                } else {
                    foreach ($packs as $pack) {
                        $capacity = $pack->packCapacity;
                        $uom = $capacity?->uom;
                        $uomName = $uom ? ($uom->name_ar ?? $uom->name_en) : '';
                        $packName = $pack->display_name ?? ($uomName ?: ('عبوة ' . (float) $pack->quantity_in_pack));
                        $priceInfo = $this->getItemPriceRecord($item, $customer, $pack->id);

                        $productCards->push([
                            'id' => $item->id,
                            'pack_id' => $pack->id,
                            'name' => $item->name,
                            'image' => $itemImage,
                            'price' => $offer ? (float) $offer->new_price : $priceInfo['price'],
                            'old_price' => $offer ? (float) $offer->old_price : null,
                            'pack_name' => $packName,
                            'uom_name' => $uomName,
                            'pack_quantity' => (float) $pack->quantity_in_pack,
                            'favorite' => in_array($item->id, $favoriteItemIds),
                        ]);
                    }
                }
            } else {
                $itemImage = $item->image ?? null;
                $packs = collect($item->packs ?? []);
                if (! $packs instanceof \Illuminate\Support\Collection) {
                    $packs = collect([]);
                }
                $offer = $activeOffers[$item->id] ?? null;

                if ($packs->isEmpty()) {
                    $priceInfo = $this->getItemPriceRecord($item, $customer);
                    $productCards->push([
                        'id' => $item->id,
                        'pack_id' => null,
                        'name' => $item->name,
                        'image' => $itemImage,
                        'price' => $offer ? (float) $offer->new_price : $priceInfo['price'],
                        'old_price' => $offer ? (float) $offer->old_price : null,
                        'pack_name' => '',
                        'uom_name' => '',
                        'pack_quantity' => 1,
                        'favorite' => in_array($item->id, $favoriteItemIds),
                    ]);
                } else {
                    foreach ($packs as $pack) {
                        $capacity = $pack->packCapacity;
                        $uom = $capacity?->uom;
                        $uomName = $uom ? ($uom->name_ar ?? $uom->name_en) : '';
                        $packName = $pack->display_name ?? ($uomName ?: ('عبوة ' . (float) $pack->quantity_in_pack));
                        $priceInfo = $this->getItemPriceRecord($item, $customer, $pack->id);

                        $productCards->push([
                            'id' => $item->id,
                            'pack_id' => $pack->id,
                            'name' => $item->name,
                            'image' => $itemImage,
                            'price' => $offer ? (float) $offer->new_price : $priceInfo['price'],
                            'old_price' => $offer ? (float) $offer->old_price : null,
                            'pack_name' => $packName,
                            'uom_name' => $uomName,
                            'pack_quantity' => (float) $pack->quantity_in_pack,
                            'favorite' => in_array($item->id, $favoriteItemIds),
                        ]);
                    }
                }
            }
        }

        $offers = Offer::with(['item', 'itemPack.packType', 'itemPack.packCapacity.uom'])
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now()->addDays(30));
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            })
            ->get()
            ->map(function ($offer) {
                $item = $offer->item;
                $defaultPack = $item ? $item->packs->first() : null;
                $offerPack = $offer->itemPack;
                $imageUrl = $offer->image ? $this->imagePathService->normalize($offer->image) : ($item ? $item->main_image_url : null);

                $packName = '';
                $uomName = '';
                $packQuantity = 0;
                $packPrice = (float) $offer->new_price;

                $resolvePack = function ($pack) {
                    $uom = $pack?->packCapacity?->uom;
                    $uomLabel = $uom ? ($uom->name_ar ?? $uom->name_en ?? '') : '';
                    $typeLabel = $pack?->packType?->name ?? '';
                    return [$uomLabel, $typeLabel];
                };

                if ($offerPack) {
                    [$uomLabel, $typeLabel] = $resolvePack($offerPack);
                    $packName = $uomLabel ?: ($typeLabel ?: ('عبوة ' . (float) $offerPack->quantity_in_pack));
                    $uomName = $uomLabel;
                    $packQuantity = (float) $offerPack->quantity_in_pack;
                } elseif ($defaultPack) {
                    [$uomLabel, $typeLabel] = $resolvePack($defaultPack);
                    $packName = $uomLabel ?: ($typeLabel ?: ('عبوة ' . (float) $defaultPack->quantity_in_pack));
                    $uomName = $uomLabel;
                    $packQuantity = (float) $defaultPack->quantity_in_pack;
                }

                return (object) [
                    'id' => $offer->id,
                    'title' => $offer->title,
                    'description' => $offer->description,
                    'item_id' => $offer->item_id,
                    'item_pack_id' => $offer->item_pack_id,
                    'name' => $item ? ($item->name_ar ?: $item->name_en) : ($offer->title ?: 'عرض خاص'),
                    'pack_type' => $uomName,
                    'pack_name' => $packName,
                    'pack_quantity' => $packQuantity,
                    'image' => $imageUrl,
                    'old_price' => (float) $offer->old_price,
                    'new_price' => (float) $offer->new_price,
                    'discount_percentage' => (float) $offer->discount_percentage,
                    'start_date' => $offer->start_date?->format('Y-m-d H:i'),
                    'end_date' => $offer->end_date?->format('Y-m-d H:i'),
                ];
            });

        if ($offers->isEmpty()) {
            $offerData = $this->fetchRemoteData('customer/offers');
            if ($offerData && isset($offerData['offers'])) {
                $rawOffers = isset($offerData['offers']['data']) ? $offerData['offers']['data'] : $offerData['offers'];
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

        // If local DB is empty, fetch products from external API (for APK)
        if ($products->isEmpty()) {
            $token = session('api_token') ?: request()->bearerToken();
            if ($token) {
                $remoteData = $this->fetchRemoteData('customer/products', $token);
                if ($remoteData && isset($remoteData['products'])) {
                    $products = collect($remoteData['products'])->map(function ($item) {
                        $o = (object) $item;
                        $o->image = isset($o->image) ? $this->imagePathService->normalize($o->image) : null;
                        return $o;
                    });
                }
            }
        }

        return view('app_android.customer_dashboard', compact('banners', 'categories', 'brands', 'productCards', 'favoriteItemIds', 'offers'));
    }
}
