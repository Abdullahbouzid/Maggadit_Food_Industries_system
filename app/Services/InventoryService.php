<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\InventoryServiceInterface;
use App\DTOs\AvailabilityResult;
use App\DTOs\CommitResult;
use App\DTOs\FefoAllocation;
use App\Enums\InventoryMovementType;
use App\Enums\StockReservationStatus;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\ItemNotFoundException;
use App\Models\BatchItemStock;
use App\Models\Inventory;
use App\Models\Invoice;
use App\Models\InvoiceItemBatch;
use App\Models\InventoryMovement;
use App\Models\ItemPack;
use App\Models\ReturnItem;
use App\Models\StockReservation;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class InventoryService implements InventoryServiceInterface
{
    public function checkAvailability(
        int $itemId,
        ?int $itemPackId,
        int $warehouseId,
    ): AvailabilityResult {
        $packId = $itemPackId ?? $this->resolveDefaultPackId($itemId);

        $inventory = Inventory::where('item_id', $itemId)
            ->where('item_pack_id', $packId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        // Also check batch item stock for production batches
        $batchStockQuantity = BatchItemStock::whereHas('batchItem', function ($q) use ($itemId, $packId) {
            $q->where('item_id', $itemId)
              ->where('item_pack_id', $packId);
        })
        ->where('warehouse_id', $warehouseId)
        ->where('quantity', '>', 0)
        ->sum('quantity');

        $onHand = $inventory ? (float) $inventory->quantity_on_hand : 0.0;
        $onHand += $batchStockQuantity;
        $reserved = $inventory ? (float) $inventory->reserved_quantity : 0.0;
        $damaged = $inventory ? (float) $inventory->damaged_quantity : 0.0;
        $reorderLevel = $inventory ? (float) $inventory->reorder_level : 0.0;

        return new AvailabilityResult(
            itemId: $itemId,
            itemPackId: $packId,
            warehouseId: $warehouseId,
            onHand: $onHand,
            reserved: $reserved,
            available: max(0.0, $onHand - $reserved),
            damaged: $damaged,
            reorderLevel: $reorderLevel,
            needsReorder: $onHand <= $reorderLevel,
        );
    }

    public function reserve(
        int $itemId,
        ?int $itemPackId,
        int $warehouseId,
        float $quantity,
        string $referenceType,
        int $referenceId,
        ?Carbon $expiresAt = null,
    ): StockReservation {
        return DB::transaction(function () use (
            $itemId, $itemPackId, $warehouseId, $quantity,
            $referenceType, $referenceId, $expiresAt
        ) {
            $packId = $itemPackId ?? $this->resolveDefaultPackId($itemId);

            $inventory = Inventory::where('item_id', $itemId)
                ->where('item_pack_id', $packId)
                ->where('warehouse_id', $warehouseId)
                ->lockForUpdate()
                ->first();

            $available = $inventory
                ? (float) $inventory->quantity_on_hand - (float) $inventory->reserved_quantity
                : 0.0;

            if ($available < $quantity) {
                throw new InsufficientStockException(
                    requested: $quantity,
                    available: $available,
                    itemId: $itemId,
                    itemPackId: $packId,
                    warehouseId: $warehouseId,
                );
            }

            if (!$inventory) {
                $inventory = Inventory::create([
                    'item_id' => $itemId,
                    'item_pack_id' => $packId,
                    'warehouse_id' => $warehouseId,
                    'quantity_on_hand' => 0,
                    'reserved_quantity' => 0,
                    'damaged_quantity' => 0,
                    'reorder_level' => 0,
                ]);
            }

            $reservationTtl = $expiresAt ?? now()->addHours(24);

            $reservation = StockReservation::create([
                'item_id' => $itemId,
                'item_pack_id' => $packId,
                'warehouse_id' => $warehouseId,
                'quantity' => $quantity,
                'reserved_for_type' => $referenceType,
                'reserved_for_id' => $referenceId,
                'expires_at' => $reservationTtl,
                'status' => StockReservationStatus::Active->value,
            ]);

            $inventory->increment('reserved_quantity', $quantity);

            $balanceAfter = (float) $inventory->reserved_quantity;

            InventoryMovement::create([
                'inventory_id' => $inventory->id,
                'item_id' => $itemId,
                'item_pack_id' => $packId,
                'warehouse_id' => $warehouseId,
                'type' => InventoryMovementType::Reserve->value,
                'quantity' => $quantity,
                'reference_type' => $reservation->reserved_for_type,
                'reference_id' => $reservation->reserved_for_id,
                'balance_after' => $balanceAfter,
                'notes' => 'Reservation created: ' . $reservation->id,
            ]);

            return $reservation;
        });
    }

    public function release(int $reservationId, ?string $reason = null): void
    {
        DB::transaction(function () use ($reservationId, $reason) {
            $reservation = StockReservation::where('id', $reservationId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($reservation->status !== StockReservationStatus::Active->value) {
                throw new \RuntimeException(
                    'Cannot release reservation #' . $reservation->id .
                    ' with status: ' . $reservation->status
                );
            }

            $inventory = Inventory::where('item_id', $reservation->item_id)
                ->where('item_pack_id', $reservation->item_pack_id)
                ->where('warehouse_id', $reservation->warehouse_id)
                ->lockForUpdate()
                ->first();

            if ($inventory) {
                $inventory->decrement('reserved_quantity', (float) $reservation->quantity);

                $balanceAfter = (float) $inventory->reserved_quantity;

                InventoryMovement::create([
                    'inventory_id' => $inventory->id,
                    'item_id' => $reservation->item_id,
                    'item_pack_id' => $reservation->item_pack_id,
                    'warehouse_id' => $reservation->warehouse_id,
                    'type' => InventoryMovementType::Release->value,
                    'quantity' => -(float) $reservation->quantity,
                    'reference_type' => StockReservation::class,
                    'reference_id' => $reservation->id,
                    'balance_after' => $balanceAfter,
                    'notes' => 'Reservation released' . ($reason ? ': ' . $reason : '') . ' (#' . $reservation->id . ')',
                ]);
            }

            $reservation->update([
                'status' => StockReservationStatus::Released->value,
                'released_at' => now(),
            ]);
        });
    }

    public function commit(Invoice $invoice): CommitResult
    {
        return DB::transaction(function () use ($invoice) {
            $itemIds = $invoice->items()->pluck('id')->toArray();

            $existingBatches = InvoiceItemBatch::whereIn('invoice_item_id', $itemIds)->exists();

            if ($existingBatches) {
                throw new \RuntimeException('Invoice already committed');
            }

            $invoiceItems = $invoice->items()->get();

            $allocations = [];
            $totalQuantity = 0.0;

            foreach ($invoiceItems as $invoiceItem) {
                $packId = $invoiceItem->item_pack_id
                    ?? $this->resolveDefaultPackId($invoiceItem->item_id);

                $itemAllocations = $this->allocateFefoLocked(
                    (int) $invoiceItem->item_id,
                    $packId,
                    (int) $invoiceItem->warehouse_id,
                    (float) $invoiceItem->quantity,
                );

                $inventory = Inventory::where('item_id', $invoiceItem->item_id)
                    ->where('item_pack_id', $packId)
                    ->where('warehouse_id', $invoiceItem->warehouse_id)
                    ->lockForUpdate()
                    ->first();

                if (!$inventory) {
                    continue;
                }

                foreach ($itemAllocations as $allocation) {
                    $batchItemStock = BatchItemStock::lockForUpdate()
                        ->findOrFail($allocation->batchStockId);
                    $batchItemStock->decrement('quantity', $allocation->quantity);

                    $inventory->decrement('quantity_on_hand', $allocation->quantity);

                    $balanceAfter = (float) $inventory->quantity_on_hand;

                    InventoryMovement::create([
                        'inventory_id' => $inventory->id,
                        'item_id' => $invoiceItem->item_id,
                        'item_pack_id' => $packId,
                        'warehouse_id' => $invoiceItem->warehouse_id,
                        'batch_item_id' => $allocation->batchItemId,
                        'type' => InventoryMovementType::Out->value,
                        'quantity' => -$allocation->quantity,
                        'reference_type' => Invoice::class,
                        'reference_id' => $invoice->id,
                        'balance_after' => $balanceAfter,
                        'notes' => 'FEFO commit for invoice #' . $invoice->id,
                    ]);

                    InvoiceItemBatch::create([
                        'invoice_item_id' => $invoiceItem->id,
                        'batch_item_id' => $allocation->batchItemId,
                        'quantity' => $allocation->quantity,
                        'unit_cost' => $allocation->unitCost ?? 0,
                    ]);

                    $allocations[] = $allocation;
                    $totalQuantity += $allocation->quantity;
                }

                $currentReserved = (float) $inventory->reserved_quantity;
                $toRelease = min($currentReserved, (float) $invoiceItem->quantity);

                if ($toRelease > 0) {
                    $inventory->decrement('reserved_quantity', $toRelease);
                }

                $reservation = StockReservation::where('reserved_for_type', Invoice::class)
                    ->where('reserved_for_id', $invoice->id)
                    ->where('item_id', $invoiceItem->item_id)
                    ->where('item_pack_id', $packId)
                    ->where('warehouse_id', $invoiceItem->warehouse_id)
                    ->lockForUpdate()
                    ->first();

                if ($reservation) {
                    $reservation->update(['status' => StockReservationStatus::Consumed->value]);
                } else {
                    Log::warning(
                        'InventoryService::commit — no active reservation found',
                        [
                            'invoice_id' => $invoice->id,
                            'item_id' => $invoiceItem->item_id,
                            'item_pack_id' => $packId,
                            'warehouse_id' => $invoiceItem->warehouse_id,
                        ]
                    );
                }
            }

            return new CommitResult(
                allocations: $allocations,
                totalQuantity: $totalQuantity,
                invoiceId: $invoice->id,
            );
        });
    }

    public function adjust(
        int $inventoryId,
        float $newQuantity,
        string $reason,
        int $userId,
    ): void {
        DB::transaction(function () use ($inventoryId, $newQuantity, $reason, $userId) {
            $inventory = Inventory::lockForUpdate()->findOrFail($inventoryId);

            $quantityDiff = $newQuantity - (float) $inventory->quantity_on_hand;
            $inventory->update(['quantity_on_hand' => $newQuantity]);

            $balanceAfter = (float) $inventory->quantity_on_hand;

            InventoryMovement::create([
                'inventory_id' => $inventory->id,
                'item_id' => $inventory->item_id,
                'item_pack_id' => $inventory->item_pack_id,
                'warehouse_id' => $inventory->warehouse_id,
                'type' => InventoryMovementType::Adjustment->value,
                'quantity' => $quantityDiff,
                'balance_after' => $balanceAfter,
                'created_by' => $userId,
                'notes' => 'Adjustment: ' . $reason,
            ]);
        });
    }

    public function receiveGoods(
        int $itemId,
        ?int $itemPackId,
        int $warehouseId,
        float $quantity,
        ?int $batchItemId = null,
        ?int $userId = null,
    ): void {
        DB::transaction(function () use ($itemId, $itemPackId, $warehouseId, $quantity, $batchItemId, $userId) {
            $packId = $itemPackId ?? $this->resolveDefaultPackId($itemId);

            $inventory = Inventory::where([
                'item_id' => $itemId,
                'item_pack_id' => $packId,
                'warehouse_id' => $warehouseId,
            ])->lockForUpdate()->first();

            if (!$inventory) {
                Inventory::create([
                    'item_id' => $itemId,
                    'item_pack_id' => $packId,
                    'warehouse_id' => $warehouseId,
                    'quantity_on_hand' => 0,
                    'reserved_quantity' => 0,
                    'damaged_quantity' => 0,
                    'reorder_level' => 0,
                ]);
                $inventory = Inventory::where([
                    'item_id' => $itemId,
                    'item_pack_id' => $packId,
                    'warehouse_id' => $warehouseId,
                ])->lockForUpdate()->first();
            }

            $inventory->increment('quantity_on_hand', $quantity);

            $balanceAfter = (float) $inventory->quantity_on_hand;

            InventoryMovement::create([
                'inventory_id' => $inventory->id,
                'item_id' => $itemId,
                'item_pack_id' => $packId,
                'warehouse_id' => $warehouseId,
                'batch_item_id' => $batchItemId,
                'type' => InventoryMovementType::In->value,
                'quantity' => $quantity,
                'balance_after' => $balanceAfter,
                'created_by' => $userId,
                'notes' => 'Goods received',
            ]);

            if ($batchItemId) {
                $batchItemStock = BatchItemStock::where([
                    'batch_item_id' => $batchItemId,
                    'warehouse_id' => $warehouseId,
                ])->lockForUpdate()->first();

                if (!$batchItemStock) {
                    BatchItemStock::create([
                        'batch_item_id' => $batchItemId,
                        'warehouse_id' => $warehouseId,
                        'quantity' => 0,
                        'reserved_quantity' => 0,
                    ]);
                    $batchItemStock = BatchItemStock::where([
                        'batch_item_id' => $batchItemId,
                        'warehouse_id' => $warehouseId,
                    ])->lockForUpdate()->first();
                }
                $batchItemStock->increment('quantity', $quantity);
            }
        });
    }

    public function receiveReturn(ReturnItem $returnItem): void
    {
        DB::transaction(function () use ($returnItem) {
            $warehouseId = $returnItem->invoiceItem->warehouse_id;
            $packId = $returnItem->item_pack_id
                ?? $returnItem->invoiceItem->item_pack_id
                ?? $this->resolveDefaultPackId($returnItem->item_id);

            $inventory = Inventory::where('item_id', $returnItem->item_id)
                ->where('item_pack_id', $packId)
                ->where('warehouse_id', $warehouseId)
                ->lockForUpdate()
                ->first();

            if (!$inventory) {
                $inventory = Inventory::create([
                    'item_id' => $returnItem->item_id,
                    'item_pack_id' => $packId,
                    'warehouse_id' => $warehouseId,
                    'quantity_on_hand' => 0,
                    'reserved_quantity' => 0,
                    'damaged_quantity' => 0,
                    'reorder_level' => 0,
                ]);
            }

            $movementType = $returnItem->condition === 'damaged'
                ? InventoryMovementType::ReturnDamaged
                : InventoryMovementType::ReturnGood;

            if ($movementType === InventoryMovementType::ReturnDamaged) {
                $inventory->increment('damaged_quantity', (float) $returnItem->quantity_returned);
            } else {
                $inventory->increment('quantity_on_hand', (float) $returnItem->quantity_returned);
            }

            $balanceAfter = $returnItem->condition === 'damaged'
                ? (float) $inventory->damaged_quantity
                : (float) $inventory->quantity_on_hand;

            InventoryMovement::create([
                'inventory_id' => $inventory->id,
                'item_id' => $returnItem->item_id,
                'item_pack_id' => $packId,
                'warehouse_id' => $warehouseId,
                'batch_item_id' => $returnItem->batch_item_id,
                'type' => $movementType->value,
                'quantity' => (float) $returnItem->quantity_returned,
                'reference_type' => ReturnItem::class,
                'reference_id' => $returnItem->id,
                'balance_after' => $balanceAfter,
                'notes' => 'Return received: ' . $returnItem->returnRequest->return_number,
            ]);

            if ($movementType === InventoryMovementType::ReturnGood && $returnItem->batch_item_id) {
                $batchItemStock = BatchItemStock::where('batch_item_id', $returnItem->batch_item_id)
                    ->where('warehouse_id', $warehouseId)
                    ->lockForUpdate()
                    ->first();

                if ($batchItemStock) {
                    $batchItemStock->increment('quantity', (float) $returnItem->quantity_returned);
                }
            }
        });
    }

    public function reverseCommit(Invoice $invoice): void
    {
        DB::transaction(function () use ($invoice) {
            $itemIds = $invoice->items()->pluck('id')->toArray();

            $batchLinks = InvoiceItemBatch::whereIn('invoice_item_id', $itemIds)
                ->get();

            foreach ($batchLinks as $batchLink) {
                $invoiceItem = $batchLink->invoiceItem;

                $packId = $invoiceItem->item_pack_id
                    ?? $this->resolveDefaultPackId($invoiceItem->item_id);

                $batchItemStock = BatchItemStock::lockForUpdate()->findOrFail($batchLink->batch_item_id);
                $batchItemStock->increment('quantity', (float) $batchLink->quantity);

                $inventory = Inventory::where('item_id', $invoiceItem->item_id)
                    ->where('item_pack_id', $packId)
                    ->where('warehouse_id', $invoiceItem->warehouse_id)
                    ->lockForUpdate()
                    ->first();

                if ($inventory) {
                    $inventory->increment('quantity_on_hand', (float) $batchLink->quantity);

                    $balanceAfter = (float) $inventory->quantity_on_hand;

                    InventoryMovement::create([
                        'inventory_id' => $inventory->id,
                        'item_id' => $inventory->item_id,
                        'item_pack_id' => $inventory->item_pack_id,
                        'warehouse_id' => $inventory->warehouse_id,
                        'batch_item_id' => $batchLink->batch_item_id,
                        'type' => InventoryMovementType::Adjustment->value,
                        'quantity' => (float) $batchLink->quantity,
                        'reference_type' => Invoice::class,
                        'reference_id' => $invoice->id,
                        'balance_after' => $balanceAfter,
                        'notes' => 'Invoice #' . $invoice->id . ' cancelled/expired — stock restored',
                    ]);
                }
            }

            InvoiceItemBatch::whereIn('invoice_item_id', $itemIds)->delete();

            StockReservation::where('reserved_for_type', Invoice::class)
                ->where('reserved_for_id', $invoice->id)
                ->where('status', StockReservationStatus::Active->value)
                ->update([
                    'status' => StockReservationStatus::Released->value,
                    'released_at' => now(),
                ]);
        });
    }

    public function getAvailable(
        int $itemId,
        ?int $itemPackId,
        int $warehouseId,
    ): float {
        $packId = $itemPackId ?? $this->resolveDefaultPackId($itemId);

        $inventory = Inventory::where('item_id', $itemId)
            ->where('item_pack_id', $packId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if (!$inventory) {
            return 0.0;
        }

        return (float) $inventory->quantity_on_hand - (float) $inventory->reserved_quantity;
    }

    public function allocateFefo(
        int $itemId,
        ?int $itemPackId,
        int $warehouseId,
        float $quantity,
    ): array {
        $packId = $itemPackId ?? $this->resolveDefaultPackId($itemId);

        $batchItemStocks = $this->queryBatchItemStocks($itemId, $packId, $warehouseId)
            ->get();

        return $this->performFefoAllocation($batchItemStocks, $quantity, $itemId, $packId, $warehouseId);
    }

    public function expireReservations(): int
    {
        return DB::transaction(function () {
            $expired = StockReservation::where('status', StockReservationStatus::Active->value)
                ->where('expires_at', '<', now())
                ->lockForUpdate()
                ->get();

            $expiredCount = 0;

            foreach ($expired as $reservation) {
                $reservation->update([
                    'status' => StockReservationStatus::Released->value,
                    'released_at' => now(),
                ]);

                $inventory = Inventory::where('item_id', $reservation->item_id)
                    ->where('item_pack_id', $reservation->item_pack_id)
                    ->where('warehouse_id', $reservation->warehouse_id)
                    ->lockForUpdate()
                    ->first();

                if ($inventory) {
                    $inventory->decrement('reserved_quantity', (float) $reservation->quantity);

                    $balanceAfter = (float) $inventory->reserved_quantity;

                    InventoryMovement::create([
                        'inventory_id' => $inventory->id,
                        'item_id' => $reservation->item_id,
                        'item_pack_id' => $reservation->item_pack_id,
                        'warehouse_id' => $reservation->warehouse_id,
                        'type' => InventoryMovementType::Release->value,
                        'quantity' => -(float) $reservation->quantity,
                        'reference_type' => StockReservation::class,
                        'reference_id' => $reservation->id,
                        'balance_after' => $balanceAfter,
                        'notes' => 'Reservation expired: ' . $reservation->id,
                    ]);
                }

                $expiredCount++;
            }

            return $expiredCount;
        });
    }

    /**
     * Locked version of allocateFefo for use within commit().
     * Acquires lockForUpdate on BatchItemStock rows before reading.
     */
    private function allocateFefoLocked(
        int $itemId,
        int $packId,
        int $warehouseId,
        float $quantity,
    ): array {
        $batchItemStocks = $this->queryBatchItemStocks($itemId, $packId, $warehouseId)
            ->lockForUpdate()
            ->get();

        return $this->performFefoAllocation($batchItemStocks, $quantity, $itemId, $packId, $warehouseId);
    }

    /**
     * Build the base FEFO query for batch item stocks.
     */
    private function queryBatchItemStocks(int $itemId, int $packId, int $warehouseId)
    {
        return BatchItemStock::query()
            ->where('batch_item_stock.quantity', '>', 0)
            ->join('batch_items as bi', 'bi.id', '=', 'batch_item_stock.batch_item_id')
            ->where('bi.item_id', $itemId)
            ->where('bi.item_pack_id', $packId)
            ->orderBy('bi.expiry_date', 'asc')
            ->orderBy('batch_item_stock.id')
            ->select(
                'batch_item_stock.id as batch_stock_id',
                'batch_item_stock.batch_item_id',
                'batch_item_stock.quantity',
                'bi.expiry_date',
                'bi.unit_cost'
            );
    }

    /**
     * Perform FEFO allocation logic on a collection of batch item stocks.
     *
     * @param mixed $batchItemStocks
     * @param float $quantity
     * @param int $itemId
     * @param int $packId
     * @param int $warehouseId
     * @return array<int, FefoAllocation>
     * @throws InsufficientStockException
     */
    private function performFefoAllocation(
        $batchItemStocks,
        float $quantity,
        int $itemId,
        int $packId,
        int $warehouseId,
    ): array {
        $allocations = [];
        $remaining = $quantity;

        foreach ($batchItemStocks as $stock) {
            if ($remaining <= 0) {
                break;
            }

            $deduct = min((float) $stock->quantity, $remaining);
            $remaining -= $deduct;

            $allocations[] = new FefoAllocation(
                batchStockId: (int) $stock->batch_stock_id,
                batchItemId: (int) $stock->batch_item_id,
                quantity: $deduct,
                expiryDate: $stock->expiry_date ? Carbon::parse($stock->expiry_date)->format('Y-m-d') : null,
                unitCost: $stock->unit_cost ? (float) $stock->unit_cost : null,
            );
        }

        if ($remaining > 0) {
            $available = $this->getAvailable($itemId, $packId, $warehouseId);
            throw new InsufficientStockException(
                requested: $quantity,
                available: $available,
                itemId: $itemId,
                itemPackId: $packId,
                warehouseId: $warehouseId,
            );
        }

        return $allocations;
    }

    /**
     * Resolve the default pack ID for an item.
     * Throws ItemNotFoundException if no pack exists.
     *
     * @param int $itemId
     * @return int
     * @throws ItemNotFoundException
     */
    private function resolveDefaultPackId(int $itemId): int
    {
        $packId = ItemPack::where('item_id', $itemId)
            ->where('is_default_pack', true)
            ->value('id')
            ?? ItemPack::where('item_id', $itemId)
                ->orderBy('id', 'asc')
                ->value('id');

        if ($packId === null) {
            throw new ItemNotFoundException(itemId: $itemId);
        }

        return (int) $packId;
    }
}
