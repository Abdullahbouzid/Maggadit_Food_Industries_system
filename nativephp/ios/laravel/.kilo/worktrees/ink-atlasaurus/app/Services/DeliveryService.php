<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\DeliveryServiceInterface;
use App\DTOs\TimelineEntry;
use App\Events\DeliveryCancelled;
use App\Events\DeliveryStageChanged;
use App\Models\AuditLog;
use App\Models\Delivery;
use App\Models\DeliveryStageLog;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DeliveryService implements DeliveryServiceInterface
{
    public function assignDriver(
        Invoice $invoice,
        ?Vehicle $vehicle = null,
        ?User $driver = null,
    ): Delivery {
        return DB::transaction(function () use ($invoice, $vehicle, $driver) {
            $order = $invoice->order;
            $delivery = Delivery::where('invoice_id', $invoice->id)
                ->lockForUpdate()
                ->first();

            if (!$delivery) {
                $delivery = Delivery::create([
                    'invoice_id' => $invoice->id,
                    'order_id' => $order?->id,
                    'customer_id' => $invoice->customer_id,
                    'vehicle_id' => $vehicle?->id,
                    'driver_id' => $driver?->id,
                    'current_stage' => 'shipping',
                    'started_at' => now(),
                ]);
            } else {
                $delivery->update([
                    'vehicle_id' => $vehicle?->id ?? $delivery->vehicle_id,
                    'driver_id' => $driver?->id ?? $delivery->driver_id,
                    'current_stage' => $delivery->current_stage === 'cancelled' ? 'shipping' : $delivery->current_stage,
                    'started_at' => $delivery->started_at ?? now(),
                ]);
            }

            $this->logStage($delivery, 'shipping', $driver);

            return $delivery;
        });
    }

    public function advanceStage(
        Delivery $delivery,
        string $stage,
        ?User $user = null,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $notes = null,
        ?string $photoPath = null,
    ): Delivery {
        return DB::transaction(function () use ($delivery, $stage, $user, $latitude, $longitude, $notes, $photoPath) {
            $delivery->update(['current_stage' => $stage]);

            if ($stage === 'received') {
                $delivery->update(['completed_at' => now()]);
            }

            $this->logStage($delivery, $stage, $user, $latitude, $longitude, $notes, $photoPath);

            event(new DeliveryStageChanged($delivery, $stage, $user));

            return $delivery;
        });
    }

    private function logStage(
        Delivery $delivery,
        string $stage,
        ?User $user = null,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $notes = null,
        ?string $photoPath = null,
    ): void {
        DeliveryStageLog::create([
            'delivery_id' => $delivery->id,
            'stage' => $stage,
            'updated_by' => $user?->id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'notes' => $notes,
            'photo_path' => $photoPath,
            'created_at' => now(),
        ]);
    }

    public function getTimeline(Invoice|Order $model): Collection
    {
        $delivery = $model instanceof Invoice
            ? Delivery::where('invoice_id', $model->id)->first()
            : Delivery::where('order_id', $model->id)->first();

        if (!$delivery) {
            return collect();
        }

        return DeliveryStageLog::where('delivery_id', $delivery->id)
            ->orderBy('created_at')
            ->get()
            ->map(fn ($log) => TimelineEntry::fromModel($log));
    }

    public function cancel(Invoice $invoice, ?string $reason = null, ?User $user = null): ?Delivery
    {
        return DB::transaction(function () use ($invoice, $reason, $user) {
            $delivery = Delivery::where('invoice_id', $invoice->id)->first();

            if ($delivery) {
                $delivery->update([
                    'current_stage' => 'cancelled',
                    'completed_at' => now(),
                ]);

                $this->logStage($delivery, 'cancelled', $user, notes: $reason);

                event(new DeliveryCancelled($delivery, $reason));

                AuditLog::create([
                    'auditable_type' => Delivery::class,
                    'auditable_id' => $delivery->id,
                    'event' => 'cancelled',
                    'old_values' => $delivery->getOriginal(),
                    'new_values' => ['current_stage' => 'cancelled', 'completed_at' => now()],
                    'user_id' => $user?->id,
                    'user_type' => 'user',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            return $delivery;
        });
    }
}
