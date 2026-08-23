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
use Illuminate\Support\Facades\Session;

class CustomerDashboardController extends Controller
{
    use CustomerAppHelpers;

    public function __construct(ImagePathService $imagePathService)
    {
        $this->imagePathService = $imagePathService;
    }

    public function dashboard()
    {
        $banners = collect();
        $categories = collect();
        $brands = collect();
        $products = collect();

        try {
            $token = Session::get('api_token');
            $response = \Illuminate\Support\Facades\Http::withToken($token)->withoutVerifying()->timeout(3)->get("{$this->baseUrl}/customer/dashboard");

            if ($response->successful()) {
                $data = $response->json();

                $banners = $this->parseData($data['banners'] ?? []);
                $categories = $this->parseData($data['categories'] ?? []);
                $brands = $this->parseData($data['brands'] ?? []);
                $products = $this->parseData($data['products'] ?? []);
            } else {
                \Illuminate\Support\Facades\Log::warning('Dashboard API returned non-success status: '.$response->status());
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Dashboard API Error: '.$e->getMessage());
        }

        if ($banners->isEmpty()) {
            $banners = Banner::where('show_in_customer_app', true)->orderBy('order', 'asc')->get()->map(function ($banner) {
                return $this->applyImagePath($banner, 'image');
            });
        }
        if ($categories->isEmpty()) {
            $categories = Category::all()->map(function ($category) {
                return $this->applyImagePath($category, 'image');
            });
        }
        if ($brands->isEmpty()) {
            $brands = Brand::all()->map(function ($brand) {
                return $this->applyImagePath($brand, 'logo');
            });
        }
        if ($products->isEmpty()) {
            $products = Item::limit(50)->get()->map(function ($item) {
                return $this->applyImagePath($item, 'image');
            });
        }

        $customer = $this->getCustomer(request());
        $favoriteItemIds = $customer ? $customer->favorites()->pluck('item_id')->toArray() : [];

        $offers = collect();
        try {
            $offers = Offer::with(['item', 'itemPack.uom'])
                ->active()
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
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Offers Error: '.$e->getMessage());
        }

        return view('app_android.customer_dashboard', compact('banners', 'categories', 'brands', 'products', 'favoriteItemIds', 'offers'));
    }
}
