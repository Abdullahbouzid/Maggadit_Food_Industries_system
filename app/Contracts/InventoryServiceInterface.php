<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\AvailabilityResult;
use App\DTOs\CommitResult;
use App\DTOs\FefoAllocation;
use App\Models\Invoice;
use App\Models\ReturnItem;
use App\Models\StockReservation;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Support\Collection;

interface InventoryServiceInterface
{
    public function checkAvailability(
        int $itemId,
        ?int $itemPackId,
        int $warehouseId,
    ): AvailabilityResult;

    public function reserve(
        int $itemId,
        ?int $itemPackId,
        int $warehouseId,
        float $quantity,
        string $referenceType,
        int $referenceId,
        ?Carbon $expiresAt = null,
    ): StockReservation;

    public function release(int $reservationId, ?string $reason = null): void;

    public function commit(Invoice $invoice): CommitResult;

    public function expireReservations(): int;

    public function getAvailable(
        int $itemId,
        ?int $itemPackId,
        int $warehouseId,
    ): float;

    public function allocateFefo(
        int $itemId,
        ?int $itemPackId,
        int $warehouseId,
        float $quantity,
    ): array;

    public function adjust(
        int $inventoryId,
        float $newQuantity,
        string $reason,
        int $userId,
    ): void;

    public function receiveGoods(
        int $itemId,
        ?int $itemPackId,
        int $warehouseId,
        float $quantity,
        ?int $batchItemId = null,
        ?int $userId = null,
    ): void;

    public function receiveReturn(ReturnItem $returnItem): void;

    public function reverseCommit(Invoice $invoice): void;
}
