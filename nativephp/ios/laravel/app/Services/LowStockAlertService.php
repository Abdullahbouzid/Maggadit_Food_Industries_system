<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BatchItemStock;
use App\Models\Item;
use App\Models\Notification;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Low-stock detection at the granularity the business actually cares about:
 * one row per batch, per pack, per warehouse.
 */
class LowStockAlertService
{
    /**
     * Every stock row, with its batch, item, pack and warehouse resolved, and
     * a computed is_low flag.
     *
     * @return \Illuminate\Support\Collection<int, object>
     */
    public function stockRows(array $filters = [])
    {
        return DB::table('batch_item_stock as bis')
            ->join('batch_items as bi', 'bi.id', '=', 'bis.batch_item_id')
            ->join('items as i', 'i.id', '=', 'bi.item_id')
            ->leftJoin('item_packs as p', 'p.id', '=', 'bi.item_pack_id')
            ->leftJoin('pack_types as pt', 'pt.id', '=', 'p.pack_type_id')
            ->leftJoin('warehouses as w', 'w.id', '=', 'bis.warehouse_id')
            ->leftJoin('production_batches as pb', 'pb.id', '=', 'bi.batch_id')
            ->when($filters['warehouse_id'] ?? null, fn ($q, $v) => $q->where('bis.warehouse_id', $v))
            ->when($filters['item_id'] ?? null, fn ($q, $v) => $q->where('bi.item_id', $v))
            ->when($filters['only_low'] ?? null, fn ($q) => $q->whereRaw(
                '(bis.quantity - bis.reserved_quantity) <= bis.low_stock_threshold'
            ))
            ->orderBy('i.name_ar')
            ->orderByDesc('bi.expiry_date')
            ->selectRaw(
                'bis.id as batch_stock_id, bis.warehouse_id, bis.quantity, bis.reserved_quantity,
                 bis.low_stock_threshold,
                 (bis.quantity - bis.reserved_quantity) as available_quantity,
                 bi.id as batch_item_id, bi.expiry_date, bi.item_id, bi.item_pack_id,
                 COALESCE(pb.batch_number, "—") as batch_number,
                 i.name_ar, i.name_en, i.code,
                 COALESCE(p.pack_type_id, NULL) as pack_type_id,
                 w.name_ar as warehouse_name_ar, w.name_en as warehouse_name_en'
            )
            ->get()
            ->map(function ($row) {
                $row->is_low = (float) $row->available_quantity <= (float) $row->low_stock_threshold;
                $row->is_expired = $row->expiry_date !== null
                    && \Carbon\Carbon::parse($row->expiry_date)->isPast();
                $row->name = $row->name_ar ?: ($row->name_en ?: 'منتج');
                $row->warehouse_name = $row->warehouse_name_ar ?: ($row->warehouse_name_en ?: '—');

                return $row;
            });
    }

    /**
     * @return \Illuminate\Support\Collection<int, object>
     */
    public function lowStockRows(array $filters = []): \Illuminate\Support\Collection
    {
        return $this->stockRows($filters + ['only_low' => true]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, object>
     */
    public function expiringRows(int $withinDays = 30, array $filters = []): \Illuminate\Support\Collection
    {
        $cutoff = now()->addDays($withinDays)->toDateString();

        return $this->stockRows($filters)->filter(
            fn ($row) => $row->expiry_date !== null && $row->expiry_date <= $cutoff
        )->values();
    }

    public function lowStockCount(array $filters = []): int
    {
        return $this->lowStockRows($filters)->count();
    }

    /**
     * Raise one notification summarising the current low-stock positions.
     * Skipped when nothing is low, so it is safe to call from a scheduler.
     *
     * @return ?Notification
     */
    public function notifyAdmins(array $filters = []): ?Notification
    {
        $rows = $this->lowStockRows($filters);

        if ($rows->isEmpty()) {
            return null;
        }

        $lines = $rows->take(10)->map(fn ($row) => sprintf(
            '%s — %s (باتش %s): متاح %s / الحد %s',
            $row->name,
            $row->warehouse_name,
            $row->batch_number,
            rtrim(rtrim(number_format((float) $row->available_quantity, 2), '0'), '.'),
            rtrim(rtrim(number_format((float) $row->low_stock_threshold, 2), '0'), '.')
        ))->implode("\n");

        $extra = $rows->count() > 10 ? "\n… (+" . ($rows->count() - 10) . ')' : '';

        return Notification::create([
            'subject_ar' => 'تنبيه انخفاض المخزون',
            'subject_en' => 'Low stock alert',
            'body_ar' => 'مواقف وصلت إلى الحد الأدنى أو أقل:' . "\n" . $lines . $extra,
            'body_en' => 'Stock positions at or below their minimum:' . "\n" . $lines . $extra,
            'group' => 'inventory',
            'channel' => 'in_app',
            'icon_type' => 'warning',
            'is_sent' => true,
        ]);
    }

    /**
     * Raise a notification for batches that are expired or about to expire.
     */
    public function notifyExpiring(int $withinDays = 30): ?Notification
    {
        $rows = $this->expiringRows($withinDays);

        if ($rows->isEmpty()) {
            return null;
        }

        $lines = $rows->take(10)->map(fn ($row) => sprintf(
            '%s (باتش %s): ينتهي في %s — المتاح %s',
            $row->name,
            $row->batch_number,
            $row->expiry_date,
            rtrim(rtrim(number_format((float) $row->available_quantity, 2), '0'), '.')
        ))->implode("\n");

        return Notification::create([
            'subject_ar' => 'تنبيه قرب انتهاء الصلاحية',
            'subject_en' => 'Expiry alert',
            'body_ar' => 'دفعات قاربت على الانتهاء أو انتهت:' . "\n" . $lines,
            'body_en' => 'Batches nearing or past expiry:' . "\n" . $lines,
            'group' => 'inventory',
            'channel' => 'in_app',
            'icon_type' => 'warning',
            'is_sent' => true,
        ]);
    }
}
