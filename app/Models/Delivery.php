<?php

namespace App\Models;

use App\Enums\DeliveryStage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Delivery extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * Matches the deliveries table. The previous list referenced columns that
     * do not exist (delivery_number, status, address, phone, notes,
     * scheduled_date, picked_up_at, delivered_at, failed_reason) and omitted
     * the ones that do, which silently discarded values on create().
     */
    protected $fillable = [
        'invoice_id',
        'order_id',
        'customer_id',
        'vehicle_id',
        'driver_id',
        'current_stage',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'current_stage' => DeliveryStage::class,
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $attributes = [
        'current_stage' => 'shipping',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function stageLogs()
    {
        return $this->hasMany(DeliveryStageLog::class);
    }

    /**
     * Whether the delivery can still move to the next stage.
     */
    public function canAdvance(): bool
    {
        if ($this->trashed()) {
            return false;
        }

        $stage = $this->current_stage instanceof DeliveryStage
            ? $this->current_stage
            : DeliveryStage::tryFrom((string) $this->current_stage);

        if ($stage === null) {
            return false;
        }

        return ! $stage->isClosed();
    }

    /**
     * The next stage in the shipping flow, or null when finished.
     */
    public function nextStage(): ?DeliveryStage
    {
        $order = [
            DeliveryStage::Shipping,
            DeliveryStage::OutForDelivery,
            DeliveryStage::Unloading,
            DeliveryStage::Received,
        ];

        $stage = $this->current_stage instanceof DeliveryStage
            ? $this->current_stage
            : DeliveryStage::tryFrom((string) $this->current_stage);

        if ($stage === null) {
            return null;
        }

        $index = array_search($stage, $order, true);

        if ($index === false || ! isset($order[$index + 1])) {
            return null;
        }

        return $order[$index + 1];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('current_stage', ['received', 'cancelled']);
    }
}
