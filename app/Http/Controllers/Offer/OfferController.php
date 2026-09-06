<?php

namespace App\Http\Controllers\Offer;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Offer;
use App\Models\PriceList;
use App\Models\PriceListVersion;
use App\Models\PriceListVersionItem;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function index(Request $request)
    {
        $query = Offer::with(['item', 'itemPack.packType', 'itemPack.packCapacity.uom', 'priceList'])->latest();

        if ($request->filled('status')) {
            if ($request->status == 'active') {
                $query->active();
            } elseif ($request->status == 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status == 'expired') {
                $query->where('end_date', '<', now());
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('item', function ($q2) use ($search) {
                        $q2->where('name_ar', 'like', "%{$search}%")
                            ->orWhere('name_en', 'like', "%{$search}%");
                    });
            });
        }

        $offers = $query->paginate(20)->appends($request->query());

        return view('dashboard.offers.index', compact('offers'));
    }

    public function create()
    {
        $priceLists = PriceList::all();
        $items = Item::where('is_inactive', false)->get()->map(function ($item) {
            $defaultPack = $item->packs->first();
            $defaultUom = $defaultPack?->packCapacity?->uom;
            $packType = $defaultUom ? ($defaultUom->name_ar ?? $defaultUom->name_en ?? '') : ($defaultPack?->packType?->name ?? '');

            $nameDisplay = $item->name_ar && $item->name_en
                ? $item->name_ar.' / '.$item->name_en
                : ($item->name_ar ?: $item->name_en);

            return [
                'id' => $item->id,
                'name_ar' => $item->name_ar,
                'name_en' => $item->name_en,
                'name' => $nameDisplay,
                'price' => (float) ($defaultPack ? $defaultPack->price : 0),
                'pack_type' => $packType,
                'full_name' => $nameDisplay.($packType ? ' ('.$packType.')' : ''),
                'packs' => $item->packs->map(function ($pack) {
                    $uom = $pack->packCapacity?->uom;
                    $name = $uom ? ($uom->name_ar ?? $uom->name_en ?? 'بدون اسم') : ($pack->packType?->name ?? 'بدون اسم');
                    return [
                        'id' => $pack->id,
                        'name' => $name,
                        'price' => (float) ($pack->price ?? 0),
                        'quantity' => (float) ($pack->quantity_in_pack ?? 0),
                    ];
                })->toArray(),
            ];
        });

        return view('dashboard.offers.create', compact('items', 'priceLists'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'item_pack_id' => 'nullable|exists:item_packs,id',
            'price_list_id' => 'nullable|exists:price_lists,id',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'old_price' => 'required|numeric|min:0',
            'new_price' => 'required|numeric|min:0|lt:old_price',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
        ]);

        if (! $validated['discount_percentage']) {
            $validated['discount_percentage'] = round((($validated['old_price'] - $validated['new_price']) / $validated['old_price']) * 100, 2);
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('offers', 'public');
        }

        Offer::create($validated);

        return redirect()->route('offers.index')->with('success', 'تم إنشاء العرض بنجاح');
    }

    public function show(Offer $offer)
    {
        $offer->load('item', 'itemPack.packType', 'itemPack.packCapacity.uom', 'priceList');

        return view('dashboard.offers.show', compact('offer'));
    }

    public function edit(Offer $offer)
    {
        $priceLists = PriceList::all();
        $items = Item::where('is_inactive', false)->get()->map(function ($item) {
            $defaultPack = $item->packs->first();
            $defaultUom = $defaultPack?->packCapacity?->uom;
            $packType = $defaultUom ? ($defaultUom->name_ar ?? $defaultUom->name_en ?? '') : ($defaultPack?->packType?->name ?? '');

            $nameDisplay = $item->name_ar && $item->name_en
                ? $item->name_ar.' / '.$item->name_en
                : ($item->name_ar ?: $item->name_en);

            return [
                'id' => $item->id,
                'name_ar' => $item->name_ar,
                'name_en' => $item->name_en,
                'name' => $nameDisplay,
                'price' => (float) ($defaultPack ? $defaultPack->price : 0),
                'pack_type' => $packType,
                'full_name' => $nameDisplay.($packType ? ' ('.$packType.')' : ''),
                'packs' => $item->packs->map(function ($pack) {
                    $uom = $pack->packCapacity?->uom;
                    $name = $uom ? ($uom->name_ar ?? $uom->name_en ?? 'بدون اسم') : ($pack->packType?->name ?? 'بدون اسم');
                    return [
                        'id' => $pack->id,
                        'name' => $name,
                        'price' => (float) ($pack->price ?? 0),
                        'quantity' => (float) ($pack->quantity_in_pack ?? 0),
                    ];
                })->toArray(),
            ];
        });

        return view('dashboard.offers.edit', compact('offer', 'items', 'priceLists'));
    }

    public function update(Request $request, Offer $offer)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'item_pack_id' => 'nullable|exists:item_packs,id',
            'price_list_id' => 'nullable|exists:price_lists,id',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'old_price' => 'required|numeric|min:0',
            'new_price' => 'required|numeric|min:0|lt:old_price',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
        ]);

        if (! $validated['discount_percentage']) {
            $validated['discount_percentage'] = round((($validated['old_price'] - $validated['new_price']) / $validated['old_price']) * 100, 2);
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('offers', 'public');
        }

        $offer->update($validated);

        return redirect()->route('offers.show', $offer)->with('success', 'تم تحديث العرض بنجاح');
    }

    public function destroy(Offer $offer)
    {
        $offer->delete();

        return redirect()->route('offers.index')->with('success', 'تم حذف العرض بنجاح');
    }

    public function getItemPrice(Request $request)
    {
        $itemId = $request->query('item_id');
        $packId = $request->query('item_pack_id');
        $priceListId = $request->query('price_list_id');

        if (!$itemId) {
            return response()->json(['success' => false, 'message' => 'منتج غير محدد'], 422);
        }

        $item = Item::find($itemId);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'المنتج غير موجود'], 404);
        }

        $price = 0;
        $source = 'افتراضي';

        if ($priceListId) {
            $latestVersion = PriceListVersion::where('price_list_id', $priceListId)
                ->where('effective_date', '<=', now())
                ->orderByDesc('effective_date')
                ->first();

            if ($latestVersion) {
                $priceItemRecord = PriceListVersionItem::where('price_list_version_id', $latestVersion->id)
                    ->where('item_id', $itemId)
                    ->when($packId, function ($q) use ($packId) {
                        $q->where('item_pack_id', $packId);
                    })
                    ->orderByDesc('item_pack_id')
                    ->first();

                if ($priceItemRecord) {
                    $price = (float) $priceItemRecord->price;
                    $source = 'قائمة الأسعار المختارة';
                }
            }
        }

        if ($price == 0 && $packId) {
            $pack = $item->packs->firstWhere('id', $packId);
            if ($pack) {
                $price = (float) ($pack->price ?? 0);
                $source = 'سعر العبوة الافتراضي';
            }
        }

        if ($price == 0) {
            $defaultPack = $item->packs->first();
            $price = $defaultPack ? (float) ($defaultPack->price ?? 0) : 0;
        }

        return response()->json([
            'success' => true,
            'price' => $price,
            'source' => $source,
        ]);
    }
}
