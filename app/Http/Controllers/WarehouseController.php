<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\BatchItem;
use App\Models\BatchItemStock;
use App\Models\BatchMovement;
use App\Models\Brand;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Item;
use App\Models\Organization;
use App\Models\State;
use App\Models\Warehouse;
use App\Models\WarehouseType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $query = Warehouse::with(['warehouseType', 'organization', 'country', 'state', 'city', 'area']);
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('name_ar', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        if ($request->filled('warehouse_type_id')) {
            $query->where('warehouse_type_id', $request->warehouse_type_id);
        }
        if ($request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status == 'active');
        }
        $perPage = $request->get('per_page', 20);
        if ($perPage === 'all') {
            $perPage = $query->count() ?: 1;
        }
        $warehouses = $query->orderBy('sort_order')->orderBy('code')->paginate($perPage)->appends($request->query());
        $total = Warehouse::count();
        $active = Warehouse::where('is_active', true)->count();
        $inactive = Warehouse::where('is_active', false)->count();
        $warehouseTypes = WarehouseType::where('is_active', true)->orderBy('name_en')->get();
        $organizations = Organization::orderBy('name')->get();

        return view('dashboard.data_definition.w&v.warehouse.index', compact('warehouses', 'total', 'active', 'inactive', 'warehouseTypes', 'organizations'));
    }

    public function create()
    {
        $warehouseTypes = WarehouseType::where('is_active', true)->orderBy('name_en')->get();
        $organizations = Organization::orderBy('name')->get();
        $countries = Country::orderBy('name')->get();

        return view('dashboard.data_definition.w&v.warehouse.create', compact('warehouseTypes', 'organizations', 'countries'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:warehouses,code',
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'warehouse_type_id' => 'required|exists:warehouse_types,id',
            'organization_id' => 'required|exists:organizations,id',
            'phone' => 'nullable|string|max:50',
            'fax' => 'nullable|string|max:50',
            'barcode' => 'nullable|string|max:255',
            'address_en' => 'nullable|string',
            'address_ar' => 'nullable|string',
            'gps_latitude' => 'nullable|string|max:50',
            'gps_longitude' => 'nullable|string|max:50',
            'zip_code' => 'nullable|string|max:20',
            'country_id' => 'nullable|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',
            'city_id' => 'nullable|exists:cities,id',
            'area_id' => 'nullable|exists:areas,id',
            'street' => 'nullable|string|max:255',
            'destinations' => 'nullable|string',
            'sources' => 'nullable|string',
            'employees' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);
        $validated['is_active'] = $request->has('is_active');
        $validated['destinations'] = $this->parseCommaField($validated['destinations'] ?? null);
        $validated['sources'] = $this->parseCommaField($validated['sources'] ?? null);
        $validated['employees'] = $this->parseCommaField($validated['employees'] ?? null);
        Warehouse::create($validated);

        return redirect()->route('warehouses.index')->with('success', 'Warehouse created successfully.');
    }

    public function show(Request $request, Warehouse $warehouse)
    {
        $search = $request->input('search');

        $stocks = BatchItemStock::where('warehouse_id', $warehouse->id)
            ->with(['batchItem.item', 'batchItem.pack', 'batchItem.batch'])
            ->get();

        $validStocks = $stocks->filter(function ($stock) {
            return $stock->batchItem !== null
                && $stock->batchItem->item !== null
                && $stock->batchItem->batch !== null;
        });

        // تطبيق البحث
        if ($search) {
            $searchLower = strtolower($search);
            $validStocks = $validStocks->filter(function ($stock) use ($searchLower) {
                $item = $stock->batchItem->item;
                $batch = $stock->batchItem->batch;
                $pack = $stock->batchItem->pack;

                return
                    stripos($item->name_en ?? '', $searchLower) !== false ||
                    stripos($item->name_ar ?? '', $searchLower) !== false ||
                    stripos($item->code ?? '', $searchLower) !== false ||
                    stripos($batch->batch_number ?? '', $searchLower) !== false ||
                    ($pack && stripos($pack->barcode ?? '', $searchLower) !== false);
            });
        }

        // تجميع حسب (item_id, item_pack_id)
        $grouped = $validStocks->groupBy(function ($stock) {
            $itemId = $stock->batchItem->item_id;
            $packId = $stock->batchItem->item_pack_id ?? 'null';

            return $itemId.'-'.$packId;
        });

        $groupedProducts = $grouped->map(function ($stockGroup) {
            $first = $stockGroup->first();
            if (! $first || ! $first->batchItem || ! $first->batchItem->item) {
                return null;
            }

            $item = $first->batchItem->item;
            $pack = $first->batchItem->pack;

            $totalQuantity = $stockGroup->sum('quantity');

            $activeBatches = $stockGroup->filter(function ($stock) {
                return $stock->batchItem->batch->status === 'active';
            })->map(function ($stock) {
                return [
                    'batch_number' => $stock->batchItem->batch->batch_number,
                    'batch_id' => $stock->batchItem->batch->id,
                    'batch_item_id' => $stock->batch_item_id,
                    'quantity' => $stock->quantity,
                    'expiry_date' => $stock->batchItem->batch->expiry_date,
                ];
            })->values();

            // Collect batch item IDs for checkboxes
            $batchItemIds = $stockGroup->pluck('batch_item_id')->unique()->values()->all();

            return [
                'item' => $item,
                'item_id' => $item->id,
                'item_name' => $item->name_en ?? 'غير معروف',
                'item_code' => $item->code ?? '',
                'image_url' => $item->main_image_url,
                'pack' => $pack,
                'item_pack_id' => $item->item_pack_id ?? null,
                'total_quantity' => $totalQuantity,
                'batches' => $activeBatches,
                'batches_count' => $activeBatches->count(),
                'batch_item_ids' => $batchItemIds,
            ];
        })->filter(function ($group) {
            return $group !== null && isset($group['total_quantity']) && $group['total_quantity'] > 0;
        })->sortBy('item_name')->values();

        // إحصائيات العبوات مع النسب المئوية
        $packStatsRaw = $validStocks->groupBy('batchItem.item_pack_id')->map(function ($group) {
            $first = $group->first();
            if (! $first || ! $first->batchItem) {
                return null;
            }

            return [
                'pack' => $first->batchItem->pack,
                'total_quantity' => $group->sum('quantity'),
            ];
        })->filter(function ($item) {
            return $item !== null && $item['total_quantity'] > 0;
        })->values();

        $totalQuantityAll = $packStatsRaw->sum('total_quantity');

        $packStats = $packStatsRaw->map(function ($item) use ($totalQuantityAll) {
            $percentage = $totalQuantityAll > 0 ? ($item['total_quantity'] / $totalQuantityAll) * 100 : 0;
            $packName = $item['pack'] ? $item['pack']->barcode : 'بدون عبوة';

            return [
                'pack' => $item['pack'],
                'pack_name' => $packName,
                'total_quantity' => $item['total_quantity'],
                'percentage' => round($percentage, 1),
            ];
        })->sortByDesc('total_quantity')->values();

        $totalPackTypes = $packStats->count();
        $totalQuantityByPack = $packStats->sum('total_quantity');

        $totalProducts = $groupedProducts->count();
        $totalQuantity = $groupedProducts->sum('total_quantity');
        $totalBatches = $groupedProducts->sum('batches_count');

        return view('dashboard.data_definition.w&v.warehouse.show', compact(
            'warehouse', 'groupedProducts', 'totalProducts', 'totalQuantity', 'totalBatches',
            'packStats', 'totalPackTypes', 'totalQuantityByPack', 'search'
        ));
    }

    public function edit(Warehouse $warehouse)
    {
        $warehouseTypes = WarehouseType::where('is_active', true)->orderBy('name_en')->get();
        $organizations = Organization::orderBy('name')->get();
        $countries = Country::orderBy('name')->get();
        $warehouse->destinations = json_decode($warehouse->destinations, true);
        $warehouse->sources = json_decode($warehouse->sources, true);
        $warehouse->employees = json_decode($warehouse->employees, true);

        return view('dashboard.data_definition.w&v.warehouse.edit', compact('warehouse', 'warehouseTypes', 'organizations', 'countries'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:warehouses,code,'.$warehouse->id,
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'warehouse_type_id' => 'required|exists:warehouse_types,id',
            'organization_id' => 'required|exists:organizations,id',
            'phone' => 'nullable|string|max:50',
            'fax' => 'nullable|string|max:50',
            'barcode' => 'nullable|string|max:255',
            'address_en' => 'nullable|string',
            'address_ar' => 'nullable|string',
            'gps_latitude' => 'nullable|string|max:50',
            'gps_longitude' => 'nullable|string|max:50',
            'zip_code' => 'nullable|string|max:20',
            'country_id' => 'nullable|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',
            'city_id' => 'nullable|exists:cities,id',
            'area_id' => 'nullable|exists:areas,id',
            'street' => 'nullable|string|max:255',
            'destinations' => 'nullable|string',
            'sources' => 'nullable|string',
            'employees' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);
        $validated['is_active'] = $request->has('is_active');
        $validated['destinations'] = $this->parseCommaField($validated['destinations'] ?? null);
        $validated['sources'] = $this->parseCommaField($validated['sources'] ?? null);
        $validated['employees'] = $this->parseCommaField($validated['employees'] ?? null);
        $warehouse->update($validated);

        return redirect()->route('warehouses.index')->with('success', 'Warehouse updated successfully.');
    }

    public function destroy(Warehouse $warehouse)
    {
        $warehouse->delete();

        return redirect()->route('warehouses.index')->with('success', 'Warehouse deleted successfully.');
    }

    public function export(Request $request)
    { /* نفس الكود */
    }

    public function downloadTemplate()
    { /* نفس الكود */
    }

    public function import(Request $request)
    { /* نفس الكود */
    }

    private function parseCommaField($field)
    {
        if (empty($field)) {
            return null;
        }
        $parts = array_map('trim', explode(',', $field));

        return json_encode(array_filter($parts));
    }

    // دوال AJAX
    public function getStatesByCountry($countryId)
    {
        $states = State::where('country_id', $countryId)
            ->where('is_active', true)
            ->orderBy('name_en')
            ->get(['id', 'name_en', 'name_ar']);

        return response()->json($states);
    }

    public function getCitiesByState($stateId)
    {
        $cities = City::where('state_id', $stateId)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json($cities);
    }

    public function getAreasByState($stateId)
    {
        $areas = Area::where('state_id', $stateId)
            ->where('is_active', true)
            ->orderBy('name_en')
            ->get(['id', 'name_en', 'name_ar']);

        return response()->json($areas);
    }

    /**
     * الخطوة 1: عرض واجهة اختيار المنتجات من المخزن المصدر
     */
    public function transferSelect(Request $request, Warehouse $warehouse)
    {
        // جلب قائمة المخازن النشطة لاختيار المخزن الهدف (باستثناء المصدر)
        $warehouses = Warehouse::where('is_active', true)
            ->where('id', '!=', $warehouse->id)
            ->orderBy('name_en')
            ->get();

        // استعلام أساسي لجلب أرصدة المنتجات في هذا المخزن
        $query = BatchItemStock::where('warehouse_id', $warehouse->id)
            ->with(['batchItem.item.category', 'batchItem.item.brand', 'batchItem.batch', 'batchItem.pack'])
            ->where('quantity', '>', 0);

        // تطبيق فلترة البحث (اختياري)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('batchItem.item', function ($q) use ($search) {
                $q->where('name_en', 'LIKE', "%{$search}%")
                    ->orWhere('name_ar', 'LIKE', "%{$search}%")
                    ->orWhere('code', 'LIKE', "%{$search}%");
            });
        }
        if ($request->filled('category_id')) {
            $query->whereHas('batchItem.item', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }
        if ($request->filled('brand_id')) {
            $query->whereHas('batchItem.item', function ($q) use ($request) {
                $q->where('brand_id', $request->brand_id);
            });
        }

        $stocks = $query->get();

        // تجميع البيانات لعرضها في الجدول (كل صف يمثل BatchItem مع كميته)
        // نضيف خاصية available_quantity وهي الكمية الموجودة حالياً
        $batchItems = $stocks->map(function ($stock) {
            $item = $stock->batchItem->item;

            return (object) [
                'batch_item_id' => $stock->batch_item_id,
                'item_name' => $item->name_en ?? $item->name_ar,
                'item_code' => $item->code,
                'category' => $item->category->name ?? '-',
                'brand' => $item->brand->name ?? '-',
                'batch_number' => $stock->batchItem->batch->batch_number,
                'available_qty' => $stock->quantity,
                'pack_info' => $stock->batchItem->pack ? $stock->batchItem->pack->barcode.' (Qty: '.$stock->batchItem->pack->quantity.')' : 'بدون عبوة',
            ];
        });

        // قوائم الفلاتر
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();

        return view('dashboard.data_definition.w&v.warehouse.transfer_select', compact(
            'warehouse', 'warehouses', 'batchItems', 'categories', 'brands'
        ));
    }

    /**
     * الخطوة 1 - حفظ الاختيارات في الجلسة والانتقال للخطوة 2
     */
    public function transferStoreSelection(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'selected_items' => 'required|array|min:1',
            'selected_items.*' => 'exists:batch_items,id',
        ]);

        // التأكد من أن العناصر المحددة تنتمي فعلاً إلى هذا المخزن ولها رصيد
        $validIds = BatchItemStock::where('warehouse_id', $warehouse->id)
            ->whereIn('batch_item_id', $validated['selected_items'])
            ->where('quantity', '>', 0)
            ->pluck('batch_item_id')
            ->toArray();

        if (empty($validIds)) {
            return back()->with('error', 'المنتجات المحددة غير متوفرة في هذا المخزن.');
        }

        // حفظ البيانات في الجلسة (المصدر والعناصر المختارة فقط)
        session([
            'warehouse_transfer' => [
                'source_warehouse_id' => $warehouse->id,
                'selected_batch_item_ids' => $validIds,
            ],
        ]);

        return redirect()->route('warehouses.transfer.confirm', $warehouse);
    }

    /**
     * الخطوة 1 من الشاشة الرئيسية للمخزن - حفظ اختيار المنتجات فقط
     */
    public function transferStoreSelectionFromWarehouse(Request $request, Warehouse $warehouse)
    {
        $selected = $request->input('selected_items');

        if (! $selected) {
            return back()->with('error', 'يرجى اختيار منتجات واحدة على الأقل.');
        }

        if (is_string($selected)) {
            // Convert comma-separated string to array
            $selected = array_map('trim', explode(',', $selected));
            $selected = array_filter($selected);
        }

        // Ensure it's an array
        if (! is_array($selected)) {
            $selected = [$selected];
        }

        $validated = [];
        foreach ($selected as $item) {
            $id = intval($item);
            if ($id <= 0) {
                continue;
            }
            // Validate that the batch_item exists and belongs to this warehouse with quantity > 0
            $exists = BatchItemStock::where('warehouse_id', $warehouse->id)
                ->where('batch_item_id', $id)
                ->where('quantity', '>', 0)
                ->exists();
            if ($exists) {
                $validated[] = $id;
            }
        }

        if (empty($validated)) {
            return back()->with('error', 'المنتجات المحددة غير متوفرة في هذا المخزن.');
        }

        // Save data in session (without target)
        session([
            'warehouse_transfer' => [
                'source_warehouse_id' => $warehouse->id,
                'selected_batch_item_ids' => $validated,
            ],
        ]);

        return redirect()->route('warehouses.transfer.confirm-from-warehouse', $warehouse);
    }

    /**
     * الخطوة 2: عرض واجهة تحديد الهدف والكميات لكل منتج مختار (من الشاشة الرئيسية)
     */
    public function transferConfirmFromWarehouse(Warehouse $warehouse)
    {
        $transferData = session('warehouse_transfer');
        if (! $transferData || $transferData['source_warehouse_id'] != $warehouse->id) {
            return redirect()->route('warehouses.transfer.select', $warehouse)
                ->with('error', 'يرجى اختيار المنتجات أولاً.');
        }

        // الحصول على قائمة المخازن النشطة باستثناء المصدر
        $warehouses = Warehouse::where('is_active', true)
            ->where('id', '!=', $warehouse->id)
            ->orderBy('name_en')
            ->get();

        // جلب بيانات المنتجات المختارة مع كمياتها في المصدر
        $batchItems = BatchItem::whereIn('id', $transferData['selected_batch_item_ids'])
            ->with(['item', 'pack', 'batch'])
            ->get();

        // إضافة الكمية المتوفرة من المصدر لكل عنصر
        $batchItems->each(function ($item) use ($warehouse) {
            $stock = BatchItemStock::where('batch_item_id', $item->id)
                ->where('warehouse_id', $warehouse->id)
                ->first();
            $item->available_quantity = $stock ? $stock->quantity : 0;
        });

        return view('dashboard.data_definition.w&v.warehouse.transfer_confirm_from_warehouse', compact(
            'warehouse', 'warehouses', 'batchItems'
        ));
    }

    /**
     * الخطوة 2: عرض واجهة تحديد الكميات لكل منتج مختار
     */
    public function transferConfirm(Warehouse $warehouse)
    {
        $transferData = session('warehouse_transfer');
        if (! $transferData || $transferData['source_warehouse_id'] != $warehouse->id) {
            return redirect()->route('warehouses.transfer.select', $warehouse)
                ->with('error', 'يرجى اختيار المنتجات أولاً.');
        }

        $targetWarehouse = Warehouse::findOrFail($transferData['target_warehouse_id']);

        // جلب بيانات المنتجات المختارة مع كمياتها في المصدر
        $batchItems = BatchItem::whereIn('id', $transferData['selected_batch_item_ids'])
            ->with(['item', 'pack', 'batch'])
            ->get();

        // إضافة الكمية المتوفرة في المصدر
        $batchItems->each(function ($item) use ($warehouse) {
            $stock = BatchItemStock::where('batch_item_id', $item->id)
                ->where('warehouse_id', $warehouse->id)
                ->first();
            $item->available_quantity = $stock ? $stock->quantity : 0;
        });

        return view('dashboard.data_definition.w&v.warehouse.transfer_confirm', compact(
            'warehouse', 'targetWarehouse', 'batchItems'
        ));
    }

    /**
     * الخطوة 2 - تنفيذ النقل بعد تحديد الكميات
     */
    public function transferExecute(Request $request, Warehouse $warehouse)
    {
        $transferData = session('warehouse_transfer');
        if (! $transferData || $transferData['source_warehouse_id'] != $warehouse->id) {
            return redirect()->route('warehouses.transfer.select', $warehouse)
                ->with('error', 'الجلسة منتهية، يرجى البدء من جديد.');
        }

        $validated = $request->validate([
            'quantities' => 'required|array',
            'quantities.*' => 'numeric|min:0.001',
            'notes' => 'nullable|string',
            'transfer_date' => 'nullable|date',
        ]);

        $targetWarehouseId = $transferData['target_warehouse_id'];
        $selectedIds = $transferData['selected_batch_item_ids'];

        DB::transaction(function () use ($warehouse, $targetWarehouseId, $selectedIds, $validated) {
            foreach ($selectedIds as $batchItemId) {
                $qty = $validated['quantities'][$batchItemId] ?? 0;
                if ($qty <= 0) {
                    continue;
                }

                // التحقق من وجود رصيد كافٍ في المصدر
                $fromStock = BatchItemStock::where('batch_item_id', $batchItemId)
                    ->where('warehouse_id', $warehouse->id)
                    ->first();

                if (! $fromStock || $fromStock->quantity < $qty) {
                    throw new \Exception("الكمية غير متوفرة للمنتج ID: {$batchItemId}");
                }

                // خصم من المصدر
                $fromStock->decrement('quantity', $qty);

                // إضافة إلى الهدف
                $toStock = BatchItemStock::firstOrNew([
                    'batch_item_id' => $batchItemId,
                    'warehouse_id' => $targetWarehouseId,
                ]);
                $toStock->quantity += $qty;
                $toStock->save();

                // تسجيل حركة النقل
                BatchMovement::create([
                    'batch_item_id' => $batchItemId,
                    'from_warehouse_id' => $warehouse->id,
                    'to_warehouse_id' => $targetWarehouseId,
                    'movement_type' => 'transfer',
                    'quantity' => $qty,
                    'movement_date' => $validated['transfer_date'] ?? now(),
                    'notes' => $validated['notes'] ?? 'نقل بين المخازن',
                ]);
            }
        });

        // مسح الجلسة بعد التنفيذ
        session()->forget('warehouse_transfer');

        return redirect()->route('warehouses.show', $warehouse)
            ->with('success', 'تم نقل الكميات بنجاح.');
    }
}
