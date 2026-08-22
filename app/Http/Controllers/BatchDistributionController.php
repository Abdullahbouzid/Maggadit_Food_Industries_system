<?php

namespace App\Http\Controllers;

use App\Models\ProductionBatch;
use App\Models\BatchItem;
use App\Models\BatchItemStock;
use App\Models\BatchMovement;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BatchDistributionController extends Controller
{
    public function create($batchId)
    {
        $batch = ProductionBatch::with(['batchItems.item', 'batchItems.pack'])->findOrFail($batchId);
        $warehouses = Warehouse::where('is_active', true)->orderBy('name_en')->get();

        return view('dashboard.data_definition.w&v.batches.distribute', compact('batch', 'warehouses'));
    }

    public function store(Request $request, $batchId)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'distribution_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.batch_item_id' => 'required|exists:batch_items,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
        ]);

        $batch = ProductionBatch::findOrFail($batchId);

        DB::transaction(function () use ($request, $batch) {
            $warehouseId = $request->warehouse_id;
            $date = $request->distribution_date ?? now()->toDateString();

            foreach ($request->items as $itemData) {
                $batchItem = BatchItem::findOrFail($itemData['batch_item_id']);
                $quantity = $itemData['quantity'];

                // التحقق من أن الكمية المطلوبة لا تتجاوز المتبقية
                if ($quantity > $batchItem->remaining_quantity) {
                    throw new \Exception("الكمية المطلوبة للمنتج {$batchItem->item->name_en} تتجاوز الكمية المتبقية ({$batchItem->remaining_quantity})");
                }

                // تحديث الكمية المرحلة والمتبقية
                $batchItem->transferred_quantity += $quantity;
                $batchItem->remaining_quantity = $batchItem->quantity - $batchItem->transferred_quantity;
                $batchItem->save();

                // تحديث أو إنشاء رصيد المخزن
                $stock = BatchItemStock::where('batch_item_id', $batchItem->id)
                                       ->where('warehouse_id', $warehouseId)
                                       ->first();
                if ($stock) {
                    $stock->quantity += $quantity;
                    $stock->save();
                } else {
                    BatchItemStock::create([
                        'batch_item_id' => $batchItem->id,
                        'warehouse_id' => $warehouseId,
                        'quantity' => $quantity,
                        'reserved_quantity' => 0,
                    ]);
                }

                // تسجيل الحركة
                BatchMovement::create([
                    'batch_item_id' => $batchItem->id,
                    'from_warehouse_id' => null, // توزيع من الإنتاج
                    'to_warehouse_id' => $warehouseId,
                    'movement_type' => 'in',
                    'quantity' => $quantity,
                    'movement_date' => $date,
                    'notes' => $request->notes ?? null,
                ]);
            }
        });

        return redirect()->route('production-batches.show', $batchId)
                         ->with('success', 'تم توزيع الدفعة بنجاح.');
    }
}