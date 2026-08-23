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

        $categories = Category::where('show_in_customer_app', true)
            ->get()
            ->map(function ($category) {
                return $this->applyImagePath($category, 'image');
            });

        $brands = Brand::where('show_in_customer_app', true)
            ->get()
            ->map(function ($brand) {
                return $this->applyImagePath($brand, 'logo');
            });

        $products = Item::where('is_inactive', false)
            ->with(['category', 'brand', 'packs'])
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $item = $this->applyImagePath($item, 'image');
                return $this->normalizeItem($item);
            });

        $customer = $this->getCustomer(request());
        $favoriteItemIds = $customer ? $customer->favorites()->pluck('item_id')->toArray() : [];

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

        return view('app_android.customer_dashboard', compact('banners', 'categories', 'brands', 'products', 'favoriteItemIds', 'offers'));
    }
}
