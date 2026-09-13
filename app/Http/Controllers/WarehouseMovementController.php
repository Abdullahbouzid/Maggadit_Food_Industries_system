<?php

namespace App\Http\Controllers;

use App\Exports\MovementDetailExport;
use App\Exports\MovementsExport;
use App\Models\BatchItem;
use App\Models\BatchMovement;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;

class WarehouseMovementController extends Controller
{
    public function index(Request $request)
    {
        // ======================
        // 1. جلب البيانات مع الفلاتر
        // ======================
        $query = BatchMovement::with(['batchItem.item', 'fromWarehouse', 'toWarehouse', 'batchItem.batch']);

        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->movement_type);
        }
        if ($request->filled('from_warehouse_id')) {
            $query->where('from_warehouse_id', $request->from_warehouse_id);
        }
        if ($request->filled('to_warehouse_id')) {
            $query->where('to_warehouse_id', $request->to_warehouse_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('movement_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('movement_date', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('notes', 'LIKE', "%{$search}%")
                    ->orWhereHas('batchItem.batch', function ($qb) use ($search) {
                        $qb->where('batch_number', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('batchItem.item', function ($qb) use ($search) {
                        $qb->where('name_en', 'LIKE', "%{$search}%")
                            ->orWhere('name_ar', 'LIKE', "%{$search}%")
                            ->orWhere('code', 'LIKE', "%{$search}%");
                    });
            });
        }

        $movements = $query->orderBy('movement_date', 'desc')->get();

        // ======================
        // 2. تجميع الحركات
        // ======================
        $grouped = $movements->groupBy(function ($item) {
            return $item->movement_type.'|'.
                ($item->from_warehouse_id ?? 'null').'|'.
                $item->to_warehouse_id.'|'.
                $item->movement_date->format('Y-m-d').'|'.
                $item->batchItem->batch_id;
        });

        $groups = $grouped->map(function ($group) {
            $first = $group->first();

            return (object) [
                'id' => $first->id,
                'movement_type' => $first->movement_type,
                'batch_number' => $first->batchItem->batch->batch_number ?? '',
                'batch_id' => $first->batchItem->batch_id,
                'from_warehouse' => $first->fromWarehouse->name_en ?? '-',
                'from_warehouse_id' => $first->from_warehouse_id,
                'to_warehouse' => $first->toWarehouse->name_en ?? '-',
                'to_warehouse_id' => $first->to_warehouse_id,
                'movement_date' => $first->movement_date,
                'items_count' => $group->count(),
                'total_quantity' => $group->sum('quantity'),
                'first_movement' => $first,
                'movement_ids' => $group->pluck('id')->toArray(),
            ];
        })->values();

        // ======================
        // 3. حساب الإحصائيات (بناءً على المجموعات)
        // ======================
        // الإحصائيات العامة (دون تطبيق الفلاتر) – أو يمكن تطبيق الفلاتر حسب الحاجة.
        // هنا سنحسب الإحصائيات بناءً على جميع البيانات (بدون فلاتر) لتعكس الأعداد الكلية.
        // ولكن إذا أردت إحصائيات متأثرة بالفلاتر، يمكنك تطبيق نفس الفلاتر على استعلام الإحصائيات.
        // سنقوم بحساب الإحصائيات باستخدام استعلامات منفصلة لتجنب جلب كل البيانات مرة أخرى.

        // دالة مساعدة لحساب عدد المجموعات حسب النوع
        $getGroupCount = function ($type = null) use ($request) {
            $query = BatchMovement::query();

            // تطبيق نفس الفلاتر (إذا أردت إحصائيات متأثرة بالفلاتر)
            if ($request->filled('from_warehouse_id')) {
                $query->where('from_warehouse_id', $request->from_warehouse_id);
            }
            if ($request->filled('to_warehouse_id')) {
                $query->where('to_warehouse_id', $request->to_warehouse_id);
            }
            if ($request->filled('date_from')) {
                $query->whereDate('movement_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('movement_date', '<=', $request->date_to);
            }
            if ($type) {
                $query->where('movement_type', $type);
            }

            // جلب البيانات وتجميعها بنفس الطريقة
            $data = $query->with('batchItem.batch')->get();
            $grouped = $data->groupBy(function ($item) {
                return $item->movement_type.'|'.
                    ($item->from_warehouse_id ?? 'null').'|'.
                    $item->to_warehouse_id.'|'.
                    $item->movement_date->format('Y-m-d').'|'.
                    $item->batchItem->batch_id;
            });

            return $grouped->count();
        };

        // حساب الإحصائيات باستخدام الدالة المساعدة
        $total = $getGroupCount(); // بدون فلتر نوع الحركة
        $inCount = $getGroupCount('in');
        $transferCount = $getGroupCount('transfer');
        $expiryCount = $getGroupCount('expiry_return');

        // ======================
        // 4. الترقيم (Pagination)
        // ======================
        $perPage = $request->input('per_page', 20);
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $groups->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $groupsPaginated = new LengthAwarePaginator(
            $currentItems,
            $groups->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );
        $groupsPaginated->appends($request->query());

        // ======================
        // 5. إعداد البيانات للـ View
        // ======================
        $warehouses = Warehouse::where('is_active', true)->orderBy('name_en')->get();
        $movementTypes = [
            'in' => 'وارد (توزيع)',
            'transfer' => 'نقل بين المخازن',
            'expiry_return' => 'استرجاع صلاحية',
        ];

        return view('dashboard.data_definition.w&v.warehouse_movement.index', compact(
            'groupsPaginated', 'total', 'inCount', 'transferCount', 'expiryCount',
            'warehouses', 'movementTypes'
        ));
    }

    public function create(Request $request)
    {
        $warehouses = Warehouse::where('is_active', true)->orderBy('name_en')->get();
        $movementTypes = [
            'in' => 'وارد (توزيع)',
            'transfer' => 'نقل بين المخازن',
            'expiry_return' => 'استرجاع صلاحية',
        ];
        $batchItems = BatchItem::with(['item', 'batch', 'pack'])->get();

        // إذا تم تحديد مخزن مصدر، احضر المنتجات المتوفرة فيه
        $sourceWarehouseId = $request->input('from_warehouse_id');
        $availableItems = collect();
        if ($sourceWarehouseId) {
            $availableItems = \App\Models\BatchItemStock::where('warehouse_id', $sourceWarehouseId)
                ->where('quantity', '>', 0)
                ->with(['batchItem.item', 'batchItem.pack', 'batchItem.pack.packType', 'batchItem.pack.packCapacity', 'batchItem.batch'])
                ->get()
                ->filter(function ($stock) {
                    return $stock->batchItem && $stock->batchItem->item;
                });
        }

        return view('dashboard.data_definition.w&v.warehouse_movement.create', compact('warehouses', 'movementTypes', 'batchItems', 'sourceWarehouseId', 'availableItems'));
    }

    /**
     * AJAX: جلب item_packs المتوفرة في مخزن معين
     */
    public function getAvailableItems($warehouseId)
    {
        $stocks = \App\Models\BatchItemStock::where('warehouse_id', $warehouseId)
            ->where('quantity', '>', 0)
            ->with(['batchItem.item', 'batchItem.pack', 'batchItem.pack.packType', 'batchItem.pack.packCapacity', 'batchItem.pack.packCapacity.uom', 'batchItem.batch'])
            ->get()
            ->filter(function ($stock) {
                return $stock->batchItem && $stock->batchItem->item;
            })
            ->map(function ($stock) {
                $pack = $stock->batchItem->pack;
                $packTypeName = $pack?->packType?->name_ar ?? $pack?->packType?->name_en ?? 'قطعة';
                $packTypeCode = $pack?->packType?->code;
                $packCapacityValue = $pack?->packCapacity?->value;
                $packCapacityUom = $pack?->packCapacity?->uom?->code;

                return [
                    'stock_id' => $stock->id,
                    'batch_item_id' => $stock->batch_item_id,
                    'item_id' => $stock->batchItem->item_id,
                    'item_name' => $stock->batchItem->item->name_en ?? '',
                    'item_name_ar' => $stock->batchItem->item->name_ar ?? '',
                    'item_code' => $stock->batchItem->item->code ?? '',
                    'batch_id' => $stock->batchItem->batch_id,
                    'batch_number' => $stock->batchItem->batch?->batch_number ?? '',
                    'pack_id' => $pack?->id,
                    'pack_barcode' => $pack?->barcode ?? '',
                    'pack_type_name' => $packTypeName,
                    'pack_type_code' => $packTypeCode,
                    'pack_capacity_value' => $packCapacityValue,
                    'pack_capacity_uom' => $packCapacityUom,
                    'quantity_in_pack' => $pack?->quantity_in_pack ?? 1,
                    'available_quantity' => (float) $stock->quantity,
                ];
            })
            ->values();

        return response()->json($stocks);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'movement_type' => 'required|in:in,transfer,expiry_return',
            'from_warehouse_id' => 'nullable|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id',
            'movement_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.batch_item_id' => 'required|exists:batch_items,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
        ]);

        $common = [
            'movement_type' => $validated['movement_type'],
            'from_warehouse_id' => $validated['from_warehouse_id'],
            'to_warehouse_id' => $validated['to_warehouse_id'],
            'movement_date' => $validated['movement_date'],
            'notes' => $validated['notes'],
        ];

        foreach ($validated['items'] as $item) {
            if (in_array($common['movement_type'], ['transfer', 'expiry_return']) && $common['from_warehouse_id']) {
                $stock = \App\Models\BatchItemStock::where('warehouse_id', $common['from_warehouse_id'])
                    ->where('batch_item_id', $item['batch_item_id'])
                    ->first();
                if (! $stock || $stock->quantity < $item['quantity']) {
                    $available = $stock ? $stock->quantity : 0;
                    return back()->withInput()->with('error', "الكمية غير متوفرة للمنتج #{$item['batch_item_id']} في المخزن المصدر. المتاح: {$available}");
                }
            }
            BatchMovement::create(array_merge($common, [
                'batch_item_id' => $item['batch_item_id'],
                'quantity' => $item['quantity'],
            ]));
        }

        return redirect()->route('warehouse-movements.index')->with('success', 'تم إنشاء الحركة بنجاح.');
    }

    public function show(BatchMovement $warehouseMovement)
    {
        $movement = $warehouseMovement->load(['batchItem.item', 'batchItem.pack', 'batchItem.batch', 'fromWarehouse', 'toWarehouse']);

        $groupedMovements = BatchMovement::where('movement_type', $movement->movement_type)
            ->where('from_warehouse_id', $movement->from_warehouse_id)
            ->where('to_warehouse_id', $movement->to_warehouse_id)
            ->whereDate('movement_date', $movement->movement_date)
            ->whereHas('batchItem', function ($q) use ($movement) {
                $q->where('batch_id', $movement->batchItem->batch_id);
            })
            ->with(['batchItem.item', 'batchItem.pack', 'batchItem.batch'])
            ->get();

        return view('dashboard.data_definition.w&v.warehouse_movement.show', compact('movement', 'groupedMovements'));
    }

    public function edit(BatchMovement $warehouseMovement)
    {
        $warehouses = Warehouse::where('is_active', true)->orderBy('name_en')->get();
        $movementTypes = [
            'in' => 'وارد (توزيع)',
            'transfer' => 'نقل بين المخازن',
            'expiry_return' => 'استرجاع صلاحية',
        ];
        $batchItems = BatchItem::with(['item', 'batch', 'pack'])->get();

        return view('dashboard.data_definition.w&v.warehouse_movement.edit', compact('warehouseMovement', 'warehouses', 'movementTypes', 'batchItems'));
    }

    public function update(Request $request, BatchMovement $warehouseMovement)
    {
        $validated = $request->validate([
            'batch_item_id' => 'required|exists:batch_items,id',
            'from_warehouse_id' => 'nullable|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id',
            'movement_type' => 'required|in:in,transfer,expiry_return',
            'quantity' => 'required|numeric|min:0.001',
            'movement_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);
        $warehouseMovement->update($validated);

        return redirect()->route('warehouse-movements.index')->with('success', 'تم تحديث الحركة بنجاح.');
    }

    public function destroy(BatchMovement $warehouseMovement)
    {
        $warehouseMovement->delete();

        return redirect()->route('warehouse-movements.index')->with('success', 'تم حذف الحركة بنجاح.');
    }

    /**
     * تصدير الحركات بناءً على الفلاتر الحالية (Excel)
     */
    public function export(Request $request)
    {
        try {
            return Excel::download(new MovementsExport($request), 'movements_'.date('Y-m-d_Hi').'.xlsx');
        } catch (\Exception $e) {
            return back()->with('error', 'فشل التصدير: '.$e->getMessage());
        }
    }

    /**
     * تصدير تفاصيل حركة معينة (جميع منتجات المجموعة)
     */
    public function exportDetail(BatchMovement $warehouseMovement)
    {
        try {
            return Excel::download(new MovementDetailExport($warehouseMovement), 'movement_detail_'.$warehouseMovement->id.'.xlsx');
        } catch (\Exception $e) {
            return back()->with('error', 'فشل تصدير التفاصيل: '.$e->getMessage());
        }
    }
}
