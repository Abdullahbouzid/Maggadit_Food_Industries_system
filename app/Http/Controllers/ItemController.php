<?php

// app/Http/Controllers/ItemController.php

namespace App\Http\Controllers;

use App\Imports\ItemsImport;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemModel;
use App\Models\ItemType;
use App\Models\PackCapacity;
use App\Models\PackGroup;
use App\Models\PackOperation;
use App\Models\PackType;
use App\Models\Uom;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::with(['itemType', 'category', 'brand', 'model', 'supplier']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('name_ar', 'like', "%{$search}%");
            });
        }

        if ($request->filled('item_type_id')) {
            $query->where('item_type_id', $request->item_type_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('is_inactive', $request->status == 'inactive');
        }

        $totalItems = Item::count();
        $activeItems = Item::where('is_inactive', false)->count();
        $inactiveItems = Item::where('is_inactive', true)->count();
        $withImage = Item::whereNotNull('image')->count();

        $perPage = $request->get('per_page', 20);
        if ($perPage === 'all') {
            $perPage = $query->count() ?: 1;
        }

        $items = $query->orderBy('created_at', 'desc')->paginate($perPage)->appends($request->query());

        $itemTypes = ItemType::where('is_active', true)->get();
        $categories = Category::all();

        return view('dashboard.data_definition.data_product.items.index', compact(
            'items', 'totalItems', 'activeItems', 'inactiveItems', 'withImage',
            'itemTypes', 'categories'
        ));
    }

    public function create()
    {
        $itemTypes = ItemType::where('is_active', true)->orderBy('name')->get();
        $categories = Category::all();
        $brands = Brand::where('show_in_customer_app', true)->orderBy('name')->get();
        $models = ItemModel::where('is_active', true)->orderBy('name_en')->get();
        $suppliers = Vendor::orderBy('vendor_name')->get();
        $packTypes = PackType::orderBy('name_en')->get();
        $packCapacities = PackCapacity::with('uom')->orderBy('value')->get();
        $packOperations = PackOperation::where('is_active', true)->orderBy('name_en')->get();
        $packGroups = PackGroup::where('is_active', true)->orderBy('name_en')->get();
        $packDefinitions = \App\Models\PackDefinition::with(['packType', 'packCapacity', 'packCapacity.uom'])->orderBy('sort_order')->orderBy('name_en')->get();

        return view('dashboard.data_definition.data_product.items.create', compact(
            'itemTypes', 'categories', 'brands', 'models', 'suppliers',
            'packTypes', 'packCapacities', 'packOperations', 'packGroups', 'packDefinitions'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:items,code',
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'definition' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'item_type_id' => 'nullable|exists:item_types,id',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'model_id' => 'nullable|exists:item_models,id',
            'supplier_id' => 'nullable|exists:vendors,id',
            'shelf_life_time' => 'nullable|integer',
            'sequence' => 'nullable|string|max:50',
            'van_stock_tolerance' => 'nullable|numeric',
            'storage_tolerance' => 'nullable|numeric',
            'weight_tolerance' => 'nullable|numeric',
            'low_stock_qty' => 'nullable|numeric',
            'is_inactive' => 'boolean',
            'is_new' => 'boolean',
            'is_price_protected' => 'boolean',
            'is_power_sku' => 'boolean',
            'is_default_batch' => 'boolean',
            'is_temp_credit' => 'boolean',
            'is_batch_priced' => 'boolean',
            'is_initiative' => 'boolean',
            'force_default_pack' => 'boolean',
            'has_serial_number' => 'boolean',
            'required_by_liter' => 'boolean',
            'required_by_weight' => 'boolean',
            'packs' => 'nullable|array',
            'packs.*.pack_definition_id' => 'required_with:packs|nullable|exists:pack_definitions,id',
            'additional_images' => 'nullable|array',
            'additional_images.*' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('items', 'uploads');
        }

        $item = Item::create($validated);

        // Save packs from pack definitions
        if ($request->has('packs')) {
            foreach ($request->packs as $index => $packData) {
                if (isset($packData['pack_definition_id'])) {
                    $packDef = \App\Models\PackDefinition::with(['packType', 'packCapacity', 'packOperations', 'packGroups'])->find($packData['pack_definition_id']);
                    if ($packDef) {
                        $pack = $item->packs()->create([
                            'pack_type_id' => $packDef->pack_type_id,
                            'pack_capacity_id' => $packDef->pack_capacity_id,
                            'quantity_in_pack' => $packDef->quantity_in_pack,
                            'barcode' => $packDef->barcode,
                            'serial_separator' => $packDef->serial_separator,
                            'equivalency_factor' => $packDef->equivalency_factor,
                            'height' => $packDef->height,
                            'width' => $packDef->width,
                            'depth' => $packDef->depth,
                            'weight' => $packDef->weight,
                            'is_weight_priced' => $packDef->is_weight_priced,
                            'allow_fraction_in_qty' => $packDef->allow_fraction_in_qty,
                            'is_default_pack' => $packDef->is_default_pack,
                            'sort_order' => $index,
                        ]);

                        $pack->packOperations()->sync($packDef->packOperations->pluck('id'));
                        $pack->packGroups()->sync($packDef->packGroups->pluck('id'));
                    }
                }
            }
        }

        // Save additional images
        if ($request->hasFile('additional_images')) {
            foreach ($request->file('additional_images') as $index => $img) {
                $path = $img->store('item_images', 'uploads');
                $item->images()->create([
                    'image_path' => $path,
                    'sort_order' => $index,
                ]);
            }
        }

        return redirect()->route('items.index')->with('success', 'Item created successfully.');
    }

    public function show(Item $item)
    {
        $item->load(['itemType', 'category', 'brand', 'model', 'supplier', 'packs.packType', 'packs.packCapacity.uom', 'packs.packOperations', 'packs.packGroups', 'images']);

        return view('dashboard.data_definition.data_product.items.show', compact('item'));
    }

    public function edit(Item $item)
    {
        $item->load(['packs.packType', 'packs.packCapacity.uom', 'packs.packOperations', 'packs.packGroups', 'images']);
        $itemTypes = ItemType::where('is_active', true)->orderBy('name')->get();
        $categories = Category::all();
        $brands = Brand::where('show_in_customer_app', true)->orderBy('name')->get();
        $models = ItemModel::where('is_active', true)->orderBy('name_en')->get();
        $suppliers = Vendor::orderBy('vendor_name')->get();
        $packTypes = PackType::orderBy('name_en')->get();
        $packCapacities = PackCapacity::with('uom')->orderBy('value')->get();
        $packOperations = PackOperation::where('is_active', true)->orderBy('name_en')->get();
        $packGroups = PackGroup::where('is_active', true)->orderBy('name_en')->get();
        $packDefinitions = \App\Models\PackDefinition::with(['packType', 'packCapacity', 'packCapacity.uom'])->orderBy('sort_order')->orderBy('name_en')->get();

        return view('dashboard.data_definition.data_product.items.edit', compact(
            'item', 'itemTypes', 'categories', 'brands', 'models', 'suppliers',
            'packTypes', 'packCapacities', 'packOperations', 'packGroups', 'packDefinitions'
        ));
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:items,code,'.$item->id,
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'definition' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'item_type_id' => 'nullable|exists:item_types,id',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'model_id' => 'nullable|exists:item_models,id',
            'supplier_id' => 'nullable|exists:vendors,id',
            'shelf_life_time' => 'nullable|integer',
            'sequence' => 'nullable|string|max:50',
            'van_stock_tolerance' => 'nullable|numeric',
            'storage_tolerance' => 'nullable|numeric',
            'weight_tolerance' => 'nullable|numeric',
            'low_stock_qty' => 'nullable|numeric',
            'is_inactive' => 'boolean',
            'is_new' => 'boolean',
            'is_price_protected' => 'boolean',
            'is_power_sku' => 'boolean',
            'is_default_batch' => 'boolean',
            'is_temp_credit' => 'boolean',
            'is_batch_priced' => 'boolean',
            'is_initiative' => 'boolean',
            'force_default_pack' => 'boolean',
            'has_serial_number' => 'boolean',
            'required_by_liter' => 'boolean',
            'required_by_weight' => 'boolean',
            'packs' => 'nullable|array',
            'packs.*.pack_definition_id' => 'nullable|exists:pack_definitions,id',
            'additional_images' => 'nullable|array',
            'additional_images.*' => 'nullable|image|max:2048',
            // أضف هذا الحقل إذا أردت السماح بحذف صور معينة
            'deleted_images' => 'nullable|array',
            'deleted_images.*' => 'nullable|exists:item_images,id',
        ]);

        if ($request->hasFile('image')) {
            if ($item->image) {
                Storage::disk('uploads')->delete($item->image);
            }
            $validated['image'] = $request->file('image')->store('items', 'uploads');
        }

        $item->update($validated);

        // === تحديث الحزم ===
        $item->packs()->delete();
        if ($request->has('packs')) {
            foreach ($request->packs as $index => $packData) {
                $packCreateData = [
                    'sort_order' => $index,
                ];

                if (isset($packData['pack_definition_id']) && $packData['pack_definition_id']) {
                    $packDef = \App\Models\PackDefinition::with(['packType', 'packCapacity', 'packOperations', 'packGroups'])->find($packData['pack_definition_id']);
                    if ($packDef) {
                        $packCreateData['pack_definition_id'] = $packDef->id;
                        $packCreateData['pack_type_id'] = $packDef->pack_type_id;
                        $packCreateData['pack_capacity_id'] = $packDef->pack_capacity_id;
                        $packCreateData['quantity_in_pack'] = $packDef->quantity_in_pack;
                        $packCreateData['barcode'] = $packDef->barcode;
                        $packCreateData['serial_separator'] = $packDef->serial_separator;
                        $packCreateData['equivalency_factor'] = $packDef->equivalency_factor;
                        $packCreateData['height'] = $packDef->height;
                        $packCreateData['width'] = $packDef->width;
                        $packCreateData['depth'] = $packDef->depth;
                        $packCreateData['weight'] = $packDef->weight;
                        $packCreateData['is_weight_priced'] = $packDef->is_weight_priced;
                        $packCreateData['allow_fraction_in_qty'] = $packDef->allow_fraction_in_qty;
                        $packCreateData['is_default_pack'] = $packDef->is_default_pack;

                        $pack = $item->packs()->create($packCreateData);
                        $pack->packOperations()->sync($packDef->packOperations->pluck('id'));
                        $pack->packGroups()->sync($packDef->packGroups->pluck('id'));
                        continue;
                    }
                }

                if (!empty($packData['pack_type_id']) && !empty($packData['pack_capacity_id'])) {
                    $packCreateData['pack_type_id'] = $packData['pack_type_id'] ?? null;
                    $packCreateData['pack_capacity_id'] = $packData['pack_capacity_id'] ?? null;
                    $packCreateData['quantity_in_pack'] = $packData['quantity_in_pack'] ?? 1;
                    $packCreateData['barcode'] = $packData['barcode'] ?? null;
                    $packCreateData['serial_separator'] = $packData['serial_separator'] ?? null;
                    $packCreateData['equivalency_factor'] = $packData['equivalency_factor'] ?? null;
                    $packCreateData['height'] = $packData['height'] ?? null;
                    $packCreateData['width'] = $packData['width'] ?? null;
                    $packCreateData['depth'] = $packData['depth'] ?? null;
                    $packCreateData['weight'] = $packData['weight'] ?? null;
                    $packCreateData['is_weight_priced'] = isset($packData['is_weight_priced']) ? 1 : 0;
                    $packCreateData['allow_fraction_in_qty'] = isset($packData['allow_fraction_in_qty']) ? 1 : 0;
                    $packCreateData['is_default_pack'] = isset($packData['is_default_pack']) ? 1 : 0;

                    $pack = $item->packs()->create($packCreateData);

                    if (!empty($packData['pack_operation_ids'])) {
                        $pack->packOperations()->sync($packData['pack_operation_ids']);
                    }
                    if (!empty($packData['pack_group_ids'])) {
                        $pack->packGroups()->sync($packData['pack_group_ids']);
                    }
                }
            }
        }

        // === تحديث الصور الإضافية ===
        if ($request->has('deleted_images')) {
            foreach ($request->deleted_images as $imageId) {
                $img = $item->images()->find($imageId);
                if ($img) {
                    Storage::disk('uploads')->delete($img->image_path);
                    $img->delete();
                }
            }
        }

        if ($request->hasFile('additional_images')) {
            $maxOrder = $item->images()->max('sort_order') ?? -1;
            foreach ($request->file('additional_images') as $index => $img) {
                $path = $img->store('item_images', 'uploads');
                $item->images()->create([
                    'image_path' => $path,
                    'sort_order' => $maxOrder + $index + 1,
                ]);
            }
        }

        return redirect()->route('items.index')->with('success', 'Item updated successfully.');
    }

    public function destroy(Item $item)
    {
        if ($item->image) {
            Storage::disk('uploads')->delete($item->image);
        }
        foreach ($item->images as $img) {
            Storage::disk('uploads')->delete($img->image_path);
        }
        $item->delete();

        return redirect()->route('items.index')->with('success', 'Item deleted successfully.');
    }

    // داخل ItemController

    public function downloadTemplate()
    {
        $export = new class implements FromCollection, WithHeadings
        {
            public function collection()
            {
                return collect([]);
            }

            public function headings(): array
            {
                return [
                    'code', 'name_en', 'name_ar', 'definition', 'item_type_id', 'category_id',
                    'brand_id', 'model_id', 'supplier_id', 'shelf_life_time', 'sequence',
                    'van_stock_tolerance', 'storage_tolerance', 'weight_tolerance', 'low_stock_qty',
                    'is_inactive', 'is_new', 'is_price_protected', 'is_power_sku', 'is_default_batch',
                    'is_temp_credit', 'is_batch_priced', 'is_initiative', 'force_default_pack',
                    'has_serial_number', 'required_by_liter', 'required_by_weight',
                ];
            }
        };

        return Excel::download($export, 'items_template.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['import_file' => 'required|file|mimes:xlsx,xls,csv']);
        try {
            $import = new ItemsImport;
            Excel::import($import, $request->file('import_file'));
            $errors = $import->getErrors();
            if (count($errors)) {
                return redirect()->route('items.index')->with('warning', 'Import with errors.')->with('import_errors', $errors);
            }

            return redirect()->route('items.index')->with('success', 'Items imported successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: '.$e->getMessage());
        }
    }
}
