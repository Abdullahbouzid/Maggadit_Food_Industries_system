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

        $products = Item::where('is_inactive', false)
            ->with(['category', 'brand', 'packs', 'images'])
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $item = $this->normalizeItem($item);
                $item->image = $item->main_image_url ?: ($item->image ? $this->imagePathService->normalize($item->image) : null);

                // Get price from active offer if available
                $offer = Offer::where('item_id', $item->id)->where('is_active', true)->first();
                if ($offer) {
                    $item->price = (float) $offer->new_price;
                    $item->old_price = (float) $offer->old_price;
                } else {
                    $item->price = (float) ($item->price ?? 0);
                    $item->old_price = null;
                }

                return $item;
            });

        if ($products->isEmpty()) {
            $prodData = $this->fetchRemoteData('customer/products');
            if ($prodData && isset($prodData['products'])) {
                $rawProds = isset($prodData['products']['data']) ? $prodData['products']['data'] : $prodData['products'];
                $products = collect($rawProds)->map(function ($item) {
                    $itemObj = (object) $item;
                    $itemObj->name = $itemObj->name ?? ($itemObj->name_ar ?? 'منتج');
                    $itemObj->image = isset($itemObj->image) ? $this->imagePathService->normalize($itemObj->image) : null;
                    $itemObj->price = (float) ($itemObj->price ?? 0);
                    $itemObj->old_price = isset($itemObj->old_price) ? (float) $itemObj->old_price : null;
                    return $itemObj;
                });
            }
        }

        $customer = $this->getCustomer(request());
        $favoriteItemIds = $customer ? $customer->favorites()->pluck('item_id')->toArray() : [];

        $offers = Offer::with(['item', 'itemPack.uom'])
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

                return (object) [
                    'id' => $offer->id,
                    'title' => $offer->title,
                    'description' => $offer->description,
                    'item_id' => $offer->item_id,
                    'item_pack_id' => $offer->item_pack_id,
                    'name' => $item ? ($item->name_ar ?: $item->name_en) : ($offer->title ?: 'عرض خاص'),
                    'pack_type' => $offerPack ? ($offerPack->uom->name_ar ?? $offerPack->uom->name_en ?? '') : ($defaultPack ? ($defaultPack->uom->name_ar ?? $defaultPack->uom->name_en ?? '') : ''),
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

        return view('app_android.customer_dashboard', compact('banners', 'categories', 'brands', 'products', 'favoriteItemIds', 'offers'));
    }
}
