<?php

namespace App\Services\Warehouse;

use App\Models\BatchItem;
use App\Models\BatchItemStock;
use App\Models\BatchMovement;
use App\Models\ProductionBatch;
use Illuminate\Support\Facades\DB;

class BatchDistributionService
{
    public function distribute(int $batchId, array $data, ?string $notes = null): void
    {
        $batch = ProductionBatch::findOrFail($batchId);

        DB::transaction(function () use ($batch, $data, $notes) {
            $warehouseId = $data['warehouse_id'];
            $date = $data['distribution_date'] ?? now()->toDateString();

            $grouped = collect($data['items'])->groupBy('batch_item_id')->map(fn ($group) => $group->sum('quantity'));

            foreach ($grouped as $batchItemId => $quantity) {
                $batchItem = BatchItem::where('batch_id', $batch->id)
                    ->with(['item', 'pack'])
                    ->findOrFail($batchItemId);

                if ($quantity > $batchItem->remaining_quantity) {
                    throw new \Exception("الكمية المطلوبة تتجاوز الكمية المتبقية ({$batchItem->remaining_quantity}) للمنتج: {$batchItem->item->name_en}.");
                }

                $batchItem->transferred_quantity += $quantity;
                $batchItem->remaining_quantity = $batchItem->quantity - $batchItem->transferred_quantity;
                $batchItem->save();

                BatchItemStock::updateOrCreate(
                    ['batch_item_id' => $batchItem->id, 'warehouse_id' => $warehouseId],
                    []
                );

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
        });
    }
}
