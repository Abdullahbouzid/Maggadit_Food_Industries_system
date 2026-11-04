<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Inventory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class NotifyLowStock extends Command
{
    protected $signature = 'inventory:notify-low-stock';
    protected $description = 'Send notifications for items with low stock levels';

    public function handle(): int
    {
        $this->info('Checking for low stock items...');

        $lowStockItems = Inventory::whereRaw('quantity_on_hand <= reorder_level')
            ->where('reorder_level', '>', 0)
            ->where('quantity_on_hand', '>', 0)
            ->with(['item', 'itemPack', 'warehouse'])
            ->get();

        $count = 0;

        foreach ($lowStockItems as $inventory) {
            $item = $inventory->item;

            if ($item && $item->low_stock_qty) {
                $this->warn(sprintf(
                    'Low stock: %s at warehouse %s - on hand: %s, reorder level: %s',
                    $item->name_en ?? $item->name_ar,
                    $inventory->warehouse->name_en ?? $inventory->warehouse->name_ar,
                    $inventory->quantity_on_hand,
                    $inventory->reorder_level,
                ));
                $count++;
            }
        }

        // Send notification to admin users
        // Implementation depends on notification system
        $this->info(sprintf('Found %d low stock items.', $count));

        return Command::SUCCESS;
    }
}
