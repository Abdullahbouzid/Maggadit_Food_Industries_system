<?php

// app/Http/Controllers/ItemPackController.php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemPack;
use App\Models\PackCapacity;
use App\Models\PackGroup;
use App\Models\PackOperation;
use App\Models\PackType;
use Illuminate\Http\Request;

class ItemPackController extends Controller
{
    public function index(Request $request)
    {
        $query = ItemPack::with(['item', 'packType', 'packCapacity.uom']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('barcode', 'like', "%{$search}%")
                    ->orWhereHas('item', function ($sub) use ($search) {
                        $sub->where('code', 'like', "%{$search}%")
                            ->orWhere('name_en', 'like', "%{$search}%")
                            ->orWhere('name_ar', 'like', "%{$search}%");
                    })
                    ->orWhereHas('packCapacity.uom', function ($sub) use ($search) {
                        $sub->where('name_en', 'like', "%{$search}%")
                            ->orWhere('name_ar', 'like', "%{$search}%")
                            ->orWhere('uom_symbol', 'like', "%{$search}%");
                    });
            });
        }

        $total = ItemPack::count();
        $defaultCount = ItemPack::where('is_default_pack', true)->count();
        $weightPricedCount = ItemPack::where('is_weight_priced', true)->count();

        $perPage = $request->get('per_page', 20);
        if ($perPage === 'all') {
            $perPage = $query->count() ?: 1;
        }

        $itemPacks = $query->orderByDesc('is_default_pack')
            ->orderBy('sort_order')
            ->orderBy('barcode')
            ->paginate($perPage)
            ->appends($request->query());

        return view('dashboard.data_definition.data_product.item_packs.index', compact(
            'itemPacks', 'total', 'defaultCount', 'weightPricedCount'
        ));
    }

    public function create()
    {
        $items = Item::orderBy('name_en')->get();
        $packTypes = PackType::orderBy('name_en')->get();
        $packCapacities = PackCapacity::with('uom')->orderBy('value')->get();
        $packOperations = PackOperation::where('is_active', true)->orderBy('name_en')->get();
        $packGroups = PackGroup::where('is_active', true)->orderBy('name_en')->get();
        $packDefinitions = collect();
        $packDefinitionsData = [];

        return view('dashboard.data_definition.data_product.item_packs.create', compact(
            'items', 'packTypes', 'packCapacities', 'packOperations', 'packGroups', 'packDefinitions', 'packDefinitionsData'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'pack_type_id' => 'nullable|exists:pack_types,id',
            'pack_capacity_id' => 'nullable|exists:pack_capacities,id',
            'quantity_in_pack' => 'nullable|numeric|min:0.001',
            'barcode' => 'nullable|string|max:255',
            'serial_separator' => 'nullable|string|max:20',
            'equivalency_factor' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'depth' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'is_weight_priced' => 'boolean',
            'allow_fraction_in_qty' => 'boolean',
            'is_default_pack' => 'boolean',
            'sort_order' => 'nullable|integer',
            'pack_operation_ids' => 'nullable|array',
            'pack_operation_ids.*' => 'exists:pack_operations,id',
            'pack_group_ids' => 'nullable|array',
            'pack_group_ids.*' => 'exists:pack_groups,id',
        ]);

        $validated['is_weight_priced'] = $request->boolean('is_weight_priced');
        $validated['allow_fraction_in_qty'] = $request->boolean('allow_fraction_in_qty');
        $validated['is_default_pack'] = $request->boolean('is_default_pack');
        $validated['quantity_in_pack'] = $validated['quantity_in_pack'] ?? 1;

        $itemPack = ItemPack::create($validated);

        if (!empty($validated['pack_operation_ids'])) {
            $itemPack->packOperations()->sync($validated['pack_operation_ids']);
        }
        if (!empty($validated['pack_group_ids'])) {
            $itemPack->packGroups()->sync($validated['pack_group_ids']);
        }

        return redirect()->route('item-packs.index')->with('success', 'Item Pack created successfully.');
    }

    public function show(ItemPack $itemPack)
    {
        $itemPack->load(['item', 'packType', 'packCapacity.uom', 'packOperations', 'packGroups']);

        return view('dashboard.data_definition.data_product.item_packs.show', compact('itemPack'));
    }

    public function edit(ItemPack $itemPack)
    {
        $items = Item::orderBy('name_en')->get();
        $packTypes = PackType::orderBy('name_en')->get();
        $packCapacities = PackCapacity::with('uom')->orderBy('value')->get();
        $packOperations = PackOperation::where('is_active', true)->orderBy('name_en')->get();
        $packGroups = PackGroup::where('is_active', true)->orderBy('name_en')->get();
        $packDefinitions = collect();

        $itemPack->load(['packOperations', 'packGroups']);

        return view('dashboard.data_definition.data_product.item_packs.edit', compact(
            'itemPack', 'items', 'packTypes', 'packCapacities', 'packOperations', 'packGroups', 'packDefinitions'
        ));
    }

    public function update(Request $request, ItemPack $itemPack)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'pack_type_id' => 'required|exists:pack_types,id',
            'pack_capacity_id' => 'required|exists:pack_capacities,id',
            'quantity_in_pack' => 'required|numeric|min:0.001',
            'barcode' => 'required|string|max:255|unique:item_packs,barcode,'.$itemPack->id,
            'serial_separator' => 'nullable|string|max:20',
            'equivalency_factor' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'depth' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'is_weight_priced' => 'boolean',
            'allow_fraction_in_qty' => 'boolean',
            'is_default_pack' => 'boolean',
            'sort_order' => 'nullable|integer',
            'pack_operation_ids' => 'nullable|array',
            'pack_operation_ids.*' => 'exists:pack_operations,id',
            'pack_group_ids' => 'nullable|array',
            'pack_group_ids.*' => 'exists:pack_groups,id',
        ]);

        $validated['is_weight_priced'] = $request->boolean('is_weight_priced');
        $validated['allow_fraction_in_qty'] = $request->boolean('allow_fraction_in_qty');
        $validated['is_default_pack'] = $request->boolean('is_default_pack');

        $itemPack->update($validated);
        $itemPack->packOperations()->sync($validated['pack_operation_ids'] ?? []);
        $itemPack->packGroups()->sync($validated['pack_group_ids'] ?? []);

        return redirect()->route('item-packs.index')->with('success', 'Item Pack updated successfully.');
    }

    public function destroy(ItemPack $itemPack)
    {
        $itemPack->packOperations()->detach();
        $itemPack->packGroups()->detach();
        $itemPack->delete();

        return redirect()->route('item-packs.index')->with('success', 'Item Pack deleted successfully.');
    }
}
