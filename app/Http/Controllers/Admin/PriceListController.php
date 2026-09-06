<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Item;
use App\Models\ItemPack;
use App\Models\PriceList;
use App\Models\PriceListVersion;
use App\Models\PriceListVersionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PriceListController extends Controller
{
    public function index(Request $request)
    {
        $query = PriceList::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%")
                ->orWhere('description', 'like', "%{$request->search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $priceLists = $query->with(['creator', 'versions' => function ($q) {
            $q->orderByDesc('effective_date')->limit(1);
        }])->latest()->paginate(15);

        // Count unique items in latest active version for each price list
        foreach ($priceLists as $priceList) {
            $latestVersion = $priceList->versions->first();
            if ($latestVersion) {
                $priceList->items_count = $latestVersion->items()->count();
            } else {
                $priceList->items_count = 0;
            }
        }

        return view('dashboard.price_lists.index', compact('priceLists'));
    }

    public function create()
    {
        return view('dashboard.price_lists.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:price_lists',
            'description' => 'nullable|string|max:1000',
            'status' => 'in:active,inactive',
        ]);

        $priceList = PriceList::create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status ?? 'active',
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('price-lists.index')->with('success', 'تم إنشاء قائمة الأسعار بنجاح ✅');
    }

    public function edit(PriceList $priceList)
    {
        return view('dashboard.price_lists.edit', compact('priceList'));
    }

    public function update(Request $request, PriceList $priceList)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:price_lists,name,' . $priceList->id,
            'description' => 'nullable|string|max:1000',
            'status' => 'in:active,inactive',
        ]);

        $priceList->update([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status ?? 'active',
        ]);

        return redirect()->route('price-lists.index')->with('success', 'تم تحديث قائمة الأسعار بنجاح ✅');
    }

    public function destroy(PriceList $priceList)
    {
        $priceList->delete();

        return redirect()->route('price-lists.index')->with('success', 'تم حذف قائمة الأسعار بنجاح ✅');
    }

    public function toggleStatus(PriceList $priceList)
    {
        $priceList->update([
            'status' => $priceList->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'تم تغيير الحالة بنجاح ✅');
    }

    public function show(PriceList $priceList)
    {
        $priceList->load(['versions' => function ($q) {
            $q->orderByDesc('effective_date')->with('creator');
        }]);

        $latestVersion = $priceList->versions->first();

        // Get latest updated price for each item/pack across all versions
        $allItems = Item::where('is_inactive', false)
            ->with(['packs.packType', 'packs.packCapacity.uom', 'category', 'brand'])
            ->get();

        $versionIds = $priceList->versions->pluck('id');
        $latestPrices = PriceListVersionItem::whereIn('price_list_version_id', $versionIds)
            ->orderByDesc('created_at')
            ->get()
            ->groupBy(function ($item) {
                return $item->item_id . '_' . ($item->item_pack_id ?? 'null');
            })
            ->map(function ($items) {
                return $items->first();
            });

        $versionItems = collect();

        foreach ($allItems as $item) {
            $packs = $item->packs->isEmpty() ? [null] : $item->packs;

            foreach ($packs as $pack) {
                $key = $item->id . '_' . ($pack?->id ?? 'null');
                $latestPrice = $latestPrices->get($key);

                $packType = $pack?->packType?->name ?? 'قطعة';
                $capacity = $pack?->packCapacity;
                $capacityText = $capacity ? "سعة {$capacity->value} " . ($capacity->uom?->uom_symbol ?? '') : '';
                
                $versionItems->push([
                    'id' => $latestPrice?->id,
                    'item_id' => $item->id,
                    'item_pack_id' => $pack?->id ?? null,
                    'name_ar' => $item->name_ar ?? '',
                    'name_en' => $item->name_en ?? '',
                    'category' => $item->category?->name ?? '—',
                    'brand' => $item->brand?->name ?? '—',
                    'pack_type' => $packType,
                    'capacity' => $capacityText,
                    'display_name' => "{$item->name_ar} - {$packType}" . ($capacityText ? " - {$capacityText}" : ''),
                    'price' => $latestPrice ? (float) $latestPrice->price : null,
                    'effective_date' => $latestPrice ? $latestPrice->created_at->format('Y-m-d H:i') : '—',
                    'is_new' => !$latestPrice,
                ]);
            }
        }

        // Prepare items for the add form
        $itemsForSelect = $allItems->map(function ($item) {
            return [
                'id' => $item->id,
                'name_ar' => $item->name_ar,
                'name_en' => $item->name_en,
                'packs' => $item->packs->map(function ($pack) {
                    $capacity = $pack->packCapacity;
                    return [
                        'id' => $pack->id,
                        'name' => $pack->display_name,
                        'pack_type' => $pack->packType?->name ?? 'قطعة',
                        'capacity' => $capacity ? "{$capacity->value} " . ($capacity->uom?->uom_symbol ?? '') : '',
                    ];
                })->toArray(),
            ];
        })->toArray();

        return view('dashboard.price_lists.show', compact('priceList', 'latestVersion', 'versionItems', 'itemsForSelect'));
    }

    public function createVersion(Request $request, PriceList $priceList)
    {
        $latestVersion = $priceList->versions()->orderByDesc('effective_date')->first();

        $newVersion = PriceListVersion::create([
            'price_list_id' => $priceList->id,
            'effective_date' => $request->effective_date ?? now()->toDateString(),
            'is_active' => true,
            'created_by' => Auth::id(),
        ]);

        if ($latestVersion) {
            $latestItems = $latestVersion->items()->get();
            foreach ($latestItems as $item) {
                PriceListVersionItem::create([
                    'price_list_version_id' => $newVersion->id,
                    'item_id' => $item->item_id,
                    'item_pack_id' => $item->item_pack_id,
                    'price' => $item->price,
                ]);
            }
        }

        return redirect()->route('price-lists.versions.edit', [$priceList, $newVersion])->with('success', 'تم إنشاء الإصدار الجديد بنجاح ✅');
    }

    public function editVersion(PriceList $priceList, PriceListVersion $version)
    {
        $version->load(['items.item', 'items.pack.packType', 'items.pack.packCapacity.uom']);

        // Get all items with their packs
        $allItems = Item::where('is_inactive', false)
            ->with(['packs.packType', 'packs.packCapacity.uom'])
            ->get();

        // Build a map of existing version items for quick lookup
        $existingItems = $version->items->keyBy(function ($item) {
            return $item->item_id . '_' . ($item->item_pack_id ?? 'null');
        });

        // Prepare all items with their packs and prices
        $versionItems = $allItems->flatMap(function ($item) use ($existingItems, $version) {
            $packs = $item->packs->isEmpty() ? [null] : $item->packs;
            $result = [];

            foreach ($packs as $pack) {
                $key = $item->id . '_' . ($pack?->id ?? 'null');
                $existing = $existingItems->get($key);
                
                $packType = $pack?->packType?->name ?? 'قطعة';
                $capacity = $pack?->packCapacity;
                $capacityText = $capacity ? "سعة {$capacity->value} " . ($capacity->uom?->uom_symbol ?? '') : '';

                $result[] = [
                    'id' => $existing?->id ?? null,
                    'item_id' => $item->id,
                    'item_pack_id' => $pack?->id ?? null,
                    'name_ar' => $item->name_ar ?? '',
                    'name_en' => $item->name_en ?? '',
                    'pack_type' => $packType,
                    'capacity' => $capacityText,
                    'display_name' => "{$item->name_ar} - {$packType}" . ($capacityText ? " - {$capacityText}" : ''),
                    'pack_quantity' => $pack ? (float) $pack->quantity_in_pack : 1,
                    'price' => $existing ? (float) $existing->price : 0,
                    'is_new' => !$existing,
                ];
            }

            return $result;
        })->toArray();

        return view('dashboard.price_lists.versions.edit', compact('priceList', 'version', 'versionItems'));
    }

    public function updateVersion(Request $request, PriceList $priceList, PriceListVersion $version)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.item_pack_id' => 'nullable|string',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        foreach ($request->items as $itemData) {
            $itemPackId = $itemData['item_pack_id'] === 'null' ? null : $itemData['item_pack_id'];

            PriceListVersionItem::updateOrCreate(
                [
                    'price_list_version_id' => $version->id,
                    'item_id' => $itemData['item_id'],
                    'item_pack_id' => $itemPackId,
                ],
                [
                    'price' => $itemData['price'],
                ]
            );
        }

        return redirect()->route('price-lists.show', $priceList)->with('success', 'تم تحديث الإصدار بنجاح ✅');
    }

    public function destroyVersion(PriceList $priceList, PriceListVersion $version)
    {
        if ($version->is_active) {
            return back()->with('error', 'لا يمكن حذف الإصدار النشط الحالي');
        }

        $version->items()->delete();
        $version->delete();

        return back()->with('success', 'تم حذف الإصدار بنجاح ✅');
    }

    public function customers(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%")
                ->orWhere('username', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%");
        }

        if ($request->filled('price_list_id')) {
            $query->where('price_list_id', $request->price_list_id);
        }

        $customers = $query->with('priceList')->latest()->paginate(20);
        $priceLists = PriceList::active()->get();

        return view('dashboard.price_lists.customers', compact('customers', 'priceLists'));
    }

    public function updateCustomerPriceList(Request $request, Customer $customer)
    {
        $request->validate([
            'price_list_id' => 'nullable|exists:price_lists,id',
        ]);

        $customer->update([
            'price_list_id' => $request->price_list_id,
        ]);

        return back()->with('success', 'تم تحديث قائمة الأسعار للعميل بنجاح ✅');
    }
}
