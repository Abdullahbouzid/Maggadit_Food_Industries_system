<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\WarehouseStockAlert;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SendWarehouseStockAlertNotification
{
    public function handle(WarehouseStockAlert $event): void
    {
        $inventory = $event->inventory;
        $item = $inventory->item;
        $pack = $inventory->pack;
        $warehouse = $inventory->warehouse;

        $itemName = $item ? ($item->name_ar ?? $item->name_en ?? 'منتج غير محدد') : 'منتج غير محدد';
        $packName = $pack ? ($pack->packType?->name ?? $pack->getDisplayNameAttribute()) : 'وحدة';
        $warehouseName = $warehouse ? ($warehouse->name_ar ?? $warehouse->name_en ?? $warehouse->code) : 'مستودع غير محدد';

        $subjectAr = 'تنبيه انخفاض المخزون';
        $subjectEn = 'Low Stock Alert';
        $bodyAr = "المنتج: {$itemName} - {$packName}\nالمستودع: {$warehouseName}\nالكمية المتاحة: {$event->availableQuantity}\nالحد الأدنى: {$event->threshold}";
        $bodyEn = "Item: {$itemName} - {$packName}\nWarehouse: {$warehouseName}\nAvailable: {$event->availableQuantity}\nThreshold: {$event->threshold}";

        $admins = User::role('admin')->get();

        foreach ($admins as $admin) {
            Notification::create([
                'subject_ar' => $subjectAr,
                'subject_en' => $subjectEn,
                'body_ar' => $bodyAr,
                'body_en' => $bodyEn,
                'type' => 'low_stock_alert',
                'data' => [
                    'inventory_id' => $inventory->id,
                    'item_id' => $inventory->item_id,
                    'pack_id' => $inventory->item_pack_id,
                    'warehouse_id' => $inventory->warehouse_id,
                    'available_quantity' => $event->availableQuantity,
                    'threshold' => $event->threshold,
                ],
                'customers' => [],
                'channel' => 'all',
                'is_sent' => true,
            ]);
        }

        Log::info('Low stock alert sent', [
            'item_id' => $inventory->item_id,
            'pack_id' => $inventory->item_pack_id,
            'warehouse_id' => $inventory->warehouse_id,
            'available_quantity' => $event->availableQuantity,
            'threshold' => $event->threshold,
        ]);
    }
}
