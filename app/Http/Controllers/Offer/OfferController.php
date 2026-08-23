<?php

namespace App\Http\Controllers\Offer;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Offer;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function index(Request $request)
    {
        $query = Offer::with(['item', 'itemPack.uom'])->latest();

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
        $items = Item::where('is_inactive', false)->get()->map(function ($item) {
            $defaultPack = $item->packs->first();
            $packType = $defaultPack ? ($defaultPack->uom->name_ar ?? $defaultPack->uom->name_en ?? '') : '';

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
                    return [
                        'id' => $pack->id,
                        'name' => $pack->uom ? ($pack->uom->name_ar ?? $pack->uom->name_en ?? 'بدون اسم') : 'بدون اسم',
                        'price' => (float) ($pack->price ?? 0),
                        'quantity' => (float) ($pack->quantity ?? 0),
                    ];
                })->toArray(),
            ];
        });

        return view('dashboard.offers.create', compact('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
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
        $offer->load('item', 'itemPack.uom');

        return view('dashboard.offers.show', compact('offer'));
    }

    public function edit(Offer $offer)
    {
        $items = Item::where('is_inactive', false)->get()->map(function ($item) {
            $defaultPack = $item->packs->first();
            $packType = $defaultPack ? ($defaultPack->uom->name_ar ?? $defaultPack->uom->name_en ?? '') : '';

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
                    return [
                        'id' => $pack->id,
                        'name' => $pack->uom ? ($pack->uom->name_ar ?? $pack->uom->name_en ?? 'بدون اسم') : 'بدون اسم',
                        'price' => (float) ($pack->price ?? 0),
                        'quantity' => (float) ($pack->quantity ?? 0),
                    ];
                })->toArray(),
            ];
        });

        return view('dashboard.offers.edit', compact('offer', 'items'));
    }

    public function update(Request $request, Offer $offer)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
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
}
