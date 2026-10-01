<?php

namespace Database\Seeders;

use App\Enums\InventoryMovementType;
use App\Enums\StockReservationStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Database\Seeders\Support\Stamped;

/**
 * Stock side of the system: production batches and their batch items, the
 * per-warehouse inventory rows, the movement ledger, stock reservations and
 * inter-warehouse batch transfers.
 */
class InventoryDataSeeder extends Seeder
{
    public function run(): void
    {
        $items = DB::table('items')->orderBy('id')->get();
        $packs = DB::table('item_packs')->orderBy('id')->get();
        $warehouses = DB::table('warehouses')->orderBy('id')->get();
        $adminId = DB::table('users')->orderBy('id')->value('id');

        if ($items->isEmpty() || $warehouses->isEmpty()) {
            $this->command?->warn('No items or warehouses found; skipping inventory seeding.');

            return;
        }

        $this->inventories($items, $packs, $warehouses, $adminId);
        $this->productionBatches($items);
        $this->batchStockAndMovements($packs, $warehouses, $adminId);
        $this->backfillBatchStockForAllPacks($warehouses, $adminId);
        $this->reservations($items, $packs, $warehouses);
    }

    private function inventories($items, $packs, $warehouses, $adminId): void
    {
        foreach ($items as $item) {
            $itemPacks = $packs->where('item_id', $item->id)->values();
            $packId = $itemPacks->isNotEmpty() ? $itemPacks->first()->id : null;

            foreach ($warehouses as $index => $warehouse) {
                $quantity = 100 + ($index * 25);

                $inventoryId = Stamped::upsert('inventories', 
                    ['item_id' => $item->id, 'warehouse_id' => $warehouse->id], 
                    [
                        'item_id' => $item->id,
                        'item_pack_id' => $packId,
                        'warehouse_id' => $warehouse->id,
                        'quantity_on_hand' => $quantity,
                        'reserved_quantity' => 0,
                        'damaged_quantity' => $index === 0 ? 2 : 0,
                        'reorder_level' => 10,
                        'last_counted_at' => now()->subDays(7),
                    ]);

                // Ledger rows for this inventory row.
                $moves = [
                    [InventoryMovementType::In->value, $quantity, 'إدخال بضاعة'],
                    [InventoryMovementType::Out->value, -10, 'صرف للعميل'],
                    [InventoryMovementType::Adjustment->value, 0, 'جرد دوري'],
                ];

                $existing = DB::table('inventory_movements')
                    ->where('item_id', $item->id)
                    ->where('warehouse_id', $warehouse->id)
                    ->count();

                if ($existing > 0) {
                    continue;
                }

                $balance = 0.0;
                foreach ($moves as $moveIndex => [$type, $qty, $note]) {
                    $balance += $qty;

                    Stamped::insert('inventory_movements', [
                        'inventory_id' => DB::table('inventories')
                            ->where('item_id', $item->id)
                            ->where('warehouse_id', $warehouse->id)
                            ->value('id'),
                        'item_id' => $item->id,
                        'item_pack_id' => $packId,
                        'warehouse_id' => $warehouse->id,
                        'type' => $type,
                        'quantity' => $qty,
                        'balance_after' => $balance,
                        'created_by' => $adminId,
                        'notes' => $note,
                        'created_at' => now()->subDays(20 - ($moveIndex * 5)),
                    ]);
                }
            }
        }
    }

    private function productionBatches($items): void
    {
        $batches = [
            ['batch_number' => 'BATCH-2026-001', 'production_date' => now()->subDays(40)->toDateString(), 'expiry_date' => now()->addDays(180)->toDateString(), 'status' => 'released'],
            ['batch_number' => 'BATCH-2026-002', 'production_date' => now()->subDays(35)->toDateString(), 'expiry_date' => now()->addDays(200)->toDateString(), 'status' => 'released'],
            ['batch_number' => 'BATCH-2026-003', 'production_date' => now()->subDays(30)->toDateString(), 'expiry_date' => now()->addDays(150)->toDateString(), 'status' => 'released'],
            ['batch_number' => 'BATCH-2026-004', 'production_date' => now()->subDays(25)->toDateString(), 'expiry_date' => now()->addDays(90)->toDateString(), 'status' => 'released'],
            ['batch_number' => 'BATCH-2026-005', 'production_date' => now()->subDays(20)->toDateString(), 'expiry_date' => now()->addDays(120)->toDateString(), 'status' => 'released'],
        ];

        foreach ($batches as $batch) {
            Stamped::upsert('production_batches', 
                ['batch_number' => $batch['batch_number']], 
                $batch + ['notes' => 'دفعة إنتاج تجريبية']);
        }

        $batchIds = DB::table('production_batches')->orderBy('id')->pluck('id');
        $packs = DB::table('item_packs')->orderBy('id')->get();
        $warehouses = DB::table('warehouses')->orderBy('id')->pluck('id');

        if ($batchIds->isEmpty() || $packs->isEmpty()) {
            return;
        }

        foreach ($batchIds as $index => $batchId) {
            for ($offset = 0; $offset < 3; $offset++) {
                $pack = $packs->get(($index + $offset) % $packs->count());

                Stamped::upsert('batch_items', 
                    ['batch_id' => $batchId, 'item_id' => $pack->item_id, 'item_pack_id' => $pack->id], 
                    [
                        'batch_id' => $batchId,
                        'item_id' => $pack->item_id,
                        'item_pack_id' => $pack->id,
                        'warehouse_id' => $warehouses->get($offset % $warehouses->count()),
                        'quantity' => 50 + ($offset * 10),
                        'dispatched_quantity' => 0,
                        'transferred_quantity' => 0,
                        'expiry_date' => now()->addDays(120 + ($offset * 30))->toDateString(),
                    ]);
            }
        }
    }

    /**
     * Guarantee every pack that has an inventories row also has batch stock.
     *
     * FEFO allocation (InventoryService::performFefoAllocation) reads
     * batch_item_stock, while getAvailable() reads inventories. If a pack has
     * inventory but no batch stock, approving an invoice fails with
     * "Insufficient stock" even though stock is visibly available.
     */
    private function backfillBatchStockForAllPacks($warehouses, $adminId): void
    {
        $batchId = DB::table('production_batches')->orderBy('id')->value('id');
        $warehouseIds = $warehouses->pluck('id')->all();

        if ($batchId === null || $warehouseIds === []) {
            return;
        }

        $covered = DB::table('batch_items')
            ->pluck('item_pack_id')
            ->flip()
            ->map(fn () => true);

        $packs = DB::table('item_packs')->orderBy('id')->get()
            ->filter(fn ($pack) => ! $covered->has($pack->id));

        $created = 0;

        foreach ($packs as $pack) {
            $stockedWarehouses = DB::table('inventories')
                ->where('item_pack_id', $pack->id)
                ->whereNotNull('warehouse_id')
                ->pluck('warehouse_id')
                ->filter(fn ($id) => in_array($id, $warehouseIds, true))
                ->unique()
                ->values();

            if ($stockedWarehouses->isEmpty()) {
                $stockedWarehouses = collect([$warehouseIds[0]]);
            }

            foreach ($stockedWarehouses as $warehouseId) {
                $batchItemId = Stamped::upsert(
                    'batch_items',
                    ['batch_id' => $batchId, 'item_id' => $pack->item_id, 'item_pack_id' => $pack->id],
                    [
                        'batch_id' => $batchId,
                        'item_id' => $pack->item_id,
                        'item_pack_id' => $pack->id,
                        'warehouse_id' => $warehouseId,
                        'quantity' => 100,
                        'dispatched_quantity' => 0,
                        'transferred_quantity' => 0,
                        'expiry_date' => now()->addDays(180)->toDateString(),
                    ],
                );

                if ($batchItemId) {
                    $batchItemId = DB::table('batch_items')
                        ->where('batch_id', $batchId)
                        ->where('item_id', $pack->item_id)
                        ->where('item_pack_id', $pack->id)
                        ->value('id');
                }

                Stamped::upsert(
                    'batch_item_stock',
                    ['batch_item_id' => $batchItemId, 'warehouse_id' => $warehouseId],
                    [
                        'batch_item_id' => $batchItemId,
                        'warehouse_id' => $warehouseId,
                        'quantity' => 100,
                        'reserved_quantity' => 0,
                    ],
                );

                $created++;
            }
        }

        if ($created > 0) {
            $this->command?->info("  Backfilled batch stock for {$created} pack/warehouse pairs.");
        }
    }

    private function batchStockAndMovements($packs, $warehouses, $adminId): void
    {
        $batchItems = DB::table('batch_items')->orderBy('id')->get();
        $warehouseIds = $warehouses->pluck('id');

        if ($batchItems->isEmpty() || $warehouseIds->isEmpty()) {
            return;
        }

        foreach ($batchItems as $index => $batchItem) {
            Stamped::upsert('batch_item_stock', 
                ['batch_item_id' => $batchItem->id, 'warehouse_id' => $batchItem->warehouse_id ?: $warehouseIds->first()], 
                [
                    'batch_item_id' => $batchItem->id,
                    'warehouse_id' => $batchItem->warehouse_id ?: $warehouseIds->first(),
                    'quantity' => (int) $batchItem->quantity,
                    'reserved_quantity' => 0,
                ]);

            if ($index % 2 === 0 && $warehouseIds->count() > 1) {
                Stamped::upsert('batch_movements', 
                    [
                        'batch_item_id' => $batchItem->id,
                        'from_warehouse_id' => $batchItem->warehouse_id ?: $warehouseIds->first(),
                        'to_warehouse_id' => $warehouseIds->get(1),
                        'movement_date' => now()->subDays($index)->toDateString(),
                    ], 
                    [
                        'batch_item_id' => $batchItem->id,
                        'from_warehouse_id' => $batchItem->warehouse_id ?: $warehouseIds->first(),
                        'to_warehouse_id' => $warehouseIds->get(1),
                        'movement_type' => 'transfer',
                        'quantity' => 5,
                        'movement_date' => now()->subDays($index)->toDateString(),
                        'notes' => 'تحويل بين المخازن',
                    ]);
            }
        }
    }

    private function reservations($items, $packs, $warehouses): void
    {
        $statuses = [
            StockReservationStatus::Active->value,
            StockReservationStatus::Consumed->value,
            StockReservationStatus::Released->value,
            StockReservationStatus::Expired->value,
            StockReservationStatus::Released->value,
        ];

        foreach ($items as $index => $item) {
            $itemPacks = $packs->where('item_id', $item->id)->values();
            $packId = $itemPacks->isNotEmpty() ? $itemPacks->first()->id : null;
            $status = $statuses[$index % count($statuses)];

            Stamped::upsert('stock_reservations', 
                [
                    'item_id' => $item->id,
                    'warehouse_id' => $warehouses->get($index % $warehouses->count())->id,
                    'reserved_for_type' => 'order',
                    'reserved_for_id' => 0,
                    'status' => $status,
                ], 
                [
                    'item_id' => $item->id,
                    'item_pack_id' => $packId,
                    'warehouse_id' => $warehouses->get($index % $warehouses->count())->id,
                    'quantity' => 5,
                    'reserved_for_type' => 'order',
                    'reserved_for_id' => 0,
                    'status' => $status,
                    'expires_at' => in_array($status, [
                        StockReservationStatus::Expired->value,
                        StockReservationStatus::Released->value,
                    ], true) ? now()->subDay() : now()->addDays(3),
                    'released_at' => $status === StockReservationStatus::Released->value ? now()->subDay() : null,
                ]);
        }
    }
}
