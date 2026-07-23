<?php

namespace App\Http\Controllers;

use App\Models\ProductionBatch;
use App\Models\BatchItem;
use App\Models\BatchItemStock;
use App\Models\BatchMovement;
use App\Models\Item;
use App\Models\ItemPack;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Str;

class ProductionBatchController extends Controller
{
    /**
     * عرض قائمة الدفعات
     */
    public function index(Request $request)
    {
        $query = ProductionBatch::withCount('batchItems');

        // فلترة حسب الحالة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // بحث حسب رقم الدفعة أو الملاحظات
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('batch_number', 'LIKE', "%{$search}%")
                  ->orWhere('notes', 'LIKE', "%{$search}%");
            });
        }

        // ترتيب
        $query->orderBy('created_at', 'desc');

        // عدد النتائج
        $perPage = $request->input('per_page', 20);
        if ($perPage === 'all') {
            $batches = $query->get();
        } else {
            $batches = $query->paginate((int)$perPage)->appends($request->query());
        }

        // إحصائيات
        $total = ProductionBatch::count();
        $active = ProductionBatch::where('status', 'active')->count();
        $expired = ProductionBatch::where('status', 'expired')->count();

        return view('dashboard.data_definition.w&v.batches.index', compact('batches', 'total', 'active', 'expired'));
    }

    /**
     * عرض نموذج إنشاء دفعة جديدة
     */
    public function create()
    {
        $items = Item::with('packs')->orderBy('name_en')->get();
        $lastBatch = ProductionBatch::orderBy('id', 'desc')->first();
        $nextNumber = $lastBatch ? intval(substr($lastBatch->batch_number, -3)) + 1 : 1;
        $batchNumber = 'BATCH-' . date('Ymd') . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return view('dashboard.data_definition.w&v.batches.create', compact('items', 'batchNumber'));
    }

    /**
     * تخزين دفعة جديدة
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'batch_number' => 'required|string|unique:production_batches,batch_number',
            'production_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:production_date',
            'status' => 'required|in:active,expired,consumed',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.item_pack_id' => 'nullable|exists:item_packs,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
        ]);

        DB::transaction(function () use ($validated) {
            $batch = ProductionBatch::create([
                'batch_number' => $validated['batch_number'],
                'production_date' => $validated['production_date'],
                'expiry_date' => $validated['expiry_date'],
                'status' => $validated['status'],
                'notes' => $validated['notes'],
            ]);

            // تجميع المكررات حسب (item_id, item_pack_id)
            $grouped = collect($validated['items'])->groupBy(function ($item) {
                return $item['item_id'] . '-' . ($item['item_pack_id'] ?? 'null');
            })->map(function ($group) {
                return [
                    'item_id' => $group->first()['item_id'],
                    'item_pack_id' => $group->first()['item_pack_id'],
                    'quantity' => $group->sum('quantity'),
                ];
            });

            foreach ($grouped as $itemData) {
                BatchItem::create([
                    'batch_id' => $batch->id,
                    'item_id' => $itemData['item_id'],
                    'item_pack_id' => $itemData['item_pack_id'],
                    'quantity' => $itemData['quantity'],
                    'dispatched_quantity' => 0,
                ]);
            }
        });

        return redirect()->route('production-batches.index')
                        ->with('success', 'تم إنشاء الدفعة بنجاح.');
    }
            /**
     * عرض تفاصيل الدفعة
     */
    public function show(ProductionBatch $productionBatch)
    {
        $batch = $productionBatch->load(['batchItems.item', 'batchItems.pack']);
        return view('dashboard.data_definition.w&v.batches.show', compact('batch'));
    }

    /**
     * عرض نموذج تعديل الدفعة
     */
    public function edit(ProductionBatch $productionBatch)
    {
        $items = Item::with('packs')->orderBy('name_en')->get();
        $batch = $productionBatch->load('batchItems');
        return view('dashboard.data_definition.w&v.batches.edit', compact('batch', 'items'));
    }

    /**
     * تحديث الدفعة
     */
        public function update(Request $request, ProductionBatch $productionBatch)
        {
            $validated = $request->validate([
                'batch_number' => 'required|string|unique:production_batches,batch_number,' . $productionBatch->id,
                'production_date' => 'nullable|date',
                'expiry_date' => 'nullable|date|after_or_equal:production_date',
                'status' => 'required|in:active,expired,consumed',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.id' => 'nullable|exists:batch_items,id',
                'items.*.item_id' => 'required|exists:items,id',
                'items.*.item_pack_id' => 'nullable|exists:item_packs,id',
                'items.*.quantity' => 'required|numeric|min:0.001',
            ]);

            DB::transaction(function () use ($validated, $productionBatch) {
                // تحديث بيانات الدفعة
                $productionBatch->update([
                    'batch_number' => $validated['batch_number'],
                    'production_date' => $validated['production_date'],
                    'expiry_date' => $validated['expiry_date'],
                    'status' => $validated['status'],
                    'notes' => $validated['notes'],
                ]);

                // قائمة المعرفات الموجودة
                $existingIds = collect($validated['items'])->pluck('id')->filter()->toArray();

                // حذف المنتجات غير الموجودة في الطلب (مع مراعاة عدم حذف ما تم توزيعه)
                $productionBatch->batchItems()
                    ->whereNotIn('id', $existingIds)
                    ->where('dispatched_quantity', 0)
                    ->delete();

                // تحديث أو إضافة المنتجات
                foreach ($validated['items'] as $itemData) {
                    if (isset($itemData['id']) && $itemData['id']) {
                        // تحديث (مع الاحتفاظ بـ dispatched_quantity)
                        $batchItem = BatchItem::find($itemData['id']);
                        if ($batchItem && $batchItem->batch_id == $productionBatch->id) {
                            $batchItem->update([
                                'item_id' => $itemData['item_id'],
                                'item_pack_id' => $itemData['item_pack_id'],
                                'quantity' => $itemData['quantity'],
                                // لا نغير dispatched_quantity
                            ]);
                        }
                    } else {
                        // إضافة جديدة
                        BatchItem::create([
                            'batch_id' => $productionBatch->id,
                            'item_id' => $itemData['item_id'],
                            'item_pack_id' => $itemData['item_pack_id'],
                            'quantity' => $itemData['quantity'],
                            'dispatched_quantity' => 0,
                        ]);
                    }
                }
            });

            return redirect()->route('production-batches.index')
                            ->with('success', 'تم تحديث الدفعة بنجاح.');
        }

    /**
     * حذف الدفعة
     */
    public function destroy(ProductionBatch $productionBatch)
    {
        $productionBatch->delete(); // soft delete
        return redirect()->route('production-batches.index')
                         ->with('success', 'تم حذف الدفعة بنجاح.');
    }

    /**
     * API: جلب عبوات منتج معين (للاستخدام في الـ Ajax)
     */
    public function getPacks($itemId)
    {
        $item = Item::findOrFail($itemId);
        $packs = $item->packs()->select('id', 'barcode', 'quantity as pack_quantity')->get();

        // نضيف نصاً وصفياً لكل عبوة لعرضها في الـ select
        $packs->transform(function ($pack) {
            $pack->display_text = $pack->barcode . ' (Qty: ' . $pack->pack_quantity . ')';
            return $pack;
        });

        return response()->json($packs);
    }
    
    public function distributeStore(Request $request, ProductionBatch $productionBatch)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'items' => 'required|array',
            'items.*.batch_item_id' => 'required|exists:batch_items,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
        ]);

        // تجميع الكميات لكل batch_item_id (لتجنب التكرار)
        $grouped = collect($validated['items'])->groupBy('batch_item_id')->map(fn($group) => $group->sum('quantity'));

        DB::transaction(function () use ($grouped, $productionBatch, $validated) {
            foreach ($grouped as $batchItemId => $quantity) {
                $batchItem = BatchItem::where('batch_id', $productionBatch->id)->findOrFail($batchItemId);
                if ($quantity > $batchItem->remaining_quantity) {
                    throw new \Exception("الكمية المطلوبة تتجاوز الكمية المتبقية للمنتج: " . $batchItem->item->name_en);
                }

                // تحديث الكمية المتبقية
                $batchItem->decrement('remaining_quantity', $quantity);

                // إضافة أو تحديث رصيد المخزن
                $stock = BatchItemStock::updateOrCreate(
                    [
                        'batch_item_id' => $batchItem->id,
                        'warehouse_id' => $validated['warehouse_id'],
                    ],
                    ['quantity' => 0] // سيتم إضافتها لاحقاً
                );
                $stock->increment('quantity', $quantity);

                // تسجيل حركة
                BatchMovement::create([
                    'batch_item_id' => $batchItem->id,
                    'from_warehouse_id' => null, // توزيع من الإنتاج
                    'to_warehouse_id' => $validated['warehouse_id'],
                    'movement_type' => 'in',
                    'quantity' => $quantity,
                    'movement_date' => now(),
                    'notes' => 'توزيع أولي من الدفعة ' . $productionBatch->batch_number,
                ]);
            }
        });

        return redirect()->route('production-batches.show', $productionBatch)
                        ->with('success', 'تم توزيع الكميات بنجاح.');
    }

    public function getWarehouseStock(ProductionBatch $productionBatch, $warehouseId)
        {
            $warehouse = Warehouse::findOrFail($warehouseId);
            $stockData = [];

            foreach ($productionBatch->batchItems as $batchItem) {
                $stock = BatchItemStock::where('batch_item_id', $batchItem->id)
                                        ->where('warehouse_id', $warehouseId)
                                        ->first();
                $stockData[$batchItem->id] = $stock ? $stock->quantity : 0;
            }

            return response()->json($stockData);
        }

    /**
 * عرض نموذج توزيع الدفعة على المخازن
 */
    public function distributeForm(ProductionBatch $productionBatch)
        {
            $batch = $productionBatch->load(['batchItems.item', 'batchItems.pack']);
            $warehouses = Warehouse::where('is_active', true)->orderBy('name_en')->get();
            return view('dashboard.data_definition.w&v.batches.distribute', compact('batch', 'warehouses'));
        }

            public function distribute(Request $request, ProductionBatch $productionBatch)
            {
                $validated = $request->validate([
                    'warehouse_id' => 'required|exists:warehouses,id',
                    'items' => 'sometimes|array', // جعلها اختيارية
                    'items.*.batch_item_id' => 'required|exists:batch_items,id',
                    'items.*.quantity' => 'required|numeric|min:0.001',
                ]);

                // إذا لم توجد items، نعرض رسالة
                if (empty($validated['items'])) {
                    return redirect()->back()->with('error', 'لا توجد منتجات متبقية للتوزيع في هذه الدفعة.');
                }

                DB::transaction(function () use ($validated, $productionBatch) {
                    $warehouseId = $validated['warehouse_id'];

                    foreach ($validated['items'] as $itemData) {
                        $batchItem = BatchItem::findOrFail($itemData['batch_item_id']);

                        if ($batchItem->batch_id != $productionBatch->id) {
                            throw new \Exception('عنصر غير صحيح في الدفعة.');
                        }

                        $remaining = $batchItem->quantity - $batchItem->dispatched_quantity;
                        if ($itemData['quantity'] > $remaining) {
                            throw new \Exception("الكمية الموزعة ({$itemData['quantity']}) تتجاوز الكمية المتبقية ({$remaining}) للمنتج: {$batchItem->item->name_en}.");
                        }

                        $batchItem->increment('dispatched_quantity', $itemData['quantity']);

                        $stock = BatchItemStock::firstOrNew([
                            'batch_item_id' => $batchItem->id,
                            'warehouse_id' => $warehouseId,
                        ]);
                        $stock->quantity += $itemData['quantity'];
                        $stock->save();

                        BatchMovement::create([
                            'batch_item_id' => $batchItem->id,
                            'from_warehouse_id' => null,
                            'to_warehouse_id' => $warehouseId,
                            'movement_type' => 'in',
                            'quantity' => $itemData['quantity'],
                            'movement_date' => now(),
                            'notes' => 'توزيع من الدفعة ' . $productionBatch->batch_number,
                        ]);
                    }
                });

                return redirect()->route('production-batches.show', $productionBatch)
                                ->with('success', 'تم توزيع الكميات بنجاح.');
            }

            // في ProductionBatchController.php

            /**
             * عرض نموذج نقل كميات بين المخازن
             */
            public function transferForm(ProductionBatch $productionBatch)
            {
                $batch = $productionBatch->load(['batchItems.item', 'batchItems.pack']);
                $warehouses = Warehouse::where('is_active', true)->orderBy('name_en')->get();

                // Retrieve stored step data if exists
                $step1 = session('transfer_step1');
                $step2 = session('transfer_step2');

                if ($step2) {
                    // Show confirmation step
                    $selectedItemIds = $step2['selected_item_ids'] ?? [];
                    $sourceWarehouseId = $step1['source_warehouse_id'] ?? null;

                    $selectedItems = collect($batch->batchItems)->whereIn('id', $selectedItemIds)->map(function($item) use ($sourceWarehouseId) {
                        $stock = 0;
                        if ($sourceWarehouseId) {
                            $stock = BatchItemStock::where('batch_item_id', $item->id)
                                ->where('warehouse_id', $sourceWarehouseId)
                                ->value('quantity') ?? 0;
                        }
                        $item->stock = $stock;
                        return $item;
                    })->all();

                    $sourceWarehouse = null;
                    if (!empty($sourceWarehouseId)) {
                        $sourceWarehouse = $warehouses->firstWhere('id', $sourceWarehouseId);
                    }

                    return view('dashboard.data_definition.w&v.batches.transfer_confirm', compact('batch', 'warehouses', 'selectedItems', 'step1', 'step2', 'sourceWarehouse'));
                }

                // Show step 1: select source warehouse and items
                return view('dashboard.data_definition.w&v.batches.transfer_select', compact('batch', 'warehouses', 'step1'));
            }

            /**
             * معالجة نقل الكميات بين المخازن (مرحلتين)
             */
            public function transferStore(Request $request, ProductionBatch $productionBatch)
            {
                $batch = $productionBatch->load(['batchItems.item', 'batchItems.pack']);

                // Step 1: source warehouse and selected items
                if ($request->has('source_warehouse_id') && $request->has('selected_items')) {
                    $validated = $request->validate([
                        'source_warehouse_id' => 'required|exists:warehouses,id',
                        'selected_items' => 'required|array',
                        'selected_items.*' => 'exists:batch_items,id',
                        'transfer_date' => 'nullable|date',
                        'notes' => 'nullable|string',
                    ]);

                    // Ensure selected items belong to this batch
                    $batchItemIds = $batch->batchItems->pluck('id')->toArray();
                    $invalid = array_diff($request->selected_items, $batchItemIds);
                    if (!empty($invalid)) {
                        return back()->with('error', 'بعض العناصر المختارة لا تنتمي إلى هذه الدفعة.');
                    }

                    // Store step 1 data
                    session(['transfer_step1' => [
                        'source_warehouse_id' => $validated['source_warehouse_id'],
                        'transfer_date' => $validated['transfer_date'] ?? now()->toDateString(),
                        'notes' => $validated['notes'],
                    ]]);

                    // Store selected items for step 2
                    session(['transfer_step2' => [
                        'selected_item_ids' => $request->selected_items,
                    ]]);

                    return redirect()->route('production-batches.transfer', $productionBatch);
                }

                // Step 2: confirm transfer with quantities and target warehouse
                if ($request->has('target_warehouse_id') && $request->has('quantities')) {
                    if (!session('transfer_step1') || !session('transfer_step2')) {
                        return redirect()->route('production-batches.transfer', $productionBatch)
                            ->with('error', 'البيانات غير مكتملة. يرجى المحاولة مرة أخرى.');
                    }

                    $step1 = session('transfer_step1');
                    $step2 = session('transfer_step2');

                    $validated = $request->validate([
                        'target_warehouse_id' => 'required|exists:warehouses,id|different:' . $step1['source_warehouse_id'],
                        'quantities' => 'required|array',
                        'quantities.*' => 'numeric|min:0.001',
                        'transfer_date' => 'nullable|date',
                        'notes' => 'nullable|string',
                    ]);

                    $sourceWarehouseId = $step1['source_warehouse_id'];
                    $targetWarehouseId = $validated['target_warehouse_id'];
                    $date = $validated['transfer_date'] ?? $step1['transfer_date'];
                    $notes = $validated['notes'] ?? $step1['notes'];
                    $quantities = $validated['quantities']; // key: batch_item_id => quantity

                    DB::transaction(function () use ($batch, $sourceWarehouseId, $targetWarehouseId, $quantities, $date, $notes) {
                        foreach ($quantities as $batchItemId => $quantity) {
                            $batchItem = $batch->batchItems->find($batchItemId);
                            if (!$batchItem) {
                                continue; // should not happen due to validation
                            }

                            // Check stock in source warehouse
                            $fromStock = BatchItemStock::where('batch_item_id', $batchItem->id)
                                ->where('warehouse_id', $sourceWarehouseId)
                                ->first();
                            if (!$fromStock || $fromStock->quantity < $quantity) {
                                throw new \Exception("الكمية المطلوبة غير متوفرة في المخزن المصدر للمنتج: {$batchItem->item->name_en}");
                            }

                            // Deduct from source
                            $fromStock->decrement('quantity', $quantity);

                            // Add to destination
                            $toStock = BatchItemStock::firstOrNew([
                                'batch_item_id' => $batchItem->id,
                                'warehouse_id' => $targetWarehouseId,
                            ]);
                            $toStock->quantity += $quantity;
                            $toStock->save();

                            // Record movement
                            BatchMovement::create([
                                'batch_item_id' => $batchItem->id,
                                'from_warehouse_id' => $sourceWarehouseId,
                                'to_warehouse_id' => $targetWarehouseId,
                                'movement_type' => 'transfer',
                                'quantity' => $quantity,
                                'movement_date' => $date,
                                'notes' => $notes ?? 'نقل بين المخازن',
                            ]);
                        }
                    });

                    // Clear session data
                    session()->forget(['transfer_step1', 'transfer_step2']);

                    return redirect()->route('production-batches.show', $batch)
                        ->with('success', 'تم نقل الكميات بنجاح بين المخازن.');
                }

                // If neither step matches, redirect back to step 1
                return redirect()->route('production-batches.transfer', $productionBatch);
            }

            /**
             * عرض قائمة الدفعات منتهية الصلاحية في المخازن
             */
            public function expiredBatches(Request $request)
            {
                $today = now()->toDateString();
                $warehouseId = $request->input('warehouse_id');

                $query = BatchItemStock::with(['batchItem.item', 'batchItem.pack', 'batchItem.batch', 'warehouse'])
                    ->whereHas('batchItem.batch', function ($q) use ($today) {
                        $q->where('expiry_date', '<', $today)->where('status', 'active');
                    });

                if ($warehouseId) {
                    $query->where('warehouse_id', $warehouseId);
                }

                $expiredStocks = $query->get()->filter(function ($stock) {
                    return $stock->quantity > 0;
                });

                $warehouses = Warehouse::where('is_active', true)->orderBy('name_en')->get();

                return view('dashboard.data_definition.w&v.batches.expired', compact('expiredStocks', 'warehouses', 'warehouseId'));
            }

            /**
             * استرجاع الباتشات منتهية الصلاحية (نقلها إلى مخزن المهملات أو تحديث الحالة)
             */
            public function returnExpired(Request $request)
            {
                $validated = $request->validate([
                    'stock_ids' => 'required|array',
                    'stock_ids.*' => 'exists:batch_item_stock,id',
                    'destination_warehouse_id' => 'required|exists:warehouses,id',
                    'return_date' => 'nullable|date',
                    'notes' => 'nullable|string',
                ]);

                DB::transaction(function () use ($validated) {
                    $destWarehouseId = $validated['destination_warehouse_id'];
                    $date = $validated['return_date'] ?? now()->toDateString();

                    foreach ($validated['stock_ids'] as $stockId) {
                        $stock = BatchItemStock::findOrFail($stockId);
                        $batchItem = $stock->batchItem;

                        if ($stock->quantity <= 0) continue;

                        $quantity = $stock->quantity;

                        // نقل الكمية من المخزن الحالي إلى مخزن المهملات
                        // خصم من المخزن الحالي
                        $stock->decrement('quantity', $quantity);

                        // إضافة إلى المخزن الهدف
                        $destStock = BatchItemStock::firstOrNew([
                            'batch_item_id' => $batchItem->id,
                            'warehouse_id' => $destWarehouseId,
                        ]);
                        $destStock->quantity += $quantity;
                        $destStock->save();

                        // تحديث حالة الدفعة إلى "expired"
                        $batchItem->batch->update(['status' => 'expired']);

                        // تسجيل حركة استرجاع الصلاحية
                        BatchMovement::create([
                            'batch_item_id' => $batchItem->id,
                            'from_warehouse_id' => $stock->warehouse_id,
                            'to_warehouse_id' => $destWarehouseId,
                            'movement_type' => 'expiry_return',
                            'quantity' => $quantity,
                            'movement_date' => $date,
                            'notes' => $validated['notes'] ?? 'استرجاع بسبب انتهاء الصلاحية',
                        ]);
                    }
                });

                return redirect()->route('production-batches.expired')
                                ->with('success', 'تم استرجاع الباتشات منتهية الصلاحية بنجاح.');
            }
}