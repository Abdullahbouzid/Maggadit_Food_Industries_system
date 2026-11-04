<?php

namespace App\Services\Warehouse;

use App\Models\BatchItem;
use App\Models\BatchItemStock;
use App\Models\BatchMovement;
use App\Models\ProductionBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BatchDistributionService
{
    /**
     * توزيع الكميات من دفعة إنتاج على المخازن.
     * يقوم بالتحقق من:
     * - أن الكمية المطلوبة لا تتجاوز الكمية المتبقية من الدفعة
     * - يقفل التوزيع تلقائياً عند اكتمال توزيع كافة الكميات
     */
    public function distribute(int $batchId, array $data, ?string $notes = null): void
    {
        $batch = ProductionBatch::findOrFail($batchId);

        DB::transaction(function () use ($batch, $data, $notes) {
            $warehouseId = $data['warehouse_id'];
            $date = $data['distribution_date'] ?? now()->toDateString();

            $grouped = collect($data['items'])
                ->groupBy('batch_item_id')
                ->map(fn ($group) => $group->sum('quantity'));

            // التحقق من كل batch_item بشكل فردي
            $errors = [];
            foreach ($grouped as $batchItemId => $quantity) {
                // Use lockForUpdate to prevent race conditions
                $batchItem = BatchItem::where('batch_id', $batch->id)
                    ->with(['item', 'pack'])
                    ->lockForUpdate()
                    ->findOrFail($batchItemId);

                $alreadyDistributed = (float) $batchItem->dispatched_quantity;
                $totalProduced = (float) $batchItem->quantity;
                $remaining = $totalProduced - $alreadyDistributed;

                if ($remaining <= 0) {
                    $errors["items.{$batchItemId}.quantity"] = "تم توزيع الكمية الكاملة من المنتج: {$batchItem->item->name_en}. لا يمكن التوزيع مرة أخرى.";
                    continue;
                }

                if ($quantity > $remaining) {
                    $errors["items.{$batchItemId}.quantity"] = "الكمية المطلوبة ({$quantity}) تتجاوز الكمية المتبقية ({$remaining}) للمنتج: {$batchItem->item->name_en}.";
                }
            }

            if (!empty($errors)) {
                throw ValidationException::withMessages($errors);
            }

            // تنفيذ التوزيع
            foreach ($grouped as $batchItemId => $quantity) {
                $batchItem = BatchItem::where('batch_id', $batch->id)
                    ->with(['item', 'pack'])
                    ->lockForUpdate()
                    ->findOrFail($batchItemId);

                $batchItem->dispatched_quantity += $quantity;
                $batchItem->remaining_quantity = $batchItem->quantity - $batchItem->dispatched_quantity;
                $batchItem->save();

                $stock = BatchItemStock::where('batch_item_id', $batchItem->id)
                    ->where('warehouse_id', $warehouseId)
                    ->first();

                if ($stock) {
                    $stock->increment('quantity', $quantity);
                } else {
                    BatchItemStock::create([
                        'batch_item_id' => $batchItem->id,
                        'warehouse_id' => $warehouseId,
                        'quantity' => $quantity,
                        'reserved_quantity' => 0,
                    ]);
                }

                BatchMovement::create([
                    'batch_item_id' => $batchItem->id,
                    'from_warehouse_id' => null,
                    'to_warehouse_id' => $warehouseId,
                    'movement_type' => 'in',
                    'quantity' => $quantity,
                    'movement_date' => $date,
                    'notes' => $notes ?? 'توزيع من الدفعة '.$batch->batch_number,
                ]);
            }

            // إذا تم توزيع كل الكميات، اقفل الدفعة
            $this->lockBatchIfComplete($batch);
        });
    }

    /**
     * يقفل الدفعة إذا تم توزيع كافة الكميات
     */
    protected function lockBatchIfComplete(ProductionBatch $batch): void
    {
        $batch->load('batchItems');

        $totalProduced = $batch->batchItems->sum('quantity');
        $totalDispatched = $batch->batchItems->sum('dispatched_quantity');

        if ($totalProduced > 0 && $totalProduced <= $totalDispatched) {
            $batch->update(['status' => 'consumed']);
        }
    }
}
