<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BatchItem;
use App\Models\BatchItemStock;
use App\Models\BatchMovement;
use App\Models\ItemPack;
use App\Models\ProductionBatch;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WarehouseTransferService
{
    /**
     * Move stock of one batch between warehouses, moving the batch_item_stock
     * rows and recording the movement so dispensing can be audited.
     */
    public function transfer(
        BatchItem $batchItem,
        Warehouse $from,
        Warehouse $to,
        float $quantity,
        ?User $user = null,
        ?string $notes = null,
    ): BatchMovement {
        if ($from->id === $to->id) {
            throw new \InvalidArgumentException('Source and destination warehouses must differ.');
        }

        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Transfer quantity must be positive.');
        }

        return DB::transaction(function () use ($batchItem, $from, $to, $quantity, $user, $notes) {
            $source = BatchItemStock::where('batch_item_id', $batchItem->id)
                ->where('warehouse_id', $from->id)
                ->lockForUpdate()
                ->first();

            $available = $source ? (float) $source->quantity - (float) $source->reserved_quantity : 0.0;

            if ($available < $quantity) {
                throw new \RuntimeException(sprintf(
                    'Insufficient stock to transfer: requested %s, available %s',
                    rtrim(rtrim(number_format($quantity, 3), '0'), '.'),
                    rtrim(rtrim(number_format($available, 3), '0'), '.')
                ));
            }

            if ($source) {
                $source->update(['quantity' => (float) $source->quantity - $quantity]);
            }

            $destination = BatchItemStock::where('batch_item_id', $batchItem->id)
                ->where('warehouse_id', $to->id)
                ->lockForUpdate()
                ->first();

            if ($destination) {
                $destination->update(['quantity' => (float) $destination->quantity + $quantity]);
            } else {
                BatchItemStock::create([
                    'batch_item_id' => $batchItem->id,
                    'warehouse_id' => $to->id,
                    'quantity' => $quantity,
                    'reserved_quantity' => 0,
                    'low_stock_threshold' => $source?->low_stock_threshold ?? 0,
                ]);
            }

            return BatchMovement::create([
                'batch_item_id' => $batchItem->id,
                'from_warehouse_id' => $from->id,
                'to_warehouse_id' => $to->id,
                'movement_type' => 'transfer',
                'quantity' => $quantity,
                'movement_date' => Carbon::now()->toDateString(),
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Dispense stock oldest-batch-first (FIFO) across the batches of one pack
     * in one warehouse, recording an outbound movement per batch touched.
     *
     * This is deliberately separate from the expiry-based FEFO used for
     * invoices: a warehouse picking for its own stock takes the oldest batch.
     *
     * @return array<int, array{batch_item_id: int, batch_number: string|null, quantity: float}>
     */
    public function dispenseFifo(
        ItemPack $pack,
        Warehouse $warehouse,
        float $quantity,
        ?User $user = null,
        ?string $notes = null,
    ): array {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Dispense quantity must be positive.');
        }

        return DB::transaction(function () use ($pack, $warehouse, $quantity, $user, $notes) {
            // Oldest production batch first, then oldest batch row.
            $candidates = BatchItem::query()
                ->join('batch_items as bi', 'bi.id', '=', 'batch_items.id')
                ->join('production_batches as pb', 'pb.id', '=', 'batch_items.batch_id')
                ->where('batch_items.item_pack_id', $pack->id)
                ->where('batch_items.warehouse_id', $warehouse->id)
                ->orderBy('pb.production_date')
                ->orderBy('batch_items.id')
                ->select('batch_items.*')
                ->lockForUpdate()
                ->get();

            $remaining = $quantity;
            $allocations = [];

            foreach ($candidates as $batchItem) {
                if ($remaining <= 0) {
                    break;
                }

                $stock = BatchItemStock::where('batch_item_id', $batchItem->id)
                    ->where('warehouse_id', $warehouse->id)
                    ->lockForUpdate()
                    ->first();

                if (! $stock) {
                    continue;
                }

                $available = (float) $stock->quantity - (float) $stock->reserved_quantity;

                if ($available <= 0) {
                    continue;
                }

                $take = min($available, $remaining);
                $remaining -= $take;

                $stock->update(['quantity' => (float) $stock->quantity - $take]);

                BatchMovement::create([
                    'batch_item_id' => $batchItem->id,
                    'from_warehouse_id' => $warehouse->id,
                    'to_warehouse_id' => null,
                    'movement_type' => 'out',
                    'quantity' => $take,
                    'movement_date' => Carbon::now()->toDateString(),
                    'notes' => $notes ?: 'FIFO dispensing',
                ]);

                $allocations[] = [
                    'batch_item_id' => $batchItem->id,
                    'batch_number' => $batchItem->batch?->batch_number,
                    'quantity' => $take,
                ];
            }

            if ($remaining > 0) {
                throw new \RuntimeException(sprintf(
                    'Insufficient stock: requested %s, available %s',
                    rtrim(rtrim(number_format($quantity, 3), '0'), '.'),
                    rtrim(rtrim(number_format($quantity - $remaining, 3), '0'), '.')
                ));
            }

            return $allocations;
        });
    }

    /**
     * Every transfer and outbound movement, newest first, with batch, product,
     * pack and warehouse resolved.
     */
    public function movements(array $filters = [])
    {
        return BatchMovement::query()
            ->with(['batchItem.batch', 'batchItem.item', 'batchItem.pack', 'fromWarehouse', 'toWarehouse'])
            ->when($filters['from_warehouse_id'] ?? null, fn ($q, $v) => $q->where('from_warehouse_id', $v))
            ->when($filters['to_warehouse_id'] ?? null, fn ($q, $v) => $q->where('to_warehouse_id', $v))
            ->when($filters['item_id'] ?? null, fn ($q, $v) => $q->whereHas('batchItem', fn ($b) => $b->where('item_id', $v)))
            ->orderByDesc('movement_date')
            ->orderByDesc('id')
            ->get();
    }
}
