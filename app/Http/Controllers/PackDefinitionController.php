<?php

namespace App\Http\Controllers;

use App\Models\PackDefinition;
use App\Models\PackGroup;
use App\Models\PackOperation;
use App\Models\PackType;
use App\Models\PackCapacity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PackDefinitionController extends Controller
{
    public function index(Request $request)
    {
        $query = PackDefinition::with(['packType', 'packCapacity']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('name_en', 'LIKE', "%{$search}%")
                    ->orWhere('name_ar', 'LIKE', "%{$search}%")
                    ->orWhere('code', 'LIKE', "%{$search}%")
                    ->orWhere('barcode', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('pack_type_id')) {
            $query->where('pack_type_id', $request->pack_type_id);
        }

        $packDefinitions = $query->orderBy('sort_order')->orderBy('name_en')->paginate(20)->appends($request->query());
        $packTypes = PackType::orderBy('name_en')->get();
        $total = PackDefinition::count();

        return view('dashboard.data_definition.pack_definitions.index', compact('packDefinitions', 'packTypes', 'total'));
    }

    public function show(PackDefinition $packDefinition)
    {
        $packDefinition->load('packType', 'packCapacity', 'packOperations', 'packGroups');

        return view('dashboard.data_definition.pack_definitions.show', compact('packDefinition'));
    }

    public function create()
    {
        $packTypes = PackType::orderBy('name_en')->get();
        $packCapacities = PackCapacity::with('uom')->orderBy('value')->get();
        $packOperations = PackOperation::orderBy('name_en')->get();
        $packGroups = PackGroup::orderBy('name_en')->get();
        $items = \App\Models\Item::orderBy('name_en')->get();

        return view('dashboard.data_definition.pack_definitions.create', compact('packTypes', 'packCapacities', 'packOperations', 'packGroups', 'items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:100|unique:pack_definitions,code',
            'item_id' => 'nullable|exists:items,id',
            'pack_type_id' => 'nullable|exists:pack_types,id',
            'pack_capacity_id' => 'required|exists:pack_capacities,id',
            'quantity_in_pack' => 'required|integer|min:1',
            'barcode' => 'nullable|string|max:255|unique:pack_definitions,barcode',
            'serial_separator' => 'nullable|string|max:50',
            'equivalency_factor' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'depth' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'is_weight_priced' => 'nullable|boolean',
            'allow_fraction_in_qty' => 'nullable|boolean',
            'is_default_pack' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'pack_operations' => 'nullable|array',
            'pack_operations.*' => 'exists:pack_operations,id',
            'pack_groups' => 'nullable|array',
            'pack_groups.*' => 'exists:pack_groups,id',
        ]);

        $validated['is_weight_priced'] = $request->has('is_weight_priced');
        $validated['allow_fraction_in_qty'] = $request->has('allow_fraction_in_qty');
        $validated['is_default_pack'] = $request->has('is_default_pack');

        DB::transaction(function () use ($validated, $request) {
            $packOperations = $validated['pack_operations'] ?? [];
            $packGroups = $validated['pack_groups'] ?? [];
            unset($validated['pack_operations'], $validated['pack_groups']);

            $packDefinition = PackDefinition::create($validated);

            $packDefinition->packOperations()->sync($packOperations);
            $packDefinition->packGroups()->sync($packGroups);
        });

        return redirect()->route('pack-definitions.index')->with('success', 'تم إنشاء تعريف الباكج بنجاح.');
    }

    public function edit(PackDefinition $packDefinition)
    {
        $packTypes = PackType::orderBy('name_en')->get();
        $packCapacities = PackCapacity::with('uom')->orderBy('value')->get();
        $packOperations = PackOperation::orderBy('name_en')->get();
        $packGroups = PackGroup::orderBy('name_en')->get();

        $packDefinition->load('packOperations', 'packGroups');

        return view('dashboard.data_definition.pack_definitions.edit', compact('packDefinition', 'packTypes', 'packCapacities', 'packOperations', 'packGroups'));
    }

    public function update(Request $request, PackDefinition $packDefinition)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:100|unique:pack_definitions,code,' . $packDefinition->id,
            'pack_type_id' => 'nullable|exists:pack_types,id',
            'pack_capacity_id' => 'required|exists:pack_capacities,id',
            'quantity_in_pack' => 'required|integer|min:1',
            'barcode' => 'nullable|string|max:255|unique:pack_definitions,barcode,' . $packDefinition->id,
            'serial_separator' => 'nullable|string|max:50',
            'equivalency_factor' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'depth' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'is_weight_priced' => 'nullable|boolean',
            'allow_fraction_in_qty' => 'nullable|boolean',
            'is_default_pack' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'pack_operations' => 'nullable|array',
            'pack_operations.*' => 'exists:pack_operations,id',
            'pack_groups' => 'nullable|array',
            'pack_groups.*' => 'exists:pack_groups,id',
        ]);

        $validated['is_weight_priced'] = $request->has('is_weight_priced');
        $validated['allow_fraction_in_qty'] = $request->has('allow_fraction_in_qty');
        $validated['is_default_pack'] = $request->has('is_default_pack');

        DB::transaction(function () use ($validated, $request, $packDefinition) {
            $packOperations = $validated['pack_operations'] ?? [];
            $packGroups = $validated['pack_groups'] ?? [];
            unset($validated['pack_operations'], $validated['pack_groups']);

            $packDefinition->update($validated);
            $packDefinition->packOperations()->sync($packOperations);
            $packDefinition->packGroups()->sync($packGroups);
        });

        return redirect()->route('pack-definitions.index')->with('success', 'تم تحديث تعريف الباكج بنجاح.');
    }

    public function destroy(PackDefinition $packDefinition)
    {
        $packDefinition->delete();

        return redirect()->route('pack-definitions.index')->with('success', 'تم حذف تعريف الباكج بنجاح.');
    }

    /**
     * AJAX: جلب بيانات تعريف الباكج
     */
    public function showAjax(PackDefinition $packDefinition)
    {
        $packDefinition->load('packType', 'packCapacity', 'packCapacity.uom', 'packOperations', 'packGroups');

        return response()->json([
            'id' => $packDefinition->id,
            'name' => $packDefinition->name,
            'name_en' => $packDefinition->name_en,
            'name_ar' => $packDefinition->name_ar,
            'code' => $packDefinition->code,
            'pack_type_id' => $packDefinition->pack_type_id,
            'pack_type_name' => $packDefinition->packType?->name_ar ?? $packDefinition->packType?->name_en,
            'pack_capacity_id' => $packDefinition->pack_capacity_id,
            'pack_capacity_value' => $packDefinition->packCapacity?->value,
            'pack_capacity_uom' => $packDefinition->packCapacity?->uom?->code,
            'quantity_in_pack' => $packDefinition->quantity_in_pack,
            'barcode' => $packDefinition->barcode,
            'serial_separator' => $packDefinition->serial_separator,
            'equivalency_factor' => $packDefinition->equivalency_factor,
            'height' => $packDefinition->height,
            'width' => $packDefinition->width,
            'depth' => $packDefinition->depth,
            'weight' => $packDefinition->weight,
            'is_weight_priced' => $packDefinition->is_weight_priced,
            'allow_fraction_in_qty' => $packDefinition->allow_fraction_in_qty,
            'is_default_pack' => $packDefinition->is_default_pack,
            'sort_order' => $packDefinition->sort_order,
            'description' => $packDefinition->description,
            'pack_operations' => $packDefinition->packOperations->pluck('id'),
            'pack_groups' => $packDefinition->packGroups->pluck('id'),
        ]);
    }
}
