<?php
// app/Http/Controllers/ItemController.php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemPack;
use App\Models\ItemImage;
use App\Models\ItemType;
use App\Models\Category;
use App\Models\Brand;
use App\Models\ItemModel;
use App\Models\Vendor;
use App\Models\Uom;
use App\Models\PackOperation;
use App\Models\PackGroup;
use App\Exports\ItemsExport;
use App\Imports\ItemsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;

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
        $uoms = Uom::where('is_active', true)->orderBy('name_en')->get();
        $packOperations = PackOperation::where('is_active', true)->orderBy('name_en')->get();
        $packGroups = PackGroup::where('is_active', true)->orderBy('name_en')->get();

        return view('dashboard.data_definition.data_product.items.create', compact(
            'itemTypes', 'categories', 'brands', 'models', 'suppliers',
            'uoms', 'packOperations', 'packGroups'
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
            'packs.*.uom_id' => 'required_with:packs|nullable|exists:uoms,id',
            'packs.*.quantity' => 'required_with:packs|nullable|numeric',
            'packs.*.barcode' => 'required_with:packs|nullable|string|max:255',
            'packs.*.pack_operation_ids' => 'nullable|array',
            'packs.*.pack_operation_ids.*' => 'nullable|exists:pack_operations,id',
            'packs.*.pack_group_ids' => 'nullable|array',
            'packs.*.pack_group_ids.*' => 'nullable|exists:pack_groups,id',
            'packs.*.serial_separator' => 'nullable|string|max:50',
            'packs.*.equivalency_factor' => 'nullable|numeric|min:0',
            'packs.*.height' => 'nullable|numeric|min:0',
            'packs.*.width' => 'nullable|numeric|min:0',
            'packs.*.depth' => 'nullable|numeric|min:0',
            'packs.*.weight' => 'nullable|numeric|min:0',
            'packs.*.is_weight_priced' => 'boolean',
            'packs.*.allow_fraction_in_qty' => 'boolean',
            'packs.*.is_default_pack' => 'boolean',
            'additional_images' => 'nullable|array',
            'additional_images.*' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('items', 'public');
        }

        $item = Item::create($validated);

        // Save packs
        if ($request->has('packs')) {
            foreach ($request->packs as $index => $packData) {
                $pack = $item->packs()->create([
                    'uom_id' => $packData['uom_id'],
                    'quantity' => $packData['quantity'],
                    'barcode' => $packData['barcode'],
                    'serial_separator' => $packData['serial_separator'] ?? null,
                    'equivalency_factor' => $packData['equivalency_factor'] ?? null,
                    'height' => $packData['height'] ?? null,
                    'width' => $packData['width'] ?? null,
                    'depth' => $packData['depth'] ?? null,
                    'weight' => $packData['weight'] ?? null,
                    'is_weight_priced' => $packData['is_weight_priced'] ?? false,
                    'allow_fraction_in_qty' => $packData['allow_fraction_in_qty'] ?? false,
                    'is_default_pack' => $packData['is_default_pack'] ?? false,
                    'sort_order' => $index,
                ]);

                if (!empty($packData['pack_operation_ids'])) {
                    $pack->packOperations()->sync($packData['pack_operation_ids']);
                }
                if (!empty($packData['pack_group_ids'])) {
                    $pack->packGroups()->sync($packData['pack_group_ids']);
                }
            }
        }

        // Save additional images
        if ($request->hasFile('additional_images')) {
            foreach ($request->file('additional_images') as $index => $img) {
                $path = $img->store('item_images', 'public');
                $item->images()->create([
                    'image_path' => $path,
                    'sort_order' => $index
                ]);
            }
        }

        return redirect()->route('items.index')->with('success', 'Item created successfully.');
    }

  public function show(Item $item)
{
    $item->load(['itemType', 'category', 'brand', 'model', 'supplier', 'packs.uom', 'packs.packOperations', 'packs.packGroups', 'images']);
    return view('dashboard.data_definition.data_product.items.show', compact('item'));
}

    public function edit(Item $item)
    {
        $item->load(['packs.uom', 'packs.packOperations', 'packs.packGroups', 'images']);
        $itemTypes = ItemType::where('is_active', true)->orderBy('name')->get();
        $categories = Category::all();
        $brands = Brand::where('show_in_customer_app', true)->orderBy('name')->get();
        $models = ItemModel::where('is_active', true)->orderBy('name_en')->get();
        $suppliers = Vendor::orderBy('vendor_name')->get();
        $uoms = Uom::where('is_active', true)->orderBy('name_en')->get();
        $packOperations = PackOperation::where('is_active', true)->orderBy('name_en')->get();
        $packGroups = PackGroup::where('is_active', true)->orderBy('name_en')->get();

        return view('dashboard.data_definition.data_product.items.edit', compact(
            'item', 'itemTypes', 'categories', 'brands', 'models', 'suppliers',
            'uoms', 'packOperations', 'packGroups'
        ));
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:items,code,' . $item->id,
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
            'packs.*.uom_id' => 'nullable|exists:uoms,id',
            'packs.*.quantity' => 'nullable|numeric',
            'packs.*.barcode' => 'nullable|string|max:255',
            'packs.*.pack_operation_ids' => 'nullable|array',
            'packs.*.pack_operation_ids.*' => 'nullable|exists:pack_operations,id',
            'packs.*.pack_group_ids' => 'nullable|array',
            'packs.*.pack_group_ids.*' => 'nullable|exists:pack_groups,id',
            'packs.*.serial_separator' => 'nullable|string|max:50',
            'packs.*.equivalency_factor' => 'nullable|numeric|min:0',
            'packs.*.height' => 'nullable|numeric|min:0',
            'packs.*.width' => 'nullable|numeric|min:0',
            'packs.*.depth' => 'nullable|numeric|min:0',
            'packs.*.weight' => 'nullable|numeric|min:0',
            'packs.*.is_weight_priced' => 'boolean',
            'packs.*.allow_fraction_in_qty' => 'boolean',
            'packs.*.is_default_pack' => 'boolean',
            'additional_images' => 'nullable|array',
            'additional_images.*' => 'nullable|image|max:2048',
            // أضف هذا الحقل إذا أردت السماح بحذف صور معينة
            'deleted_images' => 'nullable|array',
            'deleted_images.*' => 'nullable|exists:item_images,id',
        ]);

        // معالجة الصورة الرئيسية
        if ($request->hasFile('image')) {
            if ($item->image) Storage::disk('public')->delete($item->image);
            $validated['image'] = $request->file('image')->store('items', 'public');
        }

        $item->update($validated);

        // === تحديث الحزم ===
        $item->packs()->delete();
        if ($request->has('packs')) {
            foreach ($request->packs as $index => $packData) {
                // تأكد من وجود البيانات الأساسية
                if (empty($packData['uom_id']) || !isset($packData['quantity'])) {
                    continue;
                }
                $pack = $item->packs()->create([
                    'uom_id' => $packData['uom_id'],
                    'quantity' => $packData['quantity'],
                    'barcode' => $packData['barcode'] ?? null,
                    'serial_separator' => $packData['serial_separator'] ?? null,
                    'equivalency_factor' => $packData['equivalency_factor'] ?? null,
                    'height' => $packData['height'] ?? null,
                    'width' => $packData['width'] ?? null,
                    'depth' => $packData['depth'] ?? null,
                    'weight' => $packData['weight'] ?? null,
                    'is_weight_priced' => $packData['is_weight_priced'] ?? false,
                    'allow_fraction_in_qty' => $packData['allow_fraction_in_qty'] ?? false,
                    'is_default_pack' => $packData['is_default_pack'] ?? false,
                    'sort_order' => $index,
                ]);

                if (!empty($packData['pack_operation_ids'])) {
                    $pack->packOperations()->sync($packData['pack_operation_ids']);
                }
                if (!empty($packData['pack_group_ids'])) {
                    $pack->packGroups()->sync($packData['pack_group_ids']);
                }
            }
        }

        // === تحديث الصور الإضافية ===
        // حذف الصور المحددة من قبل المستخدم
        if ($request->has('deleted_images')) {
            foreach ($request->deleted_images as $imageId) {
                $img = $item->images()->find($imageId);
                if ($img) {
                    Storage::disk('public')->delete($img->image_path);
                    $img->delete();
                }
            }
        }

        // إضافة صور جديدة مع الاحتفاظ بالقديمة
        if ($request->hasFile('additional_images')) {
            $maxOrder = $item->images()->max('sort_order') ?? -1;
            foreach ($request->file('additional_images') as $index => $img) {
                $path = $img->store('item_images', 'public');
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
        if ($item->image) Storage::disk('public')->delete($item->image);
        foreach ($item->images as $img) {
            Storage::disk('public')->delete($img->image_path);
        }
        $item->delete();
        return redirect()->route('items.index')->with('success', 'Item deleted successfully.');
    }

  // داخل ItemController


        public function downloadTemplate()
        {
            $export = new class implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                public function collection() { return collect([]); }
                public function headings(): array {
                    return [
                        'code', 'name_en', 'name_ar', 'definition', 'item_type_id', 'category_id',
                        'brand_id', 'model_id', 'supplier_id', 'shelf_life_time', 'sequence',
                        'van_stock_tolerance', 'storage_tolerance', 'weight_tolerance', 'low_stock_qty',
                        'is_inactive', 'is_new', 'is_price_protected', 'is_power_sku', 'is_default_batch',
                        'is_temp_credit', 'is_batch_priced', 'is_initiative', 'force_default_pack',
                        'has_serial_number', 'required_by_liter', 'required_by_weight'
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
                return back()->with('error', 'Import failed: ' . $e->getMessage());
            }
        }
}