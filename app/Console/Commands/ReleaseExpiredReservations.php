<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\InventoryService;
use App\Models\StockReservation;
use Illuminate\Console\Command;

class ReleaseExpiredReservations extends Command
{
    protected $signature = 'reservations:release-expired';
    protected $description = 'Release stock reservations that have exceeded their expiration time';

    public function handle(InventoryService $inventoryService): int
    {
        $this->info('Releasing expired reservations...');

        $expired = StockReservation::where('status', 'active')
            ->where('expires_at', '<', now())
            ->get();

        $count = 0;

        foreach ($expired as $reservation) {
            try {
                $inventoryService->release($reservation->id);
                $count++;
            } catch (\Exception $e) {
                $this->error(sprintf('Failed to release reservation #%d: %s', $reservation->id, $e->getMessage()));
            }
        }

        $this->info(sprintf('Released %d expired reservations.', $count));

        return Command::SUCCESS;
    }
}
