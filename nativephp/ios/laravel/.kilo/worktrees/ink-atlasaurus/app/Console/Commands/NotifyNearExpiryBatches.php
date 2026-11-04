<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BatchItem;
use App\Models\BatchItemStock;
use Illuminate\Console\Command;

class NotifyNearExpiryBatches extends Command
{
    protected $signature = 'batches:notify-near-expiry';
    protected $description = 'Send notifications for batches expiring within the next 30 days';

    public function handle(): int
    {
        $this->info('Checking for near-expiry batches...');

        $threshold = now()->addDays(30);

        $nearExpiry = BatchItem::where('expiry_date', '<=', $threshold)
            ->where('expiry_date', '>=', now())
            ->with(['item', 'pack', 'batch'])
            ->get();

        $count = 0;

        foreach ($nearExpiry as $batchItem) {
            $daysUntilExpiry = now()->diffInDays($batchItem->expiry_date);

            $this->warn(sprintf(
                'Near expiry: Item %s, Batch #%s, expires in %d days (%s)',
                $batchItem->item->name_en ?? $batchItem->item->name_ar ?? 'Unknown',
                $batchItem->batch->batch_number ?? 'N/A',
                $daysUntilExpiry,
                $batchItem->expiry_date->format('Y-m-d'),
            ));

            $count++;
        }

        $this->info(sprintf('Found %d near-expiry batches.', $count));

        return Command::SUCCESS;
    }
}
