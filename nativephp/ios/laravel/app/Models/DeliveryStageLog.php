<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryStageLog extends Model
{
    /**
     * The table only has created_at. A stage log is an append-only
     * record, so it is never updated.
     */
    public const UPDATED_AT = null;

    protected $fillable = [
        'delivery_id',
        'stage',
        'updated_by',
        'latitude',
        'longitude',
        'notes',
        'photo_path',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'created_at' => 'datetime',
    ];

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
